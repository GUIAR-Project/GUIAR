<?php

namespace mvc\Services;

use DateTimeImmutable;
use DateTimeZone;
use mvc\Models\EntregaViagem;
use mvc\Models\EtaModelo;
use mvc\Models\Pedido;

/**
 * Orquestra a estimativa de tempo de entrega:
 *   1. iniciarViagem()   – entregador começou a rota: grava a previsão do OSRM e devolve o tempo ajustado pelo modelo
 *   2. registrarChegada() – GPS detectou o entregador no destino
 *   3. concluirViagem()  – entregador tocou em "Concluir Entrega": calcula o tempo real, valida e retreina o modelo
 */
class EtaService
{
    public const FUSO = 'America/Sao_Paulo';

    /** Abaixo disso não há modelo: mostra o tempo do OSRM puro. */
    public const MIN_AMOSTRAS_MODELO = 10;
    /** Retreina a cada N novas viagens válidas. */
    public const RETREINAR_A_CADA = 5;

    /** Concluída sem chegada detectada e mais longe que isso do destino → dado não confiável. */
    private const DISTANCIA_MAX_CONCLUSAO_M = 300;
    private const DURACAO_MIN_REAL_S = 30;

    private $viagens;
    private $modelos;
    private $pedidos;

    public function __construct($pdo)
    {
        $this->viagens = new EntregaViagem($pdo);
        $this->modelos = new EtaModelo($pdo);
        $this->pedidos = new Pedido($pdo);
    }

    public static function agora(): string
    {
        return (new DateTimeImmutable('now', new DateTimeZone(self::FUSO)))->format('Y-m-d H:i:s');
    }

    /**
     * @return array dados da viagem para a tela do entregador
     * @throws \DomainException quando a requisição é inválida
     */
    public function iniciarViagem(int $idEntregador, int $idPedido, float $origemLat, float $origemLng, float $duracaoOsrm, float $distanciaOsrm): array
    {
        if ($duracaoOsrm < 1 || $duracaoOsrm > 6 * 3600 || $distanciaOsrm < 1 || $distanciaOsrm > 300000) {
            throw new \DomainException('Dados de rota fora do intervalo esperado.');
        }
        if (abs($origemLat) > 90 || abs($origemLng) > 180) {
            throw new \DomainException('Coordenadas de origem inválidas.');
        }

        $pedido = $this->pedidos->getById($idPedido);
        if (!$pedido || (int)$pedido['id_entregador'] !== $idEntregador) {
            throw new \DomainException('Pedido não encontrado para este entregador.');
        }
        if ($pedido['status'] === 'entregue') {
            throw new \DomainException('Este pedido já foi entregue.');
        }
        if ($pedido['latitude'] === null || $pedido['longitude'] === null) {
            throw new \DomainException('Pedido sem coordenadas de destino.');
        }

        // Rota retomada (recarregou a página, clicou de novo): mantém o horário de início original
        $existente = $this->viagens->buscarEmAndamento($idPedido, $idEntregador);
        if ($existente) {
            return $this->resposta($existente, $this->modeloEmUso());
        }

        $this->viagens->descartarAbertasSemChegada($idEntregador, $idPedido, 'Entregador iniciou outra rota antes de chegar');

        $agora = self::agora();
        $modelo = $this->modeloEmUso();
        $dadosViagem = [
            'iniciado_em' => $agora,
            'distancia_osrm_m' => $distanciaOsrm,
            'bairro' => $pedido['bairro'],
            'id_entregador' => $idEntregador,
        ];
        $fator = EtaRegressor::preverFator($modelo['parametros'] ?? null, $dadosViagem);

        $viagem = $dadosViagem + [
            'id_pedido' => $idPedido,
            'id_empresa' => $pedido['id_empresa'],
            'origem_lat' => $origemLat,
            'origem_lng' => $origemLng,
            'destino_lat' => (float)$pedido['latitude'],
            'destino_lng' => (float)$pedido['longitude'],
            'duracao_osrm_s' => $duracaoOsrm,
            'fator_previsto' => $fator,
            'duracao_prevista_s' => round($duracaoOsrm * $fator),
            'id_modelo' => $modelo['id_modelo'] ?? null,
        ];
        $viagem['id_viagem'] = $this->viagens->criar($viagem);

        return $this->resposta($viagem, $modelo);
    }

    /**
     * Rota otimizada: recebe a ordem de visita calculada pelo OSRM (serviço trip) e os trechos,
     * ajusta cada trecho pelo modelo (no horário em que ele deve acontecer) e grava o plano.
     *
     * @param array $trechos lista na ordem de visita: [id_pedido, duracao_osrm_s, distancia_osrm_m]
     * @return array plano com o horário previsto de cada parada
     */
    public function planejarRota(int $idEntregador, array $trechos): array
    {
        if (!$trechos || count($trechos) > 50) {
            throw new \DomainException('Informe de 1 a 50 paradas.');
        }

        $modelo = $this->modeloEmUso();
        $tempoNoLocal = $this->tempoNoLocal();
        $fuso = new DateTimeZone(self::FUSO);
        $cursor = time();
        $plano = [];
        $paradas = [];

        foreach ($trechos as $t) {
            $idPedido = (int)($t['id_pedido'] ?? 0);
            $osrm = (float)($t['duracao_osrm_s'] ?? -1);
            $dist = (float)($t['distancia_osrm_m'] ?? -1);
            if ($osrm < 0 || $osrm > 6 * 3600 || $dist < 0 || $dist > 300000) {
                throw new \DomainException('Trecho com dados fora do intervalo esperado.');
            }
            $pedido = $this->pedidos->getById($idPedido);
            if (!$pedido || (int)$pedido['id_entregador'] !== $idEntregador || $pedido['status'] === 'entregue') {
                throw new \DomainException("Pedido {$idPedido} não está pendente para este entregador.");
            }
            if (isset($plano[$idPedido])) {
                throw new \DomainException("Pedido {$idPedido} repetido na rota.");
            }

            $fator = EtaRegressor::preverFator($modelo['parametros'] ?? null, [
                'iniciado_em' => (new DateTimeImmutable('@' . $cursor))->setTimezone($fuso)->format('Y-m-d H:i:s'),
                'distancia_osrm_m' => max($dist, 1),
                'bairro' => $pedido['bairro'],
                'id_entregador' => $idEntregador,
            ]);
            $trecho = $osrm * $fator;
            $cursor += (int)round($trecho);
            $plano[$idPedido] = $trecho;
            $paradas[] = [
                'id_pedido' => $idPedido,
                'ordem' => count($paradas) + 1,
                'trecho_s' => (int)round($trecho),
                'fator' => $fator,
                'chega_em' => (new DateTimeImmutable('@' . $cursor))->setTimezone($fuso)->format('H:i'),
            ];
            $cursor += $tempoNoLocal;
        }

        $this->pedidos->salvarPlanoRota($idEntregador, $plano);

        return [
            'paradas' => $paradas,
            'tempo_no_local_s' => $tempoNoLocal,
            'fonte' => $modelo ? 'modelo' : 'osrm',
        ];
    }

    /** Tempo restante enviado ao vivo pelo celular do entregador (alimenta o rastreio e o mapa do gestor). */
    public function registrarProgresso(int $idEntregador, int $idPedido, float $etaRestante, ?float $distanciaRestante): bool
    {
        if ($etaRestante < 0 || $etaRestante > 6 * 3600) {
            throw new \DomainException('Tempo restante fora do intervalo esperado.');
        }
        $viagem = $this->viagens->buscarEmAndamento($idPedido, $idEntregador);
        if (!$viagem) {
            return false;
        }
        return $this->viagens->atualizarProgresso(
            (int)$viagem['id_viagem'],
            (int)round($etaRestante),
            $distanciaRestante !== null ? (int)round($distanciaRestante) : null,
            self::agora()
        );
    }

    /**
     * Previsão de chegada de cada pedido pendente do entregador.
     * @param array $pedidosPendentes linhas de Pedido::getAllByEntregador()
     * @return array id_pedido => previsão (ver PrevisaoFila::calcular) + chega_em_hora (H:i) + fonte
     */
    public function previsoesDoEntregador(int $idEntregador, array $pedidosPendentes): array
    {
        if (!$pedidosPendentes) {
            return [];
        }
        $viagem = $this->viagens->buscarAbertaDoEntregador($idEntregador) ?: null;
        $previsoes = PrevisaoFila::calcular($pedidosPendentes, $viagem, $this->tempoNoLocal(), time());

        $fuso = new DateTimeZone(self::FUSO);
        $fonte = ($viagem && !empty($viagem['id_modelo'])) ? 'modelo' : 'osrm';
        foreach ($previsoes as &$p) {
            $p['chega_em_hora'] = $p['chega_em'] !== null
                ? (new DateTimeImmutable('@' . $p['chega_em']))->setTimezone($fuso)->format('H:i')
                : null;
            $p['fonte'] = $fonte;
        }
        return $previsoes;
    }

    /**
     * Quanto tempo o entregador costuma ficar no endereço (chegada → conclusão).
     * Aprendido do histórico (mediana); 2 min enquanto houver menos de 5 registros.
     */
    public function tempoNoLocal(): int
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }
        $tempos = array_values(array_filter($this->viagens->temposNoLocal(), fn($s) => $s >= 10 && $s <= 1800));
        if (count($tempos) < 5) {
            return $cache = 120;
        }
        sort($tempos);
        $meio = intdiv(count($tempos), 2);
        $mediana = count($tempos) % 2 ? $tempos[$meio] : ($tempos[$meio - 1] + $tempos[$meio]) / 2;
        return $cache = (int)round($mediana);
    }

    public function registrarChegada(int $idEntregador, int $idPedido): bool
    {
        $viagem = $this->viagens->buscarEmAndamento($idPedido, $idEntregador);
        return $viagem ? $this->viagens->registrarChegada((int)$viagem['id_viagem'], self::agora()) : false;
    }

    /**
     * Chamado quando o pedido é marcado como entregue. Nunca lança exceção para não
     * atrapalhar a conclusão da entrega.
     *
     * @return array|null resumo do que aconteceu (null se não havia trajeto aberto)
     */
    public function concluirViagem(int $idPedido, ?float $lat = null, ?float $lng = null): ?array
    {
        $viagem = $this->viagens->buscarEmAndamentoPorPedido($idPedido);
        if (!$viagem) {
            return null;
        }

        $agora = self::agora();
        $fim = $viagem['chegou_em'] ?: $agora;
        $duracaoReal = strtotime($fim) - strtotime($viagem['iniciado_em']);

        $distanciaFinal = null;
        if ($lat !== null && $lng !== null) {
            $distanciaFinal = self::distanciaMetros($lat, $lng, (float)$viagem['destino_lat'], (float)$viagem['destino_lng']);
        }

        $motivo = null;
        if ($duracaoReal < self::DURACAO_MIN_REAL_S) {
            $motivo = 'Duração curta demais (' . $duracaoReal . ' s)';
        } elseif (!$viagem['chegou_em'] && $distanciaFinal !== null && $distanciaFinal > self::DISTANCIA_MAX_CONCLUSAO_M) {
            $motivo = sprintf('Concluída a %.0f m do destino, sem chegada detectada', $distanciaFinal);
        } elseif (!EtaRegressor::viagemValida([
            'iniciado_em' => $viagem['iniciado_em'],
            'duracao_osrm_s' => $viagem['duracao_osrm_s'],
            'duracao_real_s' => $duracaoReal,
        ])) {
            $motivo = sprintf('Tempo real %.1f× o do OSRM (fora do intervalo plausível)', $duracaoReal / (float)$viagem['duracao_osrm_s']);
        }

        $this->viagens->finalizar((int)$viagem['id_viagem'], [
            'concluido_em' => $agora,
            'distancia_final_m' => $distanciaFinal,
            'duracao_real_s' => $duracaoReal,
            'status' => $motivo ? 'descartada' : 'concluida',
            'motivo_descarte' => $motivo,
        ]);

        $treino = null;
        if (!$motivo) {
            try {
                $treino = $this->treinarSeNecessario();
            } catch (\Throwable $e) {
                error_log('Falha ao retreinar o modelo de tempo: ' . $e->getMessage());
            }
        }

        return [
            'id_viagem' => (int)$viagem['id_viagem'],
            'duracao_real_s' => $duracaoReal,
            'duracao_prevista_s' => (float)$viagem['duracao_prevista_s'],
            'descartada' => $motivo,
            'retreinou' => $treino !== null,
        ];
    }

    public function treinarSeNecessario(): ?array
    {
        $total = $this->viagens->contarPorStatus()['concluida'];
        $ativo = $this->modelos->ativo();
        $amostrasAtivo = $ativo ? (int)$ativo['amostras'] : 0;

        if ($total < self::MIN_AMOSTRAS_MODELO) {
            return null;
        }
        if ($ativo && $total - $amostrasAtivo < self::RETREINAR_A_CADA) {
            return null;
        }
        return $this->treinar();
    }

    /** Treina com todo o histórico válido, avalia em dados não vistos e salva uma nova versão. */
    public function treinar(): ?array
    {
        $historico = array_values(array_filter($this->viagens->listarParaTreino(), [EtaRegressor::class, 'viagemValida']));
        if (count($historico) < self::MIN_AMOSTRAS_MODELO) {
            return null;
        }

        $metricas = EtaRegressor::avaliar($historico);
        $parametros = EtaRegressor::treinar($historico);
        $id = $this->modelos->salvar($parametros, $metricas, self::agora());

        return ['id_modelo' => $id, 'amostras' => $parametros['amostras'], 'lambda' => $parametros['lambda'], 'metricas' => $metricas];
    }

    /** Situação atual do modelo e da coleta de dados (para o painel/CLI). */
    public function status(): array
    {
        $ativo = $this->modelos->ativo();
        $emUso = $this->modeloEmUso();
        return [
            'viagens' => $this->viagens->contarPorStatus(),
            'min_amostras_modelo' => self::MIN_AMOSTRAS_MODELO,
            'modelo' => $ativo ? [
                'id_modelo' => (int)$ativo['id_modelo'],
                'treinado_em' => $ativo['treinado_em'],
                'amostras' => (int)$ativo['amostras'],
                'lambda' => $ativo['parametros']['lambda'] ?? null,
                'amostras_teste' => $ativo['amostras_teste'] !== null ? (int)$ativo['amostras_teste'] : null,
                'mae_osrm_s' => $ativo['mae_osrm_s'] !== null ? (float)$ativo['mae_osrm_s'] : null,
                'mae_modelo_s' => $ativo['mae_modelo_s'] !== null ? (float)$ativo['mae_modelo_s'] : null,
                'mape_osrm' => $ativo['mape_osrm'] !== null ? (float)$ativo['mape_osrm'] : null,
                'mape_modelo' => $ativo['mape_modelo'] !== null ? (float)$ativo['mape_modelo'] : null,
                'pesos' => $ativo['parametros'] ? array_combine($ativo['parametros']['colunas'], $ativo['parametros']['pesos']) : [],
                'em_uso' => $emUso !== null,
            ] : null,
        ];
    }

    /**
     * Modelo que deve ser aplicado nas previsões, ou null para usar o OSRM puro.
     * Salvaguarda: só usa o modelo se, no conjunto de teste, ele errou menos que o OSRM.
     */
    private function modeloEmUso(): ?array
    {
        $ativo = $this->modelos->ativo();
        if (!$ativo || (int)$ativo['amostras'] < self::MIN_AMOSTRAS_MODELO) {
            return null;
        }
        if ($ativo['mae_modelo_s'] === null || (float)$ativo['mae_modelo_s'] >= (float)$ativo['mae_osrm_s']) {
            return null;
        }
        return $ativo;
    }

    private function resposta(array $viagem, ?array $modelo): array
    {
        // A fonte é a do momento em que a viagem começou (pode ser uma versão anterior do modelo)
        if (empty($viagem['id_modelo'])) {
            $modelo = null;
        } elseif (!$modelo || (int)$modelo['id_modelo'] !== (int)$viagem['id_modelo']) {
            $modelo = ['id_modelo' => $viagem['id_modelo'], 'amostras' => 0, 'mape_modelo' => null];
        }
        return [
            'id_viagem' => (int)$viagem['id_viagem'],
            'iniciado_em' => $viagem['iniciado_em'],
            'duracao_osrm_s' => (float)$viagem['duracao_osrm_s'],
            'fator' => (float)$viagem['fator_previsto'],
            'duracao_prevista_s' => (float)$viagem['duracao_prevista_s'],
            'fonte' => $modelo ? 'modelo' : 'osrm',
            'modelo' => $modelo ? [
                'id_modelo' => (int)$modelo['id_modelo'],
                'amostras' => (int)$modelo['amostras'],
                'mape_modelo' => $modelo['mape_modelo'] !== null ? (float)$modelo['mape_modelo'] : null,
            ] : null,
        ];
    }

    public static function distanciaMetros(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $r = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        return 2 * $r * atan2(sqrt($a), sqrt(1 - $a));
    }
}

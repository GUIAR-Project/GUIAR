<?php

namespace mvc\Services;

use mvc\Models\EntregaViagem;
use mvc\Models\EtaModelo;

/**
 * Indicadores do painel "Inteligência de entregas" de uma empresa.
 */
class InteligenciaService
{
    /** Chegou até 2 min depois do previsto = previsão cumprida. */
    public const TOLERANCIA_PONTUALIDADE_S = 120;

    private const NOMES_FAIXA = [
        'madrugada' => 'Madrugada (0h–6h)',
        'manha' => 'Manhã (6h–10h)',
        'almoco' => 'Almoço (10h–14h)',
        'tarde' => 'Tarde (14h–17h)',
        'pico_tarde' => 'Pico (17h–20h)',
        'noite' => 'Noite (20h–24h)',
    ];

    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function painel(int $idEmpresa): array
    {
        $viagens = new EntregaViagem($this->pdo);
        $eta = new EtaService($this->pdo);

        $dados = self::agregar(
            $viagens->listarConcluidasDaEmpresa($idEmpresa),
            $viagens->temposNoLocal(500, $idEmpresa)
        );
        $dados['contagem'] = $viagens->contarPorStatusDaEmpresa($idEmpresa);
        $dados['modelo'] = $eta->status()['modelo'];
        $dados['historico_modelo'] = array_map(fn($m) => [
            'versao' => (int)$m['id_modelo'],
            'treinado_em' => $m['treinado_em'],
            'amostras' => (int)$m['amostras'],
            'mape_osrm' => $m['mape_osrm'] !== null ? (float)$m['mape_osrm'] : null,
            'mape_modelo' => $m['mape_modelo'] !== null ? (float)$m['mape_modelo'] : null,
        ], (new EtaModelo($this->pdo))->historico());
        $dados['min_amostras_modelo'] = EtaService::MIN_AMOSTRAS_MODELO;
        return $dados;
    }

    /**
     * Agregações a partir das viagens concluídas (função pura, testável).
     * "Razão" = tempo real ÷ tempo do OSRM (1,30 = 30% mais lento que o OSRM).
     */
    public static function agregar(array $viagens, array $temposNoLocal): array
    {
        $n = count($viagens);
        $resumo = [
            'entregas' => $n,
            'tempo_medio_real_s' => null,
            'pontualidade' => null,
            'tempo_no_local_s' => null,
        ];

        $porFaixa = $porBairro = $porEntregador = [];
        $somaReal = 0.0;
        $noPrazo = 0;

        foreach ($viagens as $v) {
            $real = (float)$v['duracao_real_s'];
            $osrm = max((float)$v['duracao_osrm_s'], 1.0);
            $razao = $real / $osrm;
            $somaReal += $real;
            if ($real <= (float)$v['duracao_prevista_s'] + self::TOLERANCIA_PONTUALIDADE_S) {
                $noPrazo++;
            }

            $faixa = EtaRegressor::atributos($v)['faixa_horario'];
            self::acumular($porFaixa, $faixa, $razao, $real);

            $bairro = trim((string)$v['bairro']) !== '' ? trim($v['bairro']) : 'Sem bairro';
            self::acumular($porBairro, $bairro, $razao, $real);

            $nome = trim((string)($v['nome_entregador'] ?? '')) ?: ('Entregador #' . $v['id_entregador']);
            self::acumular($porEntregador, $nome, $razao, $real);
        }

        if ($n > 0) {
            $resumo['tempo_medio_real_s'] = $somaReal / $n;
            $resumo['pontualidade'] = $noPrazo / $n;
        }
        $tempos = array_values(array_filter($temposNoLocal, fn($s) => $s >= 10 && $s <= 1800));
        if ($tempos) {
            sort($tempos);
            $resumo['tempo_no_local_s'] = $tempos[intdiv(count($tempos), 2)];
        }

        // Faixas na ordem do dia (só as que têm dados)
        $faixas = [];
        foreach (self::NOMES_FAIXA as $chave => $nome) {
            if (isset($porFaixa[$chave])) {
                $faixas[] = ['chave' => $chave, 'nome' => $nome] + self::finalizar($porFaixa[$chave]);
            }
        }

        $bairros = [];
        foreach ($porBairro as $nome => $acc) {
            $bairros[] = ['nome' => $nome] + self::finalizar($acc);
        }
        usort($bairros, fn($a, $b) => $b['entregas'] <=> $a['entregas'] ?: $b['razao_media'] <=> $a['razao_media']);

        $entregadores = [];
        foreach ($porEntregador as $nome => $acc) {
            $entregadores[] = ['nome' => $nome] + self::finalizar($acc);
        }
        usort($entregadores, fn($a, $b) => $a['razao_media'] <=> $b['razao_media']);

        $recentes = array_map(fn($v) => [
            'quando' => $v['iniciado_em'],
            'bairro' => $v['bairro'],
            'entregador' => trim((string)($v['nome_entregador'] ?? '')) ?: ('#' . $v['id_entregador']),
            'osrm_s' => (float)$v['duracao_osrm_s'],
            'previsto_s' => (float)$v['duracao_prevista_s'],
            'real_s' => (int)$v['duracao_real_s'],
            'com_modelo' => !empty($v['id_modelo']),
        ], array_reverse(array_slice($viagens, -10)));

        return [
            'resumo' => $resumo,
            'faixas' => $faixas,
            'bairros' => array_slice($bairros, 0, 8),
            'entregadores' => $entregadores,
            'recentes' => $recentes,
        ];
    }

    private static function acumular(array &$grupo, string $chave, float $razao, float $real): void
    {
        if (!isset($grupo[$chave])) {
            $grupo[$chave] = ['n' => 0, 'soma_razao' => 0.0, 'soma_real' => 0.0];
        }
        $grupo[$chave]['n']++;
        $grupo[$chave]['soma_razao'] += $razao;
        $grupo[$chave]['soma_real'] += $real;
    }

    private static function finalizar(array $acc): array
    {
        return [
            'entregas' => $acc['n'],
            'razao_media' => $acc['soma_razao'] / $acc['n'],
            'tempo_medio_s' => $acc['soma_real'] / $acc['n'],
        ];
    }
}

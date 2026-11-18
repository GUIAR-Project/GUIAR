<?php

namespace mvc\Services;

/**
 * Calcula quando cada pedido da fila de um entregador deve chegar.
 *
 *  - A parada atual usa o tempo restante enviado ao vivo pelo celular do entregador
 *    (ou, sem ele, a previsão feita no início do trajeto menos o tempo já decorrido).
 *  - As paradas seguintes somam: chegada da anterior + tempo médio no local + trecho previsto
 *    (trechos calculados na rota otimizada, já ajustados pelo modelo de machine learning).
 *
 * Classe pura (sem banco), para poder ser testada isoladamente.
 */
class PrevisaoFila
{
    /**
     * @param array      $pedidos      pedidos pendentes do entregador (id_pedido, rota_ordem, rota_trecho_s)
     * @param array|null $viagem       trajeto em andamento (id_pedido, iniciado_em, duracao_prevista_s,
     *                                 eta_restante_s, eta_atualizado_em, chegou_em)
     * @param int        $tempoNoLocal segundos que o entregador costuma ficar em cada endereço
     * @param int        $agora        timestamp atual
     * @return array id_pedido => [tipo, ordem, entregas_antes, chega_em (timestamp|null), restante_s (int|null), sinal_ha_s (int|null)]
     *               tipo: 'a_caminho' | 'chegou' | 'fila' | 'aguardando'
     */
    public static function calcular(array $pedidos, ?array $viagem, int $tempoNoLocal, int $agora): array
    {
        $atualId = $viagem ? (int)$viagem['id_pedido'] : null;
        $ordenados = self::ordenar($pedidos, $atualId);
        $temAtual = $atualId !== null && $ordenados && (int)$ordenados[0]['id_pedido'] === $atualId;

        $resultado = [];

        if (!$temAtual) {
            // Entregador ainda não começou a rota: só a ordem planejada é conhecida
            foreach ($ordenados as $i => $p) {
                $resultado[(int)$p['id_pedido']] = self::item('aguardando', $i, null, null, null);
            }
            return $resultado;
        }

        // Parada atual
        $sinalHa = null;
        if (!empty($viagem['chegou_em'])) {
            $chegouTs = self::ts($viagem['chegou_em']);
            $resultado[$atualId] = self::item('chegou', 0, $chegouTs, 0, null);
            $cursor = max($agora, $chegouTs + $tempoNoLocal);
        } else {
            if ($viagem['eta_restante_s'] !== null && !empty($viagem['eta_atualizado_em'])) {
                $sinalHa = max(0, $agora - self::ts($viagem['eta_atualizado_em']));
                $restante = (int)$viagem['eta_restante_s'] - $sinalHa;
            } else {
                $restante = (int)round((float)$viagem['duracao_prevista_s']) - ($agora - self::ts($viagem['iniciado_em']));
            }
            $restante = max(0, $restante);
            $chegaEm = $agora + $restante;
            $resultado[$atualId] = self::item('a_caminho', 0, $chegaEm, $restante, $sinalHa);
            $cursor = $chegaEm + $tempoNoLocal;
        }

        // Paradas seguintes
        $cadeiaQuebrada = false;
        for ($i = 1; $i < count($ordenados); $i++) {
            $p = $ordenados[$i];
            $chegaEm = null;
            if (!$cadeiaQuebrada && $p['rota_trecho_s'] !== null) {
                $cursor += (int)$p['rota_trecho_s'];
                $chegaEm = $cursor;
                $cursor += $tempoNoLocal;
            } else {
                $cadeiaQuebrada = true; // sem trecho planejado, não dá para prever as seguintes
            }
            $resultado[(int)$p['id_pedido']] = self::item(
                'fila',
                $i,
                $chegaEm,
                $chegaEm !== null ? $chegaEm - $agora : null,
                null
            );
        }

        return $resultado;
    }

    /** Parada atual primeiro; depois a ordem da rota otimizada; sem ordem vão para o fim. */
    public static function ordenar(array $pedidos, ?int $atualId): array
    {
        usort($pedidos, function ($a, $b) use ($atualId) {
            $aAtual = (int)$a['id_pedido'] === $atualId;
            $bAtual = (int)$b['id_pedido'] === $atualId;
            if ($aAtual !== $bAtual) {
                return $aAtual ? -1 : 1;
            }
            $oa = $a['rota_ordem'] ?? null;
            $ob = $b['rota_ordem'] ?? null;
            if ($oa === null && $ob !== null) return 1;
            if ($ob === null && $oa !== null) return -1;
            if ($oa !== $ob) return (int)$oa <=> (int)$ob;
            return (int)$a['id_pedido'] <=> (int)$b['id_pedido'];
        });
        return array_values($pedidos);
    }

    /**
     * Os horários do banco são gravados no fuso de São Paulo (EtaService::agora()).
     * Converte explicitamente nesse fuso: o fuso padrão do PHP costuma ser UTC.
     */
    private static function ts(string $dataHora): int
    {
        return (new \DateTimeImmutable($dataHora, new \DateTimeZone('America/Sao_Paulo')))->getTimestamp();
    }

    private static function item(string $tipo, int $posicao, ?int $chegaEm, ?int $restante, ?int $sinalHa): array
    {
        return [
            'tipo' => $tipo,
            'ordem' => $posicao + 1,
            'entregas_antes' => $posicao,
            'chega_em' => $chegaEm,
            'restante_s' => $restante,
            'sinal_ha_s' => $sinalHa,
        ];
    }
}

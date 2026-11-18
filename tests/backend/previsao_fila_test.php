<?php

declare(strict_types=1);

/**
 * Teste da previsão de chegada da fila de um entregador (PrevisaoFila), sem banco.
 *
 * Uso: php tests/backend/previsao_fila_test.php
 */

// Fuso padrão UTC de propósito: o servidor costuma estar assim e os horários do banco são de São Paulo
date_default_timezone_set('UTC');
require_once __DIR__ . '/../../mvc/Services/PrevisaoFila.php';

use mvc\Services\PrevisaoFila;

function check(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "Falha: {$message}\n");
        exit(1);
    }
    fwrite(STDOUT, "  ok  {$message}\n");
}

fwrite(STDOUT, "Previsão da fila de entregas (PrevisaoFila)\n");

function sp(string $dataHora): int
{
    return (new DateTimeImmutable($dataHora, new DateTimeZone('America/Sao_Paulo')))->getTimestamp();
}

$agora = sp('2026-11-18 14:00:00');
$local = 120; // 2 min no endereço
$pedidos = [
    ['id_pedido' => 30, 'rota_ordem' => 3, 'rota_trecho_s' => 600],
    ['id_pedido' => 10, 'rota_ordem' => 1, 'rota_trecho_s' => 300],
    ['id_pedido' => 20, 'rota_ordem' => 2, 'rota_trecho_s' => 480],
];

// 1) Sem trajeto aberto: só a ordem planejada
$r = PrevisaoFila::calcular($pedidos, null, $local, $agora);
check($r[10]['tipo'] === 'aguardando' && $r[10]['ordem'] === 1 && $r[30]['ordem'] === 3, 'sem rota iniciada: segue a ordem planejada, sem horário');
check($r[20]['chega_em'] === null, 'sem rota iniciada: não inventa horário');

// 2) A caminho, com tempo restante enviado há 30 s pelo celular
$viagem = [
    'id_pedido' => 10, 'iniciado_em' => '2026-11-18 13:55:00', 'duracao_prevista_s' => 600,
    'eta_restante_s' => 330, 'eta_atualizado_em' => '2026-11-18 13:59:30', 'chegou_em' => null,
];
$r = PrevisaoFila::calcular($pedidos, $viagem, $local, $agora);
check($r[10]['tipo'] === 'a_caminho' && $r[10]['restante_s'] === 300, 'parada atual: 330 s informados há 30 s → faltam 300 s');
check($r[10]['sinal_ha_s'] === 30, 'parada atual: informa a idade do sinal (30 s)');
check($r[20]['tipo'] === 'fila' && $r[20]['entregas_antes'] === 1, 'segunda parada: 1 entrega antes');
check($r[20]['chega_em'] === $agora + 300 + 120 + 480, 'segunda parada: chegada atual + 2 min no local + trecho (14:15)');
check($r[30]['chega_em'] === $agora + 300 + 120 + 480 + 120 + 600, 'terceira parada: soma mais um local + trecho (14:27)');

// 3) Sem sinal ao vivo: usa a previsão do início menos o tempo decorrido
$semSinal = ['eta_restante_s' => null, 'eta_atualizado_em' => null] + $viagem;
$r = PrevisaoFila::calcular($pedidos, $semSinal, $local, $agora);
check($r[10]['restante_s'] === 300, 'sem sinal ao vivo: 600 s previstos − 300 s decorridos');

// 4) Atrasado: nunca mostra tempo negativo
$atrasado = ['eta_restante_s' => 60, 'eta_atualizado_em' => '2026-11-18 13:50:00'] + $viagem;
$r = PrevisaoFila::calcular($pedidos, $atrasado, $local, $agora);
check($r[10]['restante_s'] === 0, 'previsão vencida vira 0 (chegando), nunca negativa');

// 5) Chegou: a próxima parada parte depois do tempo no local
$chegou = ['chegou_em' => '2026-11-18 13:59:00'] + $viagem;
$r = PrevisaoFila::calcular($pedidos, $chegou, $local, $agora);
check($r[10]['tipo'] === 'chegou', 'entregador no endereço: tipo chegou');
check($r[20]['chega_em'] === sp('2026-11-18 13:59:00') + 120 + 480, 'próxima parada sai após 2 min no local (13:59 + 2 + 8 min)');

// 6) Parada atual fora da ordem planejada vai para o topo
$foraDeOrdem = ['id_pedido' => 20] + $viagem;
$r = PrevisaoFila::calcular($pedidos, $foraDeOrdem, $local, $agora);
check($r[20]['ordem'] === 1 && $r[10]['ordem'] === 2 && $r[30]['ordem'] === 3, 'parada atual sempre em 1º; as demais seguem a ordem planejada');

// 7) Sem trecho planejado: para de prever dali em diante
$semPlano = [
    ['id_pedido' => 10, 'rota_ordem' => 1, 'rota_trecho_s' => 300],
    ['id_pedido' => 20, 'rota_ordem' => null, 'rota_trecho_s' => null],
    ['id_pedido' => 30, 'rota_ordem' => null, 'rota_trecho_s' => 600],
];
$r = PrevisaoFila::calcular($semPlano, $viagem, $local, $agora);
check($r[20]['chega_em'] === null && $r[30]['chega_em'] === null, 'sem trecho planejado: não prevê as paradas seguintes');
check($r[30]['entregas_antes'] === 2, '...mas ainda informa quantas entregas há antes');

fwrite(STDOUT, "Testes da PrevisaoFila concluídos.\n");

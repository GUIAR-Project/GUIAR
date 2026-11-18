<?php

declare(strict_types=1);

/**
 * Teste das agregações do painel "Inteligência de entregas", sem banco.
 *
 * Uso: php tests/backend/inteligencia_test.php
 */

date_default_timezone_set('America/Sao_Paulo');
require_once __DIR__ . '/../../mvc/Services/EtaRegressor.php';
require_once __DIR__ . '/../../mvc/Services/InteligenciaService.php';

use mvc\Services\InteligenciaService;

function check(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "Falha: {$message}\n");
        exit(1);
    }
    fwrite(STDOUT, "  ok  {$message}\n");
}

function v(string $quando, string $bairro, int $entregador, string $nome, float $osrm, float $previsto, int $real): array
{
    return [
        'iniciado_em' => $quando, 'bairro' => $bairro, 'id_entregador' => $entregador, 'nome_entregador' => $nome,
        'distancia_osrm_m' => 3000, 'duracao_osrm_s' => $osrm, 'duracao_prevista_s' => $previsto, 'duracao_real_s' => $real,
        'id_modelo' => null,
    ];
}

fwrite(STDOUT, "Painel de inteligência (InteligenciaService::agregar)\n");

$vazio = InteligenciaService::agregar([], []);
check($vazio['resumo']['entregas'] === 0 && $vazio['resumo']['pontualidade'] === null, 'sem entregas: indicadores vazios, sem divisão por zero');

$viagens = [
    v('2026-11-10 09:10:00', 'Centro', 1, 'Ana Souza', 300, 330, 360),      // razão 1,2 · no prazo (360 ≤ 330+120)
    v('2026-11-10 18:05:00', 'Centro', 2, 'Bruno Lima', 300, 330, 600),     // razão 2,0 · atrasado
    v('2026-11-11 18:30:00', 'Santa Fé', 1, 'Ana Souza', 400, 440, 400),    // razão 1,0 · no prazo
    v('2026-11-11 12:00:00', 'Santa Fé', 2, 'Bruno Lima', 200, 220, 300),   // razão 1,5 · no prazo
];
$r = InteligenciaService::agregar($viagens, [60, 90, 120, 5, 3000]);

check($r['resumo']['entregas'] === 4, '4 entregas contadas');
check(abs($r['resumo']['pontualidade'] - 0.75) < 1e-9, 'pontualidade: 3 de 4 chegaram até 2 min após o previsto');
check(abs($r['resumo']['tempo_medio_real_s'] - 415) < 1e-9, 'tempo médio real = 415 s');
check($r['resumo']['tempo_no_local_s'] === 90, 'tempo no local: mediana de 60/90/120 (ignora 5 s e 50 min)');

$faixas = array_column($r['faixas'], null, 'chave');
check(array_keys($faixas) === ['manha', 'almoco', 'pico_tarde'], 'faixas na ordem do dia, só as que têm dados');
check(abs($faixas['pico_tarde']['razao_media'] - 1.5) < 1e-9, 'pico: média das razões (2,0 e 1,0) = 1,5');

check($r['bairros'][0]['nome'] === 'Centro' && abs($r['bairros'][0]['razao_media'] - 1.6) < 1e-9, 'bairros: Centro com razão média 1,6 aparece primeiro (empate em volume, mais lento)');
check($r['entregadores'][0]['nome'] === 'Ana Souza' && abs($r['entregadores'][0]['razao_media'] - 1.1) < 1e-9, 'entregadores: mais rápido primeiro (Ana, 1,1)');
check(count($r['recentes']) === 4 && $r['recentes'][0]['quando'] === '2026-11-11 12:00:00', 'recentes: mais nova primeiro');

fwrite(STDOUT, "Testes do painel de inteligência concluídos.\n");

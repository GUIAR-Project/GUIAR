<?php

declare(strict_types=1);

/**
 * Teste do modelo de estimativa de tempo (EtaRegressor), sem banco de dados.
 *
 * Gera entregas sintéticas com um padrão conhecido e verifica se o modelo
 * aprende o padrão e erra menos que o OSRM puro em dados que não viu.
 *
 * Uso: php tests/backend/eta_regressor_test.php
 */

date_default_timezone_set('America/Sao_Paulo');
require_once __DIR__ . '/../../mvc/Services/EtaRegressor.php';

use mvc\Services\EtaRegressor;

function check(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "Falha: {$message}\n");
        exit(1);
    }
    fwrite(STDOUT, "  ok  {$message}\n");
}

/** Ruído gaussiano (Box-Muller). */
function ruido(float $desvio): float
{
    $u1 = max(mt_rand() / mt_getrandmax(), 1e-12);
    $u2 = mt_rand() / mt_getrandmax();
    return $desvio * sqrt(-2 * log($u1)) * cos(2 * M_PI * $u2);
}

/**
 * Padrão "verdadeiro" usado para gerar os dados:
 *   pico da tarde (17h-20h)  → 1,6× o OSRM
 *   almoço (10h-14h)         → 1,25×
 *   demais horários          → 1,1×
 *   bairro Centro            → +20%
 *   entregador 99            → 15% mais rápido
 */
function fatorVerdadeiro(int $hora, string $bairro, int $entregador): float
{
    $f = 1.1;
    if ($hora >= 17 && $hora < 20) {
        $f = 1.6;
    } elseif ($hora >= 10 && $hora < 14) {
        $f = 1.25;
    }
    if ($bairro === 'Centro') {
        $f *= 1.2;
    }
    if ($entregador === 99) {
        $f *= 0.85;
    }
    return $f;
}

function gerarViagens(int $n, int $seed): array
{
    mt_srand($seed);
    $bairros = ['Centro', 'Vila São Carlos', 'Jardim Alto dos Ypês', 'Santa Fé'];
    $entregadores = [20, 22, 23, 99];
    $inicio = strtotime('2026-09-01 08:00:00');
    $viagens = [];
    for ($i = 0; $i < $n; $i++) {
        $hora = [8, 9, 11, 12, 13, 15, 16, 17, 18, 19, 21][mt_rand(0, 10)];
        $dia = $inicio + intdiv($i, 8) * 86400;
        $ts = strtotime(date('Y-m-d', $dia) . sprintf(' %02d:%02d:00', $hora, mt_rand(0, 59)));
        $bairro = $bairros[mt_rand(0, 3)];
        $entregador = $entregadores[mt_rand(0, 3)];
        $distancia = mt_rand(600, 9000);
        $osrm = $distancia / 9.0; // ~32 km/h
        $real = $osrm * fatorVerdadeiro((int)date('G', $ts), $bairro, $entregador) * exp(ruido(0.12));
        $viagens[] = [
            'iniciado_em' => date('Y-m-d H:i:s', $ts),
            'distancia_osrm_m' => $distancia,
            'duracao_osrm_s' => $osrm,
            'duracao_real_s' => $real,
            'bairro' => $bairro,
            'id_entregador' => $entregador,
        ];
    }
    return $viagens;
}

fwrite(STDOUT, "Modelo de estimativa de tempo (EtaRegressor)\n");

// 1) Sem modelo treinado → usa OSRM puro
$exemplo = ['iniciado_em' => '2026-10-05 18:30:00', 'distancia_osrm_m' => 3000, 'bairro' => 'Centro', 'id_entregador' => 20];
check(EtaRegressor::preverFator(null, $exemplo) === 1.0, 'sem modelo, o fator é 1,0 (OSRM puro)');

// 2) Atributos de horário/dia
$a = EtaRegressor::atributos(['iniciado_em' => '2026-11-15 18:10:00', 'distancia_osrm_m' => 2000, 'bairro' => ' Jardim  Suécia ', 'id_entregador' => 7]);
check($a['faixa_horario'] === 'pico_tarde' && $a['dia'] === 'domingo', 'domingo 18h10 cai em pico_tarde/domingo');
check($a['bairro'] === 'jardim suecia', 'bairro é normalizado (minúsculas, sem acento, espaços)');

// 3) Viagens absurdas são descartadas
check(!EtaRegressor::viagemValida(['iniciado_em' => '2026-10-01 10:00:00', 'duracao_osrm_s' => 300, 'duracao_real_s' => 3000]), 'viagem 10× mais lenta que o OSRM é descartada');
check(EtaRegressor::viagemValida(['iniciado_em' => '2026-10-01 10:00:00', 'duracao_osrm_s' => 300, 'duracao_real_s' => 420]), 'viagem 1,4× o OSRM é aceita');

// 4) Aprende o padrão
$viagens = gerarViagens(400, 7);
$params = EtaRegressor::treinar($viagens);
check(isset($params['validacao_cruzada']), 'com 400 amostras o lambda é escolhido por validação cruzada (λ=' . $params['lambda'] . ')');

$fPico = EtaRegressor::preverFator($params, ['iniciado_em' => '2026-10-06 18:00:00', 'distancia_osrm_m' => 3000, 'bairro' => 'Santa Fé', 'id_entregador' => 22]);
$fManha = EtaRegressor::preverFator($params, ['iniciado_em' => '2026-10-06 09:00:00', 'distancia_osrm_m' => 3000, 'bairro' => 'Santa Fé', 'id_entregador' => 22]);
$fCentroPico = EtaRegressor::preverFator($params, ['iniciado_em' => '2026-10-06 18:00:00', 'distancia_osrm_m' => 3000, 'bairro' => 'Centro', 'id_entregador' => 22]);
$fRapido = EtaRegressor::preverFator($params, ['iniciado_em' => '2026-10-06 09:00:00', 'distancia_osrm_m' => 3000, 'bairro' => 'Santa Fé', 'id_entregador' => 99]);

check(abs($fPico - 1.6) < 0.12, sprintf('fator no pico ≈ 1,60 (aprendido: %.2f)', $fPico));
check(abs($fManha - 1.1) < 0.10, sprintf('fator de manhã ≈ 1,10 (aprendido: %.2f)', $fManha));
check(abs($fCentroPico / $fPico - 1.2) < 0.08, sprintf('Centro ≈ +20%% (aprendido: %+.0f%%)', ($fCentroPico / $fPico - 1) * 100));
check(abs($fRapido / $fManha - 0.85) < 0.07, sprintf('entregador 99 ≈ 15%% mais rápido (aprendido: %.0f%%)', (1 - $fRapido / $fManha) * 100));

// 5) Em dados que não viu, erra menos que o OSRM puro
$m = EtaRegressor::avaliar($viagens);
check($m !== null && $m['amostras_teste'] === 80, 'avaliação cronológica separa 20% das viagens para teste');
check($m['mae_modelo_s'] < $m['mae_osrm_s'] * 0.5, sprintf(
    'erro médio cai de %.0f s (OSRM) para %.0f s (modelo) — MAPE %.0f%% → %.0f%%',
    $m['mae_osrm_s'], $m['mae_modelo_s'], $m['mape_osrm'] * 100, $m['mape_modelo'] * 100
));

// 6) Poucos dados: comportamento estável (sem explodir), puxa para o fator médio observado
$poucas = array_slice(gerarViagens(6, 3), 0, 6);
$pp = EtaRegressor::treinar($poucas);
$f = EtaRegressor::preverFator($pp, $exemplo);
check($f >= 0.9 && $f <= 2.2, sprintf('com só 6 viagens o fator fica em faixa plausível (%.2f)', $f));

// 7) Parâmetros sobrevivem a JSON (como são guardados no banco)
$restaurado = json_decode(json_encode($params), true);
check(abs(EtaRegressor::preverFator($restaurado, $exemplo) - EtaRegressor::preverFator($params, $exemplo)) < 1e-9, 'parâmetros salvos em JSON dão a mesma previsão');

fwrite(STDOUT, "Testes do EtaRegressor concluídos.\n");

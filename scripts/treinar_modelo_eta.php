<?php

/**
 * Treina o modelo de estimativa de tempo com todo o histórico e mostra as métricas.
 *
 * Uso:
 *   php scripts/treinar_modelo_eta.php           # treina e mostra o resultado
 *   php scripts/treinar_modelo_eta.php --status  # só mostra a situação atual
 *
 * O modelo também é retreinado automaticamente a cada 5 entregas concluídas;
 * este script serve para forçar um treino (ex.: cron diário) e para inspecionar o modelo.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../mvc/Models/Pedido.php';
require_once __DIR__ . '/../mvc/Models/EntregaViagem.php';
require_once __DIR__ . '/../mvc/Models/EtaModelo.php';
require_once __DIR__ . '/../mvc/Services/EtaRegressor.php';
require_once __DIR__ . '/../mvc/Services/EtaService.php';

use mvc\Services\EtaService;

if (!isset($pdo)) {
    fwrite(STDERR, "Sem conexão com o banco.\n");
    exit(1);
}

$service = new EtaService($pdo);

if (!in_array('--status', $argv, true)) {
    $resultado = $service->treinar();
    if ($resultado === null) {
        echo "Ainda não há entregas válidas suficientes para treinar (mínimo: " . EtaService::MIN_AMOSTRAS_MODELO . ").\n\n";
    } else {
        echo "Novo modelo treinado: versão {$resultado['id_modelo']} com {$resultado['amostras']} entregas (λ = {$resultado['lambda']}).\n\n";
    }
}

$s = $service->status();
$v = $s['viagens'];
echo "Coleta de dados\n";
echo "  concluídas (usadas no treino): {$v['concluida']}\n";
echo "  descartadas (dados inválidos): {$v['descartada']}\n";
echo "  em andamento:                  {$v['em_andamento']}\n\n";

if (!$s['modelo']) {
    echo "Nenhum modelo treinado ainda: o sistema mostra o tempo do OSRM puro.\n";
    exit(0);
}

$m = $s['modelo'];
echo "Modelo ativo: versão {$m['id_modelo']} (treinado em {$m['treinado_em']}, {$m['amostras']} entregas)\n";
echo "  em uso nas previsões: " . ($m['em_uso'] ? 'sim' : 'não (não superou o OSRM no teste — usando OSRM puro)') . "\n";
if ($m['mae_modelo_s'] !== null) {
    printf("  teste em %d entregas mais recentes, nunca vistas no treino:\n", $m['amostras_teste']);
    printf("    erro médio OSRM puro: %5.0f s  (%4.1f%%)\n", $m['mae_osrm_s'], $m['mape_osrm'] * 100);
    printf("    erro médio do modelo: %5.0f s  (%4.1f%%)\n", $m['mae_modelo_s'], $m['mape_modelo'] * 100);
}
echo "\n  efeito de cada atributo sobre o tempo do OSRM:\n";
foreach ($m['pesos'] as $coluna => $peso) {
    if ($coluna === 'intercepto') {
        printf("    %-32s ×%.2f\n", 'fator base', exp($peso));
    } elseif ($coluna === 'log_dist_km') {
        printf("    %-32s %+.3f (elasticidade por log(km))\n", 'distância', $peso);
    } else {
        printf("    %-32s %+.0f%%\n", $coluna, (exp($peso) - 1) * 100);
    }
}

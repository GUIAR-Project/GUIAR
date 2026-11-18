<?php

/**
 * Seed de DEMONSTRAÇÃO da tela "Inteligência de entregas".
 *
 * Simula algumas semanas de entregas realistas (picos de almoço e jantar, bairros mais lentos,
 * entregadores com ritmos diferentes) e treina o modelo semana a semana, como aconteceria no
 * uso real. Assim o gráfico de evolução, a pontualidade e as previsões ficam coerentes.
 *
 * Uso:
 *   php scripts/seed_demo_inteligencia.php                 # todas as empresas com entregadores
 *   php scripts/seed_demo_inteligencia.php --empresa=21    # só uma empresa
 *   php scripts/seed_demo_inteligencia.php --dias=21 --por-dia=10
 *   php scripts/seed_demo_inteligencia.php --remover       # apaga a demonstração e retreina só com dados reais
 *
 * ATENÇÃO: o modelo de previsão é um só para todas as lojas. Enquanto a demonstração estiver no
 * banco, as previsões reais também usam o modelo treinado com esses dados. Remova depois de apresentar.
 *
 * As entregas de demonstração usam id_pedido a partir de 900000000 (não existem na tabela pedido).
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

use mvc\Models\EtaModelo;
use mvc\Services\EtaRegressor;
use mvc\Services\EtaService;

const ID_DEMO = 900000000;

$opcoes = getopt('', ['empresa:', 'dias:', 'por-dia:', 'remover']);
$fuso = new DateTimeZone(EtaService::FUSO);

if (!isset($pdo)) {
    fwrite(STDERR, "Sem conexão com o banco.\n");
    exit(1);
}

// ---------------------------------------------------------------------------
// Remover
// ---------------------------------------------------------------------------
function removerDemo(PDO $pdo): int
{
    $stmt = $pdo->prepare("DELETE FROM entrega_viagem WHERE id_pedido >= :id");
    $stmt->execute([':id' => ID_DEMO]);
    // Modelos são derivados dos dados: com a demonstração fora, são refeitos só com dados reais
    $pdo->exec("DELETE FROM eta_modelo");
    $pdo->exec("ALTER TABLE eta_modelo AUTO_INCREMENT = 1"); // versões recomeçam em v1
    return $stmt->rowCount();
}

if (isset($opcoes['remover'])) {
    $n = removerDemo($pdo);
    echo "Demonstração removida: {$n} entregas simuladas apagadas e modelos descartados.\n";
    $r = (new EtaService($pdo))->treinar();
    echo $r
        ? "Modelo retreinado só com dados reais: versão {$r['id_modelo']} ({$r['amostras']} entregas).\n"
        : "Ainda não há entregas reais suficientes para um modelo (mínimo " . EtaService::MIN_AMOSTRAS_MODELO . "): a previsão volta a usar o OSRM puro.\n";
    exit(0);
}

// ---------------------------------------------------------------------------
// Parâmetros
// ---------------------------------------------------------------------------
$dias = max(7, min(90, (int)($opcoes['dias'] ?? 28)));
$porDia = max(2, min(40, (int)($opcoes['por-dia'] ?? 8)));

$sql = "SELECT e.id_empresa, e.nome_empresa FROM empresa e
        WHERE EXISTS (SELECT 1 FROM entregador en WHERE en.FK_EMPRESA_id_empresa = e.id_empresa)";
$params = [];
if (isset($opcoes['empresa'])) {
    $sql .= " AND e.id_empresa = :id";
    $params[':id'] = (int)$opcoes['empresa'];
}
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$empresas = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (!$empresas) {
    fwrite(STDERR, "Nenhuma empresa com entregadores cadastrados" . (isset($opcoes['empresa']) ? " com id {$opcoes['empresa']}" : '') . ".\n");
    exit(1);
}

// ---------------------------------------------------------------------------
// Geração (determinística: a mesma demonstração a cada execução)
// ---------------------------------------------------------------------------
mt_srand(2026);

function aleatorio(float $min, float $max): float
{
    return $min + (mt_rand() / mt_getrandmax()) * ($max - $min);
}

function gauss(float $desvio): float
{
    $u1 = max(mt_rand() / mt_getrandmax(), 1e-12);
    $u2 = mt_rand() / mt_getrandmax();
    return $desvio * sqrt(-2 * log($u1)) * cos(2 * M_PI * $u2);
}

/** Hora do pedido com picos de almoço e jantar. */
function horaDoPedido(): float
{
    $faixas = [[8, 10, 1], [10, 11, 2], [11, 14, 6], [14, 17, 2], [17, 18, 3], [18, 21, 7], [21, 23, 3]];
    $total = array_sum(array_column($faixas, 2));
    $sorteio = aleatorio(0, $total);
    foreach ($faixas as [$ini, $fim, $peso]) {
        if ($sorteio <= $peso) {
            return aleatorio($ini, $fim);
        }
        $sorteio -= $peso;
    }
    return 19.0;
}

/** Efeito do horário sobre o tempo do OSRM (trânsito real). */
function efeitoHorario(float $hora, int $diaSemana): float
{
    $f = 1.0;
    if ($hora >= 17 && $hora < 20) {
        $f = 1.45;
    } elseif ($hora >= 11 && $hora < 14) {
        $f = 1.22;
    } elseif ($hora >= 20) {
        $f = 1.05;
    } elseif ($hora < 10) {
        $f = 1.12;
    }
    if ($diaSemana >= 6) {
        $f *= 0.92; // fim de semana: trânsito mais leve
    }
    return $f;
}

$bairrosPadrao = ['Centro', 'Vila São Carlos', 'Jardim Alto dos Ypês', 'Santa Fé', 'Jardim Santo Antônio', 'Vila Bazani'];
$agora = time();
$inicio = $agora - $dias * 86400;

removerDemo($pdo); // idempotente: refaz do zero

$insert = $pdo->prepare(
    "INSERT INTO entrega_viagem
        (id_pedido, id_entregador, id_empresa, bairro, origem_lat, origem_lng, destino_lat, destino_lng,
         distancia_osrm_m, duracao_osrm_s, fator_previsto, duracao_prevista_s, id_modelo,
         iniciado_em, chegou_em, concluido_em, distancia_final_m, duracao_real_s, status)
     VALUES
        (:id_pedido, :id_entregador, :id_empresa, :bairro, -22.3674, -46.9428, :dlat, :dlng,
         :dist, :osrm, :fator, :prevista, :id_modelo,
         :inicio, :chegou, :concluido, :dist_final, :real, 'concluida')"
);

// Monta todas as entregas (de todas as empresas) e ordena no tempo
$entregas = [];
$seq = 0;
foreach ($empresas as $emp) {
    $idEmpresa = (int)$emp['id_empresa'];

    $st = $pdo->prepare("SELECT id_entregador, nome_completo FROM entregador WHERE FK_EMPRESA_id_empresa = :e");
    $st->execute([':e' => $idEmpresa]);
    $entregadores = $st->fetchAll(PDO::FETCH_ASSOC);

    $st = $pdo->prepare("SELECT DISTINCT bairro FROM pedido WHERE id_empresa = :e AND bairro IS NOT NULL AND TRIM(bairro) <> ''");
    $st->execute([':e' => $idEmpresa]);
    $bairros = array_values(array_unique(array_merge($st->fetchAll(PDO::FETCH_COLUMN), $bairrosPadrao)));
    $bairros = array_slice($bairros, 0, 7);

    // Cada bairro e cada entregador tem um "jeito" próprio — é isso que o modelo deve descobrir
    $efeitoBairro = [];
    foreach ($bairros as $i => $b) {
        $efeitoBairro[$b] = $i === 0 ? 1.18 : aleatorio(0.94, 1.15);
    }
    $ritmo = [];
    foreach ($entregadores as $en) {
        $ritmo[$en['id_entregador']] = aleatorio(0.86, 1.14);
    }

    for ($d = 0; $d <= $dias; $d++) { // inclui hoje (até 1 h atrás)
        $base = $inicio + $d * 86400;
        $data = (new DateTimeImmutable('@' . $base))->setTimezone($fuso)->format('Y-m-d');
        $qtd = max(1, (int)round($porDia * aleatorio(0.6, 1.3)));
        for ($k = 0; $k < $qtd; $k++) {
            $hora = horaDoPedido();
            $ts = (new DateTimeImmutable($data . ' 00:00:00', $fuso))->getTimestamp() + (int)($hora * 3600);
            if ($ts > $agora - 3600) {
                continue;
            }
            $en = $entregadores[mt_rand(0, count($entregadores) - 1)];
            $bairro = $bairros[mt_rand(0, count($bairros) - 1)];
            $dist = aleatorio(900, 8500);
            $osrm = $dist / aleatorio(8.0, 9.5);
            $diaSemana = (int)(new DateTimeImmutable('@' . $ts))->setTimezone($fuso)->format('N');
            $fatorReal = 1.08 * efeitoHorario($hora, $diaSemana) * $efeitoBairro[$bairro] * $ritmo[$en['id_entregador']] * exp(gauss(0.13));
            $entregas[] = [
                'id_pedido' => ID_DEMO + (++$seq),
                'id_entregador' => (int)$en['id_entregador'],
                'id_empresa' => $idEmpresa,
                'bairro' => $bairro,
                'ts' => $ts,
                'iniciado_em' => (new DateTimeImmutable('@' . $ts))->setTimezone($fuso)->format('Y-m-d H:i:s'),
                'distancia_osrm_m' => round($dist),
                'duracao_osrm_s' => round($osrm, 1),
                'duracao_real_s' => (int)round($osrm * $fatorReal),
                'tempo_local_s' => (int)round(aleatorio(50, 210)),
            ];
        }
    }
}
usort($entregas, fn($a, $b) => $a['ts'] <=> $b['ts']);

// Entregas reais já concluídas também entram no treino (na data em que aconteceram)
$reais = $pdo->query(
    "SELECT id_entregador, bairro, iniciado_em, distancia_osrm_m, duracao_osrm_s, duracao_real_s
     FROM entrega_viagem WHERE status = 'concluida' AND id_pedido < " . ID_DEMO
)->fetchAll(PDO::FETCH_ASSOC);

// ---------------------------------------------------------------------------
// Simulação semana a semana: prevê com o modelo da época e retreina no fim da semana
// ---------------------------------------------------------------------------
$modelos = new EtaModelo($pdo);
$modeloAtual = null; // ['id' => ..., 'params' => ..., 'em_uso' => bool]
$historico = [];
$versoes = [];
$fimSemana = $inicio + 7 * 86400;

$treinarAte = function (int $limite) use (&$historico, $reais, $modelos, $fuso, &$versoes) {
    $base = array_merge($historico, array_values(array_filter($reais, fn($r) => strtotime($r['iniciado_em']) <= $limite)));
    $base = array_values(array_filter($base, [EtaRegressor::class, 'viagemValida']));
    if (count($base) < EtaService::MIN_AMOSTRAS_MODELO) {
        return null;
    }
    $metricas = EtaRegressor::avaliar($base);
    $params = EtaRegressor::treinar($base);
    $quando = (new DateTimeImmutable('@' . $limite))->setTimezone($fuso)->format('Y-m-d H:i:s');
    $id = $modelos->salvar($params, $metricas, $quando);
    $emUso = $metricas && $metricas['mae_modelo_s'] < $metricas['mae_osrm_s'];
    $versoes[] = ['id' => $id, 'quando' => $quando, 'amostras' => $params['amostras'], 'metricas' => $metricas, 'em_uso' => $emUso];
    return ['id' => $id, 'params' => $params, 'em_uso' => $emUso];
};

$pdo->beginTransaction();
foreach ($entregas as $e) {
    while ($e['ts'] > $fimSemana) {
        $novo = $treinarAte($fimSemana);
        if ($novo) {
            $modeloAtual = $novo;
        }
        $fimSemana += 7 * 86400;
    }

    $usaModelo = $modeloAtual && $modeloAtual['em_uso'];
    $fator = $usaModelo ? EtaRegressor::preverFator($modeloAtual['params'], $e) : 1.0;
    $chegou = $e['ts'] + $e['duracao_real_s'];

    $insert->execute([
        ':id_pedido' => $e['id_pedido'],
        ':id_entregador' => $e['id_entregador'],
        ':id_empresa' => $e['id_empresa'],
        ':bairro' => $e['bairro'],
        ':dlat' => -22.3674 + aleatorio(-0.03, 0.03),
        ':dlng' => -46.9428 + aleatorio(-0.03, 0.03),
        ':dist' => $e['distancia_osrm_m'],
        ':osrm' => $e['duracao_osrm_s'],
        ':fator' => $fator,
        ':prevista' => round($e['duracao_osrm_s'] * $fator),
        ':id_modelo' => $usaModelo ? $modeloAtual['id'] : null,
        ':inicio' => $e['iniciado_em'],
        ':chegou' => (new DateTimeImmutable('@' . $chegou))->setTimezone($fuso)->format('Y-m-d H:i:s'),
        ':concluido' => (new DateTimeImmutable('@' . ($chegou + $e['tempo_local_s'])))->setTimezone($fuso)->format('Y-m-d H:i:s'),
        ':dist_final' => round(aleatorio(3, 25)),
        ':real' => $e['duracao_real_s'],
    ]);
    $historico[] = $e;
}
$pdo->commit();

// Versão final, com tudo até agora
$treinarAte($agora);

// ---------------------------------------------------------------------------
// Resumo
// ---------------------------------------------------------------------------
echo "Demonstração criada: " . count($entregas) . " entregas simuladas em {$dias} dias.\n";
foreach ($empresas as $emp) {
    $n = count(array_filter($entregas, fn($e) => $e['id_empresa'] === (int)$emp['id_empresa']));
    echo "  · {$emp['nome_empresa']} (id {$emp['id_empresa']}): {$n} entregas\n";
}
echo "\nVersões do modelo (treinadas semana a semana):\n";
foreach ($versoes as $v) {
    $m = $v['metricas'];
    printf(
        "  v%-3d %s  %4d entregas  erro OSRM %s → modelo %s%s\n",
        $v['id'],
        substr($v['quando'], 0, 16),
        $v['amostras'],
        $m ? sprintf('%4.1f%%', $m['mape_osrm'] * 100) : '  — ',
        $m ? sprintf('%4.1f%%', $m['mape_modelo'] * 100) : '  — ',
        $v['em_uso'] ? '' : '  (não usado: não superou o OSRM)'
    );
}
echo "\nAbra Inteligência no painel do gestor. Para remover: php scripts/seed_demo_inteligencia.php --remover\n";
echo "Lembre: enquanto a demonstração estiver no banco, as previsões reais usam o modelo treinado com ela.\n";

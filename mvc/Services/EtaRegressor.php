<?php

namespace mvc\Services;

/**
 * Modelo de machine learning para estimar o tempo REAL de uma entrega.
 *
 * Ideia: o OSRM calcula o tempo pela malha viária (sem trânsito, perfil de carro).
 * Este modelo aprende, com as entregas já feitas no GUIAR, o quanto o tempo real
 * costuma desviar do OSRM em cada situação (horário, dia, bairro, distância, entregador).
 *
 *   alvo:      y = ln(tempo_real / tempo_osrm)
 *   modelo:    regressão linear com regularização L2 (ridge)
 *   previsão:  tempo_previsto = tempo_osrm × exp(x·w) × smearing
 *
 * - O intercepto não é regularizado: com poucos dados ele vira o "fator médio" observado.
 * - Categorias com poucas amostras são ignoradas (caem no intercepto).
 * - O lambda da ridge é escolhido por validação cruzada (k-fold) quando há dados suficientes.
 * - "smearing" (Duan) corrige o viés de voltar do log para a escala original.
 *
 * Classe pura (sem banco), para poder ser testada isoladamente.
 */
class EtaRegressor
{
    public const FATOR_MIN = 0.5;
    public const FATOR_MAX = 4.0;

    /** Razão real/OSRM fora deste intervalo é tratada como dado inválido (GPS, pausa, etc.). */
    public const RAZAO_MIN = 0.25;
    public const RAZAO_MAX = 6.0;

    private const LAMBDAS = [0.3, 1.0, 3.0, 10.0, 30.0];
    private const DEFAULT_LAMBDA = 3.0;
    private const MIN_AMOSTRAS_CATEGORIA = 3;
    private const MIN_AMOSTRAS_CV = 30;
    private const K_FOLDS = 5;

    private const FAIXAS_HORARIO = [
        // [hora inicial inclusive, hora final exclusive, nome]
        [0, 6, 'madrugada'],
        [6, 10, 'manha'],
        [10, 14, 'almoco'],
        [14, 17, 'tarde'],
        [17, 20, 'pico_tarde'],
        [20, 24, 'noite'],
    ];

    /**
     * Converte uma viagem em "atributos brutos" (antes da codificação one-hot).
     *
     * @param array $viagem chaves: iniciado_em (Y-m-d H:i:s, horário local), distancia_osrm_m,
     *                      bairro, id_entregador
     */
    public static function atributos(array $viagem): array
    {
        $ts = strtotime((string)$viagem['iniciado_em']);
        $hora = (int)date('G', $ts);
        $diaSemana = (int)date('N', $ts); // 1 = segunda ... 7 = domingo

        $faixa = 'noite';
        foreach (self::FAIXAS_HORARIO as [$ini, $fim, $nome]) {
            if ($hora >= $ini && $hora < $fim) {
                $faixa = $nome;
                break;
            }
        }

        $dia = $diaSemana === 7 ? 'domingo' : ($diaSemana === 6 ? 'sabado' : 'util');

        return [
            'faixa_horario' => $faixa,
            'dia' => $dia,
            'bairro' => self::normalizarBairro($viagem['bairro'] ?? ''),
            'entregador' => (string)($viagem['id_entregador'] ?? ''),
            'log_dist_km' => log(max(((float)$viagem['distancia_osrm_m']) / 1000, 0.1)),
        ];
    }

    public static function normalizarBairro(?string $bairro): string
    {
        $b = mb_strtolower(trim((string)$bairro), 'UTF-8');
        $b = strtr($b, [
            'á' => 'a', 'à' => 'a', 'â' => 'a', 'ã' => 'a', 'é' => 'e', 'ê' => 'e', 'í' => 'i',
            'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ú' => 'u', 'ü' => 'u', 'ç' => 'c',
        ]);
        return preg_replace('/\s+/', ' ', $b);
    }

    /**
     * Treina o modelo.
     *
     * @param array $viagens lista de viagens concluídas; cada uma precisa de
     *                       iniciado_em, distancia_osrm_m, duracao_osrm_s, duracao_real_s, bairro, id_entregador
     * @return array parâmetros serializáveis (ver prever())
     */
    public static function treinar(array $viagens, ?float $lambda = null): array
    {
        $viagens = array_values(array_filter($viagens, [self::class, 'viagemValida']));
        $n = count($viagens);
        if ($n === 0) {
            throw new \InvalidArgumentException('Nenhuma viagem válida para treinar.');
        }

        $lambdaEscolhido = $lambda;
        $cv = null;
        if ($lambdaEscolhido === null) {
            if ($n >= self::MIN_AMOSTRAS_CV) {
                $cv = self::validacaoCruzada($viagens);
                $lambdaEscolhido = $cv['melhor_lambda'];
            } else {
                $lambdaEscolhido = self::DEFAULT_LAMBDA;
            }
        }

        $params = self::ajustar($viagens, $lambdaEscolhido);
        if ($cv !== null) {
            $params['validacao_cruzada'] = $cv['mae_por_lambda'];
        }
        return $params;
    }

    /**
     * Fator multiplicativo previsto para uma viagem (tempo_real ≈ tempo_osrm × fator).
     * Sem parâmetros (modelo ainda não treinado) retorna 1.0 (= OSRM puro).
     */
    public static function preverFator(?array $params, array $viagem): float
    {
        if (empty($params) || empty($params['pesos'])) {
            return 1.0;
        }
        $x = self::vetorizar(self::atributos($viagem), $params);
        $y = 0.0;
        foreach ($params['pesos'] as $i => $w) {
            $y += $w * $x[$i];
        }
        $fator = exp($y) * ($params['smearing'] ?? 1.0);
        return max(self::FATOR_MIN, min(self::FATOR_MAX, $fator));
    }

    /**
     * Avalia em dados nunca vistos: treina com as viagens mais antigas e testa nas mais recentes
     * (divisão cronológica, como acontece na vida real).
     *
     * @return array|null métricas, ou null se não houver dados suficientes
     */
    public static function avaliar(array $viagens, float $fracaoTeste = 0.2, int $minTeste = 5): ?array
    {
        $viagens = array_values(array_filter($viagens, [self::class, 'viagemValida']));
        usort($viagens, fn($a, $b) => strcmp($a['iniciado_em'], $b['iniciado_em']));
        $n = count($viagens);
        $nTeste = max($minTeste, (int)round($n * $fracaoTeste));
        if ($n - $nTeste < $minTeste) {
            return null;
        }

        $treino = array_slice($viagens, 0, $n - $nTeste);
        $teste = array_slice($viagens, $n - $nTeste);
        $params = self::treinar($treino);

        $erroOsrm = $erroModelo = $pctOsrm = $pctModelo = 0.0;
        foreach ($teste as $v) {
            $real = (float)$v['duracao_real_s'];
            $osrm = (float)$v['duracao_osrm_s'];
            $prev = $osrm * self::preverFator($params, $v);
            $erroOsrm += abs($osrm - $real);
            $erroModelo += abs($prev - $real);
            $pctOsrm += abs($osrm - $real) / $real;
            $pctModelo += abs($prev - $real) / $real;
        }

        return [
            'amostras_treino' => count($treino),
            'amostras_teste' => $nTeste,
            'mae_osrm_s' => $erroOsrm / $nTeste,
            'mae_modelo_s' => $erroModelo / $nTeste,
            'mape_osrm' => $pctOsrm / $nTeste,
            'mape_modelo' => $pctModelo / $nTeste,
        ];
    }

    public static function viagemValida(array $v): bool
    {
        $osrm = (float)($v['duracao_osrm_s'] ?? 0);
        $real = (float)($v['duracao_real_s'] ?? 0);
        if ($osrm <= 0 || $real <= 0 || empty($v['iniciado_em'])) {
            return false;
        }
        $razao = $real / $osrm;
        return $razao >= self::RAZAO_MIN && $razao <= self::RAZAO_MAX;
    }

    // ------------------------------------------------------------------
    // Internos
    // ------------------------------------------------------------------

    private static function ajustar(array $viagens, float $lambda): array
    {
        $attrs = array_map([self::class, 'atributos'], $viagens);
        $y = array_map(
            fn($v) => log((float)$v['duracao_real_s'] / (float)$v['duracao_osrm_s']),
            $viagens
        );

        // Vocabulário: só categorias com amostras suficientes viram coluna
        $contagem = [];
        foreach ($attrs as $a) {
            foreach (['faixa_horario', 'dia', 'bairro', 'entregador'] as $campo) {
                if ($a[$campo] === '') {
                    continue;
                }
                $chave = $campo . '=' . $a[$campo];
                $contagem[$chave] = ($contagem[$chave] ?? 0) + 1;
            }
        }
        $categorias = array_keys(array_filter($contagem, fn($c) => $c >= self::MIN_AMOSTRAS_CATEGORIA));
        sort($categorias);

        // Atributo contínuo: só centralizado (sem dividir pelo desvio). Assim o peso é uma
        // elasticidade em unidades de log(km) e a ridge o encolhe quando há pouca variação de
        // distância no histórico. Na previsão, a distância é limitada à faixa vista no treino
        // para não extrapolar a inclinação.
        $dists = array_column($attrs, 'log_dist_km');
        $params = [
            'colunas' => array_merge(['intercepto', 'log_dist_km'], $categorias),
            'dist_media' => array_sum($dists) / count($dists),
            'dist_min' => min($dists),
            'dist_max' => max($dists),
        ];

        $X = array_map(fn($a) => self::vetorizar($a, $params), $attrs);
        $p = count($params['colunas']);

        // Equações normais da ridge: (XᵀX + λD) w = Xᵀy, D = diag(0, 1, 1, ...)
        $A = array_fill(0, $p, array_fill(0, $p, 0.0));
        $b = array_fill(0, $p, 0.0);
        foreach ($X as $i => $xi) {
            for ($r = 0; $r < $p; $r++) {
                if ($xi[$r] == 0.0) {
                    continue;
                }
                $b[$r] += $xi[$r] * $y[$i];
                for ($c = 0; $c < $p; $c++) {
                    $A[$r][$c] += $xi[$r] * $xi[$c];
                }
            }
        }
        for ($j = 1; $j < $p; $j++) {
            $A[$j][$j] += $lambda;
        }
        $w = self::resolverSistema($A, $b);

        // Smearing de Duan: média de exp(resíduo) corrige o viés da transformação log
        $soma = 0.0;
        foreach ($X as $i => $xi) {
            $pred = 0.0;
            for ($j = 0; $j < $p; $j++) {
                $pred += $w[$j] * $xi[$j];
            }
            $soma += exp($y[$i] - $pred);
        }

        $params['pesos'] = $w;
        $params['lambda'] = $lambda;
        $params['smearing'] = $soma / count($X);
        $params['amostras'] = count($X);
        return $params;
    }

    private static function vetorizar(array $atributos, array $params): array
    {
        $x = [];
        foreach ($params['colunas'] as $coluna) {
            if ($coluna === 'intercepto') {
                $x[] = 1.0;
            } elseif ($coluna === 'log_dist_km') {
                $d = max($params['dist_min'], min($params['dist_max'], $atributos['log_dist_km']));
                $x[] = $d - $params['dist_media'];
            } else {
                [$campo, $valor] = explode('=', $coluna, 2);
                $x[] = (($atributos[$campo] ?? null) === $valor) ? 1.0 : 0.0;
            }
        }
        return $x;
    }

    private static function validacaoCruzada(array $viagens): array
    {
        // Embaralhamento determinístico para resultados reprodutíveis
        $idx = array_keys($viagens);
        mt_srand(42);
        shuffle($idx);
        mt_srand();

        $folds = array_fill(0, self::K_FOLDS, []);
        foreach ($idx as $pos => $i) {
            $folds[$pos % self::K_FOLDS][] = $i;
        }

        $maePorLambda = [];
        foreach (self::LAMBDAS as $lambda) {
            $erro = 0.0;
            $total = 0;
            foreach ($folds as $k => $testeIdx) {
                $treino = [];
                foreach ($folds as $k2 => $f) {
                    if ($k2 !== $k) {
                        foreach ($f as $i) {
                            $treino[] = $viagens[$i];
                        }
                    }
                }
                $params = self::ajustar($treino, $lambda);
                foreach ($testeIdx as $i) {
                    $v = $viagens[$i];
                    $prev = (float)$v['duracao_osrm_s'] * self::preverFator($params, $v);
                    $erro += abs($prev - (float)$v['duracao_real_s']);
                    $total++;
                }
            }
            $maePorLambda[(string)$lambda] = $erro / $total;
        }

        $melhor = array_keys($maePorLambda, min($maePorLambda))[0];
        return ['melhor_lambda' => (float)$melhor, 'mae_por_lambda' => $maePorLambda];
    }

    /** Eliminação de Gauss com pivoteamento parcial. */
    private static function resolverSistema(array $A, array $b): array
    {
        $n = count($b);
        for ($col = 0; $col < $n; $col++) {
            $pivo = $col;
            for ($r = $col + 1; $r < $n; $r++) {
                if (abs($A[$r][$col]) > abs($A[$pivo][$col])) {
                    $pivo = $r;
                }
            }
            if (abs($A[$pivo][$col]) < 1e-12) {
                continue; // coluna degenerada: peso fica 0
            }
            [$A[$col], $A[$pivo]] = [$A[$pivo], $A[$col]];
            [$b[$col], $b[$pivo]] = [$b[$pivo], $b[$col]];

            for ($r = $col + 1; $r < $n; $r++) {
                $f = $A[$r][$col] / $A[$col][$col];
                if ($f == 0.0) {
                    continue;
                }
                for ($c = $col; $c < $n; $c++) {
                    $A[$r][$c] -= $f * $A[$col][$c];
                }
                $b[$r] -= $f * $b[$col];
            }
        }

        $x = array_fill(0, $n, 0.0);
        for ($r = $n - 1; $r >= 0; $r--) {
            if (abs($A[$r][$r]) < 1e-12) {
                continue;
            }
            $s = $b[$r];
            for ($c = $r + 1; $c < $n; $c++) {
                $s -= $A[$r][$c] * $x[$c];
            }
            $x[$r] = $s / $A[$r][$r];
        }
        return $x;
    }
}

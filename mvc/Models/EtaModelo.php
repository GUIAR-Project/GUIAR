<?php

namespace mvc\Models;

use PDO;

/**
 * Versões treinadas do modelo de estimativa de tempo. A mais recente é a ativa.
 */
class EtaModelo
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /** @return array|null linha do modelo mais recente, com 'parametros' já decodificado */
    public function ativo(): ?array
    {
        $stmt = $this->pdo->query("SELECT * FROM eta_modelo ORDER BY id_modelo DESC LIMIT 1");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }
        $row['parametros'] = json_decode($row['parametros'], true);
        return $row;
    }

    /** Versões do modelo em ordem cronológica (para o gráfico de evolução do erro). */
    public function historico(int $limite = 30): array
    {
        $stmt = $this->pdo->query(
            "SELECT id_modelo, treinado_em, amostras, amostras_teste, mae_osrm_s, mae_modelo_s, mape_osrm, mape_modelo
             FROM (SELECT * FROM eta_modelo ORDER BY id_modelo DESC LIMIT " . (int)$limite . ") ultimos
             ORDER BY id_modelo ASC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function salvar(array $parametros, ?array $metricas, string $treinadoEm): int
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO eta_modelo
                (treinado_em, amostras, parametros, amostras_teste, mae_osrm_s, mae_modelo_s, mape_osrm, mape_modelo)
             VALUES
                (:treinado_em, :amostras, :parametros, :amostras_teste, :mae_osrm_s, :mae_modelo_s, :mape_osrm, :mape_modelo)"
        );
        $stmt->execute([
            ':treinado_em' => $treinadoEm,
            ':amostras' => $parametros['amostras'],
            ':parametros' => json_encode($parametros),
            ':amostras_teste' => $metricas['amostras_teste'] ?? null,
            ':mae_osrm_s' => $metricas['mae_osrm_s'] ?? null,
            ':mae_modelo_s' => $metricas['mae_modelo_s'] ?? null,
            ':mape_osrm' => $metricas['mape_osrm'] ?? null,
            ':mape_modelo' => $metricas['mape_modelo'] ?? null,
        ]);
        return (int)$this->pdo->lastInsertId();
    }
}

<?php

namespace mvc\Models;

use PDO;

/**
 * Histórico de trajetos de entrega (base de treino do modelo de estimativa de tempo).
 */
class EntregaViagem
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function buscarEmAndamento(int $idPedido, int $idEntregador)
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM entrega_viagem
             WHERE id_pedido = :pedido AND id_entregador = :entregador AND status = 'em_andamento'
             ORDER BY id_viagem DESC LIMIT 1"
        );
        $stmt->execute([':pedido' => $idPedido, ':entregador' => $idEntregador]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function buscarEmAndamentoPorPedido(int $idPedido)
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM entrega_viagem
             WHERE id_pedido = :pedido AND status = 'em_andamento'
             ORDER BY id_viagem DESC LIMIT 1"
        );
        $stmt->execute([':pedido' => $idPedido]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function criar(array $d): int
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO entrega_viagem
                (id_pedido, id_entregador, id_empresa, bairro, origem_lat, origem_lng, destino_lat, destino_lng,
                 distancia_osrm_m, duracao_osrm_s, fator_previsto, duracao_prevista_s, id_modelo, iniciado_em, status)
             VALUES
                (:id_pedido, :id_entregador, :id_empresa, :bairro, :origem_lat, :origem_lng, :destino_lat, :destino_lng,
                 :distancia_osrm_m, :duracao_osrm_s, :fator_previsto, :duracao_prevista_s, :id_modelo, :iniciado_em, 'em_andamento')"
        );
        $stmt->execute([
            ':id_pedido' => $d['id_pedido'],
            ':id_entregador' => $d['id_entregador'],
            ':id_empresa' => $d['id_empresa'],
            ':bairro' => $d['bairro'],
            ':origem_lat' => $d['origem_lat'],
            ':origem_lng' => $d['origem_lng'],
            ':destino_lat' => $d['destino_lat'],
            ':destino_lng' => $d['destino_lng'],
            ':distancia_osrm_m' => $d['distancia_osrm_m'],
            ':duracao_osrm_s' => $d['duracao_osrm_s'],
            ':fator_previsto' => $d['fator_previsto'],
            ':duracao_prevista_s' => $d['duracao_prevista_s'],
            ':id_modelo' => $d['id_modelo'],
            ':iniciado_em' => $d['iniciado_em'],
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function registrarChegada(int $idViagem, string $quando): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE entrega_viagem SET chegou_em = :quando
             WHERE id_viagem = :id AND chegou_em IS NULL AND status = 'em_andamento'"
        );
        $stmt->execute([':quando' => $quando, ':id' => $idViagem]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Descarta trajetos abertos do entregador que ainda não chegaram ao destino
     * (ele começou outra rota antes de chegar), exceto o pedido informado.
     */
    public function descartarAbertasSemChegada(int $idEntregador, int $excetoPedido, string $motivo): int
    {
        $stmt = $this->pdo->prepare(
            "UPDATE entrega_viagem SET status = 'descartada', motivo_descarte = :motivo
             WHERE id_entregador = :entregador AND id_pedido <> :pedido
               AND status = 'em_andamento' AND chegou_em IS NULL"
        );
        $stmt->execute([':motivo' => $motivo, ':entregador' => $idEntregador, ':pedido' => $excetoPedido]);
        return $stmt->rowCount();
    }

    public function finalizar(int $idViagem, array $d): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE entrega_viagem SET
                concluido_em = :concluido_em,
                distancia_final_m = :distancia_final_m,
                duracao_real_s = :duracao_real_s,
                status = :status,
                motivo_descarte = :motivo
             WHERE id_viagem = :id"
        );
        return $stmt->execute([
            ':concluido_em' => $d['concluido_em'],
            ':distancia_final_m' => $d['distancia_final_m'],
            ':duracao_real_s' => $d['duracao_real_s'],
            ':status' => $d['status'],
            ':motivo' => $d['motivo_descarte'],
            ':id' => $idViagem,
        ]);
    }

    /** Viagens concluídas e válidas, em ordem cronológica, para treinar o modelo. */
    public function listarParaTreino(): array
    {
        $stmt = $this->pdo->query(
            "SELECT id_viagem, id_entregador, bairro, iniciado_em, distancia_osrm_m, duracao_osrm_s,
                    duracao_real_s, duracao_prevista_s
             FROM entrega_viagem
             WHERE status = 'concluida' AND duracao_real_s IS NOT NULL
             ORDER BY iniciado_em ASC, id_viagem ASC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Trajeto em andamento mais recente do entregador (a parada atual). */
    public function buscarAbertaDoEntregador(int $idEntregador)
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM entrega_viagem
             WHERE id_entregador = :entregador AND status = 'em_andamento'
             ORDER BY (chegou_em IS NULL) DESC, id_viagem DESC LIMIT 1"
        );
        $stmt->execute([':entregador' => $idEntregador]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function atualizarProgresso(int $idViagem, int $etaRestante, ?int $distanciaRestante, string $quando): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE entrega_viagem SET eta_restante_s = :eta, distancia_restante_m = :dist, eta_atualizado_em = :quando
             WHERE id_viagem = :id AND status = 'em_andamento'"
        );
        $stmt->execute([':eta' => $etaRestante, ':dist' => $distanciaRestante, ':quando' => $quando, ':id' => $idViagem]);
        return $stmt->rowCount() > 0;
    }

    /** Segundos entre chegada e conclusão das entregas mais recentes (tempo gasto no endereço). */
    public function temposNoLocal(int $limite = 200, ?int $idEmpresa = null): array
    {
        $sql = "SELECT TIMESTAMPDIFF(SECOND, chegou_em, concluido_em) AS s
                FROM entrega_viagem
                WHERE status = 'concluida' AND chegou_em IS NOT NULL AND concluido_em IS NOT NULL"
            . ($idEmpresa !== null ? " AND id_empresa = :empresa" : "")
            . " ORDER BY id_viagem DESC LIMIT " . (int)$limite;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($idEmpresa !== null ? [':empresa' => $idEmpresa] : []);
        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    /** Entregas concluídas da empresa, com o nome do entregador, para o painel de inteligência. */
    public function listarConcluidasDaEmpresa(int $idEmpresa): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT v.id_viagem, v.id_pedido, v.id_entregador, v.bairro, v.iniciado_em, v.chegou_em, v.concluido_em,
                    v.distancia_osrm_m, v.duracao_osrm_s, v.duracao_prevista_s, v.duracao_real_s, v.id_modelo,
                    e.nome_completo AS nome_entregador
             FROM entrega_viagem v
             LEFT JOIN entregador e ON e.id_entregador = v.id_entregador
             WHERE v.id_empresa = :empresa AND v.status = 'concluida' AND v.duracao_real_s IS NOT NULL
             ORDER BY v.iniciado_em ASC, v.id_viagem ASC"
        );
        $stmt->execute([':empresa' => $idEmpresa]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function contarPorStatusDaEmpresa(int $idEmpresa): array
    {
        $stmt = $this->pdo->prepare("SELECT status, COUNT(*) AS total FROM entrega_viagem WHERE id_empresa = :e GROUP BY status");
        $stmt->execute([':e' => $idEmpresa]);
        $contagem = ['em_andamento' => 0, 'concluida' => 0, 'descartada' => 0];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $contagem[$row['status']] = (int)$row['total'];
        }
        return $contagem;
    }

    public function contarPorStatus(): array
    {
        $stmt = $this->pdo->query("SELECT status, COUNT(*) AS total FROM entrega_viagem GROUP BY status");
        $contagem = ['em_andamento' => 0, 'concluida' => 0, 'descartada' => 0];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $contagem[$row['status']] = (int)$row['total'];
        }
        return $contagem;
    }
}

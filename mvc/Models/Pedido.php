<?php

namespace mvc\Models;

use PDO;
use PDOException;

class Pedido
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function getAllByEmpresa($company_id, $status = null)
    {
        try {
            $sql = "
                SELECT p.*, e.nome_completo AS nome_entregador
                FROM pedido p
                LEFT JOIN entregador e ON p.id_entregador = e.id_entregador
                WHERE p.id_empresa = :company_id
            ";

            if ($status === 'entregue') {
                $sql .= " AND p.status = 'entregue'";
            } elseif ($status === 'pendente') {
                $sql .= " AND p.status != 'entregue'";
            }

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':company_id', $company_id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar pedidos: " . $e->getMessage());
            return [];
        }
    }

    public function getAllByEntregador($entregador_id)
    {
        try {
            $sql = "SELECT id_pedido, nome_cliente, preco, endereco, bairro, descricao, latitude, longitude,
                           status, rota_ordem, rota_trecho_s
                    FROM pedido
                    WHERE id_entregador = :entregador_id AND status != 'entregue'
                    ORDER BY rota_ordem IS NULL, rota_ordem, id_pedido";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':entregador_id', $entregador_id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar pedidos do entregador: " . $e->getMessage());
            return [];
        }
    }

    public function getById($id)
    {
        try {
            $sql = "SELECT * FROM pedido WHERE id_pedido = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar pedido por ID: " . $e->getMessage());
            return false;
        }
    }

    public function create($data)
    {
        try {
            $sql = "INSERT INTO pedido (nome_cliente, preco, endereco, bairro, descricao, id_empresa, id_adm, latitude, longitude, status, telefone_cliente, token_rastreio)
                    VALUES (:nome_cliente, :preco, :endereco, :bairro, :descricao, :id_empresa, :id_adm, :latitude, :longitude, 'Pendente', :telefone_cliente, :token_rastreio)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':nome_cliente', $data['nome_cliente']);
            $stmt->bindParam(':preco', $data['preco']);
            $stmt->bindParam(':endereco', $data['endereco']);
            $stmt->bindParam(':bairro', $data['bairro']);
            $stmt->bindParam(':descricao', $data['descricao']);
            $stmt->bindParam(':id_empresa', $data['id_empresa']);
            $stmt->bindParam(':id_adm', $data['id_adm']);
            $stmt->bindParam(':latitude', $data['latitude']);
            $stmt->bindParam(':longitude', $data['longitude']);
            $telefone = self::normalizarTelefone($data['telefone_cliente'] ?? null);
            $token = self::novoToken();
            $stmt->bindParam(':telefone_cliente', $telefone);
            $stmt->bindParam(':token_rastreio', $token);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erro ao criar pedido: " . $e->getMessage());
            return false;
        }
    }

    public function update($id, $data)
    {
        try {
            $sql = "UPDATE pedido SET 
                    nome_cliente = :nome_cliente, 
                    preco = :preco, 
                    endereco = :endereco, 
                    bairro = :bairro, 
                    descricao = :descricao, 
                    latitude = :latitude, 
                    longitude = :longitude,
                    telefone_cliente = :telefone_cliente
                    WHERE id_pedido = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':nome_cliente', $data['nome_cliente']);
            $stmt->bindParam(':preco', $data['preco']);
            $stmt->bindParam(':endereco', $data['endereco']);
            $stmt->bindParam(':bairro', $data['bairro']);
            $stmt->bindParam(':descricao', $data['descricao']);
            $stmt->bindParam(':latitude', $data['latitude']);
            $stmt->bindParam(':longitude', $data['longitude']);
            $telefone = self::normalizarTelefone($data['telefone_cliente'] ?? null);
            $stmt->bindParam(':telefone_cliente', $telefone);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erro ao atualizar pedido: " . $e->getMessage());
            return false;
        }
    }

    public function delete($id)
    {
        try {
            $sql = "DELETE FROM pedido WHERE id_pedido = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erro ao excluir pedido: " . $e->getMessage());
            return false;
        }
    }

    public function assignToEntregador($pedido_ids, $entregador_id)
    {
        try {
            $ids = implode(',', array_map('intval', $pedido_ids));
            $sql = "UPDATE pedido SET id_entregador = :entregador_id, status = 'A caminho' WHERE id_pedido IN ($ids)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':entregador_id', $entregador_id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erro ao atribuir entregador: " . $e->getMessage());
            return false;
        }
    }

    public function deleteDeliveredByEmpresa($company_id)
    {
        try {
            $sql = "DELETE FROM pedido WHERE status = 'entregue' AND id_empresa = :company_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':company_id', $company_id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erro ao finalizar turno: " . $e->getMessage());
            return false;
        }
    }

    public function updateStatus($id, $status)
    {
        try {
            $sql = "UPDATE pedido SET status = :status, entregue_em = :entregue_em WHERE id_pedido = :id";
            $stmt = $this->pdo->prepare($sql);
            $entregueEm = $status === 'entregue'
                ? (new \DateTimeImmutable('now', new \DateTimeZone('America/Sao_Paulo')))->format('Y-m-d H:i:s')
                : null;
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':entregue_em', $entregueEm);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erro ao atualizar status do pedido: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Retorna contagens agrupadas por status para o dashboard.
     */
    public function getDashboardStats($company_id)
    {
        try {
            $sql = "SELECT
                        COUNT(*) AS total,
                        SUM(CASE WHEN status = 'entregue'   THEN 1 ELSE 0 END) AS entregues,
                        SUM(CASE WHEN status = 'A caminho'  THEN 1 ELSE 0 END) AS em_andamento,
                        SUM(CASE WHEN status = 'Pendente'   THEN 1 ELSE 0 END) AS pendentes
                    FROM pedido
                    WHERE id_empresa = :company_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':company_id', $company_id, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: ['total' => 0, 'entregues' => 0, 'em_andamento' => 0, 'pendentes' => 0];
        } catch (PDOException $e) {
            error_log("Erro ao buscar stats do dashboard: " . $e->getMessage());
            return ['total' => 0, 'entregues' => 0, 'em_andamento' => 0, 'pendentes' => 0];
        }
    }

    /**
     * Retorna os pedidos mais recentes com o nome do entregador associado.
     */
    public function getRecent($company_id, $limit = 8)
    {
        try {
            $sql = "SELECT p.id_pedido, p.nome_cliente, p.endereco, p.preco, p.bairro, p.status,
                           e.nome_completo AS nome_entregador
                    FROM pedido p
                    LEFT JOIN entregador e ON p.id_entregador = e.id_entregador
                    WHERE p.id_empresa = :company_id
                    ORDER BY p.id_pedido DESC
                    LIMIT :lim";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':company_id', $company_id, PDO::PARAM_INT);
            $stmt->bindParam(':lim', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar pedidos recentes: " . $e->getMessage());
            return [];
        }
    }
    // ------------------------------------------------------------------
    // Rastreio do cliente e rota otimizada
    // ------------------------------------------------------------------

    public static function novoToken(): string
    {
        return bin2hex(random_bytes(16));
    }

    /** Guarda só os dígitos do WhatsApp; vazio vira NULL. */
    public static function normalizarTelefone($telefone): ?string
    {
        $digitos = preg_replace('/\D+/', '', (string)$telefone);
        return $digitos === '' ? null : substr($digitos, 0, 20);
    }

    /** Gera token de rastreio para pedidos antigos da empresa que ainda não têm. */
    public function garantirTokens($company_id): void
    {
        try {
            $stmt = $this->pdo->prepare("SELECT id_pedido FROM pedido WHERE id_empresa = :c AND token_rastreio IS NULL");
            $stmt->execute([':c' => $company_id]);
            $upd = $this->pdo->prepare("UPDATE pedido SET token_rastreio = :t WHERE id_pedido = :id AND token_rastreio IS NULL");
            foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $id) {
                $upd->execute([':t' => self::novoToken(), ':id' => $id]);
            }
        } catch (PDOException $e) {
            error_log("Erro ao gerar tokens de rastreio: " . $e->getMessage());
        }
    }

    /** Pedido + empresa + entregador para a página pública de rastreio. */
    public function getByTokenRastreio(string $token)
    {
        if (!preg_match('/^[a-f0-9]{32}$/', $token)) {
            return false;
        }
        $sql = "SELECT p.*, emp.nome_empresa, emp.nome_arquivo AS logo_empresa,
                       e.nome_completo AS nome_entregador, e.latitude AS entregador_lat, e.longitude AS entregador_lng
                FROM pedido p
                JOIN empresa emp ON emp.id_empresa = p.id_empresa
                LEFT JOIN entregador e ON e.id_entregador = p.id_entregador
                WHERE p.token_rastreio = :t";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':t' => $token]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Grava a ordem otimizada das paradas do entregador.
     * @param array $plano lista [id_pedido => trecho_s] na ordem de visita
     */
    public function salvarPlanoRota(int $entregador_id, array $plano): void
    {
        $this->pdo->beginTransaction();
        try {
            $limpar = $this->pdo->prepare("UPDATE pedido SET rota_ordem = NULL, rota_trecho_s = NULL
                                           WHERE id_entregador = :e AND status != 'entregue'");
            $limpar->execute([':e' => $entregador_id]);
            $upd = $this->pdo->prepare("UPDATE pedido SET rota_ordem = :o, rota_trecho_s = :t
                                        WHERE id_pedido = :id AND id_entregador = :e");
            $ordem = 1;
            foreach ($plano as $id => $trecho) {
                $upd->execute([':o' => $ordem++, ':t' => (int)round($trecho), ':id' => $id, ':e' => $entregador_id]);
            }
            $this->pdo->commit();
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}

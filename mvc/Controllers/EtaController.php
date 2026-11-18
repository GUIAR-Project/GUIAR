<?php

namespace mvc\Controllers;

use mvc\Services\EtaService;

/**
 * API da estimativa de tempo de entrega (machine learning sobre o OSRM).
 */
class EtaController
{
    private $service;

    public function __construct($pdo)
    {
        $this->service = new EtaService($pdo);
    }

    /** POST {id_pedido, origem_lat, origem_lng, duracao_osrm_s, distancia_osrm_m} — entregador começou a rota. */
    public function iniciar()
    {
        $idEntregador = $this->exigirEntregador();
        $in = $this->lerJsonPost();

        $idPedido = filter_var($in['id_pedido'] ?? null, FILTER_VALIDATE_INT);
        $campos = ['origem_lat', 'origem_lng', 'duracao_osrm_s', 'distancia_osrm_m'];
        foreach ($campos as $campo) {
            if (!isset($in[$campo]) || !is_numeric($in[$campo])) {
                $this->json(['success' => false, 'error' => "Campo inválido: {$campo}"], 400);
            }
        }
        if (!$idPedido) {
            $this->json(['success' => false, 'error' => 'Campo inválido: id_pedido'], 400);
        }

        $this->executar(fn() => $this->service->iniciarViagem(
            $idEntregador,
            $idPedido,
            (float)$in['origem_lat'],
            (float)$in['origem_lng'],
            (float)$in['duracao_osrm_s'],
            (float)$in['distancia_osrm_m']
        ));
    }

    /** POST {id_pedido} — GPS detectou o entregador no destino. */
    public function chegada()
    {
        $idEntregador = $this->exigirEntregador();
        $in = $this->lerJsonPost();
        $idPedido = filter_var($in['id_pedido'] ?? null, FILTER_VALIDATE_INT);
        if (!$idPedido) {
            $this->json(['success' => false, 'error' => 'Campo inválido: id_pedido'], 400);
        }
        $this->executar(fn() => ['registrada' => $this->service->registrarChegada($idEntregador, $idPedido)]);
    }

    /** POST {trechos: [{id_pedido, duracao_osrm_s, distancia_osrm_m}, ...]} — rota otimizada na ordem de visita. */
    public function planejar()
    {
        $idEntregador = $this->exigirEntregador();
        $in = $this->lerJsonPost();
        if (!isset($in['trechos']) || !is_array($in['trechos'])) {
            $this->json(['success' => false, 'error' => 'Campo inválido: trechos'], 400);
        }
        $this->executar(fn() => $this->service->planejarRota($idEntregador, $in['trechos']));
    }

    /** POST {id_pedido, eta_restante_s, distancia_restante_m} — previsão ao vivo enviada pelo celular. */
    public function progresso()
    {
        $idEntregador = $this->exigirEntregador();
        $in = $this->lerJsonPost();
        $idPedido = filter_var($in['id_pedido'] ?? null, FILTER_VALIDATE_INT);
        if (!$idPedido || !isset($in['eta_restante_s']) || !is_numeric($in['eta_restante_s'])) {
            $this->json(['success' => false, 'error' => 'Campos inválidos.'], 400);
        }
        $dist = isset($in['distancia_restante_m']) && is_numeric($in['distancia_restante_m']) ? (float)$in['distancia_restante_m'] : null;
        $this->executar(fn() => [
            'registrado' => $this->service->registrarProgresso($idEntregador, $idPedido, (float)$in['eta_restante_s'], $dist),
        ]);
    }

    /** GET — situação do modelo e da coleta de dados (administrador). */
    public function status()
    {
        $this->exigirAdministrador();
        $this->executar(fn() => $this->service->status());
    }

    /** POST — força um novo treino com todo o histórico (administrador). */
    public function treinar()
    {
        $this->exigirAdministrador();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'error' => 'Método não permitido.'], 405);
        }
        $this->executar(function () {
            $resultado = $this->service->treinar();
            if ($resultado === null) {
                throw new \DomainException('São necessárias pelo menos ' . EtaService::MIN_AMOSTRAS_MODELO . ' entregas válidas para treinar.');
            }
            return $resultado;
        });
    }

    // ------------------------------------------------------------------

    private function executar(callable $fn)
    {
        try {
            $this->json(['success' => true] + $fn());
        } catch (\DomainException $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()], 422);
        } catch (\PDOException $e) {
            error_log('Estimativa de tempo: ' . $e->getMessage());
            $semTabela = strpos($e->getMessage(), 'entrega_viagem') !== false || strpos($e->getMessage(), 'eta_modelo') !== false;
            $this->json([
                'success' => false,
                'error' => $semTabela
                    ? 'Tabelas da estimativa de tempo não encontradas. Rode a migração database/migrations/2026_11_17_estimativa_tempo_ml.sql.'
                    : 'Erro no banco de dados.',
            ], 500);
        }
    }

    private function exigirEntregador(): int
    {
        if (empty($_SESSION['entregador_id'])) {
            $this->json(['success' => false, 'error' => 'Não autenticado'], 401);
        }
        return (int)$_SESSION['entregador_id'];
    }

    private function exigirAdministrador(): void
    {
        if (!isset($_SESSION['company_id'], $_SESSION['id_adm'])) {
            $this->json(['success' => false, 'error' => 'Sessão administrativa necessária.'], 401);
        }
    }

    private function lerJsonPost(): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'error' => 'Método não permitido.'], 405);
        }
        $in = json_decode(file_get_contents('php://input'), true);
        if (!is_array($in)) {
            $this->json(['success' => false, 'error' => 'JSON inválido.'], 400);
        }
        return $in;
    }

    private function json(array $dados, int $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($dados);
        exit;
    }
}

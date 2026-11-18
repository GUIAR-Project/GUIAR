<?php

namespace mvc\Controllers;

use mvc\Models\Pedido;
use mvc\Services\EtaService;

/**
 * Página pública de rastreio do pedido (o cliente abre pelo link, sem login).
 */
class RastreioController
{
    private $pedidoModel;
    private $etaService;

    public function __construct($pdo)
    {
        $this->pedidoModel = new Pedido($pdo);
        $this->etaService = new EtaService($pdo);
    }

    /** GET ?t=TOKEN — página. */
    public function pagina()
    {
        $token = (string)($_GET['t'] ?? '');
        $pedido = $this->pedidoModel->getByTokenRastreio($token);
        if (!$pedido) {
            http_response_code(404);
        }
        $nomeEmpresa = $pedido['nome_empresa'] ?? 'GUIAR';
        $logoEmpresa = !empty($pedido['logo_empresa'])
            ? BASE_URL . '/public/uploads/empresas/' . rawurlencode(basename($pedido['logo_empresa']))
            : BASE_URL . '/img/LogoGuiar.png';
        require __DIR__ . '/../Views/Rastreio/rastreio.php';
    }

    /** GET ?t=TOKEN — situação atual em JSON (a página consulta a cada 10 s). */
    public function api()
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');

        $pedido = $this->pedidoModel->getByTokenRastreio((string)($_GET['t'] ?? ''));
        if (!$pedido) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Pedido não encontrado.']);
            exit;
        }

        $resposta = [
            'success' => true,
            'pedido' => [
                'id' => (int)$pedido['id_pedido'],
                'cliente' => self::primeiroNome($pedido['nome_cliente']),
                'endereco' => $pedido['endereco'],
                'bairro' => $pedido['bairro'],
            ],
            'destino' => $pedido['latitude'] !== null
                ? ['lat' => (float)$pedido['latitude'], 'lng' => (float)$pedido['longitude']]
                : null,
            'entregador' => null,
            'previsao' => null,
            'agora' => EtaService::agora(),
        ];

        if ($pedido['status'] === 'entregue') {
            $resposta['etapa'] = 'entregue';
            $resposta['entregue_em'] = $pedido['entregue_em'] ? substr($pedido['entregue_em'], 11, 5) : null;
        } elseif (empty($pedido['id_entregador']) || $pedido['status'] === 'Pendente') {
            $resposta['etapa'] = 'recebido';
        } else {
            $idEntregador = (int)$pedido['id_entregador'];
            $pendentes = $this->pedidoModel->getAllByEntregador($idEntregador);
            $previsoes = $this->etaService->previsoesDoEntregador($idEntregador, $pendentes);
            $p = $previsoes[(int)$pedido['id_pedido']] ?? null;

            $mapaEtapas = ['a_caminho' => 'a_caminho', 'chegou' => 'chegou', 'fila' => 'saiu', 'aguardando' => 'saiu'];
            $resposta['etapa'] = $p ? $mapaEtapas[$p['tipo']] : 'saiu';
            if ($p) {
                $resposta['previsao'] = [
                    'tipo' => $p['tipo'],
                    'chega_em' => $p['chega_em_hora'],
                    'restante_s' => $p['restante_s'],
                    'entregas_antes' => $p['entregas_antes'],
                    'sinal_ha_s' => $p['sinal_ha_s'],
                    'fonte' => $p['fonte'],
                ];
            }

            // Posição do entregador só quando ele está vindo para ESTE endereço
            if ($p && in_array($p['tipo'], ['a_caminho', 'chegou'], true) && $pedido['entregador_lat'] !== null) {
                $resposta['entregador'] = [
                    'nome' => self::primeiroNome($pedido['nome_entregador']),
                    'lat' => (float)$pedido['entregador_lat'],
                    'lng' => (float)$pedido['entregador_lng'],
                ];
            } elseif (!empty($pedido['nome_entregador'])) {
                $resposta['entregador'] = ['nome' => self::primeiroNome($pedido['nome_entregador'])];
            }
        }

        echo json_encode($resposta);
        exit;
    }

    private static function primeiroNome(?string $nome): string
    {
        $partes = preg_split('/\s+/', trim((string)$nome));
        return $partes[0] ?? '';
    }
}

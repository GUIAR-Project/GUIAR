<?php

namespace mvc\Controllers;

use mvc\Models\Entregador;
use mvc\Models\Administrador;
use mvc\Models\Empresa;

class EntregadorController
{
    private $entregadorModel;
    private $administradorModel;
    private $empresaModel;
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->entregadorModel = new Entregador($pdo);
        $this->administradorModel = new Administrador($pdo);
        $this->empresaModel = new Empresa($pdo);
    }

    private function checkAuth()
    {
        if (!isset($_SESSION['company_id'])) {
            header("Location: " . BASE_URL . "/routes.php?action=loginEmpresa");
            exit;
        }
        if (!isset($_SESSION['id_adm']) && !isset($_SESSION['nome_usuario'])) {
            header("Location: " . BASE_URL . "/routes.php?action=escolherAdm");
            exit;
        }
    }

    public function index()
    {
        $this->checkAuth();
        $company_id = $_SESSION['company_id'];
        $nomeAdmin = $_SESSION['nome_usuario'] ?? '';
        $adminFotoUrl = $this->administradorModel->getPhotoUrlForCompany($_SESSION['id_adm'] ?? 0, $company_id);
        $logoEmpresaSidebar = $this->empresaModel->getLogoUrlById($company_id);

        $result = $this->entregadorModel->getAllByEmpresa($company_id);
        require_once __DIR__ . '/../Views/Administrador/entregadores.php';
    }

    public function cadastrar()
    {
        $this->checkAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $company_id = $_SESSION['company_id'];

            $diretorio_3x4 = __DIR__ . '/../../public/uploads/entregadores/fotos/';
            $diretorio_CNH = __DIR__ . '/../../public/uploads/entregadores/CNH/';

            if (!is_dir($diretorio_3x4)) {
                mkdir($diretorio_3x4, 0777, true);
            }
            if (!is_dir($diretorio_CNH)) {
                mkdir($diretorio_CNH, 0777, true);
            }

            $foto_3x4 = $_FILES['foto_3x4'];
            $foto_CNH = $_FILES['foto_CNH'];

            // Nome seguro
            $nome_foto_3x4 = uniqid() . '_' . basename($foto_3x4['name']);
            $nome_foto_CNH = uniqid() . '_' . basename($foto_CNH['name']);

            $caminho_3x4 = $diretorio_3x4 . $nome_foto_3x4;
            $caminho_CNH = $diretorio_CNH . $nome_foto_CNH;

            move_uploaded_file($foto_3x4['tmp_name'], $caminho_3x4);
            move_uploaded_file($foto_CNH['tmp_name'], $caminho_CNH);

            $data = [
                'nome_completo' => $_POST['nome_completo'] ?? '',
                'cpf' => $_POST['cpf'] ?? '',
                'telefone' => $_POST['telefone'] ?? '',
                'email' => $_POST['email'] ?? '',
                'nome_usuario' => $_POST['nome_usuario'] ?? '',
                'senha' => $_POST['senha'] ?? '',
                'foto_3x4' => $nome_foto_3x4,
                'foto_CNH' => $nome_foto_CNH,
                'company_id' => $company_id
            ];

            if ($this->entregadorModel->create($data)) {
                header("Location: " . BASE_URL . "/routes.php?action=entregadores");
            } else {
                echo "Erro ao adicionar entregador.";
            }
            exit;
        }

        // Se acessar por GET acidentalmente, redireciona para a listagem
        header("Location: " . BASE_URL . "/routes.php?action=entregadores");
        exit;
    }

    public function editar()
    {
        $this->checkAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $company_id = $_SESSION['company_id'];
            $id = $_POST['id'] ?? null;

            $data = [
                'nome_completo' => $_POST['nome_completo'] ?? '',
                'cpf' => $_POST['CPF'] ?? '',
                'telefone' => $_POST['telefone'] ?? '',
                'email' => $_POST['email'] ?? ''
            ];

            if ($this->entregadorModel->update($id, $company_id, $data)) {
                header("Location: " . BASE_URL . "/routes.php?action=entregadores");
            } else {
                echo "Erro ao editar entregador.";
            }
            exit;
        }
    }

    public function excluir()
    {
        $this->checkAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
            $company_id = $_SESSION['company_id'];
            $id = $_GET['id'];

            if ($this->entregadorModel->delete($id, $company_id)) {
                header("Location: " . BASE_URL . "/routes.php?action=entregadores");
            } else {
                echo "Erro ao excluir entregador.";
            }
            exit;
        }
    }

    public function login()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_SESSION['entregador_id']) && !empty($_SESSION['entregador_id'])) {
            header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
            header("Location: " . BASE_URL . "/routes.php?action=mapaEntregador");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $senha = $_POST['senha'] ?? '';

            $user = $this->entregadorModel->login($email, $senha);

            if ($user) {
                $_SESSION['entregador_id'] = $user['id_entregador'];
                session_write_close();
                header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
                header("Location: " . BASE_URL . "/routes.php?action=mapaEntregador");
                exit;
            } else {
                header("Location: " . BASE_URL . "/routes.php?action=loginEntregador&erro=" . urlencode('Email ou senha incorreto'));
                exit;
            }
        }

        require_once __DIR__ . '/../Views/Entregador/login.php';
    }

    public function logout()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        // Limpa todas as variáveis da sessão
        $_SESSION = [];

        // Exclui o cookie de sessão do navegador
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        // Destrói a sessão no servidor
        session_destroy();

        header("Location: " . BASE_URL . "/routes.php?action=loginEntregador");
        exit;
    }

    private function carregarContextoEntregador(int $entregadorId): array
    {
        $entregador = $this->entregadorModel->getById($entregadorId) ?: [];

        $empresaId = $entregador['FK_EMPRESA_id_empresa']
            ?? $entregador['fk_empresa_id_empresa']
            ?? $entregador['id_empresa']
            ?? ($_SESSION['company_id'] ?? null);

        $empresa = $empresaId ? ($this->empresaModel->getById($empresaId) ?: []) : [];
        $logoEmpresaSidebar = $empresaId ? $this->empresaModel->getLogoUrlById($empresaId) : '';
        $nomeEmpresaSidebar = $empresa['nome_empresa'] ?? '';
        $nomeEntregador = $entregador['nome_completo'] ?? ($entregador['nome_usuario'] ?? '');
        $entregadorFotoUrl = $entregador['foto_3x4'] ?? '';

        return [
            'entregador' => $entregador,
            'empresa' => $empresa,
            'logoEmpresaSidebar' => $logoEmpresaSidebar,
            'nomeEmpresaSidebar' => $nomeEmpresaSidebar,
            'nomeEntregador' => $nomeEntregador,
            'entregadorFotoUrl' => $entregadorFotoUrl,
        ];
    }

    public function meusPedidos()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['entregador_id'])) {
            header("Location: " . BASE_URL . "/routes.php?action=loginEntregador");
            exit;
        }

        $entregadorId = (int) $_SESSION['entregador_id'];
        $contexto = $this->carregarContextoEntregador($entregadorId);
        extract($contexto);

        $result = $this->buscarPedidosAtribuidosEntregador($entregadorId);

        require_once __DIR__ . '/../Views/Entregador/meusPedidos.php';
    }

    public function mapa()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['entregador_id'])) {
            header("Location: " . BASE_URL . "/routes.php?action=loginEntregador");
            exit;
        }

        $entregadorId = (int) $_SESSION['entregador_id'];
        $contexto = $this->carregarContextoEntregador($entregadorId);
        extract($contexto);

        $pedidosMapaIniciais = $this->buscarPedidosAtribuidosEntregador($entregadorId);

        require_once __DIR__ . '/../Views/Entregador/mapa.php';
    }

    private function buscarPedidosAtribuidosEntregador(int $entregadorId): array
    {
        try {
            $tablesStmt = $this->pdo->query("SHOW TABLES");
            $tables = $tablesStmt ? $tablesStmt->fetchAll(\PDO::FETCH_COLUMN) : [];
            $tablesLower = array_map('strtolower', $tables);

            $pedidoTable = null;
            foreach (['pedido', 'pedidos', 'entrega', 'entregas'] as $candTable) {
                $idx = array_search($candTable, $tablesLower, true);
                if ($idx !== false) {
                    $pedidoTable = $tables[$idx];
                    break;
                }
            }
            if (!$pedidoTable) {
                return [];
            }

            $colsStmt = $this->pdo->query("SHOW COLUMNS FROM `{$pedidoTable}`");
            $pedidoCols = $colsStmt ? $colsStmt->fetchAll(\PDO::FETCH_COLUMN) : [];

            $findCol = function (array $cols, array $candidates, ?string $contains = null): ?string {
                foreach ($candidates as $cand) {
                    foreach ($cols as $col) {
                        if (strcasecmp($col, $cand) === 0) {
                            return $col;
                        }
                    }
                }
                if ($contains !== null) {
                    foreach ($cols as $col) {
                        if (stripos($col, $contains) !== false) {
                            return $col;
                        }
                    }
                }
                return null;
            };

            $colId = $findCol($pedidoCols, ['id_pedido', 'id']);
            $colEntregador = $findCol($pedidoCols, ['FK_ENTREGADOR_id_entregador', 'fk_entregador_id_entregador', 'id_entregador', 'entregador_id'], 'entregador');
            if (!$colId || !$colEntregador) {
                return [];
            }

            $colStatus = $findCol($pedidoCols, ['status', 'status_pedido', 'situacao'], 'status');
            $colClienteNome = $findCol($pedidoCols, ['nome_cliente', 'cliente_nome', 'destinatario', 'nome']);
            $colPreco = $findCol($pedidoCols, ['preco', 'valor_total', 'valor', 'total', 'valor_pedido']);
            $colDescricao = $findCol($pedidoCols, ['descricao', 'observacao', 'observacoes', 'itens', 'detalhes']);
            $colClienteFk = $findCol($pedidoCols, ['FK_CLIENTE_id_cliente', 'fk_cliente_id_cliente', 'id_cliente'], 'cliente');
            $colEnderecoFk = $findCol($pedidoCols, ['FK_ENDERECO_id_endereco', 'fk_endereco_id_endereco', 'id_endereco'], 'endereco');

            $colEndereco = $findCol($pedidoCols, ['endereco', 'endereco_entrega', 'endereco_completo']);
            $colRua = $findCol($pedidoCols, ['rua', 'logradouro']);
            $colNumero = $findCol($pedidoCols, ['numero', 'num']);
            $colBairro = $findCol($pedidoCols, ['bairro']);
            $colCidade = $findCol($pedidoCols, ['cidade', 'municipio']);
            $colUf = $findCol($pedidoCols, ['estado', 'uf']);
            $colCep = $findCol($pedidoCols, ['cep']);
            $colLat = $findCol($pedidoCols, ['latitude', 'lat']);
            $colLng = $findCol($pedidoCols, ['longitude', 'lng', 'lon']);

            $joins = [];
            $clienteExpr = $colClienteNome ? "p.`{$colClienteNome}`" : "'Cliente'";

            foreach (['cliente', 'clientes'] as $candCliente) {
                $clienteIdx = array_search($candCliente, $tablesLower, true);
                if ($clienteIdx !== false && $colClienteFk) {
                    $clienteTable = $tables[$clienteIdx];
                    $cCols = $this->pdo->query("SHOW COLUMNS FROM `{$clienteTable}`")->fetchAll(\PDO::FETCH_COLUMN);
                    $cId = $findCol($cCols, ['id_cliente', 'id']);
                    $cNome = $findCol($cCols, ['nome_completo', 'nome', 'nome_cliente'], 'nome');
                    if ($cId && $cNome) {
                        $joins[] = "LEFT JOIN `{$clienteTable}` c ON p.`{$colClienteFk}` = c.`{$cId}`";
                        $clienteExpr = $colClienteNome
                            ? "COALESCE(NULLIF(TRIM(p.`{$colClienteNome}`), ''), c.`{$cNome}`, 'Cliente')"
                            : "COALESCE(c.`{$cNome}`, 'Cliente')";
                    }
                    break;
                }
            }

            $endAlias = 'p';
            $eCols = $pedidoCols;
            foreach (['endereco', 'enderecos'] as $candEnd) {
                $enderecoIdx = array_search($candEnd, $tablesLower, true);
                if ($enderecoIdx !== false && $colEnderecoFk) {
                    $enderecoTable = $tables[$enderecoIdx];
                    $eColsCandidate = $this->pdo->query("SHOW COLUMNS FROM `{$enderecoTable}`")->fetchAll(\PDO::FETCH_COLUMN);
                    $eId = $findCol($eColsCandidate, ['id_endereco', 'id']);
                    if ($eId) {
                        $joins[] = "LEFT JOIN `{$enderecoTable}` e ON p.`{$colEnderecoFk}` = e.`{$eId}`";
                        $endAlias = 'e';
                        $eCols = $eColsCandidate;
                    }
                    break;
                }
            }

            if ($endAlias === 'e') {
                $colEndereco = $findCol($eCols, ['endereco', 'endereco_completo', 'logradouro_completo']) ?? $colEndereco;
                $colRua = $findCol($eCols, ['rua', 'logradouro']) ?? $colRua;
                $colNumero = $findCol($eCols, ['numero', 'num']) ?? $colNumero;
                $colBairro = $findCol($eCols, ['bairro']) ?? $colBairro;
                $colCidade = $findCol($eCols, ['cidade', 'municipio']) ?? $colCidade;
                $colUf = $findCol($eCols, ['estado', 'uf']) ?? $colUf;
                $colCep = $findCol($eCols, ['cep']) ?? $colCep;
                $colLat = $findCol($eCols, ['latitude', 'lat']) ?? $colLat;
                $colLng = $findCol($eCols, ['longitude', 'lng', 'lon']) ?? $colLng;
            }

            $partesEndereco = [];
            foreach ([$colRua, $colNumero, $colBairro, $colCidade, $colUf, $colCep] as $cEnd) {
                if ($cEnd) {
                    $partesEndereco[] = "NULLIF(TRIM({$endAlias}.`{$cEnd}`), '')";
                }
            }

            if ($colEndereco && !empty($partesEndereco)) {
                $enderecoExpr = "COALESCE(NULLIF(TRIM({$endAlias}.`{$colEndereco}`), ''), CONCAT_WS(', ', " . implode(', ', $partesEndereco) . "), 'Endereço não informado')";
            } elseif ($colEndereco) {
                $enderecoExpr = "COALESCE(NULLIF(TRIM({$endAlias}.`{$colEndereco}`), ''), 'Endereço não informado')";
            } elseif (!empty($partesEndereco)) {
                $enderecoExpr = "COALESCE(CONCAT_WS(', ', " . implode(', ', $partesEndereco) . "), 'Endereço não informado')";
            } else {
                $enderecoExpr = "'Endereço não informado'";
            }

            $bairroExpr = $colBairro ? "COALESCE(NULLIF(TRIM({$endAlias}.`{$colBairro}`), ''), '')" : "''";
            $precoExpr = $colPreco ? "COALESCE(p.`{$colPreco}`, '0,00')" : "'0,00'";
            $descricaoExpr = $colDescricao ? "COALESCE(NULLIF(TRIM(p.`{$colDescricao}`), ''), 'Sem descrição')" : "'Sem descrição'";
            $latExpr = $colLat ? "{$endAlias}.`{$colLat}`" : "NULL";
            $lngExpr = $colLng ? "{$endAlias}.`{$colLng}`" : "NULL";

            $where = ["p.`{$colEntregador}` = :entregador_id"];
            if ($colStatus) {
                $where[] = "(p.`{$colStatus}` IS NULL OR LOWER(TRIM(p.`{$colStatus}`)) NOT IN ('entregue', 'entregues', 'concluido', 'concluído', 'finalizado', 'cancelado', 'cancelada'))";
            }

            $sql = "SELECT
                        p.`{$colId}` AS id_pedido,
                        {$clienteExpr} AS nome_cliente,
                        {$precoExpr} AS preco,
                        {$enderecoExpr} AS endereco,
                        {$bairroExpr} AS bairro,
                        {$descricaoExpr} AS descricao,
                        {$latExpr} AS latitude,
                        {$lngExpr} AS longitude
                    FROM `{$pedidoTable}` p
                    " . implode("\n", $joins) . "
                    WHERE " . implode(" AND ", $where) . "
                    ORDER BY p.`{$colId}` ASC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':entregador_id', $entregadorId, \PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log("Erro ao buscar pedidos atribuídos: " . $e->getMessage());
            return [];
        }
    }

    public function atualizarLocalizacao()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['entregador_id'])) {
            echo json_encode(['success' => false, 'error' => 'Não autenticado']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents("php://input"), true);
            $latitude = $data['latitude'] ?? null;
            $longitude = $data['longitude'] ?? null;

            if ($latitude !== null && $longitude !== null) {
                if ($this->entregadorModel->updateLocation($_SESSION['entregador_id'], $latitude, $longitude)) {
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Erro ao salvar no banco']);
                }
            } else {
                echo json_encode(['success' => false, 'error' => 'Dados de localização incompletos']);
            }
            exit;
        }
    }

    public function perfil()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['entregador_id'])) {
            header("Location: " . BASE_URL . "/routes.php?action=loginEntregador");
            exit;
        }

        $entregador = $this->entregadorModel->getById($_SESSION['entregador_id']);
        if (!$entregador) {
            header("Location: " . BASE_URL . "/routes.php?action=loginEntregador");
            exit;
        }

        $erro = $_GET['erro'] ?? null;
        $sucesso = $_GET['sucesso'] ?? null;

        require_once __DIR__ . '/../Views/Entregador/meuPerfil.php';
    }

    public function editarPerfil()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['entregador_id'])) {
            header("Location: " . BASE_URL . "/routes.php?action=loginEntregador");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_SESSION['entregador_id'];
            $current = $this->entregadorModel->getById($id);

            $diretorio_3x4 = __DIR__ . '/../../public/uploads/entregadores/fotos/';
            $diretorio_CNH = __DIR__ . '/../../public/uploads/entregadores/CNH/';

            if (!is_dir($diretorio_3x4)) {
                mkdir($diretorio_3x4, 0777, true);
            }
            if (!is_dir($diretorio_CNH)) {
                mkdir($diretorio_CNH, 0777, true);
            }

            $foto_3x4_nome = $current['foto_3x4'] ?? '';
            if (isset($_FILES['foto_3x4']) && $_FILES['foto_3x4']['error'] === 0) {
                $foto_3x4_nome = uniqid() . '_' . basename($_FILES['foto_3x4']['name']);
                move_uploaded_file($_FILES['foto_3x4']['tmp_name'], $diretorio_3x4 . $foto_3x4_nome);
            }

            $foto_CNH_nome = $current['foto_CNH'] ?? '';
            if (isset($_FILES['foto_CNH']) && $_FILES['foto_CNH']['error'] === 0) {
                $foto_CNH_nome = uniqid() . '_' . basename($_FILES['foto_CNH']['name']);
                move_uploaded_file($_FILES['foto_CNH']['tmp_name'], $diretorio_CNH . $foto_CNH_nome);
            }

            $senha = $_POST['senha'] ?? '';
            if (empty($senha)) {
                $senha = $current['senha'];
            }

            $data = [
                'nome_completo' => $_POST['nome_completo'] ?? '',
                'cpf' => $_POST['cpf'] ?? '',
                'telefone' => $_POST['telefone'] ?? '',
                'email' => $_POST['email'] ?? '',
                'nome_usuario' => $_POST['nome_usuario'] ?? '',
                'senha' => $senha,
                'foto_3x4' => $foto_3x4_nome,
                'foto_CNH' => $foto_CNH_nome
            ];

            if ($this->entregadorModel->updateProfile($id, $data)) {
                header("Location: " . BASE_URL . "/routes.php?action=perfilEntregador&sucesso=" . urlencode("Perfil atualizado com sucesso!"));
            } else {
                header("Location: " . BASE_URL . "/routes.php?action=perfilEntregador&erro=" . urlencode("Erro ao atualizar perfil."));
            }
            exit;
        }
    }
}
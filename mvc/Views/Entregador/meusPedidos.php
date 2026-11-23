<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Pedidos | Entregador</title>
    <link rel="shortcut icon" type="image/png" href="<?= BASE_URL ?>/img/logoIcon.png">

    <!-- Google Fonts: Inter & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?= BASE_URL ?>/style/sidebar.css">
    <style>
        .btn-emp-primary {
            background-color: var(--emp-primary, #4F46E5);
            color: var(--emp-primary-contrast, #ffffff);
            box-shadow: 0 8px 18px -4px var(--emp-primary-ring, rgba(79, 70, 229, 0.35));
        }

        .btn-emp-primary:hover {
            filter: brightness(0.95);
        }
    </style>
</head>

<body class="sidebar-page min-h-screen flex flex-col md:flex-row bg-[#f1f4fa] text-[#0F172A] overflow-x-hidden">

    <?php
    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }

    // 1. RESOLUÇÃO DO NOME DO ENTREGADOR
    $nomeEntregadorResolvido = '';
    $candidatosNome = [
        $nomeEntregador ?? null,
        $entregador['nome_completo'] ?? null,
        $entregador['nome'] ?? null,
        $_SESSION['entregador']['nome_completo'] ?? null,
        $_SESSION['entregador']['nome'] ?? null,
        $_SESSION['nome_entregador'] ?? null,
        $_SESSION['entregador_nome'] ?? null,
        $_SESSION['nome_usuario'] ?? null,
        $_SESSION['nome_completo'] ?? null,
        $_SESSION['nome'] ?? null,
    ];
    foreach ($candidatosNome as $cand) {
        if (!empty($cand) && is_string($cand) && trim($cand) !== '') {
            $nomeEntregadorResolvido = trim($cand);
            break;
        }
    }

    // 2. RESOLUÇÃO DA FOTO DO ENTREGADOR
    $entregadorFotoUrl = '';
    $rawFoto = $entregador["foto_3x4"]
        ?? ($entregador["foto"]
        ?? ($_SESSION['entregador']['foto_3x4']
        ?? ($_SESSION['foto_entregador'] ?? '')));
    if (!empty($rawFoto) && is_string($rawFoto)) {
        $rawFoto = trim($rawFoto);
        if (preg_match('#^(https?:)?//#i', $rawFoto) || strpos($rawFoto, 'data:') === 0) {
            $entregadorFotoUrl = $rawFoto;
        } elseif (strpos($rawFoto, BASE_URL) === 0) {
            $entregadorFotoUrl = $rawFoto;
        } else {
            $entregadorFotoUrl = rtrim(BASE_URL, '/') . '/public/uploads/entregadores/fotos/' . ltrim($rawFoto, '/');
        }
    }

    // 3. LOGO E NOME DA EMPRESA
    $logoEmpresaResolvida = '';
    $candidatosLogo = [
        $logoEmpresaSidebar ?? null,
        $logoEmpresa ?? null,
        $empresa['logo_url'] ?? null,
        $empresa['logo'] ?? null,
        $empresa['logo_empresa'] ?? null,
        $entregador['logo_empresa'] ?? null,
        $entregador['empresa_logo'] ?? null,
        $_SESSION['logoEmpresaSidebar'] ?? null,
        $_SESSION['logo_empresa'] ?? null,
        $_SESSION['empresa_logo'] ?? null,
        $_SESSION['empresa']['logo_url'] ?? null,
        $_SESSION['empresa']['logo'] ?? null,
    ];
    foreach ($candidatosLogo as $candLogo) {
        if (!empty($candLogo) && is_string($candLogo) && trim($candLogo) !== '') {
            $logoEmpresaResolvida = trim($candLogo);
            break;
        }
    }

    $nomeEmpresaResolvido = '';
    $candidatosEmpresaNome = [
        $nomeEmpresaSidebar ?? null,
        $nomeEmpresa ?? null,
        $empresa['nome_empresa'] ?? null,
        $empresa['nome_fantasia'] ?? null,
        $empresa['nome'] ?? null,
        $entregador['nome_empresa'] ?? null,
        $_SESSION['nome_empresa'] ?? null,
        $_SESSION['empresa_nome'] ?? null,
        $_SESSION['empresa']['nome_empresa'] ?? null,
        $_SESSION['empresa']['nome'] ?? null,
    ];
    foreach ($candidatosEmpresaNome as $candEmpNome) {
        if (!empty($candEmpNome) && is_string($candEmpNome) && trim($candEmpNome) !== '') {
            $nomeEmpresaResolvido = trim($candEmpNome);
            break;
        }
    }

    $normalizarUrlAsset = function ($path) {
        if (empty($path)) return '';
        if (preg_match('#^(https?:)?//#i', $path) || strpos($path, 'data:') === 0) {
            return $path;
        }
        if (strpos($path, BASE_URL) === 0) {
            return $path;
        }
        return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
    };

    $precisaSincronizarSidebar = ($nomeEntregadorResolvido === '' || $logoEmpresaResolvida === '');
    $nomeEntregador = $nomeEntregadorResolvido !== '' ? $nomeEntregadorResolvido : 'Entregador';
    $logoFallback = BASE_URL . '/img/LogoPreto.png';
    $logoEmpresaSidebar = $logoEmpresaResolvida !== ''
        ? $normalizarUrlAsset($logoEmpresaResolvida)
        : $logoFallback;
    $nomeEmpresaSidebar = $nomeEmpresaResolvido !== '' ? $nomeEmpresaResolvido : 'GUIAR Entregas';

    // 4. NORMALIZAÇÃO DOS PEDIDOS ATRIBUÍDOS ($result / $pedidos / PDOStatement / fallback)
    if (!isset($result) && isset($pedidos)) {
        $result = $pedidos;
    }

    if (isset($result) && $result instanceof \PDOStatement) {
        $result = $result->fetchAll(\PDO::FETCH_ASSOC);
    }

    if (!isset($result) && !empty($_SESSION['entregador_id'])) {
        $pdoRef = $pdo ?? ($GLOBALS['pdo'] ?? null);
        if ($pdoRef instanceof \PDO) {
            try {
                $entregadorSessId = (int) $_SESSION['entregador_id'];
                $tables = $pdoRef->query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN) ?: [];
                $tablesLower = array_map('strtolower', $tables);

                $pedidoTable = null;
                foreach (['pedido', 'pedidos', 'entrega', 'entregas'] as $candTable) {
                    $idx = array_search($candTable, $tablesLower, true);
                    if ($idx !== false) {
                        $pedidoTable = $tables[$idx];
                        break;
                    }
                }

                if ($pedidoTable) {
                    $pCols = $pdoRef->query("SHOW COLUMNS FROM `{$pedidoTable}`")->fetchAll(\PDO::FETCH_COLUMN) ?: [];
                    $findCol = function (array $cols, array $candidates, ?string $contains = null): ?string {
                        foreach ($candidates as $cand) {
                            foreach ($cols as $col) {
                                if (strcasecmp($col, $cand) === 0) return $col;
                            }
                        }
                        if ($contains !== null) {
                            foreach ($cols as $col) {
                                if (stripos($col, $contains) !== false) return $col;
                            }
                        }
                        return null;
                    };

                    $colId = $findCol($pCols, ['id_pedido', 'id']);
                    $colEnt = $findCol($pCols, ['FK_ENTREGADOR_id_entregador', 'fk_entregador_id_entregador', 'id_entregador', 'entregador_id'], 'entregador');

                    if ($colId && $colEnt) {
                        $colStatus = $findCol($pCols, ['status', 'status_pedido', 'situacao'], 'status');
                        $colCliente = $findCol($pCols, ['nome_cliente', 'cliente_nome', 'destinatario', 'nome']);
                        $colClienteFk = $findCol($pCols, ['FK_CLIENTE_id_cliente', 'fk_cliente_id_cliente', 'id_cliente'], 'cliente');
                        $colEndFk = $findCol($pCols, ['FK_ENDERECO_id_endereco', 'fk_endereco_id_endereco', 'id_endereco'], 'endereco');
                        $colPreco = $findCol($pCols, ['preco', 'valor_total', 'valor', 'total', 'valor_pedido']);
                        $colDesc = $findCol($pCols, ['descricao', 'observacao', 'observacoes', 'itens', 'detalhes']);
                        $colEnd = $findCol($pCols, ['endereco', 'endereco_entrega', 'endereco_completo']);
                        $colRua = $findCol($pCols, ['rua', 'logradouro']);
                        $colNum = $findCol($pCols, ['numero', 'num']);
                        $colBairro = $findCol($pCols, ['bairro']);
                        $colCidade = $findCol($pCols, ['cidade', 'municipio']);

                        $joins = [];
                        $clienteExpr = $colCliente ? "p.`{$colCliente}`" : "'Cliente'";
                        foreach (['cliente', 'clientes'] as $candCli) {
                            $cIdx = array_search($candCli, $tablesLower, true);
                            if ($cIdx !== false && $colClienteFk) {
                                $cTable = $tables[$cIdx];
                                $cCols = $pdoRef->query("SHOW COLUMNS FROM `{$cTable}`")->fetchAll(\PDO::FETCH_COLUMN) ?: [];
                                $cId = $findCol($cCols, ['id_cliente', 'id']);
                                $cNome = $findCol($cCols, ['nome_completo', 'nome', 'nome_cliente'], 'nome');
                                if ($cId && $cNome) {
                                    $joins[] = "LEFT JOIN `{$cTable}` c ON p.`{$colClienteFk}` = c.`{$cId}`";
                                    $clienteExpr = $colCliente
                                        ? "COALESCE(NULLIF(TRIM(p.`{$colCliente}`), ''), c.`{$cNome}`, 'Cliente')"
                                        : "COALESCE(c.`{$cNome}`, 'Cliente')";
                                }
                                break;
                            }
                        }

                        $endAlias = 'p';
                        $eCols = $pCols;
                        foreach (['endereco', 'enderecos'] as $candEnd) {
                            $eIdx = array_search($candEnd, $tablesLower, true);
                            if ($eIdx !== false && $colEndFk) {
                                $eTable = $tables[$eIdx];
                                $eColsCand = $pdoRef->query("SHOW COLUMNS FROM `{$eTable}`")->fetchAll(\PDO::FETCH_COLUMN) ?: [];
                                $eId = $findCol($eColsCand, ['id_endereco', 'id']);
                                if ($eId) {
                                    $joins[] = "LEFT JOIN `{$eTable}` e ON p.`{$colEndFk}` = e.`{$eId}`";
                                    $endAlias = 'e';
                                    $eCols = $eColsCand;
                                }
                                break;
                            }
                        }

                        if ($endAlias === 'e') {
                            $colEnd = $findCol($eCols, ['endereco', 'endereco_completo', 'logradouro_completo']) ?? $colEnd;
                            $colRua = $findCol($eCols, ['rua', 'logradouro']) ?? $colRua;
                            $colNum = $findCol($eCols, ['numero', 'num']) ?? $colNum;
                            $colBairro = $findCol($eCols, ['bairro']) ?? $colBairro;
                            $colCidade = $findCol($eCols, ['cidade', 'municipio']) ?? $colCidade;
                        }

                        $partes = [];
                        foreach ([$colRua, $colNum, $colCidade] as $cP) {
                            if ($cP) $partes[] = "NULLIF(TRIM({$endAlias}.`{$cP}`), '')";
                        }

                        if ($colEnd && !empty($partes)) {
                            $endExpr = "COALESCE(NULLIF(TRIM({$endAlias}.`{$colEnd}`), ''), CONCAT_WS(', ', " . implode(', ', $partes) . "), 'Endereço não informado')";
                        } elseif ($colEnd) {
                            $endExpr = "COALESCE(NULLIF(TRIM({$endAlias}.`{$colEnd}`), ''), 'Endereço não informado')";
                        } elseif (!empty($partes)) {
                            $endExpr = "COALESCE(CONCAT_WS(', ', " . implode(', ', $partes) . "), 'Endereço não informado')";
                        } else {
                            $endExpr = "'Endereço não informado'";
                        }

                        $bairroExpr = $colBairro ? "COALESCE(NULLIF(TRIM({$endAlias}.`{$colBairro}`), ''), '')" : "''";
                        $precoExpr = $colPreco ? "COALESCE(p.`{$colPreco}`, '0,00')" : "'0,00'";
                        $descExpr = $colDesc ? "COALESCE(NULLIF(TRIM(p.`{$colDesc}`), ''), 'Sem descrição')" : "'Sem descrição'";

                        $where = ["p.`{$colEnt}` = :entregador_id"];
                        if ($colStatus) {
                            $where[] = "(p.`{$colStatus}` IS NULL OR LOWER(TRIM(p.`{$colStatus}`)) NOT IN ('entregue', 'entregues', 'concluido', 'concluído', 'finalizado', 'cancelado', 'cancelada'))";
                        }

                        $sqlFallback = "SELECT p.`{$colId}` AS id_pedido, {$clienteExpr} AS nome_cliente, {$precoExpr} AS preco, {$endExpr} AS endereco, {$bairroExpr} AS bairro, {$descExpr} AS descricao
                                        FROM `{$pedidoTable}` p " . implode(' ', $joins) . "
                                        WHERE " . implode(' AND ', $where) . "
                                        ORDER BY p.`{$colId}` ASC";
                        $stmtFallback = $pdoRef->prepare($sqlFallback);
                        $stmtFallback->bindValue(':entregador_id', $entregadorSessId, \PDO::PARAM_INT);
                        $stmtFallback->execute();
                        $result = $stmtFallback->fetchAll(\PDO::FETCH_ASSOC) ?: [];
                    }
                }
            } catch (\Throwable $e) {
                $result = [];
            }
        }
    }

    $listaPedidos = is_array($result ?? null) ? $result : [];
    ?>

    <!-- SIDEBAR CLARA (DESKTOP) -->
    <aside id="sidebar"
        class="hidden md:flex w-72 bg-white flex-col fixed top-0 left-0 text-slate-700 z-50 transition-all rounded-none overflow-hidden h-screen border-r border-slate-200/80 shadow-sm">

        <div class="flex flex-col h-full">

            <!-- LOGO DA EMPRESA -->
            <div class="px-6 pt-7 pb-4 mb-2 flex justify-start items-center gap-3">
                <div style="width: 58px; height: 58px; border-radius: 50%; overflow: hidden; flex-shrink: 0; background: #fff; border: 2px solid var(--emp-primary-light-border, rgba(79,70,229,0.18)); box-shadow: 0 4px 14px var(--emp-primary-ring, rgba(79,70,229,0.12)); display: flex; align-items: center; justify-content: center; padding: 5px;">
                    <img id="sidebarEmpresaLogo" src="<?= htmlspecialchars($logoEmpresaSidebar, ENT_QUOTES, 'UTF-8') ?>"
                        onerror="this.onerror=null; this.src='<?= htmlspecialchars($logoFallback, ENT_QUOTES, 'UTF-8') ?>';"
                        alt="Logo da empresa"
                        style="width: 100%; height: 100%; object-fit: contain; object-position: center; display: block;">
                </div>
                <div class="min-w-0">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Painel do Entregador</p>
                    <p id="sidebarEmpresaNome" class="text-sm font-extrabold text-slate-800 truncate">
                        <?= htmlspecialchars($nomeEmpresaSidebar, ENT_QUOTES, 'UTF-8') ?>
                    </p>
                </div>
            </div>

            <!-- MENU DE NAVEGAÇÃO -->
            <nav class="sidebar-menu px-4 space-y-1.5 flex-grow">

                <!-- MEUS PEDIDOS (ATIVO) -->
                <a href="<?= BASE_URL ?>/routes.php?action=meusPedidosEntregador"
                    class="menu-item active flex items-center gap-3.5 px-4 py-3 rounded-xl font-bold text-sm transition-all"
                    style="background-color: var(--emp-primary, #4F46E5); color: var(--emp-primary-contrast, #ffffff); box-shadow: 0 8px 18px -4px var(--emp-primary-ring, rgba(79, 70, 229, 0.35));">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    Meus Pedidos
                </a>

                <!-- ABRIR MAPA -->
                <a href="<?= BASE_URL ?>/routes.php?action=mapaEntregador"
                    class="menu-item flex items-center gap-3.5 px-4 py-3 rounded-xl font-semibold text-sm text-slate-500 hover:text-slate-900 hover:bg-slate-100/80 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                    </svg>
                    Abrir Mapa
                </a>

                <!-- MEU PERFIL -->
                <a href="<?= BASE_URL ?>/routes.php?action=perfilEntregador"
                    class="menu-item flex items-center gap-3.5 px-4 py-3 rounded-xl font-semibold text-sm text-slate-500 hover:text-slate-900 hover:bg-slate-100/80 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Meu Perfil
                </a>
            </nav>

            <!-- FOOTER SIDEBAR -->
            <div class="p-4 mt-auto border-t border-slate-100">
                <div class="px-2 pb-3.5 flex flex-col items-center gap-1">
                    <img src="<?= BASE_URL ?>/img/LogoPreto.png" alt="GUIAR" class="w-20 h-auto object-contain opacity-90">
                    <p class="text-[10px] text-slate-400 text-center leading-tight">
                        Conectando você ao seu destino.
                    </p>
                </div>

                <div id="driverSidebarCard" class="admin-user-card flex items-center gap-3 p-2.5 rounded-2xl bg-slate-50 border border-slate-200/80">
                    <?php if (!empty($entregadorFotoUrl)): ?>
                        <img id="driverSidebarPhoto" src="<?= htmlspecialchars($entregadorFotoUrl, ENT_QUOTES, 'UTF-8') ?>"
                            alt="Foto de <?= htmlspecialchars($nomeEntregador, ENT_QUOTES, 'UTF-8') ?>"
                            class="w-9 h-9 rounded-full object-cover shadow-sm ring-2 ring-white flex-shrink-0">
                    <?php else: ?>
                        <div id="driverSidebarAvatar" class="admin-user-avatar w-9 h-9 rounded-full font-black text-xs flex items-center justify-center shadow-sm flex-shrink-0"
                            style="background-color: var(--emp-secondary, #FFD400); color: var(--emp-secondary-contrast, #0F172A);">
                            <?= strtoupper(substr($nomeEntregador, 0, 2)) ?>
                        </div>
                    <?php endif; ?>

                    <div class="flex-grow min-w-0">
                        <p id="driverSidebarName" class="admin-user-name font-bold text-xs text-slate-800 truncate">
                            <?= htmlspecialchars($nomeEntregador, ENT_QUOTES, 'UTF-8') ?>
                        </p>
                        <p class="admin-user-role text-[10px] text-slate-400 font-bold tracking-wider uppercase">
                            Entregador
                        </p>
                    </div>

                    <!-- LOGOUT -->
                    <a href="<?= BASE_URL ?>/routes.php?action=logoutEntregador" title="Sair da conta" aria-label="Sair da conta"
                        class="text-slate-400 hover:text-rose-500 transition-colors p-1.5 rounded-lg hover:bg-rose-50">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </aside>

    <!-- CONTEÚDO PRINCIPAL -->
    <div class="flex-1 min-w-0 flex flex-col min-h-screen md:ml-72">

        <!-- HEADER DA PÁGINA -->
        <header class="bg-white/80 backdrop-blur-md border-b border-slate-200/80 px-4 md:px-8 py-4 flex items-center justify-between gap-4 sticky top-0 z-20">
            <div class="flex items-center gap-3.5 min-w-0 flex-1">
                <!-- ÍCONE DA ABA -->
                <div class="flex items-center justify-center w-9 h-9 sm:w-auto sm:h-auto rounded-xl sm:rounded-none bg-indigo-50/80 sm:bg-transparent flex-shrink-0 transition-transform hover:scale-105"
                    style="color: var(--emp-primary, #4F46E5);">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                </div>

                <div class="min-w-0 flex-1">
                    <h1 class="heading-font text-lg md:text-xl font-extrabold text-slate-900 tracking-tight truncate">
                        Meus Pedidos
                    </h1>
                    <p class="text-xs md:text-sm text-slate-500 font-medium truncate">
                        Acompanhe e gerencie as entregas atribuídas a você
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3 flex-shrink-0">
                <button type="button" id="refreshPage" class="flex items-center gap-2 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs sm:text-sm px-3.5 sm:px-4 py-2 rounded-xl transition border border-slate-200 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span>Atualizar</span>
                </button>
            </div>
        </header>

        <!-- MAIN -->
        <main class="flex-grow p-4 sm:p-8 lg:px-12 xl:px-16 pb-24 lg:pb-12 space-y-8">

            <!-- Cards de Pedidos -->
            <div class="max-w-[1400px] mx-auto grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                <?php
                if (count($listaPedidos) > 0) {
                    foreach ($listaPedidos as $row) {
                        $idPedido = (string)($row['id_pedido'] ?? $row['id'] ?? '');
                        $nomeCliente = (string)($row['nome_cliente'] ?? $row['cliente_nome'] ?? $row['cliente'] ?? 'Cliente');
                        $precoBruto = $row['preco'] ?? $row['valor_total'] ?? $row['valor'] ?? $row['total'] ?? '0,00';
                        $precoFormatado = is_numeric($precoBruto)
                            ? number_format((float)$precoBruto, 2, ',', '.')
                            : (string)$precoBruto;
                        $endereco = (string)($row['endereco'] ?? $row['endereco_entrega'] ?? 'Endereço não informado');
                        $bairro = trim((string)($row['bairro'] ?? ''));
                        $descricao = (string)($row['descricao'] ?? $row['observacao'] ?? $row['observacoes'] ?? $row['itens'] ?? 'Sem descrição adicional');
                ?>
                        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm hover:shadow-md transition-shadow p-6 flex flex-col justify-between h-full relative overflow-hidden hover-card">
                            <div>
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-lg font-bold text-slate-900 truncate pr-4" title="<?= htmlspecialchars($nomeCliente, ENT_QUOTES, 'UTF-8') ?>">
                                        <?= htmlspecialchars($nomeCliente, ENT_QUOTES, 'UTF-8') ?>
                                    </h3>
                                    <span class="text-xs font-bold px-3 py-1 rounded-full border whitespace-nowrap"
                                        style="background-color: var(--emp-primary-light, #EEF2FF); color: var(--emp-primary, #4F46E5); border-color: var(--emp-primary-light-border, #C7D2FE);">
                                        R$ <?= htmlspecialchars($precoFormatado, ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </div>

                                <div class="space-y-4 mt-4">
                                    <div class="flex items-start gap-3">
                                        <div class="bg-slate-50 p-2 rounded-lg flex-shrink-0">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-slate-700"><?= htmlspecialchars($endereco, ENT_QUOTES, 'UTF-8') ?></p>
                                            <?php if ($bairro !== ''): ?>
                                                <p class="text-xs text-slate-500 font-medium mt-0.5"><?= htmlspecialchars($bairro, ENT_QUOTES, 'UTF-8') ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="flex items-start gap-3">
                                        <div class="bg-slate-50 p-2 rounded-lg flex-shrink-0">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </div>
                                        <p class="text-sm text-slate-600 font-medium line-clamp-3 mt-1">
                                            <?= htmlspecialchars($descricao, ENT_QUOTES, 'UTF-8') ?>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6 pt-5 border-t border-slate-100">
                                <button type="button"
                                    class="btn-delivered btn-emp-primary w-full flex items-center justify-center gap-2 font-bold text-sm px-4 py-3 rounded-2xl transition-all hover:scale-[1.02] active:scale-95"
                                    data-id="<?= htmlspecialchars($idPedido, ENT_QUOTES, 'UTF-8') ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Marcar como Entregue
                                </button>
                            </div>
                        </div>
                    <?php
                    }
                } else {
                    ?>
                    <div class="col-span-full flex flex-col items-center justify-center bg-white rounded-3xl border border-slate-200/80 shadow-sm p-12 text-center">
                        <div class="bg-slate-50 p-6 rounded-full mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 mb-1">Nenhum pedido atribuído</h3>
                        <p class="text-sm text-slate-500 font-medium">Você não tem entregas pendentes no momento.</p>
                    </div>
                <?php
                }
                ?>
            </div>

        </main>

    </div>

    <!-- MOBILE BOTTOM NAVIGATION -->
    <nav class="lg:hidden fixed bottom-0 left-0 right-0 bg-white/95 backdrop-blur-md border-t border-slate-200/90 flex justify-around items-center h-16 z-30 shadow-lg">
        <a href="<?= BASE_URL ?>/routes.php?action=meusPedidosEntregador"
            class="flex flex-col items-center gap-0.5 text-[11px] font-bold transition-colors"
            style="color: var(--emp-primary, #4F46E5);">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
            </svg>
            Pedidos
        </a>

        <a href="<?= BASE_URL ?>/routes.php?action=mapaEntregador"
            class="flex flex-col items-center gap-0.5 text-[11px] text-slate-500 hover:text-slate-900 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 opacity-75" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
            </svg>
            Mapa
        </a>

        <a href="<?= BASE_URL ?>/routes.php?action=perfilEntregador"
            class="flex flex-col items-center gap-0.5 text-[11px] text-slate-500 hover:text-slate-900 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 opacity-75" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            Perfil
        </a>

        <a href="<?= BASE_URL ?>/routes.php?action=logoutEntregador"
            class="flex flex-col items-center gap-0.5 text-[11px] text-slate-500 hover:text-rose-500 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 opacity-75" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
            Sair
        </a>
    </nav>

    <!-- Scripts da Página -->
    <script>
        // Função para marcar pedido como entregue
        document.querySelectorAll('.btn-delivered').forEach(button => {
            button.addEventListener('click', function() {
                const pedidoId = this.getAttribute('data-id');
                const btn = this;

                // Feedback visual de carregamento
                const originalText = btn.innerHTML;
                btn.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Processando...';
                btn.disabled = true;

                fetch('<?= BASE_URL ?>/routes.php?action=concluirEntrega', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'id_pedido=' + encodeURIComponent(pedidoId)
                    })
                    .then(response => response.text())
                    .then(result => {
                        if (result.trim() === 'success') {
                            // Encontrar o card pai e animar a saída
                            const card = btn.closest('.hover-card');
                            if (card) {
                                card.style.transition = 'all 0.5s ease';
                                card.style.opacity = '0';
                                card.style.transform = 'scale(0.9)';
                            }

                            setTimeout(() => {
                                alert('Pedido marcado como entregue!');
                                location.reload();
                            }, 300);
                        } else {
                            alert('Erro ao marcar pedido como entregue.');
                            btn.innerHTML = originalText;
                            btn.disabled = false;
                        }
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        alert('Erro na requisição.');
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                    });
            });
        });

        // Função para atualizar a página
        document.getElementById('refreshPage').addEventListener('click', function() {
            const icon = this.querySelector('svg');
            icon.classList.add('animate-spin');

            setTimeout(() => {
                location.reload();
            }, 300);
        });

        <?php if ($precisaSincronizarSidebar): ?>
        // Sincroniza nome do entregador, foto e dados da empresa a partir do perfil
        (function sincronizarDadosSidebarEntregador() {
            fetch('<?= BASE_URL ?>/routes.php?action=perfilEntregador')
                .then(res => res.text())
                .then(html => {
                    const doc = new DOMParser().parseFromString(html, 'text/html');
                    const remoteSidebar = doc.getElementById('sidebar');
                    if (!remoteSidebar) return;

                    const remoteLogo = remoteSidebar.querySelector('#sidebarEmpresaLogo, img[alt*="empresa" i]');
                    const localLogo = document.getElementById('sidebarEmpresaLogo');
                    if (remoteLogo && remoteLogo.src && localLogo && !remoteLogo.src.includes('LogoGuiar') && !remoteLogo.src.includes('LogoPreto') && !remoteLogo.src.includes('logoIcon')) {
                        localLogo.src = remoteLogo.src;
                        window.GUIAR_EMPRESA_LOGO = remoteLogo.src;

                        const oldScript = document.querySelector('script[src*="empresa-palette.js"]');
                        if (oldScript) {
                            const freshScript = document.createElement('script');
                            freshScript.src = oldScript.src.split('?')[0] + '?v=' + Date.now();
                            document.body.appendChild(freshScript);
                        }
                    }

                    const remoteTitle = remoteSidebar.querySelector('#sidebarEmpresaNome, p.font-extrabold');
                    const localTitle = document.getElementById('sidebarEmpresaNome');
                    if (remoteTitle && remoteTitle.textContent.trim() && localTitle) {
                        localTitle.textContent = remoteTitle.textContent.trim();
                    }

                    const remoteName = remoteSidebar.querySelector('#driverSidebarName, .admin-user-name');
                    const localName = document.getElementById('driverSidebarName');
                    const localAvatar = document.getElementById('driverSidebarAvatar');
                    if (remoteName && remoteName.textContent.trim() && localName) {
                        const nomeReal = remoteName.textContent.trim();
                        localName.textContent = nomeReal;
                        if (localAvatar) {
                            const iniciais = nomeReal.split(/\s+/).map(p => p[0]).slice(0, 2).join('').toUpperCase();
                            localAvatar.textContent = iniciais || nomeReal.slice(0, 2).toUpperCase();
                        }
                    }

                    const remotePhoto = remoteSidebar.querySelector('#driverSidebarPhoto, .admin-user-card img');
                    const localCard = document.getElementById('driverSidebarCard');
                    if (remotePhoto && remotePhoto.src && localCard && !document.getElementById('driverSidebarPhoto')) {
                        const img = document.createElement('img');
                        img.id = 'driverSidebarPhoto';
                        img.src = remotePhoto.src;
                        img.alt = 'Foto do entregador';
                        img.className = 'w-9 h-9 rounded-full object-cover shadow-sm ring-2 ring-white flex-shrink-0';
                        if (localAvatar) localAvatar.replaceWith(img);
                    }
                })
                .catch(() => {});
        })();
        <?php endif; ?>
    </script>

    <!-- INTEGRAÇÃO COM A PALETA DINÂMICA DA EMPRESA -->
    <script>
        window.GUIAR_EMPRESA_LOGO = <?= json_encode($logoEmpresaSidebar) ?>;
    </script>
    <script src="<?= BASE_URL ?>/public/js/empresa-palette.js"></script>
</body>

</html>
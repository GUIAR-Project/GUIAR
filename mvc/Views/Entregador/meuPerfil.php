<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Perfil | Entregador</title>
    <link rel="shortcut icon" type="image/png" href="<?= BASE_URL ?>/img/logoIcon.png">

    <!-- Google Fonts: Inter & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?= BASE_URL ?>/style/sidebar.css">

    <style>
        @font-face {
            font-family: 'Brice-Bold';
            src: url('<?= BASE_URL ?>/fonts/Brice-BoldSemiCondensed.ttf') format('truetype');
        }

        @font-face {
            font-family: 'BasisGrotesque-Regular';
            src: url('<?= BASE_URL ?>/fonts/BasisGrotesqueArabicPro-Regular.ttf') format('truetype');
        }

        @font-face {
            font-family: 'Brice-SemiBoldSemi';
            src: url('<?= BASE_URL ?>/fonts/Brice-SemiBoldSemiCondensed.ttf');
        }

        :root {
            --emp-primary: #4F46E5;
            --emp-primary-hover: #4338CA;
            --emp-primary-active: #3730A3;
            --emp-primary-dark: #312E81;
            --emp-primary-light: #EEF2FF;
            --emp-primary-light-border: #C7D2FE;
            --emp-primary-ring: rgba(79, 70, 229, 0.25);
            --emp-primary-contrast: #FFFFFF;

            --emp-secondary: #FFD400;
            --emp-secondary-hover: #E6BE00;
            --emp-secondary-light: #FEF9C3;
            --emp-secondary-contrast: #0F172A;
        }

        body {
            font-family: 'Inter', 'BasisGrotesque-Regular', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: radial-gradient(ellipse at 60% -10%, rgba(255, 255, 255, .98), transparent 48%), #f1f4fa;
        }

        .heading-font {
            font-family: 'Outfit', 'Inter', sans-serif;
        }

        /* BOTÃO PRIMÁRIO NA PALETA DO PROJETO */
        .btn-emp-primary {
            background-color: var(--emp-primary, #4F46E5);
            color: var(--emp-primary-contrast, #FFFFFF);
            box-shadow: 0 8px 18px -4px var(--emp-primary-ring, rgba(79, 70, 229, 0.35));
        }

        .btn-emp-primary:hover {
            background-color: var(--emp-primary-hover, #4338CA);
        }

        /* COLORAÇÃO CLARA DA SIDEBAR */
        #sidebar {
            background-color: #FFFFFF !important;
            color: #334155 !important;
            border-right: 1px solid rgba(226, 232, 240, 0.8) !important;
        }

        #sidebar .menu-item {
            transition: all 0.2s ease;
        }

        #sidebar .menu-item:not(.active) {
            color: #64748B !important;
        }

        #sidebar .menu-item:not(.active):hover {
            background-color: rgba(241, 245, 249, 0.8) !important;
            color: #0F172A !important;
        }

        #sidebar .menu-item.active {
            background-color: var(--emp-primary, #4F46E5) !important;
            color: var(--emp-primary-contrast, #FFFFFF) !important;
        }

        /* Scrollbar estilizada */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #F1F5F9;
        }

        ::-webkit-scrollbar-thumb {
            background: #CBD5E1;
            border-radius: 99px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94A3B8;
        }

        .hover-card {
            transition: all 0.3s ease;
        }

        .hover-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px -10px rgba(0, 0, 0, 0.08);
        }
    </style>
</head>

<body class="sidebar-page min-h-screen flex flex-col md:flex-row bg-[#f1f4fa] text-[#0F172A] overflow-x-hidden">

    <?php
    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }

    // 1. NOME DO ENTREGADOR
    $nomeEntregador = trim((string)($entregador["nome_completo"]
        ?? ($entregador["nome"]
        ?? ($_SESSION['nome_entregador']
        ?? ($_SESSION['nome_usuario'] ?? 'Entregador')))));
    if ($nomeEntregador === '') {
        $nomeEntregador = 'Entregador';
    }

    // 2. FOTO DO ENTREGADOR (USA foto_3x4 DO PERFIL SE DISPONÍVEL)
    $entregadorFotoUrl = '';
    if (!empty($entregador["foto_3x4"])) {
        $rawFoto = trim((string)$entregador["foto_3x4"]);
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

    $precisaSincronizarEmpresa = ($logoEmpresaResolvida === '');
    $logoEmpresaSidebar = $logoEmpresaResolvida !== ''
        ? $normalizarUrlAsset($logoEmpresaResolvida)
        : (BASE_URL . '/img/logoIcon.png');
    $nomeEmpresaSidebar = $nomeEmpresaResolvido !== '' ? $nomeEmpresaResolvido : 'GUIAR Entregas';
    ?>

    <!-- OVERLAY MOBILE -->
    <div id="overlay" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-40 md:hidden transition-opacity"></div>

    <!-- SIDEBAR -->
    <aside id="sidebar"
        class="flex w-72 bg-white flex-col fixed top-0 left-0 text-slate-700 z-50 transition-all rounded-none overflow-hidden h-screen border-r border-slate-200/80 shadow-sm">

        <div class="flex flex-col h-full">

            <button id="closeSidebar" type="button"
                class="absolute top-5 right-5 md:hidden text-slate-500 hover:text-slate-800 text-xl font-bold p-1 rounded-lg transition-colors z-50"
                aria-label="Fechar menu">
                ✕
            </button>

            <!-- LOGO DA EMPRESA -->
            <div class="px-6 pt-7 pb-4 mb-2 flex justify-start items-center gap-3">
                <div style="width: 58px; height: 58px; border-radius: 50%; overflow: hidden; flex-shrink: 0; background: #fff; border: 2px solid var(--emp-primary-light-border, rgba(79,70,229,0.18)); box-shadow: 0 4px 14px var(--emp-primary-ring, rgba(79,70,229,0.12)); display: flex; align-items: center; justify-content: center; padding: 5px;">
                    <img id="sidebarEmpresaLogo" src="<?= htmlspecialchars($logoEmpresaSidebar, ENT_QUOTES, 'UTF-8') ?>"
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

                <!-- MEUS PEDIDOS -->
                <a href="<?= BASE_URL ?>/routes.php?action=meusPedidosEntregador"
                    class="menu-item flex items-center gap-3.5 px-4 py-3 rounded-xl font-semibold text-sm text-slate-500 hover:text-slate-900 hover:bg-slate-100/80 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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

                <!-- MEU PERFIL (ATIVO) -->
                <a href="<?= BASE_URL ?>/routes.php?action=perfilEntregador"
                    class="menu-item active flex items-center gap-3.5 px-4 py-3 rounded-xl font-bold text-sm transition-all"
                    style="background-color: var(--emp-primary, #4F46E5); color: var(--emp-primary-contrast, #ffffff); box-shadow: 0 8px 18px -4px var(--emp-primary-ring, rgba(79, 70, 229, 0.35));">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                <button id="toggleSidebar" type="button"
                    class="md:hidden p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors flex-shrink-0"
                    aria-label="Abrir menu" aria-controls="sidebar" aria-expanded="false">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <!-- ÍCONE DA ABA -->
                <div class="flex items-center justify-center w-9 h-9 sm:w-auto sm:h-auto rounded-xl sm:rounded-none bg-indigo-50/80 sm:bg-transparent flex-shrink-0 transition-transform hover:scale-105"
                    style="color: var(--emp-primary, #4F46E5);">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>

                <div class="min-w-0 flex-1">
                    <h1 class="heading-font text-lg md:text-xl font-extrabold text-slate-900 tracking-tight truncate">
                        <?= htmlspecialchars($entregador["nome_completo"]) ?>
                    </h1>
                    <p class="text-xs md:text-sm text-slate-500 font-medium truncate">
                        Meu Perfil - Gerencie suas informações pessoais
                    </p>
                </div>
            </div>
        </header>

        <!-- MAIN -->
        <main class="flex-grow p-4 sm:p-8 lg:px-12 xl:px-16 pb-24 lg:pb-12 space-y-8">

            <!-- Alerts -->
            <?php if ($sucesso): ?>
                <div class="max-w-2xl mx-auto bg-emerald-50 border border-emerald-200 rounded-2xl p-4 flex items-center gap-3">
                    <div class="flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <p class="text-sm font-semibold text-emerald-700"><?= htmlspecialchars($sucesso) ?></p>
                </div>
            <?php endif; ?>

            <?php if ($erro): ?>
                <div class="max-w-2xl mx-auto bg-red-50 border border-red-200 rounded-2xl p-4 flex items-center gap-3">
                    <div class="flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4v2m0 4v2m0 0v2M3 7a3 3 0 013-3h12a3 3 0 013 3v10a3 3 0 01-3 3H6a3 3 0 01-3-3V7z" />
                        </svg>
                    </div>
                    <p class="text-sm font-semibold text-red-700"><?= htmlspecialchars($erro) ?></p>
                </div>
            <?php endif; ?>

            <!-- Card de Perfil Entregador -->
            <div class="max-w-2xl mx-auto">
                <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 sm:p-8 space-y-6">

                    <!-- Foto de Perfil -->
                    <div class="flex flex-col items-center space-y-4">
                        <?php
                        $foto_path = !empty($entregador["foto_3x4"])
                            ? BASE_URL . "/public/uploads/entregadores/fotos/" . htmlspecialchars($entregador["foto_3x4"])
                            : BASE_URL . "/img/G.png";
                        ?>
                        <div class="relative">
                            <img src="<?= $foto_path ?>" class="w-32 h-32 rounded-full object-cover border-4 border-slate-100 shadow-md" alt="Foto de <?= htmlspecialchars($entregador["nome_completo"]) ?>">
                        </div>
                        <div class="text-center">
                            <h2 class="text-2xl font-bold text-slate-900"><?= htmlspecialchars($entregador["nome_completo"]) ?></h2>
                            <p class="text-sm text-slate-500 font-medium mt-1">Entregador</p>
                        </div>
                    </div>

                    <!-- Divisor -->
                    <div class="h-px bg-slate-100"></div>

                    <!-- Informações Pessoais -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-bold text-slate-900">Informações Pessoais</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="bg-slate-50 rounded-xl p-4">
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Usuário</p>
                                <p class="text-sm font-semibold text-slate-900 mt-1"><?= htmlspecialchars($entregador["nome_usuario"]) ?></p>
                            </div>
                            <div class="bg-slate-50 rounded-xl p-4">
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">E-mail</p>
                                <p class="text-sm font-semibold text-slate-900 mt-1"><?= htmlspecialchars($entregador["email"]) ?></p>
                            </div>
                            <div class="bg-slate-50 rounded-xl p-4">
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">CPF</p>
                                <p class="text-sm font-semibold text-slate-900 mt-1"><?= htmlspecialchars($entregador["CPF"]) ?></p>
                            </div>
                            <div class="bg-slate-50 rounded-xl p-4">
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Telefone</p>
                                <p class="text-sm font-semibold text-slate-900 mt-1"><?= htmlspecialchars($entregador["telefone"]) ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Divisor -->
                    <div class="h-px bg-slate-100"></div>

                    <!-- CNH -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-bold text-slate-900">Documentação</h3>

                        <div class="bg-slate-50 rounded-xl p-6">
                            <p class="text-sm font-bold text-slate-700 mb-4">Foto da CNH</p>
                            <?php if (!empty($entregador["foto_CNH"])): ?>
                                <img src="<?= BASE_URL . "/public/uploads/entregadores/CNH/" . htmlspecialchars($entregador["foto_CNH"]) ?>" class="w-full max-w-sm rounded-xl border border-slate-200 object-contain" alt="Foto da CNH">
                            <?php else: ?>
                                <div class="flex items-center justify-center h-40 border-2 border-dashed border-slate-300 rounded-xl">
                                    <p class="text-sm text-slate-400 font-medium">Nenhuma foto da CNH cadastrada</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Divisor -->
                    <div class="h-px bg-slate-100"></div>

                    <!-- Botão Editar -->
                    <div class="flex items-center justify-center gap-3">
                        <button id="openEditModalBtn" type="button"
                            class="btn-emp-primary flex items-center justify-center gap-2 font-bold text-sm px-6 py-3 rounded-2xl transition-all hover:scale-[1.02] active:scale-95">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Editar Perfil
                        </button>
                    </div>

                </div>
            </div>

        </main>

    </div>

    <!-- MOBILE BOTTOM NAVIGATION -->
    <nav class="lg:hidden fixed bottom-0 left-0 right-0 bg-white/95 backdrop-blur-md border-t border-slate-200/90 flex justify-around items-center h-16 z-30 shadow-lg">
        <a href="<?= BASE_URL ?>/routes.php?action=meusPedidosEntregador"
            class="flex flex-col items-center gap-0.5 text-[11px] text-slate-500 hover:text-slate-900 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 opacity-75" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
            class="flex flex-col items-center gap-0.5 text-[11px] font-bold transition-colors"
            style="color: var(--emp-primary, #4F46E5);">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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

    <!-- MODAL DE CONFIRMAÇÃO DE LOGOUT -->
    <div id="logoutConfirmModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm hidden z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-sm w-full p-6 sm:p-7 shadow-2xl border border-slate-100 text-center space-y-5">
            <div class="w-14 h-14 rounded-2xl bg-rose-50 text-rose-500 flex items-center justify-center mx-auto">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
            </div>
            <div class="space-y-1.5">
                <h3 class="heading-font text-xl font-extrabold text-slate-900">Sair da conta?</h3>
                <p class="text-sm text-slate-500 font-medium">
                    Tem certeza que deseja encerrar sua sessão? Você precisará entrar novamente para acompanhar suas entregas.
                </p>
            </div>
            <div class="flex items-center gap-3 pt-1">
                <button type="button" id="cancelLogoutBtn"
                    class="flex-1 px-4 py-3 text-sm font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-2xl transition">
                    Cancelar
                </button>
                <a id="confirmLogoutBtn" href="<?= BASE_URL ?>/routes.php?action=logoutEntregador"
                    class="flex-1 px-4 py-3 text-sm font-bold text-white bg-rose-500 hover:bg-rose-600 rounded-2xl transition shadow-md shadow-rose-500/25 flex items-center justify-center">
                    Sair
                </a>
            </div>
        </div>
    </div>

    <!-- Modal de Edição -->
    <div id="editProfileModal" class="fixed inset-0 bg-black/50 hidden z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-2xl w-full max-h-[90vh] overflow-y-auto shadow-2xl">

            <!-- Modal Header -->
            <div class="sticky top-0 bg-white border-b border-slate-100 px-8 py-6 flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900">Editar Meu Perfil</h2>
                    <p class="text-xs text-slate-500 font-medium mt-1">Atualize suas informações pessoais</p>
                </div>
                <button id="closeEditModalBtn" class="text-slate-400 hover:text-slate-600 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Modal Content -->
            <div class="p-8">
                <form action="<?= BASE_URL ?>/routes.php?action=editarPerfilEntregador" method="POST" enctype="multipart/form-data" class="space-y-6">

                    <!-- Seção: Informações Pessoais -->
                    <div class="space-y-4">
                        <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider">Informações Pessoais</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Nome Completo -->
                            <div class="md:col-span-2">
                                <label for="nome_completo" class="block text-sm font-semibold text-slate-700 mb-2">Nome Completo</label>
                                <input type="text" id="nome_completo" name="nome_completo" class="w-full px-4 py-2 border border-slate-200 rounded-xl bg-slate-50 text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-[#1B138F] focus:bg-white transition" value="<?= htmlspecialchars($entregador["nome_completo"]) ?>" required>
                            </div>

                            <!-- Nome de Usuário -->
                            <div>
                                <label for="nome_usuario" class="block text-sm font-semibold text-slate-700 mb-2">Nome de Usuário</label>
                                <input type="text" id="nome_usuario" name="nome_usuario" class="w-full px-4 py-2 border border-slate-200 rounded-xl bg-slate-50 text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-[#1B138F] focus:bg-white transition" value="<?= htmlspecialchars($entregador["nome_usuario"]) ?>" required>
                            </div>

                            <!-- E-mail -->
                            <div>
                                <label for="email" class="block text-sm font-semibold text-slate-700 mb-2">E-mail</label>
                                <input type="email" id="email" name="email" class="w-full px-4 py-2 border border-slate-200 rounded-xl bg-slate-50 text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-[#1B138F] focus:bg-white transition" value="<?= htmlspecialchars($entregador["email"]) ?>" required>
                            </div>

                            <!-- CPF -->
                            <div>
                                <label for="cpf" class="block text-sm font-semibold text-slate-700 mb-2">CPF</label>
                                <input type="text" id="cpf" name="cpf" class="w-full px-4 py-2 border border-slate-200 rounded-xl bg-slate-50 text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-[#1B138F] focus:bg-white transition" value="<?= htmlspecialchars($entregador["CPF"]) ?>" required>
                            </div>

                            <!-- Telefone -->
                            <div>
                                <label for="telefone" class="block text-sm font-semibold text-slate-700 mb-2">Telefone</label>
                                <input type="text" id="telefone" name="telefone" class="w-full px-4 py-2 border border-slate-200 rounded-xl bg-slate-50 text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-[#1B138F] focus:bg-white transition" value="<?= htmlspecialchars($entregador["telefone"]) ?>" required>
                            </div>
                        </div>
                    </div>

                    <!-- Divisor -->
                    <div class="h-px bg-slate-100"></div>

                    <!-- Seção: Segurança -->
                    <div class="space-y-4">
                        <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider">Segurança</h3>

                        <div>
                            <label for="senha" class="block text-sm font-semibold text-slate-700 mb-2">Nova Senha</label>
                            <input type="password" id="senha" name="senha" class="w-full px-4 py-2 border border-slate-200 rounded-xl bg-slate-50 text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-[#1B138F] focus:bg-white transition" placeholder="Deixe em branco para manter a senha atual">
                            <p class="text-xs text-slate-500 font-medium mt-2">Deixe em branco para manter a senha atual</p>
                        </div>
                    </div>

                    <!-- Divisor -->
                    <div class="h-px bg-slate-100"></div>

                    <!-- Seção: Fotos -->
                    <div class="space-y-4">
                        <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider">Documentação</h3>

                        <!-- Foto 3x4 -->
                        <div class="relative">
                            <label for="foto_3x4" class="block text-sm font-semibold text-slate-700 mb-2">
                                Foto 3x4
                            </label>
                            <input
                                type="file"
                                id="foto_3x4"
                                name="foto_3x4"
                                accept="image/*"
                                class="w-full px-4 py-2 border border-slate-200 rounded-xl bg-slate-50 text-slate-500 font-medium file:mr-3 file:bg-[#E94E00] file:text-white file:font-bold file:px-4 file:py-1.5 file:border-0 file:rounded-lg file:cursor-pointer hover:file:bg-[#c63f00] transition">
                        </div>

                        <p class="text-xs text-slate-500 font-medium mt-2">
                            Formatos aceitos: JPG, PNG, etc.
                        </p>
                    </div>

                    <!-- Foto CNH -->
                    <div>
                        <label for="foto_CNH" class="block text-sm font-semibold text-slate-700 mb-2">
                            Foto da CNH
                        </label>

                        <div class="relative">
                            <input
                                type="file"
                                id="foto_CNH"
                                name="foto_CNH"
                                accept="image/*"
                                class="w-full px-4 py-2 border border-slate-200 rounded-xl bg-slate-50 text-slate-500 font-medium file:mr-3 file:bg-[#E94E00] file:text-white file:font-bold file:px-4 file:py-1.5 file:border-0 file:rounded-lg file:cursor-pointer hover:file:bg-[#c63f00] transition">
                        </div>

                        <p class="text-xs text-slate-500 font-medium mt-2">
                            Formatos aceitos: JPG, PNG, etc.
                        </p>
                    </div>

                    <!-- Divisor -->
                    <div class="h-px bg-slate-100"></div>

                    <!-- Botões de Ação -->
                    <div class="flex items-center gap-3 justify-end">

                        <button
                            type="button"
                            id="closeEditModalBtn2"
                            class="px-6 py-3 text-sm font-bold text-white bg-[#E94E00] hover:bg-[#c63f00] rounded-2xl transition shadow-md hover:scale-[1.02]">
                            Cancelar
                        </button>

                        <button
                            type="submit"
                            class="flex items-center gap-2 bg-[#E94E00] hover:bg-[#c63f00] text-white font-bold text-sm px-6 py-3 rounded-2xl transition shadow-md hover:scale-[1.02]">

                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>

                            Salvar Alterações
                        </button>

                    </div>

                </form>
            </div>

        </div>
    </div>

    <script>
        // Confirmação de logout
        (function configurarConfirmacaoLogout() {
            const logoutModal = document.getElementById('logoutConfirmModal');
            const cancelLogoutBtn = document.getElementById('cancelLogoutBtn');
            const confirmLogoutBtn = document.getElementById('confirmLogoutBtn');

            if (!logoutModal || !cancelLogoutBtn || !confirmLogoutBtn) return;

            document.querySelectorAll('a[href*="action=logoutEntregador"]').forEach(link => {
                if (link.id === 'confirmLogoutBtn') return;
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    confirmLogoutBtn.href = this.getAttribute('href');
                    logoutModal.classList.remove('hidden');
                });
            });

            cancelLogoutBtn.addEventListener('click', () => {
                logoutModal.classList.add('hidden');
            });

            logoutModal.addEventListener('click', (e) => {
                if (e.target === logoutModal) {
                    logoutModal.classList.add('hidden');
                }
            });

            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && !logoutModal.classList.contains('hidden')) {
                    logoutModal.classList.add('hidden');
                }
            });
        })();

        // Modal de edição
        const modal = document.getElementById("editProfileModal");
        const openBtn = document.getElementById("openEditModalBtn");
        const closeBtn = document.getElementById("closeEditModalBtn");
        const closeBtn2 = document.getElementById("closeEditModalBtn2");

        openBtn.addEventListener("click", () => {
            modal.classList.remove("hidden");
        });

        closeBtn.addEventListener("click", () => {
            modal.classList.add("hidden");
        });

        closeBtn2.addEventListener("click", () => {
            modal.classList.add("hidden");
        });

        window.addEventListener("click", (event) => {
            if (event.target === modal) {
                modal.classList.add("hidden");
            }
        });

        <?php if ($precisaSincronizarEmpresa): ?>
        // Sincroniza logo e nome da empresa caso o controller desta rota não os tenha enviado
        (function sincronizarEmpresaSidebar() {
            fetch('<?= BASE_URL ?>/routes.php?action=meusPedidosEntregador')
                .then(res => res.text())
                .then(html => {
                    const doc = new DOMParser().parseFromString(html, 'text/html');
                    const remoteSidebar = doc.getElementById('sidebar');
                    if (!remoteSidebar) return;

                    const remoteLogo = remoteSidebar.querySelector('img[alt*="empresa" i], img[alt*="logo" i]');
                    const localLogo = document.getElementById('sidebarEmpresaLogo');
                    if (remoteLogo && remoteLogo.src && localLogo && !remoteLogo.src.includes('LogoGuiar') && !remoteLogo.src.includes('LogoPreto')) {
                        localLogo.src = remoteLogo.src;
                        window.GUIAR_EMPRESA_LOGO = remoteLogo.src;

                        const oldScript = document.querySelector('script[src*="empresa-palette.js"]');
                        if (oldScript) {
                            const freshScript = document.createElement('script');
                            freshScript.src = oldScript.src.split('?')[0] + '?v=' + Date.now();
                            document.body.appendChild(freshScript);
                        }
                    }

                    const remoteTitle = remoteSidebar.querySelector('p.font-extrabold, .empresa-nome');
                    const localTitle = document.getElementById('sidebarEmpresaNome');
                    if (remoteTitle && remoteTitle.textContent.trim() && localTitle) {
                        localTitle.textContent = remoteTitle.textContent.trim();
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
    <script src="<?= BASE_URL ?>/public/js/mobile-sidebar.js"></script>
    <script src="<?= BASE_URL ?>/public/js/empresa-palette.js"></script>
</body>

</html>
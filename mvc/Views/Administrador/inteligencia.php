<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inteligência | GUIAR</title>
    <link rel="icon" href="<?= htmlspecialchars(!empty($logoEmpresaSidebar) ? $logoEmpresaSidebar : BASE_URL . '/img/logoIcon.png', ENT_QUOTES, 'UTF-8') ?>">


    <!-- Fonte Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

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

        body {
            font-family: 'Inter', 'BasisGrotesque-Regular', sans-serif;
            background-color: #F8FAFC;
        }

        .logo-circular {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            background-color: #ffffff;
        }

        .logo-circular-wrap {
            overflow: hidden;
            border-radius: 9999px;
            background-color: #ffffff;
            flex-shrink: 0;
        }

        .brand-crop-stage {
            position: relative;
            width: 92%;
            max-width: 520px;
            aspect-ratio: 1 / 1;
            overflow: hidden;
            border-radius: 9999px;
            background: radial-gradient(circle at center, rgba(255, 255, 255, 0.18), rgba(255, 255, 255, 0.02) 55%, rgba(255, 255, 255, 0) 70%);
            border: 10px solid rgba(255, 255, 255, 0.15);
            box-shadow: inset 0 0 0 6px rgba(255, 255, 255, 0.05);
            cursor: grab;
            user-select: none;
            touch-action: none;
        }

        .brand-crop-stage:active {
            cursor: grabbing;
        }

        .brand-crop-image {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transform: translate(0, 0) scale(1.08);
            transition: transform 0.08s ease-out;
            will-change: transform;
        }

        /* --- CORREÇÕES DE ESTILO SEM REMOVER ELEMENTOS --- */

        /* Ajuste para evitar que a sidebar empurre o conteúdo no mobile */
        @media (max-width: 1024px) {
            body {
                flex-direction: column;
                /* Em vez de linha, coloca um abaixo do outro */
            }

            #sidebar {
                position: fixed;
                left: -100%;
                top: 0;
                bottom: 0;
                z-index: 50;
                width: 280px;
                margin: 0 !important;
                height: 100vh;
                border-radius: 0 !important;
            }

            #sidebar.mobile-open {
                left: 0;
            }

            /* Garante que o conteúdo ocupe a tela toda */
            #mainContent {
                width: 100% !important;
                margin-left: 0 !important;
            }
        }

        /* Transição suave para fechar/abrir */
        #sidebar {
            transition: all 0.3s ease-in-out;
        }

        .sidebar-closed {
            width: 0 !important;
            opacity: 0;
            overflow: hidden;
            margin-left: 0 !important;
            margin-right: 0 !important;
        }

        /* Efeitos visuais mantidos */
        .menu-item {
            font-family: inherit;
            transition: all 0.25s ease;
        }

        .menu-item:hover {
            background-color: rgba(255, 255, 255, 0.08);
            transform: translateX(4px);
        }

        .active-menu {
            background: linear-gradient(90deg, rgba(255, 212, 0, 0.15) 0%, rgba(255, 212, 0, 0.02) 100%);
            border-left: 4px solid #FFD400;
            color: #FFD400 !important;
        }

        .hover-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px -10px rgba(0, 0, 0, 0.08);
        }

        /* Impede que a tabela quebre o layout no celular */
        .table-container {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        #recentOrdersCard.is-expanded {
            position: fixed;
            top: 50%;
            left: 50%;
            z-index: 60;
            display: flex;
            width: min(90vw, 1200px);
            height: min(80vh, 760px);
            min-width: min(320px, calc(100vw - 2rem));
            min-height: min(280px, calc(100vh - 2rem));
            max-width: calc(100vw - 2rem);
            max-height: calc(100vh - 2rem);
            flex-direction: column;
            overflow: auto;
            resize: both;
            transform: translate(-50%, -50%);
        }

        #recentOrdersCard.is-expanded>.recent-orders-table {
            min-height: 0;
            flex: 1;
            overflow: auto;
        }

        #recentOrdersBackdrop.is-visible {
            display: block;
        }

        /* Estilo para esconder e mostrar no mobile */
        @media (max-width: 1024px) {
            #sidebar {
                position: fixed !important;
                left: -100% !important;
                /* Começa totalmente escondida */
                top: 0;
                bottom: 0;
                margin: 0 !important;
                height: 100vh !important;
                border-radius: 0 !important;
                z-index: 9999;
                /* Garante que fique em cima de tudo */
                transition: left 0.3s ease-in-out;
            }

            /* Essa é a classe que o JavaScript vai ativar */
            #sidebar.mobile-open {
                left: 0 !important;
            }
        }

        /* Estilo para recolher no Desktop (Opcional) */
        .sidebar-closed {
            width: 0 !important;
            opacity: 0;
            pointer-events: none;
            margin-left: 0 !important;
            margin-right: 0 !important;
        }

        #mainContent {
            margin-left: 18rem;
            transition: margin-left 0.3s ease-in-out;
        }

        #mainContent.sidebar-expanded {
            margin-left: 0 !important;
        }

        @media (max-width: 1024px) {
            #mainContent {
                margin-left: 0 !important;
            }
        }
    </style>
</head>

<body class="sidebar-page min-h-screen flex bg-[#f1f4fa] text-slate-800 antialiased overflow-x-hidden">

    <!-- OVERLAY MOBILE -->
    <div id="overlay" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-40 md:hidden transition-opacity"></div>

    <?php
    $nomeAdmin = $nomeAdmin ?? ($_SESSION['nome_usuario'] ?? ($admin["nome_adm"] ?? "Admin"));
    $adminFotoUrl = $adminFotoUrl ?? '';
    ?>

    <aside id="sidebar"
        class="w-72 bg-white flex flex-col fixed top-0 left-0 text-slate-700 z-50 transition-all rounded-none overflow-hidden h-screen border-r border-slate-200/80 shadow-sm">


        <div class="flex flex-col h-full">

            <!-- BOTÃO FECHAR MOBILE -->
            <button id="closeSidebar"
                class="absolute top-5 right-5 md:hidden text-slate-500 hover:text-slate-800 text-xl font-bold p-1 rounded-lg transition-colors z-50">
                ✕
            </button>

            <!-- LOGO DA EMPRESA -->
            <div class="px-6 pt-7 pb-4 mb-2 flex justify-start items-center gap-3">
                <div style="width: 58px; height: 58px; border-radius: 50%; overflow: hidden; flex-shrink: 0; background: #fff; border: 2px solid var(--emp-primary-light-border, rgba(79,70,229,0.18)); box-shadow: 0 4px 14px var(--emp-primary-ring, rgba(79,70,229,0.12)); display: flex; align-items: center; justify-content: center; padding: 5px;">
                    <img src="<?= htmlspecialchars($logoEmpresaSidebar, ENT_QUOTES, 'UTF-8') ?>"
                        alt="Logo da empresa"
                        style="width: 100%; height: 100%; object-fit: contain; object-position: center; display: block;">
                </div>
                <div class="min-w-0">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Painel do Gestor</p>
                    <p class="text-sm font-extrabold text-slate-800 truncate">GUIAR Entregas</p>
                </div>
            </div>

            <!-- MENU DE NAVEGAÇÃO -->
            <nav id="sidebarMenu" class="sidebar-menu px-4 space-y-1.5 flex-grow">

                <!-- INÍCIO (ATIVO) -->
                <a href="<?= BASE_URL ?>/routes.php?action=dashboardAdm"
                    class="menu-item flex items-center gap-3.5 px-4 py-3 rounded-xl font-semibold text-sm text-slate-500 hover:text-slate-900 hover:bg-slate-100/80 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Início
                </a>

                <!-- PEDIDOS -->
                <a href="<?= BASE_URL ?>/routes.php?action=pedidos"
                    class="menu-item flex items-center gap-3.5 px-4 py-3 rounded-xl font-semibold text-sm text-slate-500 hover:text-slate-900 hover:bg-slate-100/80 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    Pedidos
                </a>

                <!-- ENTREGADORES -->
                <a href="<?= BASE_URL ?>/routes.php?action=entregadores"
                    class="menu-item flex items-center gap-3.5 px-4 py-3 rounded-xl font-semibold text-sm text-slate-500 hover:text-slate-900 hover:bg-slate-100/80 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-4-4h-1M9 20H4v-2a4 4 0 014-4h1m4-4a4 4 0 100-8 4 4 0 000 8zm6 4a2 2 0 100-4 2 2 0 000 4zM3 20v-2a2 2 0 012-2h1" />
                    </svg>
                    Entregadores
                </a>

                <!-- PEDIDOS ENTREGUES -->
                <a href="<?= BASE_URL ?>/routes.php?action=pedidosEntregues"
                    class="menu-item flex items-center gap-3.5 px-4 py-3 rounded-xl font-semibold text-sm text-slate-500 hover:text-slate-900 hover:bg-slate-100/80 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                    Pedidos Entregues
                </a>

                <!-- MAPA -->
                <a href="<?= BASE_URL ?>/routes.php?action=mapaAdm"
                    class="menu-item flex items-center gap-3.5 px-4 py-3 rounded-xl font-semibold text-sm text-slate-500 hover:text-slate-900 hover:bg-slate-100/80 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                    </svg>
                    Acompanhar Rotas
                </a>

                <a href="<?= BASE_URL ?>/routes.php?action=inteligenciaAdm"
                    class="menu-item active flex items-center gap-3.5 px-4 py-3 rounded-xl font-bold text-sm transition-all"
                    style="background-color: var(--emp-primary, #4F46E5); color: var(--emp-primary-contrast, #ffffff); box-shadow: 0 8px 18px -4px var(--emp-primary-ring, rgba(79, 70, 229, 0.35));">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Inteligência
                </a>

                <!-- PERFIL -->
                <a href="<?= BASE_URL ?>/routes.php?action=perfilAdm"
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
                    <img src="<?= htmlspecialchars(!empty($logoEmpresaSidebar) ? $logoEmpresaSidebar : BASE_URL . '/img/LogoPreto.png', ENT_QUOTES, 'UTF-8') ?>" alt="Logo da empresa" class="w-20 h-auto object-contain opacity-90">
                    <p class="text-[10px] text-slate-400 text-center leading-tight">
                        Conectando você ao seu destino.
                    </p>
                </div>

                <div class="admin-user-card flex items-center gap-3 p-2.5 rounded-2xl bg-slate-50 border border-slate-200/80">
                    <?php if (!empty($adminFotoUrl)): ?>
                        <img src="<?= htmlspecialchars($adminFotoUrl, ENT_QUOTES, 'UTF-8') ?>"
                            alt="Foto de <?= htmlspecialchars($nomeAdmin, ENT_QUOTES, 'UTF-8') ?>"
                            class="w-9 h-9 rounded-full object-cover shadow-sm ring-2 ring-white">
                    <?php else: ?>
                        <div class="admin-user-avatar w-9 h-9 rounded-full font-black text-xs flex items-center justify-center shadow-sm"
                            style="background-color: var(--emp-secondary, #FFD400); color: var(--emp-secondary-contrast, #0F172A);">
                            <?= strtoupper(substr($nomeAdmin, 0, 2)) ?>
                        </div>
                    <?php endif; ?>

                    <div class="flex-grow min-w-0">
                        <p class="admin-user-name font-bold text-xs text-slate-800 truncate"><?= htmlspecialchars($nomeAdmin) ?></p>
                        <p class="admin-user-role text-[10px] text-slate-400 font-bold tracking-wider uppercase">Administrador</p>
                    </div>

                    <!-- LOGOUT -->
                    <button type="button" data-logout-trigger aria-label="Sair da conta" class="text-slate-400 hover:text-rose-500 transition-colors p-1.5 rounded-lg hover:bg-rose-50">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </aside>

    <?php require __DIR__ . '/partials/logout-confirmation.php'; ?>

    <!-- ================= CONTEÚDO PRINCIPAL (DASHBOARD) ================= -->
    <div id="mainContent" class="flex-1 flex flex-col min-h-screen w-full transition-all duration-300">

        <!-- HEADER DA PÁGINA -->
        <header class="bg-white/80 backdrop-blur-md border-b border-slate-200/80 px-4 md:px-8 py-3.5 md:py-4 flex items-center justify-between sticky top-0 z-30 shadow-sm gap-4 flex-shrink-0">
            <div class="flex items-center gap-3.5 min-w-0">
                <button id="toggleSidebar" class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors flex-shrink-0" aria-label="Abrir menu">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <div class="min-w-0">
                    <h1 class="page-heading text-lg md:text-xl font-extrabold text-slate-900 tracking-tight truncate">Inteligência de entregas</h1>
                    <p class="page-subheading text-xs md:text-sm text-slate-500 font-medium truncate">Tempo real × previsto, e o quanto o modelo de previsão já aprendeu.</p>
                </div>
            </div>
            <button type="button" id="btnTreinar"
                class="flex-shrink-0 px-4 py-2.5 rounded-xl text-xs md:text-sm font-bold shadow-sm transition-all disabled:opacity-60"
                style="background-color: var(--emp-primary, #4F46E5); color: var(--emp-primary-contrast, #ffffff);">
                Treinar modelo agora
            </button>
        </header>

        <main class="viz-root flex-1 p-4 md:p-8 space-y-6 max-w-[1400px] w-full mx-auto">
            <?php
            $e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
            $fmtMin = function ($s) {
                if ($s === null) return '—';
                $m = $s / 60;
                return $m < 10 ? number_format($m, 1, ',', '.') . ' min' : number_format($m, 0, ',', '.') . ' min';
            };
            $fmtPct = fn($v) => $v === null ? '—' : number_format($v * 100, 0, ',', '.') . '%';
            ?>

            <?php if ($erroInteligencia): ?>
                <div class="bg-white rounded-2xl border border-rose-200 p-6 text-rose-700 font-semibold"><?= $e($erroInteligencia) ?></div>
            <?php else:
                $r = $painel['resumo'];
                $m = $painel['modelo'];
                $c = $painel['contagem'];
                $min = $painel['min_amostras_modelo'];
            ?>

                <!-- SITUAÇÃO DO MODELO -->
                <section class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 flex flex-col md:flex-row md:items-center gap-4">
                    <div class="text-3xl" aria-hidden="true">🧠</div>
                    <div class="min-w-0 flex-1">
                        <?php if ($m && $m['em_uso']): ?>
                            <p class="font-extrabold text-slate-900">Modelo de previsão ativo <span class="ml-1 inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">✓ em uso</span></p>
                            <p class="text-sm text-slate-500 mt-0.5">
                                Versão <?= (int)$m['id_modelo'] ?>, treinada em <?= $e(date('d/m H:i', strtotime($m['treinado_em']))) ?> com <?= (int)$m['amostras'] ?> entregas de todas as lojas.
                                Nas <?= (int)$m['amostras_teste'] ?> entregas mais recentes, que ele não viu no treino, errou em média <strong><?= $fmtPct($m['mape_modelo']) ?></strong> — contra <?= $fmtPct($m['mape_osrm']) ?> do cálculo só pelas ruas.
                            </p>
                        <?php elseif ($m): ?>
                            <p class="font-extrabold text-slate-900">Modelo treinado, mas ainda não em uso</p>
                            <p class="text-sm text-slate-500 mt-0.5">Na versão <?= (int)$m['id_modelo'] ?> ele ainda não superou o cálculo só pelas ruas nas entregas de teste. Enquanto isso, a previsão usa o tempo do OSRM. Cada nova entrega ajuda.</p>
                        <?php else: ?>
                            <p class="font-extrabold text-slate-900">Coletando dados para o modelo</p>
                            <p class="text-sm text-slate-500 mt-0.5">O modelo começa a aprender com <?= (int)$min ?> entregas válidas (todas as lojas). Até lá, a previsão usa o tempo calculado pelas ruas.</p>
                            <?php $prog = min(1, ($r['entregas']) / max(1, $min)); ?>
                            <div class="mt-2 h-2 rounded-full bg-slate-100 overflow-hidden max-w-sm" role="progressbar" aria-valuemin="0" aria-valuemax="<?= (int)$min ?>" aria-valuenow="<?= (int)$r['entregas'] ?>">
                                <div class="h-full rounded-full" style="width: <?= round($prog * 100) ?>%; background-color: var(--series-1);"></div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="text-xs text-slate-500 md:text-right flex-shrink-0">
                        <p><strong class="text-slate-800"><?= (int)$c['concluida'] ?></strong> entregas válidas da sua loja</p>
                        <p><strong class="text-slate-800"><?= (int)$c['descartada'] ?></strong> descartadas (GPS, rota abandonada…)</p>
                        <p><strong class="text-slate-800"><?= (int)$c['em_andamento'] ?></strong> em andamento</p>
                    </div>
                </section>

                <!-- INDICADORES -->
                <section class="grid grid-cols-2 lg:grid-cols-4 gap-4" aria-label="Indicadores">
                    <?php
                    $tiles = [
                        ['Pontualidade da previsão', $fmtPct($r['pontualidade']), 'chegaram até 2 min depois do horário previsto'],
                        ['Erro médio da previsão', $m && $m['mape_modelo'] !== null ? $fmtPct($m['mape_modelo']) : '—',
                            $m && $m['mape_osrm'] !== null ? 'só pelas ruas seria ' . $fmtPct($m['mape_osrm']) : 'aparece com o primeiro modelo'],
                        ['Tempo médio de entrega', $fmtMin($r['tempo_medio_real_s']), 'do início da rota até a chegada'],
                        ['Tempo no endereço', $fmtMin($r['tempo_no_local_s']), 'mediana da chegada até concluir'],
                    ];
                    foreach ($tiles as [$rotulo, $valor, $sub]): ?>
                        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5">
                            <p class="text-xs font-bold text-slate-500 uppercase tracking-wide"><?= $e($rotulo) ?></p>
                            <p class="mt-2 text-3xl font-extrabold text-slate-900 tabular-nums"><?= $e($valor) ?></p>
                            <p class="mt-1 text-xs text-slate-500"><?= $e($sub) ?></p>
                        </div>
                    <?php endforeach; ?>
                </section>

                <?php if ($r['entregas'] === 0): ?>
                    <section class="bg-white rounded-2xl border border-dashed border-slate-300 p-10 text-center">
                        <div class="text-4xl mb-3" aria-hidden="true">📊</div>
                        <h2 class="font-extrabold text-slate-900">Ainda não há entregas concluídas com rota</h2>
                        <p class="text-sm text-slate-500 mt-1 max-w-md mx-auto">Os gráficos aparecem conforme os entregadores tocam em <strong>Começar Percurso</strong>, chegam ao endereço e concluem a entrega pelo mapa.</p>
                    </section>
                <?php endif; ?>

                <!-- EVOLUÇÃO DO ERRO -->
                <section class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5">
                    <div class="flex flex-wrap items-baseline justify-between gap-2">
                        <h2 class="font-extrabold text-slate-900">Erro da previsão a cada treino</h2>
                        <div class="flex items-center gap-4 text-xs text-slate-600" aria-hidden="true">
                            <span class="flex items-center gap-1.5"><span class="w-3 h-[3px] rounded" style="background:var(--series-2)"></span>Só pelas ruas (OSRM)</span>
                            <span class="flex items-center gap-1.5"><span class="w-3 h-[3px] rounded" style="background:var(--series-1)"></span>Com o modelo</span>
                        </div>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">Erro médio percentual nas entregas de teste de cada versão. Quanto mais baixo, melhor.</p>
                    <div id="graficoErro" class="mt-4 relative" style="min-height: 220px;"></div>
                </section>

                <div class="grid lg:grid-cols-2 gap-6">
                    <section class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5">
                        <h2 class="font-extrabold text-slate-900">Por horário</h2>
                        <p class="text-xs text-slate-500 mt-1">Quanto o tempo real ficou acima (ou abaixo) do tempo calculado pelas ruas.</p>
                        <div id="graficoFaixas" class="mt-4 relative"></div>
                    </section>
                    <section class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5">
                        <h2 class="font-extrabold text-slate-900">Por bairro</h2>
                        <p class="text-xs text-slate-500 mt-1">Os bairros com mais entregas da sua loja.</p>
                        <div id="graficoBairros" class="mt-4 relative"></div>
                    </section>
                </div>

                <div class="grid lg:grid-cols-2 gap-6">
                    <section class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 overflow-x-auto">
                        <h2 class="font-extrabold text-slate-900">Entregadores</h2>
                        <p class="text-xs text-slate-500 mt-1">Ritmo comparado ao tempo calculado pelas ruas.</p>
                        <table class="mt-3 w-full text-sm">
                            <thead>
                                <tr class="text-left text-xs text-slate-500 border-b border-slate-100">
                                    <th class="py-2 font-semibold">Entregador</th>
                                    <th class="py-2 font-semibold text-right">Entregas</th>
                                    <th class="py-2 font-semibold text-right">Ritmo</th>
                                    <th class="py-2 font-semibold text-right">Média</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($painel['entregadores'] as $ent):
                                    $desvio = ($ent['razao_media'] - 1) * 100; ?>
                                    <tr class="border-b border-slate-50">
                                        <td class="py-2 font-semibold text-slate-800"><?= $e($ent['nome']) ?></td>
                                        <td class="py-2 text-right tabular-nums"><?= (int)$ent['entregas'] ?></td>
                                        <td class="py-2 text-right tabular-nums"><?= abs($desvio) < 1 ? 'igual' : number_format(abs($desvio), 0, ',', '.') . '% ' . ($desvio > 0 ? 'mais lento' : 'mais rápido') ?></td>
                                        <td class="py-2 text-right tabular-nums"><?= $fmtMin($ent['tempo_medio_s']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (!$painel['entregadores']): ?>
                                    <tr><td colspan="4" class="py-6 text-center text-slate-400">Sem dados ainda</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </section>

                    <section class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 overflow-x-auto">
                        <h2 class="font-extrabold text-slate-900">Últimas entregas</h2>
                        <p class="text-xs text-slate-500 mt-1">Previsto pelo sistema × tempo real, do início da rota até a chegada.</p>
                        <table class="mt-3 w-full text-sm">
                            <thead>
                                <tr class="text-left text-xs text-slate-500 border-b border-slate-100">
                                    <th class="py-2 font-semibold">Quando</th>
                                    <th class="py-2 font-semibold">Bairro</th>
                                    <th class="py-2 font-semibold text-right">Previsto</th>
                                    <th class="py-2 font-semibold text-right">Real</th>
                                    <th class="py-2 font-semibold text-right">Diferença</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($painel['recentes'] as $rec):
                                    $dif = $rec['real_s'] - $rec['previsto_s'];
                                    $noPrazo = $dif <= \mvc\Services\InteligenciaService::TOLERANCIA_PONTUALIDADE_S; ?>
                                    <tr class="border-b border-slate-50">
                                        <td class="py-2 whitespace-nowrap text-slate-600"><?= $e(date('d/m H:i', strtotime($rec['quando']))) ?></td>
                                        <td class="py-2 text-slate-800 truncate max-w-[140px]"><?= $e($rec['bairro']) ?></td>
                                        <td class="py-2 text-right tabular-nums"><?= $fmtMin($rec['previsto_s']) ?><?= $rec['com_modelo'] ? ' <span title="Previsão ajustada pelo modelo">🧠</span>' : '' ?></td>
                                        <td class="py-2 text-right tabular-nums"><?= $fmtMin($rec['real_s']) ?></td>
                                        <td class="py-2 text-right tabular-nums whitespace-nowrap">
                                            <span aria-hidden="true"><?= $noPrazo ? '✓' : '⚠' ?></span>
                                            <?= ($dif >= 0 ? '+' : '−') . $fmtMin(abs($dif)) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (!$painel['recentes']): ?>
                                    <tr><td colspan="5" class="py-6 text-center text-slate-400">Sem dados ainda</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </section>
                </div>
            <?php endif; ?>
        </main>

        <div id="vizTooltip" role="tooltip" class="pointer-events-none fixed z-[70] hidden max-w-[240px] rounded-xl bg-slate-900 text-white text-xs px-3 py-2 shadow-lg"></div>
    </div>

    <style>
        .viz-root {
            --surface-1: #ffffff;
            --grid: #e8eaee;
            --text-primary: #0b0b0b;
            --text-secondary: #52514e;
            --series-1: #2a78d6; /* com o modelo */
            --series-2: #eb6834; /* só pelas ruas (OSRM) */
        }
    </style>

    <?php if (!$erroInteligencia): ?>
    <script>
        const PAINEL = <?= json_encode([
            'historico' => $painel['historico_modelo'],
            'faixas' => $painel['faixas'],
            'bairros' => $painel['bairros'],
        ], JSON_UNESCAPED_UNICODE) ?>;

        const tooltip = document.getElementById('vizTooltip');
        function mostrarDica(evento, html) {
            tooltip.innerHTML = html;
            tooltip.classList.remove('hidden');
            const x = Math.min(evento.clientX + 14, window.innerWidth - tooltip.offsetWidth - 8);
            const y = Math.max(8, evento.clientY - tooltip.offsetHeight - 12);
            tooltip.style.left = x + 'px';
            tooltip.style.top = y + 'px';
        }
        function esconderDica() { tooltip.classList.add('hidden'); }
        function esc(t) { const d = document.createElement('div'); d.textContent = t == null ? '' : String(t); return d.innerHTML; }
        const pct = v => Math.round(v * 100) + '%';
        const minutos = s => { const m = s / 60; return (m < 10 ? m.toFixed(1).replace('.', ',') : Math.round(m)) + ' min'; };

        // ---------- Linha: erro por versão do modelo ----------
        function graficoErro() {
            const el = document.getElementById('graficoErro');
            const dados = PAINEL.historico.filter(h => h.mape_osrm != null && h.mape_modelo != null);
            if (dados.length < 2) {
                el.innerHTML = `<p class="py-16 text-center text-sm text-slate-400">${dados.length === 1
                    ? 'Uma versão treinada até agora. A evolução aparece a partir do segundo treino.'
                    : 'Nenhuma versão do modelo treinada ainda.'}</p>`;
                return;
            }
            const W = el.clientWidth, H = 220, m = { t: 12, r: 120, b: 28, l: 40 };
            const iw = W - m.l - m.r, ih = H - m.t - m.b;
            const maxY = Math.max(0.05, ...dados.flatMap(d => [d.mape_osrm, d.mape_modelo]));
            const passo = maxY > 0.4 ? 0.1 : 0.05;
            const topo = Math.ceil(maxY / passo) * passo;
            const x = i => m.l + (dados.length === 1 ? iw / 2 : (i / (dados.length - 1)) * iw);
            const y = v => m.t + ih - (v / topo) * ih;

            let svg = `<svg width="${W}" height="${H}" role="img" aria-label="Erro médio da previsão por versão do modelo">`;
            for (let v = 0; v <= topo + 1e-9; v += passo) {
                svg += `<line x1="${m.l}" x2="${m.l + iw}" y1="${y(v)}" y2="${y(v)}" stroke="var(--grid)" stroke-width="1"/>`;
                svg += `<text x="${m.l - 8}" y="${y(v) + 4}" text-anchor="end" font-size="11" fill="var(--text-secondary)">${Math.round(v * 100)}%</text>`;
            }
            const rotulos = Math.max(1, Math.ceil(dados.length / 8));
            dados.forEach((d, i) => {
                if (i % rotulos === 0 || i === dados.length - 1) {
                    svg += `<text x="${x(i)}" y="${H - 8}" text-anchor="middle" font-size="11" fill="var(--text-secondary)">v${d.versao}</text>`;
                }
            });
            const serie = (chave, cor) => {
                const pts = dados.map((d, i) => `${x(i)},${y(d[chave])}`).join(' ');
                let s = `<polyline points="${pts}" fill="none" stroke="${cor}" stroke-width="2" stroke-linejoin="round" stroke-linecap="round"/>`;
                const u = dados.length - 1;
                s += `<circle cx="${x(u)}" cy="${y(dados[u][chave])}" r="4" fill="${cor}" stroke="var(--surface-1)" stroke-width="2"/>`;
                return s;
            };
            svg += serie('mape_osrm', 'var(--series-2)') + serie('mape_modelo', 'var(--series-1)');
            const u = dados[dados.length - 1];
            // rótulos diretos no fim das linhas (afastados se colidirem)
            let yO = y(u.mape_osrm), yM = y(u.mape_modelo);
            if (Math.abs(yO - yM) < 16) { if (yO < yM) { yO -= 8; yM += 8; } else { yO += 8; yM -= 8; } }
            svg += `<text x="${x(dados.length - 1) + 10}" y="${yO + 4}" font-size="12" fill="var(--text-primary)">Ruas ${pct(u.mape_osrm)}</text>`;
            svg += `<text x="${x(dados.length - 1) + 10}" y="${yM + 4}" font-size="12" font-weight="700" fill="var(--text-primary)">Modelo ${pct(u.mape_modelo)}</text>`;
            svg += `<line id="erroCruz" x1="0" x2="0" y1="${m.t}" y2="${m.t + ih}" stroke="var(--text-secondary)" stroke-width="1" visibility="hidden"/>`;
            svg += `<rect x="${m.l}" y="${m.t}" width="${iw}" height="${ih}" fill="transparent" id="erroAlvo"/>`;
            svg += `</svg>`;
            el.innerHTML = svg;

            const alvo = el.querySelector('#erroAlvo'), cruz = el.querySelector('#erroCruz');
            alvo.addEventListener('mousemove', ev => {
                const r = alvo.getBoundingClientRect();
                const i = Math.round(((ev.clientX - r.left) / r.width) * (dados.length - 1));
                const d = dados[Math.max(0, Math.min(dados.length - 1, i))];
                const cx = x(dados.indexOf(d));
                cruz.setAttribute('x1', cx); cruz.setAttribute('x2', cx); cruz.setAttribute('visibility', 'visible');
                mostrarDica(ev, `<strong>Versão ${d.versao}</strong> · ${esc(d.treinado_em.slice(8, 10) + '/' + d.treinado_em.slice(5, 7) + ' ' + d.treinado_em.slice(11, 16))}<br>
                    Só pelas ruas: ${pct(d.mape_osrm)}<br>Com o modelo: <strong>${pct(d.mape_modelo)}</strong><br>${d.amostras} entregas no treino`);
            });
            alvo.addEventListener('mouseleave', () => { cruz.setAttribute('visibility', 'hidden'); esconderDica(); });
        }

        // ---------- Barras horizontais: desvio em relação ao OSRM ----------
        function graficoBarras(idEl, itens, rotuloItem) {
            const el = document.getElementById(idEl);
            if (!itens.length) {
                el.innerHTML = '<p class="py-10 text-center text-sm text-slate-400">Sem dados ainda</p>';
                return;
            }
            const valores = itens.map(i => (i.razao_media - 1) * 100);
            const temNeg = valores.some(v => v < 0);
            const maxAbs = Math.max(10, ...valores.map(Math.abs));
            const W = el.clientWidth, rotW = Math.min(150, W * 0.38), valW = 52;
            const barH = 18, gap = 14, H = itens.length * (barH + gap);
            const area = W - rotW - valW;
            const zero = rotW + (temNeg ? area / 2 : 0);
            const escala = (temNeg ? area / 2 : area) / maxAbs;

            let svg = `<svg width="${W}" height="${H}" role="img" aria-label="Desvio do tempo real em relação ao tempo pelas ruas">`;
            svg += `<line x1="${zero}" x2="${zero}" y1="0" y2="${H - gap / 2}" stroke="var(--grid)" stroke-width="1"/>`;
            itens.forEach((it, i) => {
                const v = valores[i], yb = i * (barH + gap) + gap / 2;
                const w = Math.max(2, Math.abs(v) * escala);
                const xb = v >= 0 ? zero : zero - w;
                // ponta arredondada só no lado do valor (base reta no eixo)
                const r = Math.min(4, w / 2);
                const path = v >= 0
                    ? `M${xb},${yb} h${w - r} a${r},${r} 0 0 1 ${r},${r} v${barH - 2 * r} a${r},${r} 0 0 1 -${r},${r} h-${w - r} z`
                    : `M${xb + w},${yb} h-${w - r} a${r},${r} 0 0 0 -${r},${r} v${barH - 2 * r} a${r},${r} 0 0 0 ${r},${r} h${w - r} z`;
                svg += `<text x="${rotW - 10}" y="${yb + barH / 2 + 4}" text-anchor="end" font-size="12" fill="var(--text-primary)">${esc(it.nome.length > 22 ? it.nome.slice(0, 21) + '…' : it.nome)}</text>`;
                svg += `<path d="${path}" fill="var(--series-1)" data-i="${i}" class="barra"/>`;
                const sinal = v >= 0 ? '+' : '−';
                const xv = v >= 0 ? xb + w + 6 : xb - 6;
                svg += `<text x="${xv}" y="${yb + barH / 2 + 4}" text-anchor="${v >= 0 ? 'start' : 'end'}" font-size="12" font-weight="600" fill="var(--text-secondary)">${sinal}${Math.abs(Math.round(v))}%</text>`;
                svg += `<rect x="0" y="${yb - gap / 2}" width="${W}" height="${barH + gap}" fill="transparent" data-i="${i}" class="alvo"/>`;
            });
            svg += `</svg>`;
            el.innerHTML = svg;
            el.querySelectorAll('.alvo').forEach(a => {
                a.addEventListener('mousemove', ev => {
                    const it = itens[+a.dataset.i], v = valores[+a.dataset.i];
                    mostrarDica(ev, `<strong>${esc(it.nome)}</strong><br>${v >= 0 ? Math.round(v) + '% mais lento' : Math.abs(Math.round(v)) + '% mais rápido'} que o cálculo pelas ruas<br>
                        ${it.entregas} ${it.entregas === 1 ? 'entrega' : 'entregas'} · média ${minutos(it.tempo_medio_s)}`);
                });
                a.addEventListener('mouseleave', esconderDica);
            });
        }

        function desenharTudo() {
            graficoErro();
            graficoBarras('graficoFaixas', PAINEL.faixas);
            graficoBarras('graficoBairros', PAINEL.bairros);
        }
        desenharTudo();
        let redim;
        window.addEventListener('resize', () => { clearTimeout(redim); redim = setTimeout(desenharTudo, 150); });

        // ---------- Treinar agora ----------
        document.getElementById('btnTreinar').addEventListener('click', async function () {
            this.disabled = true;
            this.textContent = 'Treinando…';
            try {
                const r = await (await fetch('<?= BASE_URL ?>/routes.php?action=apiEtaTreinar', { method: 'POST' })).json();
                if (!r.success) throw new Error(r.error);
                location.reload();
            } catch (err) {
                alert(err.message || 'Não foi possível treinar agora.');
                this.disabled = false;
                this.textContent = 'Treinar modelo agora';
            }
        });
    </script>
    <?php endif; ?>

    <script>
        // =========================
        // SIDEBAR RESPONSIVA
        // =========================

        const toggleSidebarBtn = document.getElementById('toggleSidebar');
        const sidebar = document.getElementById('sidebar');
        const closeSidebarBtn = document.getElementById('closeSidebar');
        const overlay = document.getElementById('overlay');
        const mainContent = document.getElementById('mainContent');

        if (toggleSidebarBtn) {
            toggleSidebarBtn.addEventListener('click', () => {
                if (window.innerWidth <= 1024) {
                    sidebar.classList.toggle('mobile-open');
                    if (overlay) overlay.classList.toggle('active');
                } else {
                    sidebar.classList.toggle('sidebar-closed');
                    mainContent.classList.toggle('sidebar-expanded');
                }
            });
        }

        if (closeSidebarBtn) {
            closeSidebarBtn.addEventListener('click', () => {
                sidebar.classList.remove('mobile-open');
                if (overlay) overlay.classList.remove('active');
            });
        }

        if (overlay) {
            overlay.addEventListener('click', () => {
                sidebar.classList.remove('mobile-open');
                overlay.classList.remove('active');
            });
        }
    </script>

    <script>
        window.GUIAR_EMPRESA_LOGO = <?= json_encode($logoEmpresaSidebar ?? '') ?>;
    </script>
    <script src="<?= BASE_URL ?>/public/js/empresa-palette.js"></script>
</body>

</html>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="shortcut icon" type="image/png" href="<?= BASE_URL ?>/img/logoIcon.png">

    <title>Mapa de Entregas | GUIAR</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?= BASE_URL ?>/style/sidebar.css">

    <!-- Google Fonts: Inter & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Leaflet CSS (rotas e tempos vêm direto da API do OSRM) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />

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

        /* SIDEBAR MOBILE */
        @media (max-width: 768px) {
            #sidebar {
                transform: translateX(-100%);
                transition: transform .3s cubic-bezier(0.4, 0, 0.2, 1);
                border-radius: 0 !important;
                top: 0 !important;
                left: 0 !important;
                margin: 0 !important;
                height: 100vh !important;
                width: 280px !important;
            }

            #sidebar.mobile-open {
                transform: translateX(0);
            }

            #overlay.active {
                display: block;
            }
        }

        /* MAPA */
        #map {
            height: 100%;
            width: 100%;
            z-index: 1;
            background: #eef2f6;
        }

        .leaflet-routing-container {
            display: none !important;
        }

        /* CUSTOM LEAFLET CONTROLS */
        .leaflet-bar {
            border: 0 !important;
            box-shadow: 0 4px 16px rgba(15, 23, 42, 0.12) !important;
            border-radius: 12px !important;
            overflow: hidden;
        }

        .leaflet-bar a {
            background-color: #ffffff !important;
            color: #1e293b !important;
            border-bottom: 1px solid #f1f5f9 !important;
            width: 36px !important;
            height: 36px !important;
            line-height: 36px !important;
            font-weight: 700 !important;
            transition: all 0.2s ease;
        }

        .leaflet-bar a:hover {
            background-color: #f8fafc !important;
            color: var(--emp-primary) !important;
        }

        /* CUSTOM MODERN POPUP */
        .leaflet-popup-content-wrapper {
            background: #ffffff !important;
            border-radius: 18px !important;
            box-shadow: 0 16px 36px -8px rgba(15, 23, 42, 0.2), 0 4px 12px rgba(15, 23, 42, 0.08) !important;
            border: 1px solid #edf2f7 !important;
            padding: 0 !important;
            overflow: hidden;
        }

        .leaflet-popup-content {
            margin: 0 !important;
            line-height: 1.45 !important;
        }

        .leaflet-popup-tip {
            background: #ffffff !important;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08) !important;
        }

        .leaflet-container a.leaflet-popup-close-button {
            top: 10px !important;
            right: 12px !important;
            color: #94a3b8 !important;
            font-size: 17px !important;
            padding: 4px !important;
            border-radius: 999px !important;
            transition: all 0.2s ease;
        }

        .leaflet-container a.leaflet-popup-close-button:hover {
            color: #0f172a !important;
            background: #f1f5f9;
        }

        /* BOTÃO CONCLUIR ENTREGA NO POPUP */
        .btn-concluir {
            background-color: var(--emp-primary);
            color: var(--emp-primary-contrast);
            padding: 10px 14px;
            border-radius: 12px;
            border: none;
            cursor: pointer;
            width: 100%;
            font-weight: 700;
            font-family: 'Inter', sans-serif;
            font-size: 12px;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            box-shadow: 0 6px 14px -3px var(--emp-primary-ring);
        }

        .btn-concluir:hover:not(:disabled) {
            background-color: var(--emp-primary-hover);
            transform: translateY(-1px);
        }

        .btn-concluir:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        /* BOTÃO PRIMÁRIO */
        .btn-emp-primary {
            background-color: var(--emp-primary);
            color: var(--emp-primary-contrast);
            box-shadow: 0 8px 18px -4px var(--emp-primary-ring);
        }

        .btn-emp-primary:hover:not(:disabled) {
            background-color: var(--emp-primary-hover);
        }

        /* MODERN MOTO MARKER */
        .moto-marker {
            background: none;
            border: none;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .moto-pin-bubble {
            position: relative;
            display: flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 999px;
            background: #ffffff;
            box-shadow: 0 6px 18px rgba(15, 23, 42, 0.18);
            border: 2px solid var(--emp-primary);
            font-weight: 700;
            font-size: 11px;
            color: #0f172a;
            white-space: nowrap;
            transition: transform 0.2s ease;
            z-index: 10;
        }

        .moto-pin-bubble:hover {
            transform: scale(1.08);
        }

        .moto-icon-box {
            font-size: 15px;
            line-height: 1;
            display: inline-block;
            animation: moto-bob 1s ease-in-out infinite alternate;
        }

        @keyframes moto-bob {
            from { transform: translateY(0); }
            to { transform: translateY(-2px); }
        }

        .pulse-ring {
            position: absolute;
            width: 44px;
            height: 44px;
            border: 3px solid var(--emp-primary);
            border-radius: 999px;
            animation: pulse-animation 1.6s infinite ease-out;
            pointer-events: none;
            z-index: 1;
        }

        @keyframes pulse-animation {
            0% {
                transform: scale(0.65);
                opacity: 0.95;
            }
            100% {
                transform: scale(1.75);
                opacity: 0;
            }
        }

        /* DELIVERY MARKER PIN */
        .delivery-marker {
            background: var(--emp-primary);
            border-radius: 999px;
            width: 26px;
            height: 26px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--emp-primary-contrast);
            border: 2.5px solid #ffffff;
            font-size: 11px;
            font-weight: 800;
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.25);
            transition: transform 0.2s ease;
        }

        .delivery-marker.next-stop {
            background: var(--emp-secondary);
            border-color: var(--emp-primary);
            color: var(--emp-secondary-contrast);
            transform: scale(1.15);
        }

        .delivery-marker:hover {
            transform: scale(1.18);
        }

        /* CUSTOM SCROLLBAR */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 999px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* CARDS & GLASS PANELS */
        .delivery-card {
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .delivery-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 24px -6px rgba(15, 23, 42, 0.08);
        }

        .delivery-card.focused {
            border-color: var(--emp-primary) !important;
            box-shadow: 0 0 0 3px var(--emp-primary-ring) !important;
        }

        .glass-panel {
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.8);
            box-shadow: 0 8px 30px rgba(15, 23, 42, 0.08);
        }
    </style>
</head>

<body class="sidebar-page bg-[#f1f4fa] text-slate-800 antialiased h-screen overflow-hidden flex flex-col md:flex-row">

    <!-- OVERLAY MOBILE -->
    <div id="overlay" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-40 md:hidden transition-opacity"></div>

    <?php
    $nomeEntregador = $nomeEntregador ?? ($_SESSION['nome_usuario'] ?? ($_SESSION['nome_entregador'] ?? 'Entregador'));
    ?>

    <!-- SIDEBAR -->
    <aside id="sidebar"
        class="w-72 bg-white flex flex-col fixed top-0 left-0 text-slate-700 z-50 transition-all rounded-none overflow-hidden h-screen border-r border-slate-200/80 shadow-sm">

        <div class="flex flex-col h-full">

            <!-- BOTÃO FECHAR MOBILE -->
            <button id="closeSidebar"
                class="absolute top-5 right-5 md:hidden text-slate-500 hover:text-slate-800 text-xl font-bold p-1 rounded-lg transition-colors z-50">
                ✕
            </button>

            <!-- LOGO / IDENTIFICAÇÃO DO PAINEL -->
            <div class="px-6 pt-7 pb-4 mb-2 flex justify-start items-center gap-3">
                <div style="width: 58px; height: 58px; border-radius: 50%; overflow: hidden; flex-shrink: 0; background: #fff; border: 2px solid var(--emp-primary-light-border); box-shadow: 0 4px 14px var(--emp-primary-ring); display: flex; align-items: center; justify-content: center; padding: 6px;">
                    <img src="<?= BASE_URL ?>/img/logoIcon.png"
                        alt="Logo GUIAR"
                        style="width: 100%; height: 100%; object-fit: contain; object-position: center; display: block;">
                </div>
                <div class="min-w-0">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Painel do Entregador</p>
                    <p class="text-sm font-extrabold text-slate-800 truncate">GUIAR Entregas</p>
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

                <!-- ABRIR MAPA (ATIVO) -->
                <a href="<?= BASE_URL ?>/routes.php?action=mapaEntregador"
                    class="menu-item active flex items-center gap-3.5 px-4 py-3 rounded-xl font-bold text-sm transition-all"
                    style="background-color: var(--emp-primary); color: var(--emp-primary-contrast); box-shadow: 0 8px 18px -4px var(--emp-primary-ring);">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                    <img src="<?= BASE_URL ?>/img/LogoGuiar.png" alt="GUIAR" class="w-24 h-auto object-contain opacity-90">
                    <p class="text-[10px] text-slate-400 text-center leading-tight">
                        Conectando você ao seu destino.
                    </p>
                </div>

                <div class="flex items-center gap-3 p-2.5 rounded-2xl bg-slate-50 border border-slate-200/80">
                    <div class="w-9 h-9 rounded-full font-black text-xs flex items-center justify-center shadow-sm flex-shrink-0"
                        style="background-color: var(--emp-secondary); color: var(--emp-secondary-contrast);">
                        <?= strtoupper(substr($nomeEntregador, 0, 2)) ?>
                    </div>

                    <div class="flex-grow min-w-0">
                        <p class="font-bold text-xs text-slate-800 truncate">
                            <?= htmlspecialchars($nomeEntregador, ENT_QUOTES, 'UTF-8') ?>
                        </p>
                        <p class="text-[10px] text-slate-400 font-bold tracking-wider uppercase">
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

    <!-- MAIN WRAPPER -->
    <main class="flex-1 flex flex-col h-screen min-w-0 md:ml-72 overflow-hidden">

        <!-- HEADER SUPERIOR MODERNO -->
        <header class="bg-white/80 backdrop-blur-md border-b border-slate-200/80 px-4 md:px-8 py-3.5 md:py-4 flex items-center justify-between gap-3 sticky top-0 z-20 flex-shrink-0">

            <div class="flex items-center gap-3.5 min-w-0">
                <!-- BOTÃO HAMBURGER MOBILE -->
                <button id="hamburger"
                    class="md:hidden flex items-center justify-center w-10 h-10 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <!-- ÍCONE DA ABA -->
                <div class="hidden sm:flex items-center justify-center flex-shrink-0 transition-transform hover:scale-105"
                    style="color: var(--emp-primary);">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                    </svg>
                </div>

                <div class="min-w-0">
                    <h1 class="heading-font text-lg md:text-xl font-extrabold text-slate-900 tracking-tight truncate">
                        Mapa de Entregas
                    </h1>
                    <p class="text-xs md:text-sm text-slate-500 font-medium truncate">
                        Visualize a rota das suas entregas e sua localização em tempo real
                    </p>
                </div>
            </div>

            <!-- STATUS GPS & AÇÕES DE TOPO -->
            <div class="flex items-center gap-2 sm:gap-2.5 flex-shrink-0">

                <!-- INDICADOR DE GPS AO VIVO -->
                <div id="gpsStatusBadge" class="hidden xl:flex items-center gap-2 px-3 py-1.5 rounded-full bg-amber-50 border border-amber-200/80 text-amber-700 text-xs font-semibold">
                    <span class="relative flex h-2.5 w-2.5">
                        <span id="gpsPing" class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                        <span id="gpsDot" class="relative inline-flex rounded-full h-2.5 w-2.5 bg-amber-500"></span>
                    </span>
                    <span id="gpsStatusText">Buscando GPS...</span>
                </div>

                <!-- CONTADOR DE ENTREGAS PENDENTES -->
                <div class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold border transition-all"
                    style="background-color: var(--emp-primary-light); color: var(--emp-primary); border-color: var(--emp-primary-light-border);">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    <span><strong id="badgePedidosCount">0</strong> entregas</span>
                </div>

                <!-- BOTÃO ENQUADRAR MAPA -->
                <button type="button" id="btnFitBounds" title="Enquadrar sua posição e todas as entregas no mapa"
                    class="p-2 sm:px-3 sm:py-2 rounded-xl bg-white border border-slate-200 hover:border-slate-300 text-slate-700 hover:text-slate-900 shadow-sm text-xs font-bold flex items-center gap-1.5 transition-all active:scale-95">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                    </svg>
                    <span class="hidden md:inline">Enquadrar</span>
                </button>

                <!-- BOTÃO PRINCIPAL: COMEÇAR PERCURSO -->
                <button type="button" id="criarRota"
                    class="btn-emp-primary flex items-center gap-2 font-bold text-xs sm:text-sm px-3.5 sm:px-5 py-2 sm:py-2.5 rounded-xl transition-all hover:scale-[1.02] active:scale-95">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 sm:w-5 sm:h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    <span>Começar Percurso</span>
                </button>
            </div>
        </header>

        <!-- SELETOR MOBILE ENTRE MAPA E LISTA -->
        <div class="lg:hidden px-4 pt-3 pb-1 flex gap-2 bg-slate-100 border-b border-slate-200 flex-shrink-0">
            <button type="button" id="tabToggleMap"
                class="flex-1 py-2 text-xs font-bold rounded-xl transition-all shadow-sm"
                style="background-color: var(--emp-primary); color: var(--emp-primary-contrast);">
                🗺️ Visualizar Mapa
            </button>
            <button type="button" id="tabToggleList"
                class="flex-1 py-2 text-xs font-bold rounded-xl bg-white text-slate-700 border border-slate-200 transition-all">
                📦 Entregas (<span id="mobileCountBadge">0</span>)
            </button>
        </div>

        <!-- CONTEÚDO PRINCIPAL (MAPA + LISTA LATERAL DE ENTREGAS) -->
        <section class="flex-1 overflow-hidden p-3 sm:p-5 lg:p-6 pb-20 lg:pb-6">
            <div class="h-full flex flex-col lg:flex-row gap-4 sm:gap-5">

                <!-- ÁREA PRINCIPAL DO MAPA -->
                <div id="mapWrapper"
                    class="flex-1 h-full min-h-[380px] bg-white rounded-2xl sm:rounded-3xl border border-slate-200/90 shadow-sm overflow-hidden flex flex-col relative transition-all">

                    <!-- BARRA FLUTUANTE DE CONTROLES NO MAPA -->
                    <div class="absolute top-3 left-3 right-3 sm:left-4 sm:right-auto z-10 flex flex-wrap items-center gap-2 pointer-events-auto">
                        <div class="glass-panel px-3 py-1.5 rounded-2xl flex items-center gap-2 w-full sm:w-72 shadow-md">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <input id="inputBuscaMapa" type="text"
                                placeholder="Buscar cliente ou endereço..."
                                class="w-full bg-transparent text-xs font-medium text-slate-800 placeholder-slate-400 focus:outline-none" />
                            <button type="button" id="btnClearSearch" class="hidden text-slate-400 hover:text-slate-600 text-xs font-bold px-1">✕</button>
                        </div>

                        <!-- ATALHO: CENTRALIZAR NA MOTO -->
                        <button type="button" id="btnCentralizarMoto"
                            class="hidden sm:flex items-center gap-1.5 glass-panel px-3 py-2 rounded-2xl shadow-md text-xs font-bold text-slate-700 hover:text-[#4F46E5] transition-all active:scale-95">
                            <span>🏍️</span>
                            <span>Minha Posição</span>
                        </button>
                    </div>

                    <!-- LEAFLET MAP ELEMENT -->
                    <div id="map"></div>

                    <!-- PAINEL DE PREVISÃO DE CHEGADA (estilo navegação) -->
                    <div id="etaPainel" class="hidden absolute z-[1000] left-3 right-3 top-16 sm:left-4 sm:right-auto sm:w-[23rem] max-w-[calc(100%-1.5rem)] bg-white/95 backdrop-blur rounded-2xl shadow-xl border border-slate-200 px-5 py-4" role="status" aria-live="polite">
                        <div class="flex items-baseline gap-3">
                            <span id="etaTempo" class="heading-font text-3xl font-extrabold leading-none" style="color: var(--emp-primary);"></span>
                            <span id="etaDistancia" class="text-base font-semibold text-slate-500"></span>
                        </div>
                        <p id="etaLinhaChegada" class="mt-1.5 text-sm text-slate-700"></p>
                        <p id="etaCliente" class="mt-0.5 text-sm text-slate-500 truncate"></p>
                        <details id="etaParadas" class="hidden mt-2 pt-2 border-t border-slate-100 group">
                            <summary class="cursor-pointer select-none text-sm font-semibold text-slate-700 list-none flex items-center justify-between">
                                <span id="etaParadasTitulo">Próximas paradas</span>
                                <span class="text-slate-400 transition-transform group-open:rotate-180">▾</span>
                            </summary>
                            <ol id="etaListaParadas" class="mt-2 space-y-1.5 text-sm"></ol>
                        </details>
                        <p id="etaFonte" class="mt-2 pt-2 border-t border-slate-100 text-xs text-slate-500"></p>
                        <p id="etaTela" class="hidden mt-1 text-xs"></p>
                    </div>

                    <!-- LEGENDA FLUTUANTE INFERIOR -->
                    <div class="absolute bottom-4 left-4 z-10 glass-panel px-3.5 py-2 rounded-2xl text-[11px] font-semibold text-slate-700 flex items-center gap-3.5 shadow-md hidden sm:flex pointer-events-auto">
                        <div class="flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded-full border border-white shadow-sm" style="background-color: var(--emp-primary);"></span>
                            <span>Sua localização</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded-full border border-white shadow-sm" style="background-color: var(--emp-secondary);"></span>
                            <span>Próxima parada</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="text-xs">📍</span>
                            <span>Ponto de entrega</span>
                        </div>
                        <div class="flex items-center gap-1.5 border-l border-slate-200 pl-3">
                            <span class="w-4 h-1 rounded-full" style="background-color: var(--emp-primary);"></span>
                            <span>Rota otimizada</span>
                        </div>
                    </div>

                </div>

                <!-- LISTA LATERAL DE ENTREGAS -->
                <div id="deliveriesListWrapper"
                    class="w-full lg:w-[390px] xl:w-[420px] flex-shrink-0 h-full bg-white rounded-2xl sm:rounded-3xl border border-slate-200/90 shadow-sm flex flex-col overflow-hidden hidden lg:flex">

                    <!-- TOPO DA LISTA -->
                    <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between gap-3 bg-slate-50/50">
                        <div>
                            <h2 class="heading-font text-base sm:text-lg font-bold text-slate-900">
                                Entregas na Rota
                            </h2>
                            <p class="text-xs text-slate-500 font-medium">
                                Selecione uma parada para visualizar no mapa
                            </p>
                        </div>

                        <!-- BADGE TOTAL -->
                        <div class="flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border"
                            style="background-color: var(--emp-primary-light); color: var(--emp-primary); border-color: var(--emp-primary-light-border);">
                            <span class="w-2 h-2 rounded-full animate-pulse" style="background-color: var(--emp-primary);"></span>
                            <span id="totalPedidosBadge">0 pendentes</span>
                        </div>
                    </div>

                    <!-- CORPO SCROLLÁVEL COM CARDS DOS PEDIDOS -->
                    <div id="pedidosListContainer" class="flex-1 overflow-y-auto p-3.5 sm:p-4 space-y-3">
                        <div class="flex flex-col items-center justify-center py-16 text-center text-slate-400 gap-3">
                            <svg class="animate-spin h-7 w-7" style="color: var(--emp-primary);" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span class="text-xs font-semibold">Carregando entregas disponíveis...</span>
                        </div>
                    </div>

                    <!-- FOOTER INFORMATIVO -->
                    <div class="px-4 py-3 bg-slate-50 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                        <span class="flex items-center gap-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Recálculo automático a cada 30 s
                        </span>
                        <span id="routeStatusFooter" class="font-bold text-slate-600">Aguardando percurso</span>
                    </div>

                </div>

            </div>
        </section>

    </main>

    <!-- MOBILE BOTTOM NAVIGATION (TELAS PEQUENAS) -->
    <nav class="lg:hidden fixed bottom-0 left-0 right-0 bg-white/95 backdrop-blur-md border-t border-slate-200/90 flex justify-around items-center h-16 z-30 shadow-lg">
        <a href="<?= BASE_URL ?>/routes.php?action=meusPedidosEntregador"
            class="flex flex-col items-center gap-0.5 text-[11px] text-slate-500 hover:text-slate-900 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 opacity-75" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
            </svg>
            Pedidos
        </a>

        <a href="<?= BASE_URL ?>/routes.php?action=mapaEntregador"
            class="flex flex-col items-center gap-0.5 text-[11px] font-bold transition-colors"
            style="color: var(--emp-primary);">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
    </nav>

    <!-- Scripts Leaflet -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <script>
        // =====================================================================
        // MAPA DO ENTREGADOR
        //  - Rota otimizada com todas as paradas (serviço trip do OSRM)
        //  - Previsão de chegada ajustada pelo modelo treinado com as entregas reais do GUIAR
        //  - Mapa acompanha o entregador; tela fica ligada durante o percurso
        //  - Previsão enviada ao vivo para o link do cliente e o mapa do gestor
        // =====================================================================
        const API = '<?= BASE_URL ?>/routes.php?action=';
        const OSRM = 'https://router.project-osrm.org';
        const RECALCULO_MS = 30000;          // recalcula a rota no máximo a cada 30 s…
        const RECALCULO_DIST_M = 150;        // …ou quando andar 150 m desde o último cálculo
        const RAIO_CHEGADA_M = 40;           // distância do destino considerada "chegou"
        const ZOOM_SEGUINDO = 16;
        const VISAO_GERAL_ROTA_MS = 4000;    // tempo mostrando a rota inteira ao começar
        const COR_ROTA = getComputedStyle(document.documentElement).getPropertyValue('--emp-primary').trim() || '#4F46E5';

        // CRIA O MAPA (zoom no canto inferior direito)
        var map = L.map('map', { zoomControl: false }).setView([-22.3674, -46.9428], 15);
        L.control.zoom({ position: 'bottomright' }).addTo(map);

        // ÍCONE MODERNO DA MOTO DO ENTREGADOR
        var emojiIcon = L.divIcon({
            className: 'moto-marker',
            html: `
                <div class="pulse-ring"></div>
                <div class="moto-pin-bubble">
                    <span class="moto-icon-box">🛵</span>
                    <span>Você</span>
                </div>
            `,
            iconSize: [76, 40],
            iconAnchor: [38, 20]
        });

        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}.png?key=cb1_3p8m_1_09944103c911a8c84b986e32', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>, &copy; <a href="https://carto.com/attributions">CARTO</a>',
            subdomains: 'abcd',
            maxZoom: 20
        }).addTo(map);

        var userMarker = null;
        var currentUserPosition = null;
        var pedidos = [];            // pedidos em aberto do entregador
        var marcadores = {};         // id_pedido -> marcador
        var percursoAtivo = false;
        var destinoAtual = null;     // { pedido, destino, viagem, linha, restanteS, distanciaM, calculadoEm, posCalculo, chegou }
        var recalculando = false;
        var plano = null;            // rota otimizada: { paradas: [{id_pedido, ordem, trecho_s}], tempo_no_local_s, fonte, pedidos }
        var telaLigada = null;       // Wake Lock
        var seguindo = true;         // mapa acompanha o entregador (desliga ao arrastar)
        var pausarSeguirAte = 0;
        var termoBusca = '';

        // ---------- utilidades ----------
        function escaparHtml(texto) {
            const div = document.createElement('div');
            div.textContent = texto == null ? '' : String(texto);
            return div.innerHTML;
        }

        function formatarDuracao(segundos) {
            const min = Math.round(segundos / 60);
            if (min < 1) return '< 1 min';
            if (min < 60) return min + ' min';
            return Math.floor(min / 60) + ' h ' + String(min % 60).padStart(2, '0') + ' min';
        }

        function formatarDistancia(metros) {
            if (metros < 1000) return Math.round(metros / 10) * 10 + ' m';
            return (metros / 1000).toLocaleString('pt-BR', { maximumFractionDigits: 1 }) + ' km';
        }

        function formatarHora(data) {
            return data.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
        }

        async function postJson(acao, corpo) {
            const res = await fetch(API + acao, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(corpo)
            });
            return res.json();
        }

        function temCoordenadas(p) {
            return p.latitude && p.longitude;
        }

        // ---------- OSRM ----------
        function coord(latLng) {
            return latLng.lng.toFixed(6) + ',' + latLng.lat.toFixed(6);
        }

        /** Melhor ordem de visita (serviço trip do OSRM), começando na posição atual. */
        async function osrmViagem(origem, destinos) {
            const pontos = [origem, ...destinos].map(coord).join(';');
            const res = await fetch(`${OSRM}/trip/v1/driving/${pontos}?source=first&roundtrip=false&destination=any&overview=false`);
            const data = await res.json();
            if (data.code !== 'Ok' || !data.trips.length) throw new Error('OSRM: ' + (data.message || data.code));
            const ordem = destinos.map((_, i) => i).sort((a, b) => data.waypoints[a + 1].waypoint_index - data.waypoints[b + 1].waypoint_index);
            return {
                ordem: ordem,
                trechos: data.trips[0].legs.map(l => ({ duracao: l.duration, distancia: l.distance }))
            };
        }

        async function osrmRota(origem, destino) {
            const res = await fetch(`${OSRM}/route/v1/driving/${coord(origem)};${coord(destino)}?overview=full&geometries=geojson`);
            const data = await res.json();
            if (data.code !== 'Ok' || !data.routes.length) throw new Error('OSRM: ' + (data.message || data.code));
            const rota = data.routes[0];
            return {
                duracao: rota.duration,
                distancia: rota.distance,
                linha: rota.geometry.coordinates.map(c => [c[1], c[0]])
            };
        }

        // ---------- linha da rota (contorno branco + cor da empresa) ----------
        function criarLinhaRota(pontos) {
            return L.featureGroup([
                L.polyline(pontos, { color: '#ffffff', weight: 8, opacity: 0.8 }),
                L.polyline(pontos, { color: COR_ROTA, weight: 5, opacity: 0.95 })
            ]).addTo(map);
        }

        function atualizarLinhaRota(linha, pontos) {
            linha.eachLayer(l => l.setLatLngs(pontos));
        }

        // ---------- mapa acompanha o entregador ----------
        const BotaoCentralizar = L.Control.extend({
            options: { position: 'bottomright' },
            onAdd: function() {
                const btn = L.DomUtil.create('button', 'leaflet-bar');
                btn.type = 'button';
                btn.id = 'btnCentralizar';
                btn.title = 'Centralizar na minha localização';
                btn.setAttribute('aria-label', 'Centralizar na minha localização');
                btn.innerHTML = '📍 Centralizar';
                btn.style.cssText = 'display:none;background:#fff;border:none;padding:8px 12px;font:700 12px Inter,sans-serif;color:#0F172A;cursor:pointer;';
                L.DomEvent.disableClickPropagation(btn);
                L.DomEvent.on(btn, 'click', recentralizar);
                return btn;
            }
        });
        new BotaoCentralizar().addTo(map);

        function ativarSeguir(ligado) {
            seguindo = ligado;
            document.getElementById('btnCentralizar').style.display = ligado ? 'none' : 'block';
        }

        /** Altura ocupada pelo painel de previsão no topo do mapa (0 se escondido). */
        function alturaPainel() {
            const p = document.getElementById('etaPainel');
            return p.classList.contains('hidden') ? 0 : p.offsetTop + p.offsetHeight;
        }

        /** Centro do mapa que deixa a posição no meio da área visível (abaixo do painel). */
        function centroVisivel(posicao, zoom) {
            const ponto = map.project(posicao, zoom).subtract([0, alturaPainel() / 2]);
            return map.unproject(ponto, zoom);
        }

        function recentralizar() {
            if (!currentUserPosition) {
                alert('Aguardando sinal de GPS...');
                return;
            }
            pausarSeguirAte = 0;
            ativarSeguir(true);
            const zoom = Math.max(map.getZoom(), ZOOM_SEGUINDO);
            map.setView(centroVisivel(currentUserPosition, zoom), zoom);
        }

        function seguirEntregador() {
            if (!currentUserPosition) return;
            if (pausarSeguirAte && Date.now() >= pausarSeguirAte) {
                pausarSeguirAte = 0;
                ativarSeguir(true);
            }
            if (seguindo) map.panTo(centroVisivel(currentUserPosition, map.getZoom()), { animate: true });
        }

        // Arrastar o mapa = quer olhar outro lugar: para de seguir até tocar em "Centralizar"
        map.on('dragstart', () => {
            pausarSeguirAte = 0;
            ativarSeguir(false);
        });

        // ---------- painel de previsão ----------
        const painel = document.getElementById('etaPainel');

        /** Horário previsto de cada parada do plano: chegada atual + tempo no local + trechos. */
        function horariosPrevistos() {
            const horarios = {};
            if (!plano || !destinoAtual) return horarios;
            const d = destinoAtual;
            const local = plano.tempo_no_local_s || 120;
            const restante = d.chegou ? 0 : Math.max(0, d.restanteS - (Date.now() - d.calculadoEm) / 1000);
            let cursor = Date.now() + restante * 1000;
            plano.paradas.forEach((parada, i) => {
                if (i > 0) cursor += (local + parada.trecho_s) * 1000;
                horarios[parada.id_pedido] = new Date(cursor);
            });
            return horarios;
        }

        function renderizarPainel() {
            if (!destinoAtual) {
                painel.classList.add('hidden');
                return;
            }
            painel.classList.remove('hidden');
            const d = destinoAtual;
            document.getElementById('etaCliente').textContent = d.pedido.nome_cliente + ' · ' + d.pedido.endereco;

            if (d.chegou) {
                document.getElementById('etaTempo').textContent = 'Você chegou';
                document.getElementById('etaDistancia').textContent = '';
                document.getElementById('etaLinhaChegada').textContent = 'Toque em Concluir quando entregar o pedido.';
            } else {
                const restante = Math.max(0, d.restanteS - (Date.now() - d.calculadoEm) / 1000);
                document.getElementById('etaTempo').textContent = formatarDuracao(restante);
                document.getElementById('etaDistancia').textContent = formatarDistancia(d.distanciaM);
                document.getElementById('etaLinhaChegada').innerHTML =
                    'Chegada prevista às <b>' + formatarHora(new Date(Date.now() + restante * 1000)) + '</b>';
            }

            const v = d.viagem;
            let fonte;
            if (v.fonte === 'modelo') {
                fonte = '🧠 Ajustado pelo histórico real de entregas do GUIAR';
                if (v.modelo && v.modelo.amostras) {
                    fonte += ` (modelo v${v.modelo.id_modelo}, ${v.modelo.amostras} entregas`;
                    if (v.modelo.mape_modelo != null) fonte += `, erro médio ${Math.round(v.modelo.mape_modelo * 100)}%`;
                    fonte += ')';
                }
            } else if (v.offline) {
                fonte = '⚠️ Estimativa só pelas ruas (servidor de previsão indisponível)';
            } else {
                fonte = '🛣️ Estimativa pelas ruas, sem trânsito — o modelo aprende com cada entrega concluída';
            }
            document.getElementById('etaFonte').textContent = fonte;
            renderizarParadas();
        }

        function renderizarParadas() {
            const caixa = document.getElementById('etaParadas');
            const lista = document.getElementById('etaListaParadas');
            if (!plano || !destinoAtual || plano.paradas.length < 2) {
                caixa.classList.add('hidden');
                return;
            }
            const horarios = horariosPrevistos();
            const itens = plano.paradas.slice(1).map(parada => {
                const pedido = plano.pedidos.find(p => String(p.id_pedido) === String(parada.id_pedido));
                return `
                    <li class="flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full text-[11px] font-extrabold flex items-center justify-center flex-shrink-0"
                            style="background-color: var(--emp-primary); color: var(--emp-primary-contrast);">${parada.ordem}</span>
                        <span class="truncate text-slate-700">${escaparHtml(pedido ? pedido.nome_cliente : 'Pedido #' + parada.id_pedido)}</span>
                        <span class="ml-auto font-bold text-slate-900 tabular-nums">${formatarHora(horarios[parada.id_pedido])}</span>
                    </li>`;
            });
            const ultima = plano.paradas[plano.paradas.length - 1];
            lista.innerHTML = itens.join('');
            document.getElementById('etaParadasTitulo').textContent =
                `Próximas paradas (${itens.length}) · fim às ${formatarHora(horarios[ultima.id_pedido])}`;
            caixa.classList.remove('hidden');
        }

        setInterval(() => {
            if (destinoAtual && !destinoAtual.chegou) {
                renderizarPainel();
                renderizarListaPedidos();
            }
        }, 15000);

        // ---------- tela sempre ligada (Wake Lock) ----------
        function mostrarAvisoTela() {
            const el = document.getElementById('etaTela');
            if (!percursoAtivo) { el.classList.add('hidden'); return; }
            el.classList.remove('hidden');
            if (telaLigada) {
                el.className = 'mt-1 text-xs text-emerald-700';
                el.textContent = '🔆 Tela mantida ligada durante o percurso';
            } else {
                el.className = 'mt-1 text-xs text-amber-700';
                el.textContent = '⚠️ Mantenha a tela ligada para a previsão continuar atualizando';
            }
        }

        async function manterTelaLigada() {
            if ('wakeLock' in navigator && !telaLigada) {
                try {
                    telaLigada = await navigator.wakeLock.request('screen');
                    telaLigada.addEventListener('release', () => { telaLigada = null; mostrarAvisoTela(); });
                } catch (err) {
                    console.warn('Não foi possível manter a tela ligada:', err.message);
                }
            }
            mostrarAvisoTela();
        }

        function liberarTelaLigada() {
            if (telaLigada) telaLigada.release();
            telaLigada = null;
            mostrarAvisoTela();
        }

        // O navegador solta o Wake Lock quando a aba fica em segundo plano: pede de novo ao voltar
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible' && percursoAtivo) manterTelaLigada();
        });

        // ---------- previsão ao vivo para o cliente e o gestor ----------
        function enviarProgresso() {
            const d = destinoAtual;
            if (!d || d.chegou || d.viagem.offline) return;
            postJson('apiEtaProgresso', {
                id_pedido: d.pedido.id_pedido,
                eta_restante_s: Math.round(d.restanteS),
                distancia_restante_m: Math.round(d.distanciaM)
            }).catch(err => console.error('Erro ao enviar previsão:', err));
        }

        // ---------- marcadores dos pedidos ----------
        /** Número de cada parada: ordem da rota otimizada; sem rota, ordem da lista. */
        function numeroParada(pedido) {
            if (plano) {
                const parada = plano.paradas.find(p => String(p.id_pedido) === String(pedido.id_pedido));
                if (parada) return parada.ordem;
            }
            return pedidos.findIndex(p => String(p.id_pedido) === String(pedido.id_pedido)) + 1;
        }

        function ehProxima(pedido) {
            return destinoAtual && String(destinoAtual.pedido.id_pedido) === String(pedido.id_pedido);
        }

        /** Pedidos na ordem da rota otimizada (os fora do plano vão para o fim). */
        function pedidosOrdenados() {
            return [...pedidos].sort((a, b) => numeroParada(a) - numeroParada(b));
        }

        function popupPedido(pedido, numero) {
            return `
                <div class="p-4 min-w-[240px]" style="font-family: 'Inter', sans-serif;">
                    <div class="flex items-center justify-between gap-2 pb-2.5 mb-2.5 border-b border-slate-100">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold text-white"
                            style="background-color: var(--emp-primary);">
                            Entrega #${numero}
                        </span>
                        <span class="text-[11px] font-bold text-slate-400">ID #${Number(pedido.id_pedido)}</span>
                    </div>

                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Cliente</p>
                    <p class="text-sm font-bold text-slate-900 mb-2.5">${escaparHtml(pedido.nome_cliente)}</p>

                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Endereço</p>
                    <p class="text-xs font-medium text-slate-600 mb-3.5 leading-relaxed">${escaparHtml(pedido.endereco)}</p>

                    <button type="button" class="btn-concluir" data-id="${Number(pedido.id_pedido)}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                        Concluir Entrega
                    </button>
                </div>
            `;
        }

        function atualizarMarcadores() {
            pedidos.filter(temCoordenadas).forEach(pedido => {
                const numero = numeroParada(pedido);
                const icone = L.divIcon({
                    className: 'delivery-marker-icon',
                    html: `<div class="delivery-marker ${ehProxima(pedido) ? 'next-stop' : ''}" id="pinMarker_${Number(pedido.id_pedido)}">${numero}</div>`,
                    iconSize: [26, 26],
                    iconAnchor: [13, 26]
                });
                let marker = marcadores[pedido.id_pedido];
                if (!marker) {
                    marker = L.marker([pedido.latitude, pedido.longitude], { icon: icone }).addTo(map);
                    marker.bindPopup('');
                    marker.on('popupopen', e => {
                        const btn = e.popup.getElement().querySelector('.btn-concluir');
                        if (btn) btn.onclick = () => concluirEntrega(pedido.id_pedido, btn);
                    });
                    marcadores[pedido.id_pedido] = marker;
                } else {
                    marker.setIcon(icone);
                }
                marker.setPopupContent(popupPedido(pedido, numero));
            });
        }

        // ---------- lista lateral de entregas ----------
        function renderizarListaPedidos() {
            const container = document.getElementById('pedidosListContainer');
            const total = pedidos.length;

            const badgePedidosCount = document.getElementById('badgePedidosCount');
            if (badgePedidosCount) badgePedidosCount.textContent = total;
            const totalPedidosBadge = document.getElementById('totalPedidosBadge');
            if (totalPedidosBadge) totalPedidosBadge.textContent = total + (total === 1 ? ' pendente' : ' pendentes');
            const mobileCountBadge = document.getElementById('mobileCountBadge');
            if (mobileCountBadge) mobileCountBadge.textContent = total;

            const footer = document.getElementById('routeStatusFooter');
            if (footer) {
                footer.textContent = destinoAtual
                    ? `Destino: ${destinoAtual.pedido.nome_cliente.split(' ')[0]} (${formatarDistancia(destinoAtual.distanciaM)})`
                    : (percursoAtivo ? 'Calculando rota…' : 'Aguardando percurso');
            }

            if (!container) return;

            const filtrados = pedidosOrdenados().filter(p => {
                if (!termoBusca) return true;
                return (p.nome_cliente || '').toLowerCase().includes(termoBusca) ||
                    (p.endereco || '').toLowerCase().includes(termoBusca) ||
                    String(p.id_pedido || '').includes(termoBusca);
            });

            if (filtrados.length === 0) {
                container.innerHTML = `
                    <div class="flex flex-col items-center justify-center py-12 px-4 text-center">
                        <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-400 mb-3 text-xl">📦</div>
                        <h4 class="font-bold text-slate-700 text-sm">Nenhuma entrega encontrada</h4>
                        <p class="text-xs text-slate-400 mt-1 max-w-xs">
                            ${termoBusca ? 'Nenhum pedido corresponde à sua pesquisa.' : 'Você não possui entregas pendentes no momento.'}
                        </p>
                    </div>
                `;
                return;
            }

            const horarios = horariosPrevistos();
            container.innerHTML = '';

            filtrados.forEach(pedido => {
                const id = Number(pedido.id_pedido);
                const isProxima = ehProxima(pedido);
                const chegou = isProxima && destinoAtual.chegou;
                const horario = horarios[pedido.id_pedido];

                const card = document.createElement('div');
                card.id = 'pedidoCard_' + id;
                card.className = 'delivery-card bg-white border border-slate-200 rounded-2xl p-4 shadow-sm border-l-4 cursor-pointer';
                card.style.borderLeftColor = isProxima ? 'var(--emp-secondary)' : 'var(--emp-primary)';
                card.onclick = () => focarPedidoNoMapa(id, pedido.latitude, pedido.longitude);

                const avatarStyle = isProxima
                    ? 'background-color: var(--emp-secondary); color: var(--emp-secondary-contrast);'
                    : 'background-color: var(--emp-primary); color: var(--emp-primary-contrast);';
                const pillStyle = isProxima
                    ? 'background-color: var(--emp-secondary-light); color: var(--emp-secondary-contrast); border-color: var(--emp-secondary);'
                    : 'background-color: var(--emp-primary-light); color: var(--emp-primary); border-color: var(--emp-primary-light-border);';
                const dotStyle = isProxima ? 'background-color: var(--emp-secondary-contrast);' : 'background-color: var(--emp-primary);';

                let previsao = '';
                if (chegou) {
                    previsao = '📍 Você chegou — conclua a entrega';
                } else if (horario) {
                    previsao = `🕒 ${isProxima ? 'Chegada' : 'Previsão'} às ${formatarHora(horario)}`;
                }

                card.innerHTML = `
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 rounded-xl font-extrabold text-xs flex items-center justify-center flex-shrink-0 shadow-sm" style="${avatarStyle}">
                                ${numeroParada(pedido)}
                            </div>
                            <div class="min-w-0">
                                <h3 class="font-bold text-slate-900 text-sm truncate">${escaparHtml(pedido.nome_cliente)}</h3>
                                <p class="text-[11px] text-slate-400 font-medium">Pedido #${id}</p>
                            </div>
                        </div>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold border" style="${pillStyle}">
                            <span class="w-1.5 h-1.5 rounded-full ${isProxima ? 'animate-ping' : ''}" style="${dotStyle}"></span>
                            <span>${isProxima ? 'Próxima parada' : 'Pendente'}</span>
                        </span>
                    </div>

                    <div class="mt-3 pt-2.5 border-t border-slate-100">
                        <p class="text-xs text-slate-600 flex items-start gap-1.5 leading-relaxed">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-slate-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span>${escaparHtml(pedido.endereco)}</span>
                        </p>
                        ${previsao ? `<p class="mt-1.5 text-xs font-bold" style="color: var(--emp-primary);">${previsao}</p>` : ''}
                    </div>

                    <div class="mt-3.5 pt-1 flex items-center gap-2">
                        <button type="button" data-acao="ver"
                            class="flex-1 py-2 px-3 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 transition-all flex items-center justify-center gap-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            Ver no mapa
                        </button>
                        <button type="button" data-acao="concluir"
                            class="btn-emp-primary flex-1 py-2 px-3 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                            </svg>
                            Concluir
                        </button>
                    </div>
                `;
                card.querySelector('[data-acao=ver]').onclick = ev => {
                    ev.stopPropagation();
                    focarPedidoNoMapa(id, pedido.latitude, pedido.longitude);
                };
                card.querySelector('[data-acao=concluir]').onclick = function(ev) {
                    ev.stopPropagation();
                    concluirEntrega(id, this);
                };
                container.appendChild(card);
            });
        }

        function focarPedidoNoMapa(pedidoId, lat, lng) {
            document.querySelectorAll('.delivery-card').forEach(c => c.classList.remove('focused'));
            const card = document.getElementById('pedidoCard_' + pedidoId);
            if (card) card.classList.add('focused');

            if (window.innerWidth < 1024 && tabToggleMap) tabToggleMap.click();

            if (lat && lng) {
                pausarSeguirAte = 0;
                ativarSeguir(false); // olhando um pedido: o mapa para de seguir a moto
                map.flyTo([lat, lng], 17, { duration: 0.9 });
                if (marcadores[pedidoId]) marcadores[pedidoId].openPopup();
            }
        }

        // ---------- pedidos ----------
        function carregarPedidos() {
            return fetch(API + 'apiPedidosMapa')
                .then(r => r.json())
                .then(data => {
                    pedidos = Array.isArray(data) ? data : [];
                    // remove marcadores de pedidos que não estão mais em aberto
                    Object.keys(marcadores).forEach(id => {
                        if (!pedidos.some(p => String(p.id_pedido) === id)) {
                            map.removeLayer(marcadores[id]);
                            delete marcadores[id];
                        }
                    });
                    atualizarMarcadores();
                    renderizarListaPedidos();
                })
                .catch(err => {
                    console.error('Erro ao carregar pedidos:', err);
                    renderizarListaPedidos();
                });
        }

        // ---------- percurso ----------
        /** Calcula a rota otimizada com as entregas restantes e segue para a primeira parada. */
        async function proximoDestino() {
            limparRota();
            const comCoordenadas = pedidos.filter(temCoordenadas);
            if (comCoordenadas.length === 0) {
                percursoAtivo = false;
                plano = null;
                liberarTelaLigada();
                atualizarMarcadores();
                renderizarListaPedidos();
                alert(pedidos.length ? 'As entregas restantes não têm localização no mapa.' : 'Todas as entregas foram concluídas!');
                return;
            }

            const destinos = comCoordenadas.map(p => L.latLng(p.latitude, p.longitude));
            const viagem = await osrmViagem(currentUserPosition, destinos);
            const ordenados = viagem.ordem.map(i => comCoordenadas[i]);
            const trechos = ordenados.map((p, k) => ({
                id_pedido: p.id_pedido,
                duracao_osrm_s: viagem.trechos[k].duracao,
                distancia_osrm_m: viagem.trechos[k].distancia
            }));

            try {
                const resp = await postJson('apiEtaPlanejar', { trechos: trechos });
                if (!resp.success) throw new Error(resp.error);
                plano = resp;
            } catch (err) {
                console.error('Plano de rota sem o servidor de previsão:', err);
                plano = {
                    paradas: trechos.map((t, k) => ({ id_pedido: t.id_pedido, ordem: k + 1, trecho_s: Math.round(t.duracao_osrm_s) })),
                    tempo_no_local_s: 120,
                    fonte: 'osrm'
                };
            }
            plano.pedidos = ordenados;
            await iniciarRota(ordenados[0]);
        }

        async function iniciarRota(pedido) {
            const destino = L.latLng(pedido.latitude, pedido.longitude);
            const rota = await osrmRota(currentUserPosition, destino);

            let viagem;
            try {
                const resp = await postJson('apiEtaIniciar', {
                    id_pedido: pedido.id_pedido,
                    origem_lat: currentUserPosition.lat,
                    origem_lng: currentUserPosition.lng,
                    duracao_osrm_s: rota.duracao,
                    distancia_osrm_m: rota.distancia
                });
                if (!resp.success) throw new Error(resp.error);
                viagem = resp;
            } catch (err) {
                console.error('Previsão de tempo indisponível:', err);
                viagem = { fator: 1, fonte: 'osrm', offline: true };
            }

            destinoAtual = {
                pedido: pedido,
                destino: destino,
                viagem: viagem,
                linha: criarLinhaRota(rota.linha),
                restanteS: rota.duracao * viagem.fator,
                distanciaM: rota.distancia,
                calculadoEm: Date.now(),
                posCalculo: currentUserPosition,
                chegou: currentUserPosition.distanceTo(destino) <= RAIO_CHEGADA_M
            };

            atualizarMarcadores();
            renderizarPainel();
            renderizarListaPedidos();

            // Mostra a rota inteira (abaixo do painel) por alguns segundos e depois volta a seguir o entregador
            map.fitBounds(destinoAtual.linha.getBounds().extend(currentUserPosition), {
                paddingTopLeft: [40, alturaPainel() + 30],
                paddingBottomRight: [40, 40]
            });
            ativarSeguir(false);
            pausarSeguirAte = Date.now() + VISAO_GERAL_ROTA_MS;
            setTimeout(seguirEntregador, VISAO_GERAL_ROTA_MS + 50); // mesmo se o GPS não mandar posição nova

            if (destinoAtual.chegou) registrarChegada();
            else enviarProgresso();
        }

        async function recalcularRota() {
            const d = destinoAtual;
            if (!d || d.chegou || recalculando) return;
            recalculando = true;
            try {
                const rota = await osrmRota(currentUserPosition, d.destino);
                if (destinoAtual !== d) return; // destino mudou durante a consulta
                atualizarLinhaRota(d.linha, rota.linha);
                d.restanteS = rota.duracao * d.viagem.fator;
                d.distanciaM = rota.distancia;
                d.calculadoEm = Date.now();
                d.posCalculo = currentUserPosition;
                renderizarPainel();
                renderizarListaPedidos();
                enviarProgresso();
            } catch (err) {
                console.error('Erro ao recalcular rota:', err);
            } finally {
                recalculando = false;
            }
        }

        function registrarChegada() {
            postJson('apiEtaChegada', { id_pedido: destinoAtual.pedido.id_pedido })
                .catch(err => console.error('Erro ao registrar chegada:', err));
        }

        function acompanharProgresso() {
            const d = destinoAtual;
            if (!d || d.chegou) return;

            if (currentUserPosition.distanceTo(d.destino) <= RAIO_CHEGADA_M) {
                d.chegou = true;
                registrarChegada();
                renderizarPainel();
                renderizarListaPedidos();
                return;
            }

            const tempoDesdeCalculo = Date.now() - d.calculadoEm;
            const andouDesdeCalculo = currentUserPosition.distanceTo(d.posCalculo);
            if (tempoDesdeCalculo >= RECALCULO_MS || andouDesdeCalculo >= RECALCULO_DIST_M) {
                recalcularRota();
            }
        }

        function limparRota() {
            if (destinoAtual && destinoAtual.linha) map.removeLayer(destinoAtual.linha);
            destinoAtual = null;
            renderizarPainel();
        }

        // ---------- concluir entrega (popup do mapa ou card da lista) ----------
        function concluirEntrega(pedidoId, btn) {
            const textoOriginal = btn.innerHTML;
            btn.innerHTML = 'Processando...';
            btn.disabled = true;

            const corpo = { id_pedido: pedidoId };
            if (currentUserPosition) {
                corpo.latitude = currentUserPosition.lat;
                corpo.longitude = currentUserPosition.lng;
            }

            fetch(API + 'concluirEntrega', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(corpo)
            })
                .then(r => r.text())
                .then(texto => {
                    if (texto.trim() !== 'success') throw new Error(texto);

                    map.closePopup();
                    if (marcadores[pedidoId]) {
                        map.removeLayer(marcadores[pedidoId]);
                        delete marcadores[pedidoId];
                    }
                    pedidos = pedidos.filter(p => String(p.id_pedido) !== String(pedidoId));
                    if (plano) {
                        plano.paradas = plano.paradas.filter(p => String(p.id_pedido) !== String(pedidoId));
                        plano.pedidos = plano.pedidos.filter(p => String(p.id_pedido) !== String(pedidoId));
                    }

                    const eraODestino = destinoAtual && String(destinoAtual.pedido.id_pedido) === String(pedidoId);
                    if (eraODestino) limparRota();
                    atualizarMarcadores();
                    renderizarListaPedidos();

                    alert('Entrega concluída com sucesso!');

                    // Segue automaticamente para a próxima entrega
                    if (percursoAtivo && (eraODestino || !destinoAtual) && currentUserPosition) {
                        proximoDestino().catch(err => {
                            console.error(err);
                            alert('Não foi possível calcular a próxima rota. Tente "Começar Percurso" novamente.');
                        });
                    }
                })
                .catch(err => {
                    console.error('Erro ao concluir entrega:', err);
                    alert('Erro ao concluir a entrega.');
                    btn.innerHTML = textoOriginal;
                    btn.disabled = false;
                });
        }

        // ---------- geolocalização ----------
        if (navigator.geolocation) {
            navigator.geolocation.watchPosition(function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                currentUserPosition = L.latLng(lat, lng);

                const gpsBadge = document.getElementById('gpsStatusBadge');
                const gpsPing = document.getElementById('gpsPing');
                const gpsDot = document.getElementById('gpsDot');
                const gpsText = document.getElementById('gpsStatusText');
                if (gpsBadge && gpsText) {
                    gpsBadge.className = 'hidden xl:flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-50 border border-emerald-200/80 text-emerald-700 text-xs font-semibold';
                    if (gpsPing) gpsPing.className = 'animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75';
                    if (gpsDot) gpsDot.className = 'relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500';
                    gpsText.textContent = 'GPS Ativo';
                }

                fetch(API + 'apiLocalizacaoEntregador', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ latitude: lat, longitude: lng })
                }).catch(err => console.error('Erro ao atualizar localização:', err));

                if (userMarker) {
                    userMarker.setLatLng(currentUserPosition);
                } else {
                    userMarker = L.marker(currentUserPosition, { icon: emojiIcon }).addTo(map)
                        .bindPopup("<div class='p-3 text-center' style='font-family:Inter,sans-serif;'><strong class='text-xs font-bold text-slate-900'>Você está aqui 🚀</strong></div>");
                    // Primeira localização: leva o mapa até o entregador
                    map.setView(currentUserPosition, ZOOM_SEGUINDO);
                }

                seguirEntregador();
                acompanharProgresso();
            }, function(error) {
                console.error('Erro ao obter localização: ' + error.message);
            }, {
                enableHighAccuracy: true,
                maximumAge: 0,
                timeout: 5000
            });
        } else {
            alert('Geolocalização não é suportada pelo seu navegador.');
        }

        // ---------- botão Começar Percurso ----------
        document.getElementById('criarRota').addEventListener('click', async function() {
            if (!currentUserPosition) {
                alert('Localização ainda não foi carregada. Aguarde...');
                return;
            }
            const botao = this;
            botao.disabled = true;
            try {
                await carregarPedidos();
                if (pedidos.length === 0) {
                    alert('Nenhuma entrega disponível.');
                    return;
                }
                percursoAtivo = true;
                manterTelaLigada();
                renderizarListaPedidos();
                await proximoDestino();
            } catch (err) {
                console.error('Erro ao montar percurso:', err);
                alert('Não foi possível calcular a rota agora. Verifique a conexão e tente novamente.');
            } finally {
                botao.disabled = false;
            }
        });

        // ===== CONTROLES DE NAVEGAÇÃO DO MAPA E BUSCA =====
        document.getElementById('btnCentralizarMoto')?.addEventListener('click', function() {
            recentralizar();
            if (userMarker) userMarker.openPopup();
        });

        document.getElementById('btnFitBounds')?.addEventListener('click', function() {
            const bounds = [];
            if (currentUserPosition) bounds.push(currentUserPosition);
            Object.values(marcadores).forEach(m => bounds.push(m.getLatLng()));

            pausarSeguirAte = 0;
            ativarSeguir(false); // visão geral: o mapa para de seguir a moto
            if (bounds.length > 0) {
                map.fitBounds(L.latLngBounds(bounds), { padding: [60, 60], maxZoom: 16 });
            } else {
                map.setView([-22.3674, -46.9428], 15);
            }
        });

        var inputBusca = document.getElementById('inputBuscaMapa');
        var btnClearSearch = document.getElementById('btnClearSearch');

        if (inputBusca) {
            inputBusca.addEventListener('input', function(e) {
                termoBusca = (e.target.value || '').trim().toLowerCase();
                if (btnClearSearch) btnClearSearch.classList.toggle('hidden', termoBusca.length === 0);
                renderizarListaPedidos();
            });
        }

        if (btnClearSearch) {
            btnClearSearch.addEventListener('click', function() {
                if (inputBusca) inputBusca.value = '';
                termoBusca = '';
                btnClearSearch.classList.add('hidden');
                renderizarListaPedidos();
            });
        }

        // ===== ABAS MOBILE (MAPA vs ENTREGAS) =====
        var tabToggleMap = document.getElementById('tabToggleMap');
        var tabToggleList = document.getElementById('tabToggleList');
        var mapWrapper = document.getElementById('mapWrapper');
        var deliveriesListWrapper = document.getElementById('deliveriesListWrapper');

        if (tabToggleMap && tabToggleList && mapWrapper && deliveriesListWrapper) {
            tabToggleMap.addEventListener('click', function() {
                mapWrapper.classList.remove('hidden');
                deliveriesListWrapper.classList.add('hidden');

                tabToggleMap.style.backgroundColor = '#4F46E5';
                tabToggleMap.style.color = '#ffffff';
                tabToggleList.style.backgroundColor = '#ffffff';
                tabToggleList.style.color = '#334155';

                setTimeout(() => map.invalidateSize(), 200);
            });

            tabToggleList.addEventListener('click', function() {
                mapWrapper.classList.add('hidden');
                deliveriesListWrapper.classList.remove('hidden');

                tabToggleList.style.backgroundColor = '#4F46E5';
                tabToggleList.style.color = '#ffffff';
                tabToggleMap.style.backgroundColor = '#ffffff';
                tabToggleMap.style.color = '#334155';
            });
        }

        // ===== SCRIPT DA SIDEBAR MOBILE =====
        const menuBtn = document.getElementById('hamburger');
        const sidebar = document.getElementById('sidebar');
        const closeSidebar = document.getElementById('closeSidebar');
        const overlay = document.getElementById('overlay');

        if (menuBtn && sidebar && overlay) {
            menuBtn.addEventListener('click', () => {
                sidebar.classList.add('mobile-open');
                overlay.classList.add('active');
            });
        }

        if (closeSidebar && sidebar && overlay) {
            closeSidebar.addEventListener('click', () => {
                sidebar.classList.remove('mobile-open');
                overlay.classList.remove('active');
            });
        }

        if (overlay && sidebar) {
            overlay.addEventListener('click', () => {
                sidebar.classList.remove('mobile-open');
                overlay.classList.remove('active');
            });
        }

        window.addEventListener('resize', function() {
            map.invalidateSize();
        });

        carregarPedidos();
    </script>
</body>

</html>
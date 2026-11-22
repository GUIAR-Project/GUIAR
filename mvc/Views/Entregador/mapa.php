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
    <link rel="stylesheet" href="<?= BASE_URL ?>/style/mapa.css">

    <!-- Google Fonts: Inter & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Leaflet CSS & Routing Machine -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />
</head>

<body class="sidebar-page bg-[#f1f4fa] text-slate-800 antialiased h-screen overflow-hidden flex flex-col md:flex-row">

    <?php
    $normalizarUrlAsset = function ($path) {
        if (empty($path) || !is_string($path)) return '';
        $path = trim($path);
        if (preg_match('#^(https?:)?//#i', $path) || strpos($path, 'data:') === 0) {
            return $path;
        }
        if (strpos($path, BASE_URL) === 0) {
            return $path;
        }
        return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
    };

    $nomeEntregadorRecebido = trim((string)($nomeEntregador ?? ($entregador['nome_completo'] ?? ($entregador['nome'] ?? ''))));
    $nomeEntregador = $nomeEntregadorRecebido !== '' ? $nomeEntregadorRecebido : 'Entregador';

    $fotoRaw = $entregadorFotoUrl ?? ($entregador['foto_3x4'] ?? '');
    if (!empty($fotoRaw) && strpos($fotoRaw, '/') === false && strpos($fotoRaw, 'data:') !== 0) {
        $entregadorFotoUrl = rtrim(BASE_URL, '/') . '/public/uploads/entregadores/fotos/' . ltrim($fotoRaw, '/');
    } else {
        $entregadorFotoUrl = $normalizarUrlAsset($fotoRaw);
    }

    $logoFallback = BASE_URL . '/img/LogoPreto.png';
    $logoRaw = $logoEmpresaSidebar ?? ($logoEmpresa ?? ($empresa['logo_url'] ?? ($empresa['logo'] ?? '')));
    $logoEmpresaSidebar = !empty($logoRaw)
        ? $normalizarUrlAsset($logoRaw)
        : $logoFallback;

    $nomeEmpresaSidebar = trim((string)($nomeEmpresaSidebar ?? ($nomeEmpresa ?? ($empresa['nome_empresa'] ?? ($empresa['nome'] ?? 'GUIAR Entregas')))));
    if ($nomeEmpresaSidebar === '') {
        $nomeEmpresaSidebar = 'GUIAR Entregas';
    }

    $precisaSincronizarSidebar = ($nomeEntregadorRecebido === '' || empty($logoRaw));
    $pedidosMapaIniciaisJson = json_encode($pedidosMapaIniciais ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '[]';
    ?>

    <!-- SIDEBAR CLARA (DESKTOP) -->
    <aside id="sidebar"
        class="hidden md:flex w-72 bg-white flex-col fixed top-0 left-0 text-slate-700 z-50 transition-all rounded-none overflow-hidden h-screen border-r border-slate-200/80 shadow-sm">

        <div class="flex flex-col h-full">

            <!-- LOGO DA EMPRESA -->
            <div class="px-6 pt-7 pb-4 mb-2 flex justify-start items-center gap-3">
                <div style="width: 58px; height: 58px; border-radius: 50%; overflow: hidden; flex-shrink: 0; background: #fff; border: 2px solid var(--emp-primary-light-border, rgba(79,70,229,0.18)); box-shadow: 0 4px 14px var(--emp-primary-ring, rgba(79,70,229,0.12)); display: flex; align-items: center; justify-content: center; padding: 5px;">
                    <img id="sidebarEmpresaLogo"
                        src="<?= htmlspecialchars($logoEmpresaSidebar, ENT_QUOTES, 'UTF-8') ?>"
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
                    style="background-color: var(--emp-primary, #4F46E5); color: var(--emp-primary-contrast, #ffffff); box-shadow: 0 8px 18px -4px var(--emp-primary-ring, rgba(79, 70, 229, 0.35));">
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

    <!-- MAIN WRAPPER -->
    <main class="flex-1 flex flex-col h-screen min-w-0 md:ml-72 overflow-hidden">

        <!-- HEADER SUPERIOR MODERNO -->
        <header class="bg-white/80 backdrop-blur-md border-b border-slate-200/80 px-4 md:px-8 py-3 md:py-4 flex items-center justify-between gap-2.5 sm:gap-4 sticky top-0 z-20 flex-shrink-0">

            <div class="flex items-center gap-2.5 sm:gap-3.5 min-w-0 flex-1">
                <!-- ÍCONE DA ABA -->
                <div class="flex items-center justify-center w-9 h-9 sm:w-auto sm:h-auto rounded-xl sm:rounded-none bg-indigo-50/80 sm:bg-transparent flex-shrink-0 transition-transform hover:scale-105"
                    style="color: var(--emp-primary, #4F46E5);">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                    </svg>
                </div>

                <div class="min-w-0 flex-1">
                    <h1 class="heading-font text-base sm:text-lg md:text-xl font-extrabold text-slate-900 tracking-tight truncate">
                        Mapa
                    </h1>
                </div>
            </div>

            <!-- STATUS GPS & AÇÕES DE TOPO -->
            <div class="flex items-center gap-2 sm:gap-3 flex-shrink-0">

                <!-- PULSO DE GPS AO VIVO -->
                <div id="gpsStatusBadge" class="hidden xl:flex items-center gap-2 px-3 py-1.5 rounded-full bg-amber-50 border border-amber-200/80 text-amber-700 text-xs font-semibold">
                    <span class="relative flex h-2.5 w-2.5">
                        <span id="gpsPing" class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                        <span id="gpsDot" class="relative inline-flex rounded-full h-2.5 w-2.5 bg-amber-500"></span>
                    </span>
                    <span id="gpsStatusText">Buscando GPS...</span>
                </div>

                <!-- CONTADOR RÁPIDO DE ENTREGAS -->
                <div class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold border transition-all"
                    style="background-color: var(--emp-primary-light, #EEF2FF); color: var(--emp-primary, #4F46E5); border-color: var(--emp-primary-light-border, #C7D2FE);">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    <span><strong id="badgePedidosCount">0</strong> entregas</span>
                </div>

                <!-- BOTÃO ENQUADRAR MAPA -->
                <button type="button" id="btnFitBounds" title="Enquadrar sua posição e todas as entregas no mapa"
                    class="w-9 h-9 sm:w-auto sm:h-auto justify-center p-0 sm:px-3 sm:py-2 rounded-xl bg-white border border-slate-200 hover:border-slate-300 text-slate-700 hover:text-slate-900 shadow-sm text-xs font-bold flex items-center gap-1.5 transition-all active:scale-95 flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                    </svg>
                    <span class="hidden md:inline">Enquadrar Mapa</span>
                </button>

                <!-- BOTÃO PRINCIPAL: COMEÇAR PERCURSO -->
                <button type="button" id="criarRota"
                    class="btn-emp-primary flex items-center justify-center gap-1.5 sm:gap-2 font-bold text-xs sm:text-sm px-3 sm:px-5 h-9 sm:h-auto sm:py-2.5 rounded-xl transition-all hover:scale-[1.02] active:scale-95 whitespace-nowrap flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 sm:w-5 sm:h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                style="background-color: var(--emp-primary, #4F46E5); color: var(--emp-primary-contrast, #ffffff);">
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

                    <!-- LEGENDA FLUTUANTE INFERIOR -->
                    <div class="absolute bottom-4 left-4 z-10 glass-panel px-3.5 py-2 rounded-2xl text-[11px] font-semibold text-slate-700 flex items-center gap-3.5 shadow-md hidden sm:flex pointer-events-auto">
                        <div class="flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded-full border border-white shadow-sm" style="background-color: var(--emp-primary, #4F46E5);"></span>
                            <span>Sua localização</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded-full border border-white shadow-sm" style="background-color: var(--emp-secondary, #FFD400);"></span>
                            <span>Próxima parada</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="text-xs">📍</span>
                            <span>Ponto de entrega</span>
                        </div>
                        <div class="flex items-center gap-1.5 border-l border-slate-200 pl-3">
                            <span class="w-4 h-1 rounded-full" style="background-color: var(--emp-primary, #4F46E5);"></span>
                            <span>Rota mais curta</span>
                        </div>
                    </div>

                </div>

                <!-- LISTA LATERAL DE ENTREGAS -->
                <div id="deliveriesListWrapper"
                    class="w-full lg:w-[390px] xl:w-[430px] flex-shrink-0 h-full bg-white rounded-2xl sm:rounded-3xl border border-slate-200/90 shadow-sm flex flex-col overflow-hidden hidden lg:flex">

                    <!-- TOPO DA LISTA -->
                    <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between gap-3 bg-slate-50/50">
                        <div class="min-w-0">
                            <h2 class="heading-font text-base sm:text-lg font-bold text-slate-900">
                                Entregas na Rota
                            </h2>
                            <p class="text-xs text-slate-500 font-medium">
                                Selecione uma parada para visualizar no mapa
                            </p>
                        </div>

                        <!-- BADGE TOTAL -->
                        <div class="flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border flex-shrink-0 whitespace-nowrap"
                            style="background-color: var(--emp-primary-light, #EEF2FF); color: var(--emp-primary, #4F46E5); border-color: var(--emp-primary-light-border, #C7D2FE);">
                            <span class="w-2 h-2 rounded-full animate-pulse flex-shrink-0" style="background-color: var(--emp-primary, #4F46E5);"></span>
                            <span id="totalPedidosBadge" class="whitespace-nowrap">0 pendentes</span>
                        </div>
                    </div>

                    <!-- CORPO SCROLLÁVEL COM CARDS DOS PEDIDOS -->
                    <div id="pedidosListContainer" class="flex-1 overflow-y-auto p-3.5 sm:p-4 space-y-3">
                        <div class="flex flex-col items-center justify-center py-16 text-center text-slate-400 gap-3">
                            <svg class="animate-spin h-7 w-7" style="color: var(--emp-primary, #4F46E5);" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
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
                            Recálculo automático a cada 20m
                        </span>
                        <span id="routeStatusFooter" class="font-bold text-slate-600">Aguardando percurso</span>
                    </div>

                </div>

            </div>
        </section>

    </main>

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
            class="flex flex-col items-center gap-0.5 text-[11px] font-bold transition-colors"
            style="color: var(--emp-primary, #4F46E5);">
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

        <a href="<?= BASE_URL ?>/routes.php?action=logoutEntregador"
            class="flex flex-col items-center gap-0.5 text-[11px] text-slate-500 hover:text-rose-500 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 opacity-75" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
            Sair
        </a>
    </nav>

    <!-- Scripts Leaflet -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>
    <script src="https://rawgit.com/bbecquet/Leaflet.RotatedMarker/master/leaflet.rotatedMarker.js"></script>

    <script>
        const PEDIDOS_MAPA_INICIAIS = <?= $pedidosMapaIniciaisJson ?>;

        function getEmpresaPrimaryColor() {
            var val = getComputedStyle(document.documentElement).getPropertyValue('--emp-primary').trim();
            return val || '#4F46E5';
        }

        function escapeHtml(str) {
            return String(str ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function parseCoord(value) {
            if (value === null || value === undefined || value === '') return null;
            var num = parseFloat(String(value).replace(',', '.'));
            if (!Number.isFinite(num) || num === 0) return null;
            return num;
        }

        function extrairListaPedidos(payload) {
            if (!payload) return [];
            if (Array.isArray(payload)) return payload;
            if (Array.isArray(payload.pedidos)) return payload.pedidos;
            if (Array.isArray(payload.entregas)) return payload.entregas;
            if (Array.isArray(payload.data)) return payload.data;
            if (payload.data && Array.isArray(payload.data.pedidos)) return payload.data.pedidos;
            return [];
        }

        function normalizarPedido(raw, fallbackIndex) {
            var id = raw.id_pedido ?? raw.id ?? raw.pedido_id ?? (fallbackIndex + 1);
            var nomeCliente = (raw.nome_cliente ?? raw.cliente_nome ?? raw.cliente ?? raw.nome ?? 'Cliente').toString().trim() || 'Cliente';

            var enderecoComp = [raw.rua || raw.logradouro, raw.numero, raw.bairro, raw.cidade, raw.estado || raw.uf, raw.cep]
                .filter(Boolean)
                .map(v => String(v).trim())
                .join(', ');
            var endereco = (raw.endereco ?? raw.endereco_entrega ?? enderecoComp ?? 'Endereço não informado').toString().trim() || 'Endereço não informado';

            var latitude = parseCoord(raw.latitude ?? raw.lat ?? raw.latitude_entrega);
            var longitude = parseCoord(raw.longitude ?? raw.lng ?? raw.lon ?? raw.longitude_entrega);

            return {
                id_pedido: id,
                nome_cliente: nomeCliente,
                endereco: endereco,
                latitude: latitude,
                longitude: longitude
            };
        }

        // CRIA O MAPA
        var map = L.map('map', {
            zoomControl: false
        }).setView([-22.3674, -46.9428], 15);

        // CONTROLE DE ZOOM NO CANTO INFERIOR DIREITO
        L.control.zoom({
            position: 'bottomright'
        }).addTo(map);

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

        var userMarker, routeControl;
        var currentUserPosition;
        var points = [];
        var shortestRouteControl;
        var pedidosCache = [];
        var marcadoresPedidos = {};
        var proximoPedidoId = null;
        var termoBusca = '';
        var mapaJaEnquadrado = false;

        function enquadrarTodosNoMapa(forcar) {
            var bounds = [];
            if (currentUserPosition) bounds.push(currentUserPosition);
            Object.values(marcadoresPedidos).forEach(m => bounds.push(m.getLatLng()));

            if (bounds.length > 0 && (forcar || !mapaJaEnquadrado)) {
                map.fitBounds(L.latLngBounds(bounds), { padding: [60, 60], maxZoom: 16 });
                mapaJaEnquadrado = true;
            }
        }

        function criarOuAtualizarMarcadorPedido(pedido, index) {
            if (pedido.latitude === null || pedido.longitude === null) return;

            var key = String(pedido.id_pedido);
            if (marcadoresPedidos[key]) {
                marcadoresPedidos[key].setLatLng([pedido.latitude, pedido.longitude]);
                marcadoresPedidos[key].latitude = pedido.latitude;
                marcadoresPedidos[key].longitude = pedido.longitude;
                return;
            }

            var deliveryIcon = L.divIcon({
                className: 'delivery-marker-icon',
                html: `<div class="delivery-marker" id="pinMarker_${escapeHtml(key)}">${index + 1}</div>`,
                iconSize: [26, 26],
                iconAnchor: [13, 26]
            });

            var marker = L.marker([pedido.latitude, pedido.longitude], {
                icon: deliveryIcon
            }).addTo(map);

            marker.bindPopup(`
                <div class="p-4 min-w-[240px]" style="font-family: 'Inter', sans-serif;">
                    <div class="flex items-center justify-between gap-2 pb-2.5 mb-2.5 border-b border-slate-100">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold text-white"
                            style="background-color: var(--emp-primary, #4F46E5);">
                            Entrega #${index + 1}
                        </span>
                        <span class="text-[11px] font-bold text-slate-400">ID #${escapeHtml(key)}</span>
                    </div>

                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Cliente</p>
                    <p class="text-sm font-bold text-slate-900 mb-2.5">${escapeHtml(pedido.nome_cliente)}</p>

                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Endereço</p>
                    <p class="text-xs font-medium text-slate-600 mb-3.5 leading-relaxed">${escapeHtml(pedido.endereco)}</p>

                    <button type="button" class="btn-concluir" data-id="${escapeHtml(key)}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                        Concluir Entrega
                    </button>
                </div>
            `);

            marker.pedidoId = pedido.id_pedido;
            marker.latitude = pedido.latitude;
            marker.longitude = pedido.longitude;
            marcadoresPedidos[key] = marker;

            marker.on('popupopen', function(e) {
                var popupEl = e.popup.getElement();
                if (!popupEl) return;
                var btn = popupEl.querySelector('.btn-concluir');
                if (btn) {
                    btn.onclick = function() {
                        const originalText = btn.innerHTML;
                        btn.innerHTML = 'Processando...';
                        btn.disabled = true;
                        concluirEntrega(this.getAttribute('data-id'), marker, btn, originalText);
                    };
                }
            });
        }

        async function geocodificarPedidosSemCoordenadas(lista) {
            var atualizouAlgum = false;
            for (let i = 0; i < lista.length; i++) {
                const p = lista[i];
                if (p.latitude !== null && p.longitude !== null) continue;
                if (!p.endereco || p.endereco === 'Endereço não informado') continue;

                const cacheKey = 'guiar_geo_' + p.endereco.toLowerCase();
                try {
                    const cached = sessionStorage.getItem(cacheKey);
                    if (cached) {
                        const coords = JSON.parse(cached);
                        if (coords && coords.lat && coords.lng) {
                            p.latitude = coords.lat;
                            p.longitude = coords.lng;
                            criarOuAtualizarMarcadorPedido(p, i);
                            atualizouAlgum = true;
                            continue;
                        }
                    }

                    const resp = await fetch('https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' + encodeURIComponent(p.endereco));
                    const geoData = await resp.json();
                    if (Array.isArray(geoData) && geoData.length > 0) {
                        const lat = parseCoord(geoData[0].lat);
                        const lng = parseCoord(geoData[0].lon);
                        if (lat !== null && lng !== null) {
                            p.latitude = lat;
                            p.longitude = lng;
                            sessionStorage.setItem(cacheKey, JSON.stringify({ lat, lng }));
                            criarOuAtualizarMarcadorPedido(p, i);
                            atualizouAlgum = true;
                        }
                    }
                } catch (e) {
                    console.warn('Falha ao geocodificar endereço do pedido #' + p.id_pedido, e);
                }
            }

            if (atualizouAlgum) {
                renderizarListaPedidos();
                enquadrarTodosNoMapa(false);
            }
        }

        function aplicarPedidosNoMapa(listaBruta) {
            const listaNormalizada = listaBruta.map((item, idx) => normalizarPedido(item, idx));

            // Remove marcadores de pedidos que não existem mais na lista atual
            const idsAtivos = new Set(listaNormalizada.map(p => String(p.id_pedido)));
            Object.keys(marcadoresPedidos).forEach(key => {
                if (!idsAtivos.has(key)) {
                    map.removeLayer(marcadoresPedidos[key]);
                    delete marcadoresPedidos[key];
                }
            });

            pedidosCache = listaNormalizada;

            pedidosCache.forEach((pedido, index) => {
                criarOuAtualizarMarcadorPedido(pedido, index);
            });

            renderizarListaPedidos();
            enquadrarTodosNoMapa(false);
            geocodificarPedidosSemCoordenadas(pedidosCache);
        }

        // 1) CARGA IMEDIATA DOS PEDIDOS ENVIADOS PELO CONTROLLER
        if (Array.isArray(PEDIDOS_MAPA_INICIAIS) && PEDIDOS_MAPA_INICIAIS.length > 0) {
            aplicarPedidosNoMapa(PEDIDOS_MAPA_INICIAIS);
        }

        // 2) SINCRONIZA COM A API DO MAPA
        fetch('<?= BASE_URL ?>/routes.php?action=apiPedidosMapa', {
            credentials: 'same-origin',
            cache: 'no-store'
        })
            .then(response => response.text())
            .then(rawText => {
                const cleanText = (rawText || '').trim().replace(/^\uFEFF/, '');
                if (!cleanText) return;

                let data;
                try {
                    data = JSON.parse(cleanText);
                } catch (parseErr) {
                    console.error('Resposta inválida de apiPedidosMapa:', cleanText);
                    if (pedidosCache.length === 0) renderizarListaPedidos();
                    return;
                }

                if (data && !data.error) {
                    const listaApi = extrairListaPedidos(data);
                    if (listaApi.length > 0 || pedidosCache.length === 0) {
                        aplicarPedidosNoMapa(listaApi);
                    }
                } else if (pedidosCache.length === 0) {
                    renderizarListaPedidos();
                }
            })
            .catch(err => {
                console.error("Erro ao carregar pedidos:", err);
                if (pedidosCache.length === 0) {
                    renderizarListaPedidos();
                }
            });

        // RENDERIZA LISTA LATERAL DE PEDIDOS
        function renderizarListaPedidos() {
            var container = document.getElementById('pedidosListContainer');
            var total = pedidosCache.length;

            var badgePedidosCount = document.getElementById('badgePedidosCount');
            if (badgePedidosCount) badgePedidosCount.textContent = total;

            var totalPedidosBadge = document.getElementById('totalPedidosBadge');
            if (totalPedidosBadge) totalPedidosBadge.textContent = total + (total === 1 ? ' pendente' : ' pendentes');

            var mobileCountBadge = document.getElementById('mobileCountBadge');
            if (mobileCountBadge) mobileCountBadge.textContent = total;

            if (!container) return;

            var filtrados = pedidosCache.filter(p => {
                if (!termoBusca) return true;
                var cliente = (p.nome_cliente || '').toLowerCase();
                var endereco = (p.endereco || '').toLowerCase();
                var idStr = String(p.id_pedido || '');
                return cliente.includes(termoBusca) || endereco.includes(termoBusca) || idStr.includes(termoBusca);
            });

            if (filtrados.length === 0) {
                container.innerHTML = `
                    <div class="flex flex-col items-center justify-center py-12 px-4 text-center">
                        <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-400 mb-3 text-xl">
                            📦
                        </div>
                        <h4 class="font-bold text-slate-700 text-sm">Nenhuma entrega encontrada</h4>
                        <p class="text-xs text-slate-400 mt-1 max-w-xs">
                            ${termoBusca ? 'Nenhum pedido corresponde à sua pesquisa.' : 'Você não possui entregas pendentes no momento.'}
                        </p>
                    </div>
                `;
                return;
            }

            container.innerHTML = '';

            filtrados.forEach((pedido) => {
                var idStr = String(pedido.id_pedido);
                var indexOriginal = pedidosCache.findIndex(item => String(item.id_pedido) === idStr);
                var numeroParada = indexOriginal >= 0 ? indexOriginal + 1 : 1;
                var isProxima = (proximoPedidoId !== null && idStr === String(proximoPedidoId));

                var card = document.createElement('div');
                card.id = 'pedidoCard_' + idStr;
                card.className = 'delivery-card bg-white border border-slate-200 rounded-2xl p-4 shadow-sm border-l-4 cursor-pointer';
                card.style.borderLeftColor = isProxima
                    ? 'var(--emp-secondary, #FFD400)'
                    : 'var(--emp-primary, #4F46E5)';

                card.onclick = function() {
                    focarPedidoNoMapa(pedido.id_pedido);
                };

                var avatarStyle = isProxima
                    ? 'background-color: var(--emp-secondary, #FFD400); color: var(--emp-secondary-contrast, #0F172A);'
                    : 'background-color: var(--emp-primary, #4F46E5); color: var(--emp-primary-contrast, #ffffff);';

                var pillStyle = isProxima
                    ? 'background-color: var(--emp-secondary-light, #FEF9C3); color: var(--emp-secondary-contrast, #0F172A); border-color: var(--emp-secondary, #FFD400);'
                    : 'background-color: var(--emp-primary-light, #EEF2FF); color: var(--emp-primary, #4F46E5); border-color: var(--emp-primary-light-border, #C7D2FE);';

                var dotStyle = isProxima
                    ? 'background-color: var(--emp-secondary-contrast, #0F172A);'
                    : 'background-color: var(--emp-primary, #4F46E5);';

                card.innerHTML = `
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 rounded-xl font-extrabold text-xs flex items-center justify-center flex-shrink-0 shadow-sm"
                                style="${avatarStyle}">
                                ${numeroParada}
                            </div>
                            <div class="min-w-0">
                                <h3 class="font-bold text-slate-900 text-sm truncate">
                                    ${escapeHtml(pedido.nome_cliente)}
                                </h3>
                                <p class="text-[11px] text-slate-400 font-medium">
                                    Pedido #${escapeHtml(idStr)}
                                </p>
                            </div>
                        </div>

                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold border flex-shrink-0"
                            style="${pillStyle}">
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
                            <span>${escapeHtml(pedido.endereco)}</span>
                        </p>
                    </div>

                    <div class="mt-3.5 pt-1 flex items-center gap-2">
                        <button type="button"
                            onclick="event.stopPropagation(); focarPedidoNoMapa(${Number(pedido.id_pedido)});"
                            class="flex-1 py-2 px-3 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 transition-all flex items-center justify-center gap-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            Ver no mapa
                        </button>
                        <button type="button"
                            onclick="event.stopPropagation(); concluirEntregaPeloCard(${Number(pedido.id_pedido)}, this);"
                            class="btn-emp-primary flex-1 py-2 px-3 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                            </svg>
                            Concluir
                        </button>
                    </div>
                `;

                container.appendChild(card);
            });
        }

        function focarPedidoNoMapa(pedidoId) {
            var key = String(pedidoId);
            document.querySelectorAll('.delivery-card').forEach(c => c.classList.remove('focused'));
            var card = document.getElementById('pedidoCard_' + key);
            if (card) card.classList.add('focused');

            if (window.innerWidth < 1024 && tabToggleMap) {
                tabToggleMap.click();
            }

            var pedido = pedidosCache.find(p => String(p.id_pedido) === key);
            var marker = marcadoresPedidos[key];
            var lat = marker ? marker.latitude : (pedido ? pedido.latitude : null);
            var lng = marker ? marker.longitude : (pedido ? pedido.longitude : null);

            if (lat !== null && lng !== null) {
                map.flyTo([lat, lng], 17, { duration: 0.9 });
                if (marker) {
                    marker.openPopup();
                }
            } else {
                alert("Não foi possível localizar as coordenadas deste endereço no mapa.");
            }
        }

        function concluirEntregaPeloCard(pedidoId, btn) {
            const originalText = btn.innerHTML;
            btn.innerHTML = 'Processando...';
            btn.disabled = true;
            concluirEntrega(pedidoId, marcadoresPedidos[String(pedidoId)], btn, originalText);
        }

        // FUNÇÃO PARA CALCULAR A ROTA MAIS CURTA ENTRE O USUÁRIO E OS PONTOS
        function calcularMelhorRota() {
            if (points.length === 0) {
                alert("Todas as entregas foram concluídas!");
                proximoPedidoId = null;
                renderizarListaPedidos();
                var footerStatus = document.getElementById('routeStatusFooter');
                if (footerStatus) footerStatus.textContent = 'Percurso finalizado';
                if (shortestRouteControl) {
                    map.removeControl(shortestRouteControl);
                    shortestRouteControl = null;
                }
                return;
            }

            var promises = points.map(ponto => {
                if (ponto.latitude === null || ponto.longitude === null) return Promise.resolve(null);

                var waypoints = [
                    currentUserPosition,
                    L.latLng(ponto.latitude, ponto.longitude)
                ];

                return new Promise((resolve) => {
                    var tempControl = L.Routing.control({
                        waypoints: waypoints,
                        router: L.Routing.osrmv1({
                            serviceUrl: 'https://router.project-osrm.org/route/v1'
                        }),
                        show: false,
                        lineOptions: {
                            styles: [{
                                color: 'rgba(0,0,0,0)',
                                weight: 1
                            }]
                        },
                        createMarker: function() {
                            return null;
                        }
                    }).on('routesfound', function(e) {
                        var routeLength = e.routes[0].summary.totalDistance;
                        try { map.removeControl(tempControl); } catch (err) {}
                        resolve({
                            ponto: ponto,
                            distance: routeLength
                        });
                    }).on('routingerror', function() {
                        try { map.removeControl(tempControl); } catch (err) {}
                        resolve(null);
                    }).addTo(map);
                });
            });

            Promise.all(promises).then(results => {
                results = results.filter(r => r !== null);
                if (results.length === 0) {
                    alert("Não foi possível traçar rota para os endereços disponíveis.");
                    return;
                }

                if (shortestRouteControl) {
                    map.removeControl(shortestRouteControl);
                }

                var melhorRota = results.reduce((acc, cur) => cur.distance < acc.distance ? cur : acc);
                proximoPedidoId = melhorRota.ponto.id_pedido;

                document.querySelectorAll('.delivery-marker').forEach(el => el.classList.remove('next-stop'));
                var nextPin = document.getElementById('pinMarker_' + proximoPedidoId);
                if (nextPin) nextPin.classList.add('next-stop');

                var distKm = (melhorRota.distance / 1000).toFixed(1);
                var footerStatus = document.getElementById('routeStatusFooter');
                if (footerStatus) footerStatus.textContent = `Destino: #${proximoPedidoId} (${distKm} km)`;

                renderizarListaPedidos();

                shortestRouteControl = L.Routing.control({
                    waypoints: [
                        currentUserPosition,
                        L.latLng(melhorRota.ponto.latitude, melhorRota.ponto.longitude)
                    ],
                    show: false,
                    router: L.Routing.osrmv1({
                        serviceUrl: 'https://router.project-osrm.org/route/v1'
                    }),
                    lineOptions: {
                        styles: [
                            { color: '#ffffff', weight: 8, opacity: 0.8 },
                            { color: getEmpresaPrimaryColor(), weight: 5, opacity: 0.95 }
                        ]
                    },
                    createMarker: function() {
                        return null;
                    }
                }).addTo(map);
            });
        }

        // FUNÇÃO PARA CONCLUIR A ENTREGA
        function concluirEntrega(pedidoId, marker, btn, originalText) {
            fetch('<?= BASE_URL ?>/routes.php?action=concluirEntrega', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id_pedido: pedidoId
                    })
                })
                .then(response => response.text())
                .then(text => {
                    if (text.trim() === 'success') {
                        if (marker) {
                            map.removeLayer(marker);
                        }
                        alert("Entrega concluída com sucesso!");
                        location.reload();
                    } else {
                        alert("Erro ao concluir a entrega.");
                        if (btn) {
                            btn.innerHTML = originalText;
                            btn.disabled = false;
                        }
                    }
                })
                .catch(error => {
                    console.error("Erro na requisição:", error);
                    if (btn) {
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                    }
                });
        }

        function rotaParaPontoProximo() {
            calcularMelhorRota();
        }

        function MonitorarProgressoRota() {
            if (!shortestRouteControl || !currentUserPosition) return;

            var routeCoords = shortestRouteControl.getPlan().getWaypoints();
            if (routeCoords.length < 2) return;

            var destination = routeCoords[routeCoords.length - 1].latLng;
            var distanceToDestination = currentUserPosition.distanceTo(destination);

            if (distanceToDestination < 20) {
                points = points.filter(p => p.latitude != destination.lat && p.longitude != destination.lng);
                rotaParaPontoProximo();
            }
        }

        // ===== GEOLOCALIZAÇÃO DO MOTOBOY =====
        if (navigator.geolocation) {
            navigator.geolocation.watchPosition(function(position) {
                let lat = position.coords.latitude;
                let lng = position.coords.longitude;

                var userLatLng = L.latLng(lat, lng);
                currentUserPosition = userLatLng;

                var gpsBadge = document.getElementById('gpsStatusBadge');
                var gpsPing = document.getElementById('gpsPing');
                var gpsDot = document.getElementById('gpsDot');
                var gpsText = document.getElementById('gpsStatusText');
                if (gpsBadge && gpsText) {
                    gpsBadge.className = 'hidden xl:flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-50 border border-emerald-200/80 text-emerald-700 text-xs font-semibold';
                    if (gpsPing) gpsPing.className = 'animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75';
                    if (gpsDot) gpsDot.className = 'relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500';
                    gpsText.textContent = 'GPS Ativo';
                }

                // ===== ENVIA LOCALIZAÇÃO PRO BANCO =====
                fetch('<?= BASE_URL ?>/routes.php?action=apiLocalizacaoEntregador', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        latitude: lat,
                        longitude: lng
                    })
                }).catch(err => console.error("Erro ao atualizar localização:", err));

                // ===== ATUALIZA MARCADOR =====
                if (userMarker) {
                    userMarker.setLatLng(userLatLng);
                } else {
                    userMarker = L.marker(userLatLng, {
                            icon: emojiIcon
                        }).addTo(map)
                        .bindPopup("<div class='p-3 text-center' style='font-family:Inter,sans-serif;'><strong class='text-xs font-bold text-slate-900'>Você está aqui 🚀</strong></div>");
                    enquadrarTodosNoMapa(false);
                }

                // MONITORA ROTA
                MonitorarProgressoRota();

            }, function(error) {
                console.error("Erro ao obter localização: " + error.message);
            }, {
                enableHighAccuracy: true,
                maximumAge: 0,
                timeout: 5000
            });

        } else {
            alert("Geolocalização não é suportada pelo seu navegador.");
        }

        // ===== BOTÃO CRIAR ROTA =====
        document.getElementById('criarRota').addEventListener('click', function() {
            if (!currentUserPosition) {
                alert("Localização ainda não foi carregada. Aguarde...");
                return;
            }

            if (pedidosCache.length === 0) {
                alert("Nenhuma entrega disponível.");
                return;
            }

            points = pedidosCache.filter(p => p.latitude !== null && p.longitude !== null);
            rotaParaPontoProximo();
        });

        // ===== CONTROLES DE NAVEGAÇÃO DO MAPA E BUSCA =====
        document.getElementById('btnCentralizarMoto')?.addEventListener('click', function() {
            if (currentUserPosition) {
                map.flyTo(currentUserPosition, 17, { duration: 0.9 });
                if (userMarker) userMarker.openPopup();
            } else {
                alert("Aguardando sinal de GPS...");
            }
        });

        document.getElementById('btnFitBounds')?.addEventListener('click', function() {
            var bounds = [];
            if (currentUserPosition) bounds.push(currentUserPosition);
            Object.values(marcadoresPedidos).forEach(m => bounds.push(m.getLatLng()));

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
                if (btnClearSearch) {
                    btnClearSearch.classList.toggle('hidden', termoBusca.length === 0);
                }
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

                tabToggleMap.style.backgroundColor = 'var(--emp-primary, #4F46E5)';
                tabToggleMap.style.color = 'var(--emp-primary-contrast, #ffffff)';
                tabToggleList.style.backgroundColor = '#ffffff';
                tabToggleList.style.color = '#334155';

                setTimeout(() => map.invalidateSize(), 200);
            });

            tabToggleList.addEventListener('click', function() {
                mapWrapper.classList.add('hidden');
                deliveriesListWrapper.classList.remove('hidden');

                tabToggleList.style.backgroundColor = 'var(--emp-primary, #4F46E5)';
                tabToggleList.style.color = 'var(--emp-primary-contrast, #ffffff)';
                tabToggleMap.style.backgroundColor = '#ffffff';
                tabToggleMap.style.color = '#334155';
            });
        }

        window.addEventListener('resize', function() {
            map.invalidateSize();
        });

        <?php if ($precisaSincronizarSidebar): ?>
        // Sincroniza logo da empresa, nome e foto do entregador via rotas MVC existentes caso mapa() não os tenha enviado
        (function sincronizarSidebarEntregador() {
            const isLogoGenerica = (src) => {
                if (!src) return true;
                const s = src.toLowerCase();
                return s.includes('logopreto.png') || s.includes('logoguiar') || s.includes('logoicon.png') || s.includes('/uploads/entregadores/');
            };

            const aplicarLogoEmpresa = (novaSrc) => {
                if (!novaSrc || isLogoGenerica(novaSrc)) return false;
                const localLogo = document.getElementById('sidebarEmpresaLogo');
                if (localLogo && localLogo.src !== novaSrc) {
                    localLogo.src = novaSrc;
                }
                window.GUIAR_EMPRESA_LOGO = novaSrc;

                const oldScript = document.querySelector('script[src*="empresa-palette.js"]');
                if (oldScript) {
                    const freshScript = document.createElement('script');
                    freshScript.src = oldScript.src.split('?')[0] + '?v=' + Date.now();
                    document.body.appendChild(freshScript);
                }
                return true;
            };

            const extrairDaPagina = (doc) => {
                const remoteSidebar = doc.getElementById('sidebar') || doc;
                const candidatasLogo = [
                    remoteSidebar.querySelector('#sidebarEmpresaLogo'),
                    remoteSidebar.querySelector('img[alt*="Logo da empresa" i]'),
                    remoteSidebar.querySelector('img[alt*="empresa" i]'),
                    remoteSidebar.querySelector('img[src*="/uploads/empresas/"]'),
                    ...Array.from(remoteSidebar.querySelectorAll('img'))
                ].filter(Boolean);

                for (const img of candidatasLogo) {
                    const rawSrc = img.getAttribute('src') || '';
                    if (rawSrc && !isLogoGenerica(rawSrc)) {
                        aplicarLogoEmpresa(img.src);
                        break;
                    }
                }

                const elNomeEmp = remoteSidebar.querySelector('#sidebarEmpresaNome');
                const localNomeEmp = document.getElementById('sidebarEmpresaNome');
                if (elNomeEmp && elNomeEmp.textContent.trim() && localNomeEmp) {
                    localNomeEmp.textContent = elNomeEmp.textContent.trim();
                }

                const elNome = doc.querySelector('#driverSidebarName, .admin-user-name, main h2, header h1');
                const nomeReal = elNome ? elNome.textContent.trim() : '';
                if (nomeReal && nomeReal.toLowerCase() !== 'entregador') {
                    const localName = document.getElementById('driverSidebarName');
                    if (localName) localName.textContent = nomeReal;

                    const localAvatar = document.getElementById('driverSidebarAvatar');
                    if (localAvatar) {
                        const partes = nomeReal.split(/\s+/).filter(Boolean);
                        const iniciais = partes.length > 1
                            ? (partes[0][0] + partes[partes.length - 1][0]).toUpperCase()
                            : nomeReal.slice(0, 2).toUpperCase();
                        localAvatar.textContent = iniciais;
                    }
                }

                const elFoto = doc.querySelector('#driverSidebarPhoto, img[src*="/uploads/entregadores/fotos/"]');
                if (elFoto && elFoto.getAttribute('src')) {
                    const fotoSrc = elFoto.src;
                    const localPhoto = document.getElementById('driverSidebarPhoto');
                    const localAvatar = document.getElementById('driverSidebarAvatar');

                    if (localPhoto) {
                        localPhoto.src = fotoSrc;
                    } else if (localAvatar) {
                        const img = document.createElement('img');
                        img.id = 'driverSidebarPhoto';
                        img.src = fotoSrc;
                        img.alt = nomeReal ? ('Foto de ' + nomeReal) : 'Foto do entregador';
                        img.className = 'w-9 h-9 rounded-full object-cover shadow-sm ring-2 ring-white flex-shrink-0';
                        localAvatar.replaceWith(img);
                    }
                }
            };

            const buscarHtml = (action) =>
                fetch('<?= BASE_URL ?>/routes.php?action=' + action, { credentials: 'same-origin' })
                    .then(res => res.text())
                    .then(html => {
                        const doc = new DOMParser().parseFromString(html, 'text/html');
                        extrairDaPagina(doc);
                    })
                    .catch(() => {});

            buscarHtml('meusPedidosEntregador').then(() => {
                const localLogo = document.getElementById('sidebarEmpresaLogo');
                const localName = document.getElementById('driverSidebarName');
                if ((localLogo && isLogoGenerica(localLogo.src)) || (localName && localName.textContent.trim() === 'Entregador')) {
                    return buscarHtml('perfilEntregador');
                }
            });
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
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

                    <!-- PAINEL DE PREVISÃO DE CHEGADA (estilo navegação) -->
                    <div id="etaPainel" class="hidden absolute z-[1000] left-3 right-3 top-16 sm:left-4 sm:right-auto sm:w-[23rem] max-w-[calc(100%-1.5rem)] bg-white/95 backdrop-blur rounded-2xl shadow-xl border border-slate-200 px-5 py-4" role="status" aria-live="polite" style="display: none;">
                        <div class="flex items-baseline gap-3">
                            <span id="etaTempo" class="heading-font text-3xl font-extrabold leading-none" style="color: var(--emp-primary, #4F46E5);"></span>
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
                            <span>Rota otimizada</span>
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
                            Recálculo automático a cada 30 s
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

    <!-- Scripts Leaflet -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

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

        const API = '<?= BASE_URL ?>/routes.php?action=';
        const OSRM = 'https://router.project-osrm.org';
        const RECALCULO_MS = 30000;          // recalcula a rota no máximo a cada 30 s…
        const RECALCULO_DIST_M = 150;        // …ou quando andar 150 m desde o último cálculo
        const RAIO_CHEGADA_M = 40;           // distância do destino considerada "chegou"
        const ZOOM_SEGUINDO = 16;
        const VISAO_GERAL_ROTA_MS = 4000;    // tempo mostrando a rota inteira ao começar

        var userMarker;
        var currentUserPosition;
        var pedidosCache = [];
        var marcadoresPedidos = {};
        var termoBusca = '';
        var mapaJaEnquadrado = false;

        // Estado do percurso / previsão de chegada
        var percursoAtivo = false;
        var destinoAtual = null;     // { pedido, destino, viagem, linha, restanteS, distanciaM, calculadoEm, posCalculo, chegou }
        var recalculando = false;
        var plano = null;            // rota otimizada: { paradas: [{id_pedido, ordem, trecho_s}], tempo_no_local_s, fonte, pedidos }
        var telaLigada = null;       // Wake Lock
        var seguindo = false;        // mapa acompanha o entregador durante o percurso (desliga ao arrastar)
        var pausarSeguirAte = 0;
        const painel = document.getElementById('etaPainel');

        function temCoordenadas(p) {
            return p.latitude !== null && p.longitude !== null;
        }

        /** Número de cada parada: ordem da rota otimizada; sem rota, ordem da lista. */
        function numeroParada(pedido) {
            if (plano) {
                const parada = plano.paradas.find(p => String(p.id_pedido) === String(pedido.id_pedido));
                if (parada) return parada.ordem;
            }
            return pedidosCache.findIndex(p => String(p.id_pedido) === String(pedido.id_pedido)) + 1;
        }

        function ehProxima(pedido) {
            return !!destinoAtual && String(destinoAtual.pedido.id_pedido) === String(pedido.id_pedido);
        }

        /** Pedidos na ordem da rota otimizada (os fora do plano vão para o fim). */
        function pedidosOrdenados() {
            return [...pedidosCache].sort((a, b) => numeroParada(a) - numeroParada(b));
        }

        function enquadrarTodosNoMapa(forcar) {
            var bounds = [];
            if (currentUserPosition) bounds.push(currentUserPosition);
            Object.values(marcadoresPedidos).forEach(m => bounds.push(m.getLatLng()));

            if (bounds.length > 0 && (forcar || !mapaJaEnquadrado)) {
                map.fitBounds(L.latLngBounds(bounds), { padding: [60, 60], maxZoom: 16 });
                mapaJaEnquadrado = true;
            }
        }

        function atualizarMarcadores() {
            pedidosCache.forEach(pedido => criarOuAtualizarMarcadorPedido(pedido));
        }

        function criarOuAtualizarMarcadorPedido(pedido) {
            if (!temCoordenadas(pedido)) return;

            var key = String(pedido.id_pedido);
            var numero = numeroParada(pedido);
            var deliveryIcon = L.divIcon({
                className: 'delivery-marker-icon',
                html: `<div class="delivery-marker ${ehProxima(pedido) ? 'next-stop' : ''}" id="pinMarker_${escapeHtml(key)}">${numero}</div>`,
                iconSize: [26, 26],
                iconAnchor: [13, 26]
            });

            var marker = marcadoresPedidos[key];
            if (marker) {
                marker.setLatLng([pedido.latitude, pedido.longitude]);
                marker.setIcon(deliveryIcon);
            } else {
                marker = L.marker([pedido.latitude, pedido.longitude], {
                    icon: deliveryIcon
                }).addTo(map);
                marker.bindPopup('');
                marker.on('popupopen', function(e) {
                    var popupEl = e.popup.getElement();
                    if (!popupEl) return;
                    var btn = popupEl.querySelector('.btn-concluir');
                    if (btn) {
                        btn.onclick = function() {
                            concluirEntrega(this.getAttribute('data-id'), btn);
                        };
                    }
                });
                marker.pedidoId = pedido.id_pedido;
                marcadoresPedidos[key] = marker;
            }
            marker.latitude = pedido.latitude;
            marker.longitude = pedido.longitude;

            marker.setPopupContent(`
                <div class="p-4 min-w-[240px]" style="font-family: 'Inter', sans-serif;">
                    <div class="flex items-center justify-between gap-2 pb-2.5 mb-2.5 border-b border-slate-100">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold text-white"
                            style="background-color: var(--emp-primary, #4F46E5);">
                            Entrega #${numero}
                        </span>
                    </div>

                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Cliente</p>
                    <p class="text-sm font-bold text-slate-900 mb-2.5">${escapeHtml(pedido.nome_cliente)}</p>

                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Endereço</p>
                    <p class="text-xs font-medium text-slate-600 mb-3.5 leading-relaxed">${escapeHtml(pedido.endereco)}</p>

                    <button type="button" class="btn-concluir" data-id="${escapeHtml(key)}">

                        Concluir Entrega
                    </button>
                </div>
            `);
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

            // Mantém as coordenadas já geocodificadas de pedidos que continuam na lista
            listaNormalizada.forEach(p => {
                if (temCoordenadas(p)) return;
                const anterior = pedidosCache.find(a => String(a.id_pedido) === String(p.id_pedido));
                if (anterior && temCoordenadas(anterior)) {
                    p.latitude = anterior.latitude;
                    p.longitude = anterior.longitude;
                }
            });

            // Remove marcadores de pedidos que não existem mais na lista atual
            const idsAtivos = new Set(listaNormalizada.map(p => String(p.id_pedido)));
            Object.keys(marcadoresPedidos).forEach(key => {
                if (!idsAtivos.has(key)) {
                    map.removeLayer(marcadoresPedidos[key]);
                    delete marcadoresPedidos[key];
                }
            });

            pedidosCache = listaNormalizada;

            atualizarMarcadores();

            renderizarListaPedidos();
            enquadrarTodosNoMapa(false);
            geocodificarPedidosSemCoordenadas(pedidosCache);
        }

        // 1) CARGA IMEDIATA DOS PEDIDOS ENVIADOS PELO CONTROLLER
        if (Array.isArray(PEDIDOS_MAPA_INICIAIS) && PEDIDOS_MAPA_INICIAIS.length > 0) {
            aplicarPedidosNoMapa(PEDIDOS_MAPA_INICIAIS);
        }

        // 2) SINCRONIZA COM A API DO MAPA (também ao tocar em "Começar Percurso")
        function sincronizarPedidos() {
        return fetch('<?= BASE_URL ?>/routes.php?action=apiPedidosMapa', {
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
        }
        sincronizarPedidos();

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

            var footerStatus = document.getElementById('routeStatusFooter');
            if (footerStatus) {
                footerStatus.textContent = destinoAtual
                    ? `Destino: ${destinoAtual.pedido.nome_cliente.split(' ')[0]} (${formatarDistancia(destinoAtual.distanciaM)})`
                    : (percursoAtivo ? 'Calculando rota…' : 'Aguardando percurso');
            }

            if (!container) return;

            var filtrados = pedidosOrdenados().filter(p => {
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

            var horarios = horariosPrevistos();
            container.innerHTML = '';

            filtrados.forEach((pedido) => {
                var idStr = String(pedido.id_pedido);
                var numero = numeroParada(pedido);
                var isProxima = ehProxima(pedido);
                var chegou = isProxima && destinoAtual.chegou;
                var horario = horarios[pedido.id_pedido];
                var previsao = '';
                if (chegou) {
                    previsao = '📍 Você chegou — conclua a entrega';
                } else if (horario) {
                    previsao = `🕒 ${isProxima ? 'Chegada' : 'Previsão'} às ${formatarHora(horario)}`;
                }

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
                    ? 'background-color: var(--emp-secondary-light, #fdf16d); color: var(--emp-secondary-contrast, #0F172A); border-color: var(--emp-secondary, #FFD400);'
                    : 'background-color: var(--emp-primary-light, rgb(154, 175, 245)); color: var(--emp-primary, #4F46E5); border-color: var(--emp-primary-light-border, #C7D2FE);';

                var dotStyle = isProxima
                    ? 'background-color: var(--emp-secondary-contrast, #0F172A);'
                    : 'background-color: var(--emp-primary, #4F46E5);';

                card.innerHTML = `
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 rounded-xl font-extrabold text-xs flex items-center justify-center flex-shrink-0 shadow-sm"
                                style="${avatarStyle}">
                                ${numero}
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
                            <span class="${isProxima ? 'text-red-600' : ''}">${isProxima ? 'Próxima parada' : 'Pendente'}</span>
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
                        ${previsao ? `<p class="mt-1.5 text-xs font-bold" style="color: var(--emp-primary, #4F46E5);">${previsao}</p>` : ''}
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
                            onclick="event.stopPropagation(); concluirEntrega(${Number(pedido.id_pedido)}, this);"
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
                pausarSeguirAte = 0;
                ativarSeguir(false); // olhando um pedido: o mapa para de seguir a moto
                map.flyTo([lat, lng], 17, { duration: 0.9 });
                if (marker) {
                    marker.openPopup();
                }
            } else {
                alert("Não foi possível localizar as coordenadas deste endereço no mapa.");
            }
        }

        // =====================================================================
        // PERCURSO COM PREVISÃO DE CHEGADA
        //  - Rota otimizada com todas as paradas (serviço trip do OSRM)
        //  - Previsão ajustada pelo modelo treinado com as entregas reais do GUIAR
        //  - Mapa acompanha o entregador; tela fica ligada durante o percurso
        //  - Previsão enviada ao vivo para o link do cliente e o mapa do gestor
        // =====================================================================

        // ---------- utilidades ----------
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
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(corpo)
            });
            return res.json();
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
                L.polyline(pontos, { color: getEmpresaPrimaryColor(), weight: 5, opacity: 0.95 })
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
            const btn = document.getElementById('btnCentralizar');
            if (btn) btn.style.display = (!ligado && percursoAtivo) ? 'block' : 'none';
        }

        /** Altura ocupada pelo painel de previsão no topo do mapa (0 se escondido). */
        function alturaPainel() {
            return painel.classList.contains('hidden') ? 0 : painel.offsetTop + painel.offsetHeight;
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
            ativarSeguir(percursoAtivo);
            const zoom = Math.max(map.getZoom(), ZOOM_SEGUINDO);
            map.setView(centroVisivel(currentUserPosition, zoom), zoom);
        }

        function seguirEntregador() {
            if (!currentUserPosition || !percursoAtivo) return;
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
                            style="background-color: var(--emp-primary, #4F46E5); color: var(--emp-primary-contrast, #ffffff);">${parada.ordem}</span>
                        <span class="truncate text-slate-700">${escapeHtml(pedido ? pedido.nome_cliente : 'Pedido #' + parada.id_pedido)}</span>
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

        // ---------- percurso ----------
        /** Calcula a rota otimizada com as entregas restantes e segue para a primeira parada. */
        async function proximoDestino() {
            limparRota();
            const comCoordenadas = pedidosCache.filter(temCoordenadas);
            if (comCoordenadas.length === 0) {
                percursoAtivo = false;
                plano = null;
                liberarTelaLigada();
                ativarSeguir(false);
                atualizarMarcadores();
                renderizarListaPedidos();
                alert(pedidosCache.length ? 'As entregas restantes não têm localização no mapa.' : 'Todas as entregas foram concluídas!');
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

            // Mostra a rota inteira (abaixo do painel) por alguns segundos e depois passa a seguir o entregador
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
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(corpo)
            })
                .then(r => r.text())
                .then(texto => {
                    if (texto.trim() !== 'success') throw new Error(texto);

                    const key = String(pedidoId);
                    map.closePopup();
                    if (marcadoresPedidos[key]) {
                        map.removeLayer(marcadoresPedidos[key]);
                        delete marcadoresPedidos[key];
                    }
                    pedidosCache = pedidosCache.filter(p => String(p.id_pedido) !== key);
                    if (plano) {
                        plano.paradas = plano.paradas.filter(p => String(p.id_pedido) !== key);
                        plano.pedidos = plano.pedidos.filter(p => String(p.id_pedido) !== key);
                    }

                    const eraODestino = destinoAtual && String(destinoAtual.pedido.id_pedido) === key;
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

        // ===== GEOLOCALIZAÇÃO DO MOTOBOY =====
        if (navigator.geolocation) {
            navigator.geolocation.watchPosition(function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                currentUserPosition = L.latLng(lat, lng);

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
                fetch(API + 'apiLocalizacaoEntregador', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ latitude: lat, longitude: lng })
                }).catch(err => console.error('Erro ao atualizar localização:', err));

                // ===== ATUALIZA MARCADOR =====
                if (userMarker) {
                    userMarker.setLatLng(currentUserPosition);
                } else {
                    userMarker = L.marker(currentUserPosition, { icon: emojiIcon }).addTo(map)
                        .bindPopup("<div class='p-3 text-center' style='font-family:Inter,sans-serif;'><strong class='text-xs font-bold text-slate-900'>Você está aqui 🚀</strong></div>");
                    enquadrarTodosNoMapa(false);
                }

                // ===== ACOMPANHA O PERCURSO =====
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

        // ===== BOTÃO COMEÇAR PERCURSO =====
        document.getElementById('criarRota').addEventListener('click', async function() {
            if (!currentUserPosition) {
                alert('Localização ainda não foi carregada. Aguarde...');
                return;
            }
            const botao = this;
            botao.disabled = true;
            try {
                await sincronizarPedidos();
                if (pedidosCache.length === 0) {
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
            var bounds = [];
            if (currentUserPosition) bounds.push(currentUserPosition);
            Object.values(marcadoresPedidos).forEach(m => bounds.push(m.getLatLng()));

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
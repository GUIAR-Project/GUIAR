<?php
$tokenJson = json_encode((string)($_GET['t'] ?? ''));
$e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title><?= $pedido ? 'Acompanhe seu pedido · ' . $e($nomeEmpresa) : 'Pedido não encontrado' ?></title>
    <meta name="robots" content="noindex">
    <meta name="theme-color" content="#ffffff">
    <link rel="shortcut icon" type="image/png" href="<?= BASE_URL ?>/img/logoIcon.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <style>
        :root { --cor: var(--emp-primary, #E94E00); --cor-contraste: var(--emp-primary-contrast, #ffffff); }
        body { font-family: 'Inter', sans-serif; background: #F1F5F9; color: #0F172A; }
        #mapaRastreio { height: 42vh; min-height: 260px; }
        .etapa .bolinha { width: 14px; height: 14px; border-radius: 999px; border: 2px solid #CBD5E1; background: #fff; flex-shrink: 0; }
        .etapa.feita .bolinha { background: var(--cor); border-color: var(--cor); }
        .etapa.atual .bolinha { background: #fff; border-color: var(--cor); box-shadow: 0 0 0 4px color-mix(in srgb, var(--cor) 25%, transparent); }
        .etapa .linha { position: absolute; left: 6px; top: 16px; bottom: -14px; width: 2px; background: #E2E8F0; }
        .etapa.feita .linha { background: var(--cor); }
        .etapa:last-child .linha { display: none; }
        .etapa.pendente .texto { color: #94A3B8; }
        .moto-icone { font-size: 30px; line-height: 30px; filter: drop-shadow(0 3px 3px rgb(0 0 0 / .25)); }
        .casa-icone { font-size: 28px; line-height: 28px; filter: drop-shadow(0 3px 3px rgb(0 0 0 / .2)); }
        @keyframes pulsar { 0%, 100% { opacity: 1 } 50% { opacity: .35 } }
        .ao-vivo::before { content: ''; display: inline-block; width: 8px; height: 8px; border-radius: 99px; background: #16A34A; margin-right: 6px; animation: pulsar 1.6s infinite; }
    </style>
</head>

<body class="min-h-screen">
    <?php if (!$pedido): ?>
        <main class="min-h-screen flex items-center justify-center p-6">
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-8 max-w-sm text-center">
                <div class="text-5xl mb-4">📦</div>
                <h1 class="text-xl font-extrabold">Pedido não encontrado</h1>
                <p class="mt-2 text-slate-500 text-sm">O link de acompanhamento está incorreto ou não existe mais. Confira a mensagem que você recebeu da loja.</p>
            </div>
        </main>
    <?php else: ?>
        <main class="max-w-lg mx-auto pb-10">
            <!-- Loja -->
            <header class="flex items-center gap-3 px-4 pt-5 pb-4">
                <img src="<?= $e($logoEmpresa) ?>" alt="" class="w-11 h-11 rounded-xl object-contain bg-white border border-slate-200 p-1">
                <div class="min-w-0">
                    <p class="text-xs font-semibold text-slate-500">Seu pedido em</p>
                    <h1 class="text-base font-extrabold truncate"><?= $e($nomeEmpresa) ?></h1>
                </div>
                <span id="aoVivo" class="ml-auto text-xs font-semibold text-slate-500 ao-vivo">Ao vivo</span>
            </header>

            <!-- Previsão -->
            <section class="mx-4 bg-white rounded-3xl shadow-sm border border-slate-200 p-5" aria-live="polite">
                <p id="titulo" class="text-sm font-bold" style="color: var(--cor)">Carregando…</p>
                <div class="mt-1 flex items-baseline gap-2 flex-wrap">
                    <span id="horario" class="text-4xl font-extrabold tracking-tight"></span>
                    <span id="restante" class="text-base font-semibold text-slate-500"></span>
                </div>
                <p id="detalhe" class="mt-1 text-sm text-slate-600"></p>

                <!-- Etapas -->
                <ol id="etapas" class="mt-5 space-y-3.5">
                    <li class="etapa relative flex items-start gap-3" data-etapa="recebido"><span class="bolinha mt-0.5"></span><span class="linha"></span><span class="texto text-sm font-semibold">Pedido recebido pela loja</span></li>
                    <li class="etapa relative flex items-start gap-3" data-etapa="saiu"><span class="bolinha mt-0.5"></span><span class="linha"></span><span class="texto text-sm font-semibold">Saiu para entrega</span></li>
                    <li class="etapa relative flex items-start gap-3" data-etapa="a_caminho"><span class="bolinha mt-0.5"></span><span class="linha"></span><span class="texto text-sm font-semibold">A caminho de você</span></li>
                    <li class="etapa relative flex items-start gap-3" data-etapa="entregue"><span class="bolinha mt-0.5"></span><span class="linha"></span><span class="texto text-sm font-semibold">Entregue</span></li>
                </ol>
            </section>

            <!-- Mapa -->
            <section class="mx-4 mt-4 bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
                <div id="mapaRastreio"></div>
                <div class="px-5 py-3.5 flex items-center gap-3 text-sm">
                    <span class="text-xl" aria-hidden="true">📍</span>
                    <div class="min-w-0">
                        <p class="font-semibold truncate" id="endereco"></p>
                        <p class="text-slate-500 text-xs" id="entregadorNome"></p>
                    </div>
                </div>
            </section>

            <p class="mt-5 text-center text-xs text-slate-400">
                <span id="atualizado"></span><br>
                Previsão calculada pelo GUIAR com base no trajeto e no histórico real de entregas.
            </p>
        </main>

        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
        <script>
            const TOKEN = <?= $tokenJson ?>;
            const API = '<?= BASE_URL ?>/routes.php?action=apiRastreio&t=' + encodeURIComponent(TOKEN);
            const OSRM = 'https://router.project-osrm.org';
            const ORDEM_ETAPAS = ['recebido', 'saiu', 'a_caminho', 'entregue'];

            const mapa = L.map('mapaRastreio', { zoomControl: false, attributionControl: true }).setView([-22.3674, -46.9428], 14);
            L.control.zoom({ position: 'bottomright' }).addTo(mapa);
            // Mesmo mapa (e chave) das demais telas do GUIAR
            L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}.png?key=cb1_3p8m_1_09944103c911a8c84b986e32', {
                attribution: '&copy; OpenStreetMap, &copy; CARTO', subdomains: 'abcd', maxZoom: 20
            }).addTo(mapa);

            let marcadorCasa = null, marcadorMoto = null, linhaRota = null;
            let ultimaRota = { pos: null, quando: 0 };
            let enquadrado = false;
            let ultimoSucesso = null;
            let temporizador = null;

            function formatarRestante(s) {
                const min = Math.round(s / 60);
                if (min < 1) return 'chegando';
                if (min < 60) return 'em ' + min + ' min';
                return 'em ' + Math.floor(min / 60) + ' h ' + String(min % 60).padStart(2, '0') + ' min';
            }

            function definir(id, texto) { document.getElementById(id).textContent = texto || ''; }

            function marcarEtapas(etapa) {
                const atual = ORDEM_ETAPAS.indexOf(etapa === 'chegou' ? 'a_caminho' : etapa);
                document.querySelectorAll('.etapa').forEach(li => {
                    const i = ORDEM_ETAPAS.indexOf(li.dataset.etapa);
                    li.classList.remove('feita', 'atual', 'pendente');
                    if (i < atual || etapa === 'entregue') li.classList.add('feita');
                    else if (i === atual) li.classList.add('atual');
                    else li.classList.add('pendente');
                });
            }

            function textos(d) {
                const p = d.previsao;
                const nomeEnt = d.entregador && d.entregador.nome ? d.entregador.nome : 'O entregador';
                switch (d.etapa) {
                    case 'entregue':
                        return { titulo: 'Pedido entregue', horario: d.entregue_em ? 'às ' + d.entregue_em : '✓', restante: '', detalhe: 'Bom apetite! Obrigado por pedir com a gente.' };
                    case 'chegou':
                        return { titulo: 'O entregador chegou!', horario: '📍 Na porta', restante: '', detalhe: nomeEnt + ' está no seu endereço.' };
                    case 'a_caminho':
                        return {
                            titulo: 'Chega por volta de',
                            horario: p && p.chega_em ? p.chega_em : '',
                            restante: p ? formatarRestante(p.restante_s) : '',
                            detalhe: nomeEnt + ' está vindo até você.'
                        };
                    case 'saiu': {
                        if (p && p.chega_em) {
                            const antes = p.entregas_antes;
                            return {
                                titulo: 'Previsão de chegada',
                                horario: p.chega_em,
                                restante: formatarRestante(p.restante_s),
                                detalhe: antes > 0
                                    ? `${nomeEnt} faz ${antes} entrega${antes > 1 ? 's' : ''} antes da sua.`
                                    : `${nomeEnt} já vai sair para o seu endereço.`
                            };
                        }
                        const antes = p ? p.entregas_antes : 0;
                        return {
                            titulo: 'Saiu para entrega',
                            horario: '🛵',
                            restante: '',
                            detalhe: antes > 0
                                ? `Seu pedido está na rota — ${antes} entrega${antes > 1 ? 's' : ''} antes da sua.`
                                : `${nomeEnt} vai iniciar a rota em instantes.`
                        };
                    }
                    default:
                        return { titulo: 'Pedido recebido', horario: '🧾', restante: '', detalhe: 'A loja está preparando o seu pedido.' };
                }
            }

            async function desenharRota(origem, destino) {
                const moveu = !ultimaRota.pos || ultimaRota.pos.distanceTo(origem) > 120;
                if (!moveu && Date.now() - ultimaRota.quando < 60000) return;
                ultimaRota = { pos: origem, quando: Date.now() };
                try {
                    const url = `${OSRM}/route/v1/driving/${origem.lng},${origem.lat};${destino.lng},${destino.lat}?overview=full&geometries=geojson`;
                    const r = await (await fetch(url)).json();
                    if (r.code !== 'Ok') return;
                    const pontos = r.routes[0].geometry.coordinates.map(c => [c[1], c[0]]);
                    if (linhaRota) linhaRota.setLatLngs(pontos);
                    else linhaRota = L.polyline(pontos, { color: getComputedStyle(document.documentElement).getPropertyValue('--emp-primary').trim() || '#E94E00', weight: 5, opacity: .85 }).addTo(mapa);
                } catch (err) { /* sem rota desenhada: os marcadores bastam */ }
            }

            function atualizarMapa(d) {
                if (d.destino) {
                    const destino = L.latLng(d.destino.lat, d.destino.lng);
                    if (!marcadorCasa) {
                        marcadorCasa = L.marker(destino, { icon: L.divIcon({ className: 'casa-icone', html: '🏠', iconSize: [30, 30], iconAnchor: [15, 26] }) }).addTo(mapa);
                    }
                    const temMoto = d.entregador && d.entregador.lat != null && d.etapa !== 'entregue';
                    if (temMoto) {
                        const pos = L.latLng(d.entregador.lat, d.entregador.lng);
                        if (marcadorMoto) marcadorMoto.setLatLng(pos);
                        else marcadorMoto = L.marker(pos, { icon: L.divIcon({ className: 'moto-icone', html: '🛵', iconSize: [32, 32], iconAnchor: [16, 26] }) }).addTo(mapa);
                        if (d.etapa === 'a_caminho') desenharRota(pos, destino);
                        if (!enquadrado) {
                            mapa.fitBounds(L.latLngBounds([pos, destino]), { padding: [50, 50], maxZoom: 16 });
                            enquadrado = true;
                        }
                    } else {
                        if (marcadorMoto) { mapa.removeLayer(marcadorMoto); marcadorMoto = null; }
                        if (linhaRota) { mapa.removeLayer(linhaRota); linhaRota = null; }
                        if (!enquadrado) { mapa.setView(destino, 16); enquadrado = true; }
                    }
                }
            }

            async function atualizar() {
                try {
                    const resp = await fetch(API, { cache: 'no-store' });
                    const d = await resp.json();
                    if (!d.success) throw new Error(d.error);
                    ultimoSucesso = Date.now();

                    const t = textos(d);
                    definir('titulo', t.titulo);
                    definir('horario', t.horario);
                    definir('restante', t.restante);
                    definir('detalhe', t.detalhe);
                    marcarEtapas(d.etapa);
                    definir('endereco', [d.pedido.endereco, d.pedido.bairro].filter(Boolean).join(' · '));
                    definir('entregadorNome', d.entregador && d.entregador.nome ? 'Entregador: ' + d.entregador.nome : '');
                    atualizarMapa(d);

                    const sinal = d.previsao && d.previsao.sinal_ha_s;
                    definir('atualizado', sinal != null && sinal > 120
                        ? `Última posição do entregador há ${Math.round(sinal / 60)} min`
                        : 'Atualizado às ' + new Date().toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' }));
                    document.getElementById('aoVivo').style.visibility = d.etapa === 'entregue' ? 'hidden' : 'visible';

                    if (d.etapa === 'entregue') { clearTimeout(temporizador); return; } // não precisa mais consultar
                } catch (err) {
                    definir('atualizado', 'Sem conexão — tentando novamente…');
                }
                clearTimeout(temporizador);
                temporizador = setTimeout(atualizar, 10000);
            }

            document.addEventListener('visibilitychange', () => {
                if (document.visibilityState === 'visible' && ultimoSucesso && Date.now() - ultimoSucesso > 15000) atualizar();
            });

            atualizar();
        </script>
        <script src="<?= BASE_URL ?>/public/js/empresa-palette.js"></script>
        <script>
            // Cores da loja extraídas do logo (mesmo algoritmo do painel do gestor)
            (function () {
                if (!window.EmpresaPalette) return;
                const img = new Image();
                img.onload = () => window.EmpresaPalette.extractFromImage(img, p => {
                    if (!p || !p.primary) return;
                    const raiz = document.documentElement.style;
                    raiz.setProperty('--emp-primary', p.primary);
                    raiz.setProperty('--emp-primary-contrast', p.primaryContrast);
                    if (linhaRota) linhaRota.setStyle({ color: p.primary });
                });
                img.src = <?= json_encode($logoEmpresa) ?>;
            })();
        </script>
    <?php endif; ?>
</body>

</html>

<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Termos de Uso | Empresa</title>
  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="Shortcut Icon" type="image/png" href="img/logoIcon.png" class=" w-50 h-60 object-contain">

  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: {
            sans: ['Inter', 'sans-serif'],
          },
          colors: {
            brand: {
              blue: '#001b69',
              yellow: '#ffd400',
              inputborder: '#c3d2e6',
              link: '#00288a'
            }
          },
          animation: {
            'fade-in-up': 'fadeInUp 0.8s ease-out forwards',
            'float': 'float 6s ease-in-out infinite',
          },
          keyframes: {
            fadeInUp: {
              '0%': {
                opacity: '0',
                transform: 'translateY(30px)'
              },
              '100%': {
                opacity: '1',
                transform: 'translateY(0)'
              },
            },
            float: {
              '0%, 100%': {
                transform: 'translateY(0)'
              },
              '50%': {
                transform: 'translateY(-15px)'
              },
            }
          }
        }
      }
    }
  </script>
  <style>
    body {
      background: linear-gradient(135deg, #e6eaf0 0%, #f0f4f8 100%);
    }

    body {
            background-color: #eef5ff !important;
            background-image: none !important;
            color: #102b61;
            font-family: 'Inter', sans-serif !important;
        }

    body > header {
      min-height: 72px;
      padding-right: 12px !important;
      padding-left: 12px !important;
      background: rgba(255, 255, 255, .96) !important;
      box-shadow: none !important;
      -webkit-backdrop-filter: blur(8px);
      backdrop-filter: blur(8px);
    }

    body > header > div:first-child {
      width: 100%;
      max-width: 1140px;
      min-height: 72px;
    }

    body > header > div:first-child > div {
      margin-top: 0 !important;
      margin-bottom: 0 !important;
    }

    body > header img {
      width: auto !important;
      height: 48px !important;
      max-height: none;
      filter: none !important;
      object-fit: contain;
    }

    body > header nav {
      gap: 24px !important;
      font-size: 1rem !important;
      font-weight: 600 !important;
      font-family: 'Inter', Arial, sans-serif !important;
    }

    body > header nav a,
    body > header #nav-menu a {
      color: #061d3b !important;
      line-height: 1.5 !important;
      text-decoration: none !important;
      transition: color .25s ease;
    }

    body > header nav a {
      padding: .5rem !important;
    }

    body > header nav a:hover,
    body > header #nav-menu a:hover {
      color: #2e8bdc !important;
    }

    body > header nav a.text-brand-yellow,
    body > header #nav-menu a.text-brand-yellow {
      color: #061d3b !important;
    }

    body > header #nav-toggle {
      align-items: center;
      justify-content: center;
      border: 1px solid #073b7840;
      color: #073b78 !important;
      background: transparent !important;
    }

    body > header #nav-menu {
      margin-top: .8rem;
      padding: 1rem 1.2rem !important;
      border-radius: 12px;
      background: rgba(255, 255, 255, .98);
    }

    body > header #nav-menu > div {
      gap: 0;
      margin-top: 0;
      font-size: 1rem;
      font-weight: 600;
    }

    body > header #nav-menu a {
      padding: .5rem 0 !important;
    }

    @supports ((-webkit-backdrop-filter: blur(8px)) or (backdrop-filter: blur(8px))) {
      body > header {
        background: rgba(255, 255, 255, .62) !important;
      }
    }

    @media (min-width: 768px) and (max-width: 991.98px) {
      body > header nav {
        display: none !important;
      }

      body > header #nav-toggle {
        display: inline-flex !important;
      }

      body > header #nav-menu.hidden {
        display: none !important;
      }

      body > header #nav-menu.flex {
        display: block !important;
      }
    }

     body > .flex-grow > div > div:first-child {
      position: relative !important;
      width: 48% !important;
      padding: 100px !important;
      background:
        linear-gradient(135deg, transparent 0 73%, rgb(10 42 151 / 88%) 73% 84%, transparent 84%),
        linear-gradient(135deg, transparent 0 86%, #101c8b 86% 100%),
        #111979 !important;
    }

    body > .flex-grow > div > div:first-child > img {
      width: 150% !important;
      height: 150% !important;
      max-width: none;
      object-fit: contain;
      opacity: .16 !important;
      filter: brightness(0) invert(1) !important;
    }

    body > .flex-grow > div > div:first-child > div:first-of-type {
      display: none;
    }

    body > .flex-grow > div > div:first-child > div:last-child {
      position: relative;
      width: 100%;
    }

    body > .flex-grow > div > div:first-child > div:last-child > div:first-child {
      margin-bottom: 40px !important;
    }

    body > .flex-grow > div > div:first-child > div:last-child > div:first-child img {
      width: 230px !important;
      height: 58px !important;
      max-height: 58px;
      object-fit: cover;
      object-position: center;
    }

    body > .flex-grow > div > div:first-child h1 {
      color: #fff !important;
      font-size: 29px !important;
      line-height: 1.15 !important;
    }

    body > .flex-grow > div > div:first-child .h-1 {
      width: 62px !important;
      height: 4px !important;
      margin: 0 !important;
      background: #0878f9 !important;
    }

    body > .flex-grow > div > div:last-child {
      width: 52% !important;
      padding: 56px !important;
      background: #fff !important;
    }

    body > .flex-grow form label {
      color: #102b61 !important;
      font-size: 16px !important;
      font-weight: 500 !important;
    }

    body > footer {
      border-color: #e1ebf8 !important;
      background: #fff !important;
      color: #58769f !important;
      box-shadow: 0 -2px 12px rgb(16 43 97 / 4%) !important;
    }

     body > footer a {
      color: #31567f !important;
    }

    body > footer a:hover {
      color: #0878f9 !important;
    }

    body > .flex-grow > div > div:first-child,
      body > .flex-grow > div > div:last-child {
        padding: 24px 20px !important;
      }

      body > main {
      position: relative;
      isolation: isolate;
      min-height: calc(100vh - 164px);
      padding: 52px 24px !important;
      background-color: #eef5ff !important;
      background-image: none !important;
    }

    /* Estilização suave para a barra de rolagem dos termos */
    .custom-scrollbar::-webkit-scrollbar {
      width: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
      background: #f1f5f9;
      border-radius: 8px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
      background: #cbd5e1;
      border-radius: 8px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
      background: #94a3b8;
    }

    body > .flex-grow {
      position: relative;
      isolation: isolate;
      min-height: calc(100vh - 164px);
      padding: clamp(24px, 4vh, 52px) 24px !important;
      background: #eef5ff !important;
    }

    body > .flex-grow > div {
      width: 100% !important;
      max-width: 1100px !important;
      min-height: 540px !important;
      border-radius: 14px !important;
      box-shadow: 0 24px 60px rgb(31 91 166 / 16%) !important;
      animation: none !important;
      opacity: 1 !important;
    }

    body > .flex-grow > div > div:first-child {
      position: relative !important;
      width: 45% !important;
      padding: 52px !important;
      background:
        linear-gradient(135deg, transparent 0 73%, rgb(10 42 151 / 88%) 73% 84%, transparent 84%),
        linear-gradient(135deg, transparent 0 86%, #101c8b 86% 100%),
        #111979 !important;
    }

    body > .flex-grow > div > div:first-child > img {
      width: 150% !important;
      height: 150% !important;
      max-width: none;
      object-fit: contain;
      opacity: .16 !important;
      filter: brightness(0) invert(1) !important;
    }

    body > .flex-grow > div > div:first-child > div:first-of-type {
      display: none;
    }

    body > .flex-grow > div > div:first-child > div:last-child {
      position: relative;
      width: 100%;
    }

    body > .flex-grow > div > div:first-child > div:last-child > div:first-child {
      margin-bottom: 40px !important;
    }

    body > .flex-grow > div > div:first-child > div:last-child > div:first-child img {
      width: 230px !important;
      height: 58px !important;
      max-height: 58px;
      object-fit: cover;
      object-position: center;
    }

    body > .flex-grow > div > div:first-child h1 {
      color: #fff !important;
      font-size: 29px !important;
      line-height: 1.15 !important;
    }

    body > .flex-grow > div > div:first-child .h-1 {
      width: 62px !important;
      height: 4px !important;
      margin: 0 !important;
      background: #0878f9 !important;
    }

    body > .flex-grow > div > div:last-child {
      width: 55% !important;
      padding: 48px !important;
      background: #fff !important;
      justify-content: flex-start !important;
    }

    .login-form-heading {
      display: block !important;
      position: relative;
      width: 100%;
      max-width: 508px;
      margin: 0 0 24px !important;
      padding-top: 21px;
    }

    .login-form-heading > div:first-child {
      display: none !important;
    }

    .login-form-title {
      display: block !important;
      visibility: visible !important;
      position: static !important;
      margin: 0 !important;
      padding: 0 !important;
      color: #102b61 !important;
      font-size: 24px !important;
      letter-spacing: .025em;
      text-transform: uppercase;
      font-weight: 900 !important;
      line-height: 1.25 !important;
    }

    .login-form-heading::before {
      position: absolute;
      top: 0;
      left: 0;
      width: 46px;
      height: 5px;
      border-radius: 3px;
      background: #111979;
      content: '';
    }

    .terms-content {
      width: 100%;
      max-width: 508px;
    }

    .terms-scroll-area {
      height: 280px !important;
      margin-bottom: 20px !important;
      border-color: #c4d8ff !important;
      border-radius: 11px !important;
      background: #fff !important;
      padding: 16px !important;
      color: #526785 !important;
      box-shadow: 0 1px 3px rgb(16 43 97 / 5%) !important;
    }

    .terms-scroll-area h3 {
      color: #102b61 !important;
    }

    .terms-actions {
      display: flex;
      width: 100%;
      gap: 12px;
    }

    .terms-action {
      display: inline-flex;
      min-height: 52px;
      flex: 1 1 0;
      align-items: center;
      justify-content: center;
      box-sizing: border-box;
      padding: 12px 16px !important;
      border: 0 !important;
      border-radius: 11px !important;
      font-size: 16px !important;
      font-weight: 700 !important;
      text-align: center;
    }

    .terms-action-primary {
      background: #19179a !important;
      color: #fff !important;
    }

    .terms-action-primary:hover {
      background: #111078 !important;
    }

    .terms-action-secondary {
      border: 1px solid #c4d8ff !important;
      background: #fff !important;
      color: #31567f !important;
    }

    .terms-action-secondary:hover {
      background: #eef5ff !important;
    }

    body > footer {
      border-color: #e1ebf8 !important;
      background: #fff !important;
      color: #58769f !important;
      box-shadow: 0 -2px 12px rgb(16 43 97 / 4%) !important;
    }

    body > footer a {
      color: #31567f !important;
    }

    body > footer a:hover {
      color: #0878f9 !important;
    }

    @media (max-width: 1100px) {
      body > .flex-grow > div {
        max-width: 1000px !important;
      }

      body > .flex-grow > div > div:first-child {
        padding: 36px !important;
      }

      body > .flex-grow > div > div:last-child {
        padding: 36px 40px !important;
      }

      body > .flex-grow > div > div:first-child h1 {
        font-size: 26px !important;
      }
    }

    @media (max-width: 767px) {
      body > header,
      body > header > div:first-child {
        min-height: 72px;
      }

      body > header img {
        height: 48px !important;
      }

      body > header nav {
        font-size: 1rem !important;
      }

      body > .flex-grow {
        min-height: 0;
        padding: 32px 18px !important;
      }

      body > .flex-grow > div {
        max-width: 520px !important;
        min-height: 0 !important;
        border-radius: 12px !important;
      }

      body > .flex-grow > div > div:first-child,
      body > .flex-grow > div > div:last-child {
        width: 100% !important;
        padding: 30px !important;
      }

      body > .flex-grow > div > div:first-child {
        display: none;
      }

      .terms-scroll-area {
        height: min(45vh, 280px) !important;
      }

      .terms-actions {
        flex-direction: column;
      }

      .terms-action {
        width: 100%;
      }
    }

    body > footer > div {
      color: #58769f !important;
    }
  </style>
</head>

<body class="min-h-screen flex flex-col font-sans">

  <!-- HEADER -->
  <header class="bg-brand-blue shadow-md py-0 px-8 sticky top-0 z-50">

    <div class="max-w-7xl mx-auto flex items-center justify-between">

      <!-- LOGO -->
      <div class="flex items-center -my-6">
        <a href="<?= BASE_URL ?>/index.html">
          <img src="<?= BASE_URL ?>/img/LogoGAzul.png" alt="GUIAR Logística" class="w-52 h-auto object-contain brightness-0 invert">
        </a>
      </div>

      <!-- MENU DESKTOP -->
      <nav class="hidden md:flex items-center gap-12 font-semibold text-xl">
        <a href="<?= BASE_URL ?>/index.html" class="text-white/90 hover:text-brand-yellow transition duration-300">Início</a>
        <a href="<?= BASE_URL ?>/routes.php?action=loginEmpresa" class="text-white/90 hover:text-brand-yellow transition duration-300">Administrador</a>
        <a href="<?= BASE_URL ?>/routes.php?action=loginEntregador" class="text-white/90 hover:text-brand-yellow transition duration-300">Entregador</a>
      </nav>

      <!-- BOTÃO MOBILE -->
      <button id="nav-toggle" class="md:hidden p-2 text-white focus:outline-none hover:bg-white/10 rounded-lg transition-colors" type="button">
        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
        </svg>
      </button>
    </div>

    <!-- MENU MOBILE -->
    <div id="nav-menu" class="hidden w-full md:hidden pb-4">
      <div class="flex flex-col gap-4 text-lg font-semibold mt-4">
      <a href="<?= BASE_URL ?>/index.html" class="text-white/90">Início</a>
      <a href="<?= BASE_URL ?>/routes.php?action=loginEmpresa" class="text-brand-yellow font-bold">Empresa</a>
        <a href="<?= BASE_URL ?>/routes.php?action=loginEntregador" class="text-white/90">Entregador</a>
      </div>
    </div>

  </header>

  <!-- Container centralizador do card -->
  <div class="flex-grow flex items-center justify-center p-4 sm:p-8">

    <!-- Card Principal com Bordas Arredondadas -->
    <div class="bg-white rounded-[14px] shadow-2xl flex flex-col md:flex-row w-full max-w-[950px] min-h-[580px] overflow-hidden opacity-0 animate-fade-in-up">

      <!-- Painel Esquerdo (Azul) -->
      <div class="bg-brand-blue w-full md:w-[45%] p-10 flex flex-col relative overflow-hidden">
        <!-- Imagem do Mapa de Fundo -->
       <img src="<?= BASE_URL ?>/img/Mapa.png" alt="Mapa Background" class="absolute inset-0 w-[150%] h-[150%] max-w-none -left-[20%] -top-[10%] object-contain opacity-40 pointer-events-none z-0 mix-blend-overlay animate-float">


        <div class="hidden md:block absolute top-[75%] left-[25%] transform -translate-x-1/2 -translate-y-1/2 z-0 opacity-80 pointer-events-none">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-[4.5rem] w-[4.5rem] text-brand-yellow drop-shadow-md" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
          </svg>
        </div>

        <!-- Conteúdo da Lateral Esquerda -->
        <div class="relative z-10 flex flex-col h-full">
          <!-- Logo GUIAR -->
          <div class="mb-8">
            <img src="<?= BASE_URL ?>/img/LogoGAzul.png" alt="Logo GUIAR" class="w-32 brightness-0 invert">
          </div>

          <!-- Slogan e Linha Amarela -->
          <div class="my-auto">
            <h1 class="text-white text-[2rem] leading-[1.2] font-bold mb-5 tracking-tight">
              Termos de Uso<br>E Privacidade.
            </h1>
            <div class="h-1 w-20 bg-brand-yellow"></div>
          </div>
        </div>
      </div>

      <!-- Painel Direito (Termos e Ação) -->
      <div class="w-full md:w-[55%] bg-white p-8 sm:p-12 flex flex-col justify-center items-center relative">

        <div class="terms-content">

          <!-- Cabeçalho: Ícone do Escudo/Contrato e Título -->
          <div class="login-form-heading flex items-center justify-center gap-3 mb-6">
            <!-- Ícone de Contrato (Fundo circular azul claro) -->
            <div class="w-[4rem] h-[4rem] rounded-full bg-[#d6e0f5] flex items-center justify-center text-brand-blue shrink-0">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
              </svg>
            </div>
            <!-- Título -->
            <h2 class="login-form-title text-[1.6rem] font-bold text-gray-900 tracking-tight leading-none pt-2">Termos de Uso</h2>
          </div>

          <!-- Caixa Rolável de Conteúdo dos Termos -->
          <div class="terms-scroll-area w-full h-64 overflow-y-auto border border-brand-inputborder rounded-2xl p-4 text-[0.8rem] text-gray-600 mb-6 bg-slate-50 leading-relaxed custom-scrollbar shadow-[inset_0_2px_4px_rgba(0,0,0,0.03)]">
            <h3 class="font-bold text-gray-800 mb-2 text-[0.85rem]">1. Aceitação dos Termos</h3>
            <p class="mb-4 text-justify">
              Ao utilizar a plataforma e os serviços de rastreamento e roteirização do <strong>GUIAR</strong>, você declara estar ciente e de acordo com todas as disposições estabelecidas neste documento. Caso não concorde com qualquer uma das condições, por favor, interrompa imediatamente a utilização do sistema.
            </p>

            <h3 class="font-bold text-gray-800 mb-2 text-[0.85rem]">2. Descrição dos Serviços</h3>
            <p class="mb-4 text-justify">
              O GUIAR oferece uma solução tecnológica para empresas e entregadores parceiros com o objetivo de facilitar a gestão de rotas, acompanhamento de entregas em tempo real e administração de pedidos.
            </p>

            <h3 class="font-bold text-gray-800 mb-2 text-[0.85rem]">3. Cadastro de Empresa e Segurança</h3>
            <p class="mb-4 text-justify">
              Para acessar os recursos exclusivos da plataforma, a empresa deverá realizar o cadastro fornecendo informações verdadeiras, atualizadas e completas (incluindo Razão Social, CNPJ e e-mail). A segurança das credenciais de acesso (nome de usuário e senha) é de responsabilidade única e exclusiva do usuário administrador da empresa cadastrada.
            </p>

            <h3 class="font-bold text-gray-800 mb-2 text-[0.85rem]">4. Privacidade e Proteção de Dados (LGPD)</h3>
            <p class="mb-4 text-justify">
              Nós do GUIAR levamos sua privacidade a sério. Coletamos e processamos dados de localização geográfica dos entregadores e informações dos pedidos cadastrados estritamente para o funcionamento de nossos serviços. Toda a coleta e processamento são regidos em estrita conformidade com a Lei Geral de Proteção de Dados Pessoais (LGPD - Lei nº 13.709/2018).
            </p>

            <h3 class="font-bold text-gray-800 mb-2 text-[0.85rem]">5. Responsabilidades do Usuário</h3>
            <p class="mb-4 text-justify">
              O usuário concorda em utilizar a plataforma de forma ética e de acordo com a finalidade para a qual foi projetada. É expressamente proibida a inserção de dados falsos, bem como qualquer tentativa de engenharia reversa, violação de segurança ou envio de dados maliciosos ao servidor do GUIAR.
            </p>

            <h3 class="font-bold text-gray-800 mb-2 text-[0.85rem]">6. Limitação de Responsabilidade</h3>
            <p class="mb-4 text-justify">
              O GUIAR se esforça para manter a plataforma sempre acessível e estável. Contudo, não nos responsabilizamos por quedas temporárias de sinal do servidor, lentidão gerada por provedores de internet externos ou imprecisões decorrentes de falhas de hardware nos dispositivos móveis dos entregadores.
            </p>

            <h3 class="font-bold text-gray-800 mb-2 text-[0.85rem]">7. Alterações e Rescisão</h3>
            <p class="text-justify">
              O GUIAR reserva-se o direito de atualizar ou modificar este termo a qualquer momento para refletir melhorias no sistema ou mudanças regulatórias. Notificações sobre alterações significativas serão enviadas por e-mail ou publicadas em destaque em nosso sistema.
            </p>
          </div>

          <!-- Botão Voltar ou Ações de Aceite -->
          <?php if (isset($_SESSION['aceite_termos_pendente']) && $_SESSION['aceite_termos_pendente'] === true): ?>
            <div class="terms-actions flex flex-col sm:flex-row gap-3 w-full">
              <a href="<?= BASE_URL ?>/routes.php?action=recusarTermos"
                class="terms-action terms-action-secondary w-full sm:w-1/2 border border-gray-300 text-gray-700 font-bold text-base py-3 rounded-xl hover:bg-gray-50 hover:shadow-sm transform hover:-translate-y-[0.5px] transition-all flex items-center justify-center gap-2 text-center">
                Recusar
              </a>
              <a href="<?= BASE_URL ?>/routes.php?action=confirmarTermos"
                class="terms-action terms-action-primary w-full sm:w-1/2 bg-[#1B138F] text-white font-bold text-base py-3 rounded-xl hover:bg-[#111979] hover:shadow-md transform hover:-translate-y-[0.5px] transition-all flex items-center justify-center gap-2 text-center">
                Aceitar e Continuar
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
              </a>
            </div>
          <?php else: ?>
            <div class="terms-actions flex flex-col gap-3">
              <a href="<?= BASE_URL ?>/routes.php?action=cadastroEmpresa"
                class="terms-action terms-action-primary w-full bg-[#1B138F] text-white text-base py-3 rounded-xl hover:bg-[#111979]
                 hover:shadow-md transform hover:-translate-y-[0.5px] transition-all flex items-center justify-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Voltar para o login da empresa
              </a>
            </div>
          <?php endif; ?>

        </div>
      </div>
    </div>
  </div>

  <!-- RODAPÉ -->
  <footer class="w-full bg-brand-blue px-6 py-4 mt-auto border-t border-white/10 shadow-inner z-50">
    <div class="w-full flex flex-col md:flex-row items-center justify-between text-[0.85rem]">
      <p class="mb-2 md:mb-0">&copy; <?= date('Y') ?> GUIAR. Todos os direitos reservados.</p>
      <div class="flex space-x-4 font-medium">
        <a href="#" class="hover:text-brand-yellow transition-colors">Política de Privacidade</a>
        <a href="<?= BASE_URL ?>/routes.php?action=termosUso" class="hover:text-brand-yellow transition-colors">Termos de Serviço</a>
      </div>
    </div>
  </footer>

  <script>
    // Toggle do menu mobile
    const btn = document.getElementById('nav-toggle');
    const menu = document.getElementById('nav-menu');
    if (btn && menu) {
      btn.addEventListener('click', () => {
        menu.classList.toggle('hidden');
        menu.classList.toggle('flex');
      });
    }
  </script>
</body>

</html>

<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login Entregador | Guiar</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= BASE_URL ?>/style/login.css?v=20261126-1517">
  <link rel="stylesheet" href="<?= BASE_URL ?>/style/navbar.css">
  <link rel="Shortcut Icon" type="image/png" href="<?= BASE_URL ?>/img/logoIcon.png" class=" w-50 h-60 object-contain">

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
</head>

<body class="min-h-screen flex flex-col font-sans login-page">
  <nav id="navbar" class="navbar navbar-expand-lg fixed-top py-3">
    <div class="container">
      <a class="navbar-brand d-flex align-items-center" href="<?= BASE_URL ?>/index.html">
        <img class="brand-logo" src="<?= BASE_URL ?>/img/LogoGAzul.png" alt="GUIAR Logística">
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu"
        aria-controls="menu" aria-expanded="false" aria-label="Abrir menu">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="menu">
        <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-4">
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/index.html">Início</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/routes.php?action=loginEmpresa">Administrador</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/routes.php?action=loginEntregador">Entregador</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Container centralizador do formulário -->
  <div class="flex-grow flex items-center justify-center p-4 sm:p-8">

    <!-- Card Principal com Bordas Arredondadas -->
    <div class="bg-white rounded-[2rem] shadow-2xl flex flex-col md:flex-row w-full max-w-[950px] min-h-[580px] overflow-hidden opacity-0 animate-fade-in-up">

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
            <img
              src="<?= BASE_URL ?>/img/LogoGuiar.png"
              alt="Logo GUIAR"
              class="w-32 brightness-0 invert">
          </div>

          <!-- Slogan e Linha Amarela -->
          <div class="my-auto">
            <h1 class="text-white text-[2rem] leading-[1.2] font-bold mb-5 tracking-tight">
              Entre e deixe o Guiar<br>te mostrar a direção.
            </h1>
            <div class="h-1 w-20 bg-brand-yellow"></div>
          </div>
        </div>
      </div>

      <!-- Painel Direito (Formulário) -->
      <div class="w-full md:w-[55%] bg-white p-8 sm:p-14 flex flex-col justify-center items-center relative">

        <div class="login-form-content w-full max-w-[508px]">

          <!-- Cabeçalho: Ícone e Título -->
          <div class="login-form-heading flex items-center justify-center gap-3 mb-10">
            <div class="w-[4.5rem] h-[4.5rem] rounded-full bg-[#d6e0f5] flex items-center justify-center text-brand-blue shrink-0">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
              </svg>
            </div>
            <h2 class="login-form-title text-[1.8rem] font-bold text-gray-900 tracking-tight leading-none pt-2">
              Login <br class="md:hidden"> <span class="block md:inline">Entregadores</span>
            </h2>
          </div>

          <!-- Exibir mensagem de erro se houver -->
          <?php if (isset($_GET['erro'])): ?>
            <div class="bg-red-50 border border-red-300 text-red-600 px-4 py-3 rounded-xl text-sm text-center font-medium shadow-sm mb-5" role="alert">
              <span><?php echo htmlspecialchars($_GET['erro']); ?></span>
            </div>
          <?php endif ?>

          <!-- Início do Formulário -->
          <form class="flex flex-col gap-5" method="post" action="<?= BASE_URL ?>/routes.php?action=loginEntregador">

            <!-- Input Email -->
            <div>
              <label for="exampleInputEmail1" class="block text-[0.85rem] font-medium text-gray-700 mb-1.5 ml-1">Email</label>
              <input type="email" id="exampleInputEmail1" name="email" aria-describedby="emailHelp" required
                class="w-full px-4 py-3 rounded-xl border border-brand-inputborder focus:outline-none focus:border-brand-blue focus:ring-2 focus:ring-blue-100 transition-all text-gray-800 shadow-[0_2px_10px_rgba(0,0,0,0.02)]"
                placeholder="">
            </div>

            <!-- Input Senha -->
            <div>
              <label for="exampleInputPassword1" class="block text-[0.85rem] font-medium text-gray-700 mb-1.5 ml-1">Senha</label>
              <div class="password-field">
                <input type="password" id="exampleInputPassword1" name="senha" required
                  class="w-full px-4 py-3 rounded-xl border border-brand-inputborder focus:outline-none focus:border-brand-blue focus:ring-2 focus:ring-blue-100 transition-all text-gray-800 shadow-[0_2px_10px_rgba(0,0,0,0.02)]"
                  placeholder="">
                <button id="toggleCourierPassword" class="password-toggle" type="button" aria-label="Mostrar senha" aria-controls="exampleInputPassword1" aria-pressed="false">
                  <svg data-eye-hidden viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M3 3l18 18M10.6 10.6a2 2 0 0 0 2.8 2.8" />
                    <path d="M9.9 5.2A10.8 10.8 0 0 1 12 5c5 0 8.5 4.5 9.5 7-.4 1.1-1.2 2.2-2.2 3.2M6.2 6.2C3.8 7.7 2.6 10.2 2.5 12c.5 1.2 3.8 7 9.5 7 1 0 2-.2 2.9-.5" />
                  </svg>
                  <svg data-eye-visible hidden viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M2.5 12s3.5-7 9.5-7 9.5 7 9.5 7-3.5 7-9.5 7-9.5-7-9.5-7Z" />
                    <circle cx="12" cy="12" r="3" />
                  </svg>
                </button>
              </div>
            </div>

            <!-- Botão Entrar -->
            <button type="submit" id="botao" name="entrar" value="Entrar"
              class="w-full bg-brand-yellow text-gray-900 font-bold text-lg py-3.5 rounded-xl hover:bg-[#eacc00] hover:shadow-md transform hover:-translate-y-[1px] transition-all mt-1 cursor-pointer">
              Entrar
            </button>

            <!-- Link Esqueceu a Senha -->
            <div class="text-center mt-4">
              <a href="<?= BASE_URL ?>/routes.php?action=esqueceuSenhaEntregador" class="text-brand-link hover:text-blue-700 text-sm font-medium transition-colors">
                Esqueceu sua senha?
              </a>
            </div>

          </form>

        </div>
      </div>
    </div>
  </div>

  <!-- RODAPÉ -->
  <footer class="w-full bg-brand-blue px-6 py-4 mt-auto border-t border-white/10 shadow-inner z-50">
    <div class="w-full flex flex-col md:flex-row items-center justify-between text-[0.85rem] text-white/80">
      <p class="mb-2 md:mb-0">&copy; <?= date('Y') ?> GUIAR. Todos os direitos reservados.</p>
      <div class="flex space-x-4 font-medium">
        <a href="#" class="hover:text-brand-yellow transition-colors">Política de Privacidade</a>
        <a href="<?= BASE_URL ?>/routes.php?action=termosUso" class="hover:text-brand-yellow transition-colors">Termos de Serviço</a>
      </div>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    const navbar = document.getElementById('navbar');
    window.addEventListener('scroll', () => navbar.classList.toggle('scrolled', window.scrollY > 40));
    document.querySelectorAll('#menu .nav-link').forEach(link => link.addEventListener('click', () => {
      bootstrap.Collapse.getOrCreateInstance(document.getElementById('menu')).hide();
    }));

    const courierPassword = document.getElementById('exampleInputPassword1');
    const toggleCourierPassword = document.getElementById('toggleCourierPassword');
    const hiddenEye = toggleCourierPassword.querySelector('[data-eye-hidden]');
    const visibleEye = toggleCourierPassword.querySelector('[data-eye-visible]');

    toggleCourierPassword.addEventListener('click', () => {
      const showPassword = courierPassword.type === 'password';
      courierPassword.type = showPassword ? 'text' : 'password';
      toggleCourierPassword.setAttribute('aria-label', showPassword ? 'Ocultar senha' : 'Mostrar senha');
      toggleCourierPassword.setAttribute('aria-pressed', String(showPassword));
      hiddenEye.hidden = showPassword;
      visibleEye.hidden = !showPassword;
    });
  </script>
</body>

</html>
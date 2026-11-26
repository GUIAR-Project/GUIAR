<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Esqueceu sua senha | Entregador</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="Shortcut Icon" type="image/png" href="<?= BASE_URL ?>/img/logoIcon.png">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/style/login.css?v=20261125-1820">
    <link rel="stylesheet" href="<?= BASE_URL ?>/style/navbar.css">
    
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
                    },
                    keyframes: {
                        fadeInUp: {
                            '0%': { opacity: '0', transform: 'translateY(30px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        },
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

    <main class="flex-grow flex items-center justify-center p-4 sm:p-8">
        <div class="bg-white rounded-[2rem] shadow-2xl flex flex-col md:flex-row w-full max-w-[950px] min-h-[580px] overflow-hidden">
            <div class="bg-brand-blue w-full md:w-[45%] p-10 flex flex-col relative overflow-hidden">
                <img src="<?= BASE_URL ?>/img/Mapa.png" alt="" class="absolute inset-0 w-[150%] h-[150%] max-w-none -left-[20%] -top-[10%] object-contain opacity-40 pointer-events-none z-0 mix-blend-overlay">
                <div class="hidden md:block absolute top-[75%] left-[25%] transform -translate-x-1/2 -translate-y-1/2 z-0 opacity-80 pointer-events-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-[4.5rem] w-[4.5rem] text-brand-yellow drop-shadow-md" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5c0-1.656-1.344-3-3-3s-3 1.344-3 3 1.344 3 3 3 3-1.344 3-3Zm4.5 0c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                    </svg>
                </div>
                <div class="relative z-10 flex flex-col h-full">
                    <div class="mb-8">
                        <img src="<?= BASE_URL ?>/img/LogoGuiar.png" alt="GUIAR" class="w-32 brightness-0 invert">
                    </div>
                    <div class="my-auto">
                        <h1 class="text-white text-[2rem] leading-[1.2] font-bold mb-5 tracking-tight">
                            Esqueceu sua senha?<br>Vamos ajudar você.
                        </h1>
                        <div class="h-1 w-20 bg-brand-yellow"></div>
                    </div>
                </div>
            </div>

            <div class="w-full md:w-[55%] bg-white p-8 sm:p-14 flex flex-col justify-center items-center relative">
                <div class="login-form-content w-full max-w-[508px]">
                    <div class="login-form-heading flex items-center justify-center gap-3 mb-10">
                        <div class="w-[4.5rem] h-[4.5rem] rounded-full bg-[#d6e0f5] flex items-center justify-center text-brand-blue shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                            </svg>
                        </div>
                        <h2 class="login-form-title text-[1.8rem] font-bold text-gray-900 tracking-tight leading-none pt-2">Esqueceu sua senha?</h2>
                    </div>
                    <p class="mb-5 text-sm text-gray-600">Digite seu e-mail para receber as instruções de recuperação.</p>

                    <?php if (isset($erro)): ?>
                        <div class="bg-red-50 border border-red-300 text-red-700 px-4 py-3 rounded-xl mb-5 text-sm font-medium" role="alert">
                            <?= htmlspecialchars($erro) ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($sucesso)): ?>
                        <div class="bg-green-50 border border-green-300 text-green-700 px-4 py-3 rounded-xl mb-5 text-sm font-medium" role="status">
                            <?= htmlspecialchars($sucesso) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="<?= BASE_URL ?>/routes.php?action=esqueceuSenhaEntregador" class="flex flex-col gap-5">
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">E-mail</label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                autocomplete="email"
                                required
                                class="w-full px-4 py-3 rounded-xl border border-brand-inputborder focus:outline-none focus:border-brand-blue focus:ring-2 focus:ring-blue-100 transition-all"
                                placeholder="seu@email.com"
                            >
                        </div>
                        <button type="submit" class="w-full font-bold text-lg py-3.5 rounded-xl transition-all">
                            Enviar instruções
                        </button>
                    </form>

                    <div class="mt-6 text-center">
                        <a href="<?= BASE_URL ?>/routes.php?action=loginEntregador" class="text-brand-link hover:text-blue-700 text-sm font-medium transition-colors">
                            ← Voltar para o login
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- RODAPÉ -->
    <footer class="w-full bg-brand-blue px-6 py-4 mt-auto border-t border-white/10">
        <div class="w-full flex flex-col md:flex-row items-center justify-between text-sm text-white/80">
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
    </script>

</body>
</html>

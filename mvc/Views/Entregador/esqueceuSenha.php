<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Esqueceu sua senha | Entregador</title>
    <link rel="Shortcut Icon" type="image/png" href="<?= BASE_URL ?>/img/logoIcon.png">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
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

<body class="min-h-screen flex flex-col font-sans bg-[#eef5ff]">

    <!-- HEADER -->
    <header class="bg-brand-blue shadow-md py-0 px-8 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <div class="flex items-center -my-6">
                <a href="<?= BASE_URL ?>/index.html">
                    <img src="<?= BASE_URL ?>/img/LogoGAzul.png" alt="LOGO" class="h-10 md:h-14 w-auto object-contain">
                </a>
            </div>
            
            <nav class="hidden md:flex items-center gap-12 font-semibold text-xl">
                <a href="<?= BASE_URL ?>/index.html" class="text-white/90 hover:text-brand-yellow transition duration-300">Home</a>
                <a href="<?= BASE_URL ?>/routes.php?action=loginEmpresa" class="text-white/90 hover:text-brand-yellow transition duration-300">Administrador</a>
                <a href="<?= BASE_URL ?>/routes.php?action=loginEntregador" class="text-brand-yellow font-bold">Entregador</a>
            </nav>
            
            <button id="nav-toggle" class="md:hidden p-2 text-white focus:outline-none hover:bg-white/10 rounded-lg transition-colors" type="button">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>
        </div>
        
        <div id="nav-menu" class="hidden w-full md:hidden pb-4">
            <div class="flex flex-col gap-4 text-lg font-semibold mt-4">
                <a href="<?= BASE_URL ?>/index.html" class="text-white/90">Home</a>
                <a href="<?= BASE_URL ?>/routes.php?action=loginEmpresa" class="text-white/90">Administrador</a>
                <a href="<?= BASE_URL ?>/routes.php?action=loginEntregador" class="text-brand-yellow font-bold">Entregador</a>
            </div>
        </div>
    </header>

    <!-- Container Principal -->
    <div class="flex-grow flex items-center justify-center p-4 sm:p-8">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-8 sm:p-10 opacity-0 animate-fade-in-up">
            
            <!-- Ícone e Título -->
            <div class="flex flex-col items-center mb-8">
                <div class="w-20 h-20 rounded-full bg-blue-100 flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-brand-blue" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-gray-900 text-center mb-2">Esqueceu sua senha?</h1>
                <p class="text-gray-600 text-center text-sm">Digite seu e-mail para receber as instruções de recuperação</p>
            </div>

            <!-- Mensagens de Erro/Sucesso -->
            <?php if (isset($erro)): ?>
                <div class="bg-red-50 border border-red-300 text-red-700 px-4 py-3 rounded-xl mb-6 text-sm font-medium" role="alert">
                    <?= htmlspecialchars($erro) ?>
                </div>
            <?php endif; ?>

            <?php if (isset($sucesso)): ?>
                <div class="bg-green-50 border border-green-300 text-green-700 px-4 py-3 rounded-xl mb-6 text-sm font-medium" role="alert">
                    <div class="flex items-start gap-2">
                        <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span><?= htmlspecialchars($sucesso) ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Formulário -->
            <form method="POST" action="<?= BASE_URL ?>/routes.php?action=esqueceuSenhaEntregador" class="space-y-6">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">E-mail</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        required
                        class="w-full px-4 py-3 rounded-xl border border-brand-inputborder focus:outline-none focus:border-brand-blue focus:ring-2 focus:ring-blue-100 transition-all"
                        placeholder="seu@email.com"
                    >
                </div>

                <button 
                    type="submit"
                    class="w-full bg-brand-yellow text-gray-900 font-bold text-lg py-3.5 rounded-xl hover:bg-[#eacc00] hover:shadow-md transform hover:-translate-y-[1px] transition-all"
                >
                    Enviar instruções
                </button>
            </form>

            <!-- Link para voltar -->
            <div class="mt-6 text-center">
                <a href="<?= BASE_URL ?>/routes.php?action=loginEntregador" class="text-brand-link hover:text-blue-700 text-sm font-medium transition-colors">
                    ← Voltar para o login
                </a>
            </div>

        </div>
    </div>

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

    <script>
        // Menu mobile toggle
        document.getElementById('nav-toggle')?.addEventListener('click', function() {
            document.getElementById('nav-menu').classList.toggle('hidden');
        });
    </script>

</body>
</html>

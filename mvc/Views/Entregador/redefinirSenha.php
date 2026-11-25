<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir Senha | Entregador</title>
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
    <style>
        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #58769f;
            padding: 4px;
            transition: color 0.2s;
        }
        .password-toggle:hover {
            color: #001b69;
        }
        .password-field {
            position: relative;
        }
    </style>
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
        </div>
    </header>

    <!-- Container Principal -->
    <div class="flex-grow flex items-center justify-center p-4 sm:p-8">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-8 sm:p-10 opacity-0 animate-fade-in-up">
            
            <!-- Ícone e Título -->
            <div class="flex flex-col items-center mb-8">
                <div class="w-20 h-20 rounded-full bg-blue-100 flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-brand-blue" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-gray-900 text-center mb-2">Nova Senha</h1>
                <p class="text-gray-600 text-center text-sm">Digite sua nova senha para redefinir o acesso</p>
            </div>

            <!-- Mensagens de Erro/Sucesso -->
            <?php if (isset($erro)): ?>
                <div class="bg-red-50 border border-red-300 text-red-700 px-4 py-3 rounded-xl mb-6 text-sm font-medium" role="alert">
                    <?= htmlspecialchars($erro) ?>
                </div>
            <?php endif; ?>

            <?php if (isset($sucesso)): ?>
                <div class="bg-green-50 border border-green-300 text-green-700 px-4 py-3 rounded-xl mb-6 text-sm font-medium" role="alert">
                    <div class="flex flex-col gap-3">
                        <div class="flex items-start gap-2">
                            <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span><?= htmlspecialchars($sucesso) ?></span>
                        </div>
                        <a href="<?= BASE_URL ?>/routes.php?action=loginEntregador" class="inline-block text-center bg-brand-yellow text-gray-900 font-bold py-2.5 px-6 rounded-lg hover:bg-[#eacc00] transition-colors">
                            Ir para o Login
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!isset($sucesso)): ?>
            <!-- Formulário -->
            <form method="POST" action="<?= BASE_URL ?>/routes.php?action=redefinirSenhaEntregador" class="space-y-6" onsubmit="return validatePassword()">
                <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token'] ?? '') ?>">
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Nova Senha</label>
                    <div class="password-field">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            required
                            minlength="6"
                            class="w-full px-4 py-3 pr-12 rounded-xl border border-brand-inputborder focus:outline-none focus:border-brand-blue focus:ring-2 focus:ring-blue-100 transition-all"
                            placeholder="Mínimo 6 caracteres"
                        >
                        <button type="button" class="password-toggle" onclick="togglePassword('password', this)">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div>
                    <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-2">Confirmar Senha</label>
                    <div class="password-field">
                        <input 
                            type="password" 
                            id="confirm_password" 
                            name="confirm_password" 
                            required
                            minlength="6"
                            class="w-full px-4 py-3 pr-12 rounded-xl border border-brand-inputborder focus:outline-none focus:border-brand-blue focus:ring-2 focus:ring-blue-100 transition-all"
                            placeholder="Repita a senha"
                        >
                        <button type="button" class="password-toggle" onclick="togglePassword('confirm_password', this)">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                </div>

                <button 
                    type="submit"
                    class="w-full bg-brand-yellow text-gray-900 font-bold text-lg py-3.5 rounded-xl hover:bg-[#eacc00] hover:shadow-md transform hover:-translate-y-[1px] transition-all"
                >
                    Redefinir Senha
                </button>
            </form>
            <?php endif; ?>

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
        function togglePassword(inputId, button) {
            const input = document.getElementById(inputId);
            if (input.type === 'password') {
                input.type = 'text';
                button.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>';
            } else {
                input.type = 'password';
                button.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>';
            }
        }

        function validatePassword() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                alert('As senhas não coincidem!');
                return false;
            }
            
            if (password.length < 6) {
                alert('A senha deve ter no mínimo 6 caracteres!');
                return false;
            }
            
            return true;
        }
    </script>

</body>
</html>

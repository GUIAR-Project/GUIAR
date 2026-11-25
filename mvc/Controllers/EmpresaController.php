<?php

namespace mvc\Controllers;

use mvc\Models\Empresa;
use mvc\Models\Administrador;
use mvc\Services\EmailService;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmpresaController
{
    private $empresaModel;
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->empresaModel = new Empresa($pdo);
    }

    public function login()
    {
        (new AdministradorController($this->pdo))->login();
    }

    public function termosUso()
    {
        require_once __DIR__ . '/../Views/Empresa/TermosUso.php';
    }

    public function confirmarTermos()
    {
        if (isset($_SESSION['aceite_termos_pendente']) && $_SESSION['aceite_termos_pendente'] === true) {
            $email = $_SESSION['email'] ?? '';

            // Gerar código de verificação (4 números)
            $codigo_verificacao = rand(1000, 9999);
            $_SESSION['codigo_verificacao'] = $codigo_verificacao;

            // Enviar o código de verificação via email
            $mail = new PHPMailer(true);
            try {
                // Configurações do servidor de email
                EmailService::configure($mail);

                // Configurações de envio
                $mail->setFrom('projGuiar@gmail.com', 'GUIAR');
                $mail->addAddress($email);

                // Conteúdo do email
                $mail->isHTML(true);
                $mail->Subject = 'GUIAR - Verificacao de E-mail para Cadastro';
                $mail->Body = "
                    <div style='font-family: Arial, sans-serif; color: #102b61; line-height: 1.6; max-width: 600px; margin: 0 auto; background-color: #eef5ff; border: 1px solid #c4d8ff; padding: 20px; border-radius: 8px;'>
                        <h2 style='color: #ffffff; background-color: #111979; text-align: center; margin: -20px -20px 20px; padding: 20px; border-radius: 8px 8px 0 0;'>GUIAR</h2>
                        <h3 style='color: #102b61;'>Seja bem-vindo(a) ao GUIAR!</h3>
                        <p>Agradecemos por iniciar o seu cadastro como empresa parceira. Para dar continuidade ao processo e garantir a segurança da sua conta, precisamos confirmar o seu endereço de e-mail.</p>
                        <p>Por favor, utilize o código de verificação de 4 dígitos abaixo na tela de cadastro:</p>
                        <div style='text-align: center; margin: 30px 0;'>
                            <span style='background-color: #ffffff; border: 2px dashed #111979; color: #111979; padding: 15px 30px; border-radius: 5px; font-weight: bold; font-size: 24px; display: inline-block; letter-spacing: 5px;'>{$codigo_verificacao}</span>
                        </div>
                        <p>Caso você não tenha se cadastrado na plataforma <strong>GUIAR</strong>, por favor, desconsidere este e-mail de forma segura.</p>
                        <hr style='border: none; border-top: 1px solid #c4d8ff; margin: 20px 0;'>
                        <p style='font-size: 12px; color: #58769f; text-align: center;'>
                            Atenciosamente,<br>
                            <strong>Equipe GUIAR</strong><br>
                        </p>
                    </div>
                ";

                // Enviar email
                $mail->send();

                unset($_SESSION['aceite_termos_pendente']);

                // Redirecionar para a página de verificação
                header('Location: ' . BASE_URL . '/routes.php?action=verificarCodigo');
                exit();
            } catch (Exception $e) {
                // Ao invés de um simples echo, envia como erro para a página de cadastro
                $erro = "Erro ao enviar email: {$mail->ErrorInfo}";
                session_unset();
                header("Location: " . BASE_URL . "/routes.php?action=cadastroEmpresa&erro=" . urlencode($erro));
                exit();
            }
        } else {
            header("Location: " . BASE_URL . "/index.html");
            exit();
        }
    }

    public function recusarTermos()
    {
        session_unset();
        header("Location: " . BASE_URL . "/routes.php?action=cadastroEmpresa");
        exit();
    }

    public function cadastro()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $nome_empresa = $_POST['nome_empresa'] ?? '';
            $nome_adm = $_POST['nome_adm'] ?? '';
            $cnpj = $_POST['cnpj'] ?? '';
            $nome_usuario = $_POST['nome_usuario'] ?? '';
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $senha = $_POST['senha'] ?? '';

            if (trim($nome_adm) === '' || trim($nome_usuario) === '' || $senha === '') {
                $erro = 'Informe o nome, o usuário e a senha do primeiro administrador.';
                require_once __DIR__ . '/../Views/Empresa/cadastro.php';
                return;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $erro = "E-mail inválido!";
                require_once __DIR__ . '/../Views/Empresa/cadastro.php';
                return;
            }

            if ($this->empresaModel->emailExists($email)) {
                $erro = "Este e-mail já está em uso!";
                require_once __DIR__ . '/../Views/Empresa/cadastro.php';
                return;
            }

            // Lógica para o upload da imagem
            $diretorio = __DIR__ . '/../../public/uploads/empresas/';

            // Verifica se o diretório existe, se não existir, tenta criar
            if (!is_dir($diretorio)) {
                mkdir($diretorio, 0777, true);
            }

            $nome_imagem = '';
            // Lógica para verificar e mover a imagem
            if (isset($_FILES['foto_logo']) && $_FILES['foto_logo']['error'] == 0) {
                $nome_imagem = basename($_FILES['foto_logo']['name']);
                $caminho_imagem = $diretorio . $nome_imagem;
                move_uploaded_file($_FILES['foto_logo']['tmp_name'], $caminho_imagem);
            }

            // Iniciar sessão para salvar temporariamente os dados do cadastro
            $_SESSION['nome_empresa'] = $nome_empresa;
            $_SESSION['nome_adm_cadastro'] = $nome_adm;
            $_SESSION['cnpj'] = $cnpj;
            $_SESSION['nome_usuario'] = $nome_usuario;
            $_SESSION['email'] = $email;
            $_SESSION['senha'] = $senha;
            $_SESSION['nome_imagem'] = $nome_imagem;
            $_SESSION['aceite_termos_pendente'] = true;

            // Redirecionar para a página de Termos de Uso
            header('Location: ' . BASE_URL . '/routes.php?action=termosUso');
            exit();
        }
        // Se for GET, renderiza a View
        $erro = $_GET['erro'] ?? null;
        '';
        require_once __DIR__ . '/../Views/Empresa/cadastro.php';
    }

    public function esqueceuSenha()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $erro = "E-mail inválido!";
                require_once __DIR__ . '/../Views/Empresa/esqueceuSenha.php';
                return;
            }

            $stmt = $this->pdo->prepare("SELECT id_empresa FROM empresa WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                $erro = "E-mail não encontrado!";
                require_once __DIR__ . '/../Views/Empresa/esqueceuSenha.php';
                return;
            }

            $userId = $stmt->fetchColumn();
            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $stmt = $this->pdo->prepare("INSERT INTO password_resets (user_id, token, expiry) VALUES (:user_id, :token, :expiry)");
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':token', $token);
            $stmt->bindParam(':expiry', $expiry);
            $stmt->execute();

            $mail = new PHPMailer(true);
            try {
                EmailService::configure($mail);

                $mail->setFrom('projGuiar@gmail.com', 'GUIAR');
                $mail->addAddress($email);

                $resetLink = BASE_URL . "/routes.php?action=redefinirSenha&token=$token";

                $mail->isHTML(true);
                $mail->Subject = 'GUIAR - Solicitacao de Redefinicao de Senha';
                $mail->Body = "
                    <div style='font-family: Arial, sans-serif; color: #102b61; line-height: 1.6; max-width: 600px; margin: 0 auto; background-color: #eef5ff; border: 1px solid #c4d8ff; padding: 20px; border-radius: 8px;'>
                        <h2 style='color: #ffffff; background-color: #111979; text-align: center; margin: -20px -20px 20px; padding: 20px; border-radius: 8px 8px 0 0;'>GUIAR</h2>
                        <h3 style='color: #102b61;'>Prezado(a) parceiro(a),</h3>
                        <p>Recebemos uma solicitação para redefinir a senha da sua conta empresarial no sistema <strong>GUIAR</strong>.</p>
                        <p>Para cadastrar uma nova senha e recuperar o acesso, por favor, clique no botão abaixo:</p>
                        <div style='text-align: center; margin: 30px 0;'>
                            <a href='{$resetLink}' style='background-color: #ffd400; color: #102b61; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 16px; display: inline-block;'>Redefinir Minha Senha</a>
                        </div>
                        <p>Se o botão não funcionar, você também pode acessar o endereço abaixo, copiando-o e colando-o em seu navegador:</p>
                        <p style='word-break: break-all; color: #00288a;'><a href='{$resetLink}' style='color: #00288a;'>{$resetLink}</a></p>
                        <p>Ressaltamos que este link de redefinição é válido por apenas <strong>1 hora</strong>. Caso você não tenha solicitado esta alteração, por favor, desconsidere este e-mail. Sua senha atual permanecerá segura e inalterada.</p>
                        <hr style='border: none; border-top: 1px solid #c4d8ff; margin: 20px 0;'>
                        <p style='font-size: 12px; color: #58769f; text-align: center;'>
                            Atenciosamente,<br>
                            <strong>Equipe GUIAR</strong><br>
                        </p>
                    </div>
                ";

                $mail->send();

                $sucesso = "Um e-mail foi enviado para " . htmlspecialchars($email) . " com as instruções.";
                require_once __DIR__ . '/../Views/Empresa/esqueceuSenha.php';
                return;
            } catch (Exception $e) {
                $erro = "Erro ao enviar email: {$mail->ErrorInfo}";
                require_once __DIR__ . '/../Views/Empresa/esqueceuSenha.php';
                return;
            }
        }

        require_once __DIR__ . '/../Views/Empresa/esqueceuSenha.php';
    }

    public function redefinirSenha()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['token'] ?? '';
            $newPassword = $_POST['password'] ?? '';

            $stmt = $this->pdo->prepare("SELECT user_id FROM password_resets WHERE token = :token AND expiry > NOW()");
            $stmt->bindParam(':token', $token);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                $erro = "Token inválido ou expirado.";
                require_once __DIR__ . '/../Views/Empresa/redefinirSenha.php';
                return;
            }

            $userId = $stmt->fetchColumn();

            $stmt = $this->pdo->prepare("UPDATE empresa SET senha = :senha WHERE id_empresa = :id");
            $stmt->bindParam(':senha', $newPassword);
            $stmt->bindParam(':id', $userId);
            $stmt->execute();

            $stmt = $this->pdo->prepare("DELETE FROM password_resets WHERE token = :token");
            $stmt->bindParam(':token', $token);
            $stmt->execute();

            $sucesso = "Senha redefinida com sucesso.";
            require_once __DIR__ . '/../Views/Empresa/redefinirSenha.php';
            return;
        }

        require_once __DIR__ . '/../Views/Empresa/redefinirSenha.php';
    }

    public function verificarCodigo()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $codigo_inserido = $_POST['codigo_verificacao'] ?? '';

            if (isset($_SESSION['codigo_verificacao']) && $codigo_inserido == $_SESSION['codigo_verificacao']) {
                $data = [
                    'nome_empresa' => $_SESSION['nome_empresa'] ?? '',
                    'cnpj' => $_SESSION['cnpj'] ?? '',
                    'nome_usuario' => $_SESSION['nome_usuario'] ?? '',
                    'email' => $_SESSION['email'] ?? '',
                    'senha' => $_SESSION['senha'] ?? '',
                    'nome_arquivo' => $_SESSION['nome_imagem'] ?? ''
                ];

                $this->pdo->beginTransaction();
                $empresaCriada = $this->empresaModel->create($data);
                $empresaId = (int)$this->pdo->lastInsertId();
                $administradorCriado = $empresaCriada && (new Administrador($this->pdo))->create([
                    'nome_adm' => $_SESSION['nome_adm_cadastro'] ?? '',
                    'nome_usuario' => $_SESSION['nome_usuario'] ?? '',
                    'nome_foto' => '',
                    'senha' => $_SESSION['senha'] ?? '',
                    'company_id' => $empresaId
                ]);

                if ($administradorCriado) {
                    $this->pdo->commit();
                    session_unset(); // Limpar a sessão após o sucesso
                    header("Location: " . BASE_URL . "/routes.php?action=loginEmpresa");
                    exit();
                } else {
                    $this->pdo->rollBack();
                    $erro = 'Erro ao cadastrar a empresa e o primeiro administrador.';
                    require_once __DIR__ . '/../Views/Empresa/verificarCodigo.php';
                }
            } else {
                $erro = 'Código de verificação incorreto.';
                require_once __DIR__ . '/../Views/Empresa/verificarCodigo.php';
            }
        } else {
            // Renderiza a view
            require_once __DIR__ . '/../Views/Empresa/verificarCodigo.php';
        }
    }

    public function logout()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        // Limpa todas as variáveis da sessão
        $_SESSION = [];

        // Exclui o cookie de sessão do navegador
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        // Destrói a sessão no servidor
        session_destroy();

        header("Location: " . BASE_URL . "/routes.php?action=loginEmpresa");
        exit();
    }
}

<?php
require_once 'includes/config.php';

// Se já estiver logado, redireciona para o painel
if (verificarLogin()) {
    header('Location: index.php');
    exit();
}

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $senha = $_POST['senha'] ?? '';

    // Validar campos obrigatórios
    if (empty($login) || empty($senha)) {
        $erro = 'Por favor, preencha todos os campos.';
    }
    else {
        try {
            $db = new Database();
            $conn = $db->getConnection();

            $sql = "SELECT id, nome_completo, login, senha, perfil, ativo FROM usuarios WHERE login = ? AND ativo = 1";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$login]);
            $usuario = $stmt->fetch();

            if ($usuario && password_verify($senha, $usuario['senha'])) {
                // Login válido, armazenar dados temporários para 2FA
                $_SESSION['temp_login'] = [
                    'id' => $usuario['id'],
                    'nome' => $usuario['nome_completo'],
                    'login' => $usuario['login'],
                    'perfil' => $usuario['perfil']
                ];
                
                header('Location: 2fa.php');
                exit();
            } else {
                $erro = 'Login ou senha incorretos.';
            }
        } catch (Exception $e) {
            $erro = 'Erro interno do servidor. Tente novamente.';
        }
    }
}

// Verificar mensagens da URL
if (isset($_GET['msg'])) {
    switch ($_GET['msg']) {
        case 'cadastro_sucesso':
            $sucesso = 'Cadastro realizado com sucesso! Faça seu login.';
            break;
        case 'logout':
            $sucesso = 'Logout realizado com sucesso!';
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo SITE_NAME; ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Arial', sans-serif;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 3rem;
            width: 100%;
            max-width: 450px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header h2 {
            color: #2563eb;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .login-header p {
            color: #6b7280;
            margin: 0;
        }

        .form-control {
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            padding: 0.75rem;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .btn-login {
            background: linear-gradient(135deg, #2563eb, #1e40af);
            border: none;
            border-radius: 10px;
            padding: 0.75rem;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            background: linear-gradient(135deg, #1e40af, #1e3a8a);
            transform: translateY(-1px);
            color: white;
        }

        .btn-clear {
            background: #6b7280;
            border: none;
            border-radius: 10px;
            padding: 0.75rem;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
        }

        .btn-clear:hover {
            background: #4b5563;
            color: white;
        }

        .alert {
            border-radius: 10px;
            border: none;
        }

        .login-footer {
            text-align: center;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e5e7eb;
        }

        .login-footer a {
            color: #2563eb;
            text-decoration: none;
            font-weight: 500;
        }

        .login-footer a:hover {
            color: #1e40af;
            text-decoration: underline;
        }

        .back-link {
            position: absolute;
            top: 20px;
            left: 20px;
            color: white;
            text-decoration: none;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }

        .back-link:hover {
            color: #f1f5f9;
            transform: translateX(-5px);
        }

        @media (max-width: 576px) {
            .login-container {
                margin: 1rem;
                padding: 2rem;
            }
            
            .back-link {
                position: relative;
                top: auto;
                left: auto;
                display: block;
                text-align: center;
                margin-bottom: 2rem;
                color: white;
            }
        }
    </style>
</head>
<body>
    <a href="index.php" class="back-link">
        <i class="fas fa-arrow-left"></i> Voltar ao Início
    </a>

    <div class="login-container">
        <div class="login-header">
            <h2><i class="fas fa-sign-in-alt"></i> Fazer Login</h2>
            <p>Entre na sua conta para acessar a loja</p>
        </div>

        <?php if ($erro): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($erro); ?>
            </div>
        <?php endif; ?>

        <?php if ($sucesso): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($sucesso); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="mb-3">
                <label for="login" class="form-label">
                    <i class="fas fa-user"></i> Login
                </label>
                <input type="text" 
                       class="form-control" 
                       id="login" 
                       name="login" 
                       placeholder="Digite seu login (6 caracteres)"
                       maxlength="6"
                       value="<?php echo htmlspecialchars($_POST['login'] ?? ''); ?>"
                       required>
            </div>

            <div class="mb-4">
                <label for="senha" class="form-label">
                    <i class="fas fa-lock"></i> Senha
                </label>
                <input type="password" 
                       class="form-control" 
                       id="senha" 
                       name="senha" 
                       placeholder="Digite sua senha"
                       required>
            </div>

            <div class="row g-2 mb-3">
                <div class="col">
                    <button type="submit" class="btn btn-login w-100">
                        <i class="fas fa-sign-in-alt"></i> Entrar
                    </button>
                </div>
                <div class="col">
                    <button type="button" class="btn btn-clear w-100" onclick="limparFormulario()">
                        <i class="fas fa-eraser"></i> Limpar
                    </button>
                </div>
            </div>
        </form>

        <div class="login-footer">
            <p class="mb-2">Não tem uma conta?</p>
            <a href="cadastro.php">
                <i class="fas fa-user-plus"></i> Cadastre-se aqui
            </a>
        </div>

        <div class="text-center mt-3">
            <small class="text-muted">
                <i class="fas fa-info-circle"></i> 
                           </small>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function limparFormulario() {
            document.getElementById('login').value = '';
            document.getElementById('senha').value = '';
            document.getElementById('login').focus();
        }

        // Foco automático no primeiro campo
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('login').focus();
        });
    </script>
</body>
</html>
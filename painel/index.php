<?php
require_once '../includes/config.php';

// Verificar se está logado
if (!verificarLogin()) {
    header('Location: ../login.php');
    exit();
}

// Buscar dados básicos do usuário
$nome_usuario = $_SESSION['usuario_nome'];
$perfil_usuario = $_SESSION['usuario_perfil'];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel - <?php echo SITE_NAME; ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Arial', sans-serif;
        }

        .container {
            padding: 2rem 0;
        }

        .welcome-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 3rem;
            text-align: center;
        }

        .btn {
            margin: 0.5rem;
            padding: 1rem 2rem;
            border-radius: 10px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="welcome-card">
            <h1>Bem-vindo, <?php echo htmlspecialchars($nome_usuario); ?>!</h1>
            <p class="lead">Perfil: <strong><?php echo ucfirst($perfil_usuario); ?></strong></p>
            
            <div class="mt-4">
                <a href="../index.php" class="btn btn-primary">
                    <i class="fas fa-home"></i> Voltar à Loja
                </a>
                
                <?php if ($perfil_usuario === 'master'): ?>
                    <a href="usuarios.php" class="btn btn-success">
                        <i class="fas fa-users"></i> Gerenciar Usuários
                    </a>
                <?php endif; ?>
                
                <a href="../alterar_senha.php" class="btn btn-warning">
                    <i class="fas fa-key"></i> Alterar Senha
                </a>
                
                <a href="../logout.php" class="btn btn-danger">
                    <i class="fas fa-sign-out-alt"></i> Sair
                </a>
            </div>
        </div>
    </div>
</body>
</html>
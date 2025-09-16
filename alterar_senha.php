<?php
require_once 'includes/config.php';

// Verificar se está logado
if (!verificarLogin()) {
    header('Location: login.php');
    exit();
}

// Apenas usuários comuns podem alterar senha (requisito do documento)
if (verificarMaster()) {
    header('Location: erro.php?tipo=acesso_negado');
    exit();
}

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $senha_atual = $_POST['senha_atual'] ?? '';
    $senha_nova = $_POST['senha_nova'] ?? '';
    $confirma_senha = $_POST['confirma_senha'] ?? '';

    // Validações
    if (empty($senha_atual) || empty($senha_nova) || empty($confirma_senha)) {
        $erro = 'Todos os campos são obrigatórios.';
    }
    elseif (strlen($senha_nova) !== 8) {
        $erro = 'A nova senha deve ter exatamente 8 caracteres alfabéticos.';
    }
    elseif (!preg_match('/^[a-zA-Z]{8}$/', $senha_nova)) {
        $erro = 'A nova senha deve conter apenas caracteres alfabéticos.';
    }
    elseif ($senha_nova !== $confirma_senha) {
        $erro = 'A confirmação da senha não confere.';
    }
    else {
        try {
            $db = new Database();
            $conn = $db->getConnection();

            // Verificar senha atual
            $sql = "SELECT senha FROM usuarios WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$_SESSION['usuario_id']]);
            $usuario = $stmt->fetch();

            if (!$usuario || !password_verify($senha_atual, $usuario['senha'])) {
                $erro = 'Senha atual incorreta.';
            } else {
                // Atualizar senha
                $senha_hash = password_hash($senha_nova, PASSWORD_DEFAULT);
                $sql_update = "UPDATE usuarios SET senha = ? WHERE id = ?";
                $stmt_update = $conn->prepare($sql_update);
                
                if ($stmt_update->execute([$senha_hash, $_SESSION['usuario_id']])) {
                    $sucesso = 'Senha alterada com sucesso!';
                    
                    // Fazer logout após alterar senha por segurança
                    $_SESSION = array();
                    session_destroy();
                    
                    // Redirecionar para login após 3 segundos
                    header('refresh:3;url=login.php?msg=senha_alterada');
                } else {
                    $erro = 'Erro ao alterar senha. Tente novamente.';
                }
            }
        } catch (Exception $e) {
            $erro = 'Erro interno do servidor. Tente novamente.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alterar Senha - <?php echo SITE_NAME; ?></title>
    
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
            padding: 2rem 1rem;
        }

        .senha-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 3rem;
            width: 100%;
            max-width: 500px;
        }

        .senha-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .senha-header h2 {
            color: #2563eb;
            font-weight: bold;
            margin-bottom: 0.5rem;
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

        .btn-alterar {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            border: none;
            border-radius: 10px;
            padding: 0.75rem;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
        }

        .btn-alterar:hover {
            background: linear-gradient(135deg, #d97706, #b45309);
            transform: translateY(-1px);
            color: white;
        }

        .btn-cancelar {
            background: #6b7280;
            border: none;
            border-radius: 10px;
            padding: 0.75rem;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
        }

        .btn-cancelar:hover {
            background: #4b5563;
            color: white;
        }

        .alert {
            border-radius: 10px;
            border: none;
        }

        .security-info {
            background: #f3f4f6;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 2rem;
            color: #374151;
            font-size: 0.9rem;
        }

        .password-strength {
            margin-top: 0.5rem;
        }

        .strength-bar {
            height: 6px;
            border-radius: 3px;
            background: #e5e7eb;
            overflow: hidden;
        }

        .strength-fill {
            height: 100%;
            transition: all 0.3s ease;
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
            .senha-container {
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
    <a href="painel/" class="back-link">
        <i class="fas fa-arrow-left"></i> Voltar ao Painel
    </a>

    <div class="senha-container">
        <div class="senha-header">
            <h2><i class="fas fa-key"></i> Alterar Senha</h2>
            <p class="text-muted">Atualize sua senha para manter sua conta segura</p>
        </div>

        <div class="security-info">
            <i class="fas fa-info-circle text-primary"></i>
            <strong>Requisitos da senha:</strong>
            <ul class="mb-0 mt-2">
                <li>Exatamente 8 caracteres</li>
                <li>Apenas letras (A-Z, a-z)</li>
                <li>Não use números ou símbolos</li>
            </ul>
        </div>

        <?php if ($erro): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($erro); ?>
            </div>
        <?php endif; ?>

        <?php if ($sucesso): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($sucesso); ?>
                <br><small>Redirecionando para login em alguns segundos...</small>
            </div>
        <?php endif; ?>

        <?php if (!$sucesso): ?>
            <form method="POST" action="alterar_senha.php" id="formSenha">
                <div class="mb-3">
                    <label for="senha_atual" class="form-label">
                        <i class="fas fa-lock"></i> Senha Atual
                    </label>
                    <input type="password" 
                           class="form-control" 
                           id="senha_atual" 
                           name="senha_atual" 
                           placeholder="Digite sua senha atual"
                           required>
                </div>

                <div class="mb-3">
                    <label for="senha_nova" class="form-label">
                        <i class="fas fa-key"></i> Nova Senha
                    </label>
                    <input type="password" 
                           class="form-control" 
                           id="senha_nova" 
                           name="senha_nova" 
                           placeholder="Digite a nova senha (8 caracteres)"
                           maxlength="8"
                           required>
                    <div class="password-strength">
                        <div class="strength-bar">
                            <div class="strength-fill" id="strengthFill"></div>
                        </div>
                        <small id="strengthText" class="text-muted"></small>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="confirma_senha" class="form-label">
                        <i class="fas fa-check-double"></i> Confirmar Nova Senha
                    </label>
                    <input type="password" 
                           class="form-control" 
                           id="confirma_senha" 
                           name="confirma_senha" 
                           placeholder="Digite a nova senha novamente"
                           maxlength="8"
                           required>
                </div>

                <div class="row g-2">
                    <div class="col">
                        <button type="submit" class="btn btn-alterar w-100">
                            <i class="fas fa-save"></i> Alterar Senha
                        </button>
                    </div>
                    <div class="col">
                        <a href="painel/" class="btn btn-cancelar w-100">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                </div>
            </form>
        <?php endif; ?>

        <div class="text-center mt-4">
            <small class="text-muted">
                <i class="fas fa-shield-alt"></i> 
                Por segurança, você será deslogado após alterar a senha
            </small>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Validação de força da senha
        document.getElementById('senha_nova').addEventListener('input', function() {
            const senha = this.value;
            const strengthFill = document.getElementById('strengthFill');
            const strengthText = document.getElementById('strengthText');
            
            let strength = 0;
            let message = '';
            let color = '#ef4444';
            
            if (senha.length >= 8) strength += 25;
            if (/[a-z]/.test(senha)) strength += 25;
            if (/[A-Z]/.test(senha)) strength += 25;
            if (/^[a-zA-Z]{8}$/.test(senha)) strength += 25;
            
            if (strength <= 25) {
                message = 'Muito fraca';
                color = '#ef4444';
            } else if (strength <= 50) {
                message = 'Fraca';
                color = '#f59e0b';
            } else if (strength <= 75) {
                message = 'Boa';
                color = '#3b82f6';
            } else {
                message = 'Excelente';
                color = '#10b981';
            }
            
            strengthFill.style.width = strength + '%';
            strengthFill.style.backgroundColor = color;
            strengthText.textContent = message;
            strengthText.style.color = color;
        });

        // Validar confirmação de senha
        document.getElementById('confirma_senha').addEventListener('input', function() {
            const senha = document.getElementById('senha_nova').value;
            const confirma = this.value;
            
            if (confirma && senha !== confirma) {
                this.setCustomValidity('As senhas não coincidem');
                this.classList.add('is-invalid');
            } else {
                this.setCustomValidity('');
                this.classList.remove('is-invalid');
                if (confirma) this.classList.add('is-valid');
            }
        });

        // Foco no primeiro campo
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('senha_atual').focus();
        });
    </script>
</body>
</html>
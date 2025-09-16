<?php
require_once 'includes/config.php';

// Verificar se chegou aqui pelo login
if (!isset($_SESSION['temp_login'])) {
    header('Location: login.php');
    exit();
}

$erro = '';
$tentativas = $_SESSION['tentativas_2fa'] ?? 0;
$pergunta_atual = $_SESSION['pergunta_2fa'] ?? null;

// Gerar pergunta aleatória se não existir
if (!$pergunta_atual) {
    $perguntas = [
        'nome_materno' => 'Qual o nome da sua mãe?',
        'data_nascimento' => 'Qual a data do seu nascimento? (formato: AAAA-MM-DD)',
        'cep' => 'Qual o CEP do seu endereço?'
    ];
    
    $campo_aleatorio = array_rand($perguntas);
    $pergunta_atual = [
        'campo' => $campo_aleatorio,
        'texto' => $perguntas[$campo_aleatorio]
    ];
    
    $_SESSION['pergunta_2fa'] = $pergunta_atual;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resposta = trim($_POST['resposta'] ?? '');
    
    if (empty($resposta)) {
        $erro = 'Por favor, forneça uma resposta.';
    } else {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            // Buscar o valor correto no banco
            $user_id = $_SESSION['temp_login']['id'];
            $sql = "SELECT {$pergunta_atual['campo']} FROM usuarios WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$user_id]);
            $valor_correto = $stmt->fetchColumn();
            
            // Verificar resposta
            $resposta_correta = false;
            if ($pergunta_atual['campo'] === 'data_nascimento') {
                $resposta_correta = ($resposta === $valor_correto);
            } else {
                $resposta_correta = (strtolower($resposta) === strtolower($valor_correto));
            }
            
            if ($resposta_correta) {
                // 2FA correto - fazer login completo
                $_SESSION['usuario_id'] = $_SESSION['temp_login']['id'];
                $_SESSION['usuario_nome'] = $_SESSION['temp_login']['nome'];
                $_SESSION['usuario_login'] = $_SESSION['temp_login']['login'];
                $_SESSION['usuario_perfil'] = $_SESSION['temp_login']['perfil'];
                
                // Registrar log de sucesso
                $tipo_2fa = $pergunta_atual['texto'];
                $sql_log = "INSERT INTO logs_autenticacao (usuario_id, tipo_2fa, ip_acesso, sucesso) VALUES (?, ?, ?, ?)";
                $stmt_log = $conn->prepare($sql_log);
                $stmt_log->execute([$user_id, $tipo_2fa, $_SERVER['REMOTE_ADDR'], 1]);
                
                // Limpar dados temporários
                unset($_SESSION['temp_login']);
                unset($_SESSION['pergunta_2fa']);
                unset($_SESSION['tentativas_2fa']);
                
                // Redirecionar baseado no perfil
                if ($_SESSION['usuario_perfil'] === 'master') {
                    header('Location: painel/index.php');
                } else {
                    header('Location: index.php');
                }
                exit();
            } else {
                // Resposta incorreta
                $tentativas++;
                $_SESSION['tentativas_2fa'] = $tentativas;
                
                if ($tentativas >= 3) {
                    // Registrar log de falha
                    $tipo_2fa = $pergunta_atual['texto'];
                    $sql_log = "INSERT INTO logs_autenticacao (usuario_id, tipo_2fa, ip_acesso, sucesso) VALUES (?, ?, ?, ?)";
                    $stmt_log = $conn->prepare($sql_log);
                    $stmt_log->execute([$user_id, $tipo_2fa, $_SERVER['REMOTE_ADDR'], 0]);
                    
                    // Limpar dados e redirecionar
                    unset($_SESSION['temp_login']);
                    unset($_SESSION['pergunta_2fa']);
                    unset($_SESSION['tentativas_2fa']);
                    
                    header('Location: login.php?msg=2fa_fail');
                    exit();
                } else {
                    $erro = 'Resposta incorreta. Tentativa ' . $tentativas . ' de 3.';
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
    <title>Verificação de Segurança - <?php echo SITE_NAME; ?></title>
    
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

        .twofa-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 3rem;
            width: 100%;
            max-width: 500px;
        }

        .twofa-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .twofa-header h2 {
            color: #2563eb;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .security-icon {
            font-size: 3rem;
            color: #f59e0b;
            margin-bottom: 1rem;
        }

        .question-box {
            background: #f8fafc;
            border: 2px solid #e5e7eb;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            text-align: center;
        }

        .question-box h4 {
            color: #374151;
            font-weight: 600;
            margin-bottom: 1rem;
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

        .btn-verify {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            border: none;
            border-radius: 10px;
            padding: 0.75rem;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
        }

        .btn-verify:hover {
            background: linear-gradient(135deg, #d97706, #b45309);
            transform: translateY(-1px);
            color: white;
        }

        .alert {
            border-radius: 10px;
            border: none;
        }

        .tentativas-info {
            text-align: center;
            color: #6b7280;
            margin-bottom: 1rem;
        }

        .progress {
            height: 8px;
            border-radius: 10px;
            margin-bottom: 2rem;
        }

        .progress-bar {
            border-radius: 10px;
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
            .twofa-container {
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
    <a href="login.php" class="back-link">
        <i class="fas fa-arrow-left"></i> Voltar ao Login
    </a>

    <div class="twofa-container">
        <div class="twofa-header">
            <div class="security-icon">
                <i class="fas fa-shield-alt"></i>
            </div>
            <h2>Verificação de Segurança</h2>
            <p class="text-muted">Para sua segurança, responda a pergunta abaixo</p>
        </div>

        <!-- Barra de progresso de tentativas -->
        <div class="tentativas-info">
            <small>Tentativa <?php echo $tentativas + 1; ?> de 3</small>
        </div>
        <div class="progress">
            <div class="progress-bar bg-warning" 
                 role="progressbar" 
                 style="width: <?php echo (($tentativas + 1) / 3) * 100; ?>%">
            </div>
        </div>

        <?php if ($erro): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($erro); ?>
            </div>
        <?php endif; ?>

        <div class="question-box">
            <i class="fas fa-question-circle text-primary mb-2" style="font-size: 2rem;"></i>
            <h4><?php echo htmlspecialchars($pergunta_atual['texto']); ?></h4>
        </div>

        <form method="POST" action="2fa.php">
            <div class="mb-4">
                <label for="resposta" class="form-label">
                    <i class="fas fa-edit"></i> Sua resposta:
                </label>
                <input type="text" 
                       class="form-control" 
                       id="resposta" 
                       name="resposta" 
                       placeholder="Digite sua resposta..."
                       autocomplete="off"
                       required>
                <?php if ($pergunta_atual['campo'] === 'data_nascimento'): ?>
                    <small class="form-text text-muted">
                        <i class="fas fa-info-circle"></i> Use o formato: AAAA-MM-DD (ex: 1990-01-15)
                    </small>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-verify w-100">
                <i class="fas fa-check-shield"></i> Verificar
            </button>
        </form>

        <div class="text-center mt-3">
            <small class="text-muted">
                <i class="fas fa-lock"></i> 
                Esta verificação garante que apenas você tenha acesso à sua conta
            </small>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Foco automático no campo de resposta
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('resposta').focus();
        });
    </script>
</body>
</html>
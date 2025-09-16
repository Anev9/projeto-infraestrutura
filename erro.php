<?php
require_once 'includes/config.php';

// Tipos de erro possíveis
$tipos_erro = [
    'login_failed' => [
        'titulo' => 'Falha na Autenticação',
        'mensagem' => 'Login ou senha incorretos. Tente novamente.',
        'icone' => 'fas fa-user-times',
        'cor' => 'danger'
    ],
    '2fa_failed' => [
        'titulo' => 'Verificação de Segurança Falhou',
        'mensagem' => '3 tentativas sem sucesso no 2FA. Faça login novamente.',
        'icone' => 'fas fa-shield-alt',
        'cor' => 'warning'
    ],
    'acesso_negado' => [
        'titulo' => 'Acesso Negado',
        'mensagem' => 'Você não tem permissão para acessar esta área.',
        'icone' => 'fas fa-ban',
        'cor' => 'danger'
    ],
    'sessao_expirada' => [
        'titulo' => 'Sessão Expirada',
        'mensagem' => 'Sua sessão expirou. Faça login novamente.',
        'icone' => 'fas fa-clock',
        'cor' => 'info'
    ],
    'cadastro_erro' => [
        'titulo' => 'Erro no Cadastro',
        'mensagem' => 'Ocorreu um erro durante o cadastro. Tente novamente.',
        'icone' => 'fas fa-user-plus',
        'cor' => 'danger'
    ],
    'servidor_erro' => [
        'titulo' => 'Erro do Servidor',
        'mensagem' => 'Erro interno do servidor. Tente novamente mais tarde.',
        'icone' => 'fas fa-server',
        'cor' => 'danger'
    ],
    'pagina_nao_encontrada' => [
        'titulo' => 'Página Não Encontrada',
        'mensagem' => 'A página que você procura não foi encontrada.',
        'icone' => 'fas fa-search',
        'cor' => 'warning'
    ]
];

// Pegar o tipo de erro da URL
$tipo = $_GET['tipo'] ?? 'servidor_erro';

// Se o tipo não existe, usar erro padrão
if (!isset($tipos_erro[$tipo])) {
    $tipo = 'servidor_erro';
}

$erro = $tipos_erro[$tipo];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $erro['titulo']; ?> - <?php echo SITE_NAME; ?></title>
    
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

        .erro-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 3rem;
            width: 100%;
            max-width: 600px;
            text-align: center;
        }

        .erro-icon {
            font-size: 5rem;
            margin-bottom: 2rem;
        }

        .erro-titulo {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 1rem;
        }

        .erro-mensagem {
            font-size: 1.1rem;
            color: #6b7280;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .btn-group-custom {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-primary {
            background: linear-gradient(135deg, #2563eb, #1e40af);
            border: none;
            border-radius: 10px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #1e40af, #1e3a8a);
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: #6b7280;
            border: none;
            border-radius: 10px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-secondary:hover {
            background: #4b5563;
            transform: translateY(-1px);
        }

        .btn-success {
            background: linear-gradient(135deg, #10b981, #059669);
            border: none;
            border-radius: 10px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-success:hover {
            background: linear-gradient(135deg, #059669, #047857);
            transform: translateY(-1px);
        }

        .erro-detalhes {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #e5e7eb;
            color: #9ca3af;
            font-size: 0.9rem;
        }

        .animacao-flutuante {
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        @media (max-width: 576px) {
            .erro-container {
                margin: 1rem;
                padding: 2rem;
            }
            
            .erro-icon {
                font-size: 3.5rem;
            }
            
            .erro-titulo {
                font-size: 1.5rem;
            }
            
            .btn-group-custom {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="erro-container">
        <div class="erro-icon text-<?php echo $erro['cor']; ?> animacao-flutuante">
            <i class="<?php echo $erro['icone']; ?>"></i>
        </div>
        
        <h1 class="erro-titulo text-<?php echo $erro['cor']; ?>">
            <?php echo htmlspecialchars($erro['titulo']); ?>
        </h1>
        
        <p class="erro-mensagem">
            <?php echo htmlspecialchars($erro['mensagem']); ?>
        </p>

        <div class="btn-group-custom">
            <?php if ($tipo === '2fa_failed' || $tipo === 'login_failed' || $tipo === 'sessao_expirada'): ?>
                <a href="login.php" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i> Fazer Login
                </a>
            <?php endif; ?>
            
            <?php if ($tipo === 'acesso_negado'): ?>
                <?php if (verificarLogin()): ?>
                    <a href="painel/" class="btn btn-primary">
                        <i class="fas fa-tachometer-alt"></i> Voltar ao Painel
                    </a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt"></i> Fazer Login
                    </a>
                <?php endif; ?>
            <?php endif; ?>
            
            <?php if ($tipo === 'cadastro_erro'): ?>
                <a href="cadastro.php" class="btn btn-success">
                    <i class="fas fa-user-plus"></i> Tentar Cadastro Novamente
                </a>
            <?php endif; ?>
            
            <?php if ($tipo === 'pagina_nao_encontrada'): ?>
                <a href="index.php" class="btn btn-primary">
                    <i class="fas fa-home"></i> Ir para Início
                </a>
            <?php endif; ?>
            
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-home"></i> Página Inicial
            </a>
            
            <button onclick="history.back()" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Voltar
            </button>
        </div>

        <div class="erro-detalhes">
            <p class="mb-1">
                <i class="fas fa-info-circle"></i> 
                Se o problema persistir, entre em contato com o suporte
            </p>
            <p class="mb-0">
                <i class="fas fa-clock"></i> 
                <?php echo date('d/m/Y H:i:s'); ?> | 
                <i class="fas fa-hashtag"></i> 
                Código: <?php echo strtoupper($tipo); ?>
            </p>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Auto-redirect para alguns tipos de erro após 10 segundos
        <?php if (in_array($tipo, ['2fa_failed', 'sessao_expirada'])): ?>
            let contador = 10;
            const timer = setInterval(function() {
                if (contador <= 0) {
                    clearInterval(timer);
                    window.location.href = 'login.php';
                } else {
                    contador--;
                }
            }, 1000);
        <?php endif; ?>

        // Adicionar efeito de hover nos botões
        document.addEventListener('DOMContentLoaded', function() {
            const buttons = document.querySelectorAll('.btn');
            buttons.forEach(button => {
                button.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-2px) scale(1.05)';
                });
                button.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                });
            });
        });
    </script>
</body>
</html>
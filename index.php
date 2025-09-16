<?php
require_once 'includes/config.php';

// Buscar produtos ativos
$db = new Database();
$conn = $db->getConnection();

$sql = "SELECT * FROM produtos WHERE ativo = 1 ORDER BY data_cadastro DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$produtos = $stmt->fetchAll();

$usuario_logado = verificarLogin();
$nome_usuario = $usuario_logado ? $_SESSION['usuario_nome'] : '';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Loja de Tecnologia</title>
    
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
        
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .navbar-brand {
            font-weight: bold;
            color: #2563eb !important;
        }
        
        .hero {
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.9), rgba(30, 64, 175, 0.9));
            color: white;
            padding: 4rem 0;
            margin-bottom: 3rem;
        }
        
        .product-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            border: none;
            height: 100%;
        }
        
        .product-card:hover {
            transform: translateY(-8px);
        }
        
        .product-image {
            height: 200px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: white;
            border-radius: 15px 15px 0 0;
        }
        
        .product-price {
            font-size: 1.25rem;
            font-weight: bold;
            color: #f59e0b;
        }
        
        .btn-primary {
            background: #2563eb;
            border: none;
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
        }
        
        .btn-primary:hover {
            background: #1e40af;
            transform: translateY(-1px);
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-store"></i> <?php echo SITE_NAME; ?>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if ($usuario_logado): ?>
                        <li class="nav-item">
                            <span class="navbar-text">
                                <i class="fas fa-user"></i> Olá, <?php echo $nome_usuario; ?>!
                            </span>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">
                                <i class="fas fa-sign-out-alt"></i> Sair
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">
                                <i class="fas fa-sign-in-alt"></i> Entrar
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="cadastro.php">
                                <i class="fas fa-user-plus"></i> Cadastrar
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="hero">
        <div class="container text-center">
            <h1><i class="fas fa-bolt"></i> Bem-vindo à <?php echo SITE_NAME; ?></h1>
            <p class="lead">Descubra os melhores produtos de tecnologia com qualidade e garantia</p>
            <?php if (!$usuario_logado): ?>
                <a href="cadastro.php" class="btn btn-light btn-lg mt-3">
                    <i class="fas fa-rocket"></i> Comece Agora
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Produtos -->
    <div class="container mb-5">
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="text-center text-white mb-4">
                    <i class="fas fa-star"></i> Nossos Produtos em Destaque
                </h2>
            </div>
        </div>

        <div class="row g-4">
            <?php 
            $icons = ['fas fa-mobile-alt', 'fas fa-laptop', 'fas fa-tablet-alt', 'fas fa-headphones', 'fas fa-tv', 'fas fa-camera'];
            foreach ($produtos as $index => $produto): 
            ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card product-card">
                        <div class="product-image">
                            <i class="<?php echo $icons[$index % count($icons)]; ?>"></i>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($produto['nome']); ?></h5>
                            <p class="card-text">
                                <?php echo htmlspecialchars(substr($produto['descricao'], 0, 100)) . '...'; ?>
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="product-price">
                                    R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?>
                                </span>
                                <?php if ($usuario_logado): ?>
                                    <button class="btn btn-primary btn-sm">
                                        <i class="fas fa-cart-plus"></i> Comprar
                                    </button>
                                <?php else: ?>
                                    <a href="login.php" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-sign-in-alt"></i> Entrar
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (empty($produtos)): ?>
            <div class="row">
                <div class="col-12 text-center">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        Nenhum produto disponível no momento.
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
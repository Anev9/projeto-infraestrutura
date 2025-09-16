<?php
require_once 'includes/config.php';

// Verificar se está logado
if (!verificarLogin()) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modelo do Banco de Dados - <?php echo SITE_NAME; ?></title>
    
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

        .main-content {
            padding: 2rem 0;
        }

        .page-header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            margin-bottom: 2rem;
            text-align: center;
        }

        .diagram-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .table-box {
            background: #f8fafc;
            border: 2px solid #2563eb;
            border-radius: 10px;
            margin: 1rem;
            padding: 1rem;
            display: inline-block;
            min-width: 280px;
            vertical-align: top;
        }

        .table-title {
            background: #2563eb;
            color: white;
            margin: -1rem -1rem 1rem -1rem;
            padding: 0.75rem;
            border-radius: 8px 8px 0 0;
            font-weight: bold;
            text-align: center;
        }

        .field {
            padding: 0.25rem 0;
            border-bottom: 1px solid #e5e7eb;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
        }

        .field:last-child {
            border-bottom: none;
        }

        .primary-key {
            color: #dc2626;
            font-weight: bold;
        }

        .foreign-key {
            color: #059669;
            font-weight: bold;
        }

        .field-type {
            color: #6b7280;
            font-size: 0.8rem;
        }

        .relationship-line {
            stroke: #2563eb;
            stroke-width: 2;
            fill: none;
        }

        .info-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 1.5rem;
            margin-bottom: 1rem;
        }

        .legend {
            display: flex;
            justify-content: center;
            gap: 2rem;
            flex-wrap: wrap;
            margin-top: 2rem;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .legend-color {
            width: 15px;
            height: 15px;
            border-radius: 3px;
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 1rem;
            }
            
            .page-header {
                padding: 1.5rem;
            }
            
            .table-box {
                min-width: 250px;
                margin: 0.5rem;
            }
            
            .diagram-container {
                padding: 1rem;
            }
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
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="fas fa-home"></i> Início
                        </a>
                    </li>
                    <?php if (verificarLogin()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="painel/">
                                <i class="fas fa-tachometer-alt"></i> Painel
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
                
                <ul class="navbar-nav">
                    <?php if (verificarLogin()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user"></i> <?php echo $_SESSION['usuario_nome']; ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="painel/"><i class="fas fa-tachometer-alt"></i> Painel</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">
                                <i class="fas fa-sign-in-alt"></i> Entrar
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="main-content">
        <div class="container">
            <!-- Header -->
            <div class="page-header">
                <h1><i class="fas fa-database text-primary"></i> Modelo do Banco de Dados</h1>
                <p class="text-muted mb-0">Diagrama Entidade-Relacionamento do Sistema</p>
            </div>

            <!-- Diagrama ER -->
            <div class="diagram-container">
                <h3 class="text-center mb-4">
                    <i class="fas fa-project-diagram text-primary"></i> Diagrama ER - projeto_infraestrutura
                </h3>
                
                <div class="text-center">
                    <!-- Tabela Usuários -->
                    <div class="table-box">
                        <div class="table-title">
                            <i class="fas fa-users"></i> usuarios
                        </div>
                        <div class="field primary-key">
                            <i class="fas fa-key"></i> id <span class="field-type">INT AI PK</span>
                        </div>
                        <div class="field">nome_completo <span class="field-type">VARCHAR(80)</span></div>
                        <div class="field">data_nascimento <span class="field-type">DATE</span></div>
                        <div class="field">sexo <span class="field-type">ENUM</span></div>
                        <div class="field">nome_materno <span class="field-type">VARCHAR(80)</span></div>
                        <div class="field">cpf <span class="field-type">VARCHAR(14) UNIQUE</span></div>
                        <div class="field">email <span class="field-type">VARCHAR(100) UNIQUE</span></div>
                        <div class="field">telefone_celular <span class="field-type">VARCHAR(20)</span></div>
                        <div class="field">telefone_fixo <span class="field-type">VARCHAR(20)</span></div>
                        <div class="field">cep <span class="field-type">VARCHAR(10)</span></div>
                        <div class="field">endereco_completo <span class="field-type">TEXT</span></div>
                        <div class="field">login <span class="field-type">VARCHAR(6) UNIQUE</span></div>
                        <div class="field">senha <span class="field-type">VARCHAR(255)</span></div>
                        <div class="field">perfil <span class="field-type">ENUM('comum','master')</span></div>
                        <div class="field">data_cadastro <span class="field-type">TIMESTAMP</span></div>
                        <div class="field">ativo <span class="field-type">BOOLEAN</span></div>
                    </div>

                    <!-- Tabela Produtos -->
                    <div class="table-box">
                        <div class="table-title">
                            <i class="fas fa-box"></i> produtos
                        </div>
                        <div class="field primary-key">
                            <i class="fas fa-key"></i> id <span class="field-type">INT AI PK</span>
                        </div>
                        <div class="field">nome <span class="field-type">VARCHAR(100)</span></div>
                        <div class="field">descricao <span class="field-type">TEXT</span></div>
                        <div class="field">preco <span class="field-type">DECIMAL(10,2)</span></div>
                        <div class="field">imagem <span class="field-type">VARCHAR(255)</span></div>
                        <div class="field">ativo <span class="field-type">BOOLEAN</span></div>
                        <div class="field">data_cadastro <span class="field-type">TIMESTAMP</span></div>
                    </div>

                    <!-- Tabela Logs -->
                    <div class="table-box">
                        <div class="table-title">
                            <i class="fas fa-history"></i> logs_autenticacao
                        </div>
                        <div class="field primary-key">
                            <i class="fas fa-key"></i> id <span class="field-type">INT AI PK</span>
                        </div>
                        <div class="field foreign-key">
                            <i class="fas fa-link"></i> usuario_id <span class="field-type">INT FK</span>
                        </div>
                        <div class="field">data_hora <span class="field-type">TIMESTAMP</span></div>
                        <div class="field">tipo_2fa <span class="field-type">VARCHAR(50)</span></div>
                        <div class="field">ip_acesso <span class="field-type">VARCHAR(45)</span></div>
                        <div class="field">sucesso <span class="field-type">BOOLEAN</span></div>
                    </div>
                </div>

                <!-- Legenda -->
                <div class="legend">
                    <div class="legend-item">
                        <div class="legend-color primary-key"></div>
                        <span><i class="fas fa-key"></i> Chave Primária</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color foreign-key"></div>
                        <span><i class="fas fa-link"></i> Chave Estrangeira</span>
                    </div>
                    <div class="legend-item">
                        <span><i class="fas fa-arrow-right text-primary"></i> Relacionamento</span>
                    </div>
                </div>
            </div>

            <!-- Informações Técnicas -->
            <div class="row">
                <div class="col-md-6">
                    <div class="info-card">
                        <h5><i class="fas fa-info-circle text-primary"></i> Informações do Banco</h5>
                        <ul class="list-unstyled mb-0">
                            <li><strong>Nome:</strong> projeto_infraestrutura</li>
                            <li><strong>SGBD:</strong> MySQL 5.7+</li>
                            <li><strong>Charset:</strong> UTF-8</li>
                            <li><strong>Engine:</strong> InnoDB</li>
                            <li><strong>Tabelas:</strong> 3</li>
                        </ul>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="info-card">
                        <h5><i class="fas fa-sitemap text-success"></i> Relacionamentos</h5>
                        <ul class="list-unstyled mb-0">
                            <li><strong>usuarios → logs_autenticacao</strong></li>
                            <li>Tipo: One-to-Many (1:N)</li>
                            <li>Chave: usuario_id</li>
                            <li>Integridade: ON DELETE CASCADE</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Descrição das Tabelas -->
            <div class="info-card">
                <h5><i class="fas fa-table text-info"></i> Descrição das Tabelas</h5>
                
                <div class="row">
                    <div class="col-md-4">
                        <h6><i class="fas fa-users text-primary"></i> usuarios</h6>
                        <p class="small text-muted">
                            Armazena informações completas dos usuários do sistema, incluindo dados pessoais, 
                            contato, endereço e credenciais de acesso. Suporta dois perfis: 'master' e 'comum'.
                        </p>
                    </div>
                    
                    <div class="col-md-4">
                        <h6><i class="fas fa-box text-success"></i> produtos</h6>
                        <p class="small text-muted">
                            Catálogo de produtos da loja virtual com informações básicas como nome, descrição, 
                            preço e status ativo/inativo.
                        </p>
                    </div>
                    
                    <div class="col-md-4">
                        <h6><i class="fas fa-history text-warning"></i> logs_autenticacao</h6>
                        <p class="small text-muted">
                            Registro de tentativas de autenticação 2FA, incluindo data/hora, tipo de verificação 
                            utilizada, IP de origem e status de sucesso/falha.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Índices e Performance -->
            <div class="info-card">
                <h5><i class="fas fa-tachometer-alt text-warning"></i> Índices para Performance</h5>
                <div class="row">
                    <div class="col-md-6">
                        <h6>Tabela usuarios:</h6>
                        <ul class="small">
                            <li>PRIMARY KEY (id)</li>
                            <li>UNIQUE KEY (login)</li>
                            <li>UNIQUE KEY (email)</li>
                            <li>UNIQUE KEY (cpf)</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6>Tabela logs_autenticacao:</h6>
                        <ul class="small">
                            <li>PRIMARY KEY (id)</li>
                            <li>INDEX (usuario_id)</li>
                            <li>INDEX (data_hora)</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
require_once '../includes/config.php';

// Verificar se está logado e é master
if (!verificarMaster()) {
    header('Location: ../erro.php?tipo=acesso_negado');
    exit();
}

$db = new Database();
$conn = $db->getConnection();

$erro = '';
$sucesso = '';

// Processar exclusão de usuário
if (isset($_POST['excluir_usuario'])) {
    $usuario_id = (int)$_POST['usuario_id'];
    
    try {
        // Não permitir excluir a si mesmo
        if ($usuario_id === $_SESSION['usuario_id']) {
            $erro = 'Você não pode excluir sua própria conta.';
        } else {
            $sql_delete = "UPDATE usuarios SET ativo = 0 WHERE id = ? AND perfil = 'comum'";
            $stmt_delete = $conn->prepare($sql_delete);
            
            if ($stmt_delete->execute([$usuario_id])) {
                $sucesso = 'Usuário excluído com sucesso.';
            } else {
                $erro = 'Erro ao excluir usuário.';
            }
        }
    } catch (Exception $e) {
        $erro = 'Erro interno do servidor.';
    }
}

// Busca de usuários
$busca = trim($_GET['busca'] ?? '');
$sql_base = "SELECT * FROM usuarios WHERE ativo = 1";
$params = [];

if (!empty($busca)) {
    $sql_base .= " AND (nome_completo LIKE ? OR login LIKE ? OR email LIKE ?)";
    $busca_param = "%{$busca}%";
    $params = [$busca_param, $busca_param, $busca_param];
}

$sql_base .= " ORDER BY perfil DESC, data_cadastro DESC";

try {
    $stmt = $conn->prepare($sql_base);
    $stmt->execute($params);
    $usuarios = $stmt->fetchAll();
} catch (Exception $e) {
    $usuarios = [];
    $erro = 'Erro ao buscar usuários.';
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Usuários - <?php echo SITE_NAME; ?></title>
    
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
        }

        .search-card {
            background: white;
            border-radius: 15px;
            border: none;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .users-card {
            background: white;
            border-radius: 15px;
            border: none;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            background: linear-gradient(135deg, #2563eb, #1e40af);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            border: none;
        }

        .user-avatar {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }

        .perfil-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }

        .badge-master {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
        }

        .badge-comum {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }

        .btn-danger {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            border: none;
        }

        .btn-danger:hover {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
        }

        .table {
            margin: 0;
        }

        .table th {
            border-top: none;
            font-weight: 600;
            color: #374151;
        }

        .form-control {
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .btn-primary {
            background: linear-gradient(135deg, #2563eb, #1e40af);
            border: none;
            border-radius: 10px;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #1e40af, #1e3a8a);
        }

        .stats-row {
            background: #f8fafc;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #6b7280;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 1rem;
            }
            
            .page-header {
                padding: 1.5rem;
            }
            
            .table-responsive {
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand" href="../index.php">
                <i class="fas fa-store"></i> <?php echo SITE_NAME; ?>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="fas fa-tachometer-alt"></i> Painel
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="usuarios.php">
                            <i class="fas fa-users"></i> Usuários
                        </a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> <?php echo $_SESSION['usuario_nome']; ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="../modelo_bd.php"><i class="fas fa-database"></i> Modelo do BD</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="main-content">
        <div class="container">
            <!-- Header -->
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h2 class="mb-1">
                            <i class="fas fa-users text-primary"></i> Gerenciamento de Usuários
                        </h2>
                        <p class="text-muted mb-0">
                            Consulte e gerencie os usuários do sistema
                        </p>
                    </div>
                    <div class="col-auto">
                        <a href="index.php" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left"></i> Voltar ao Painel
                        </a>
                    </div>
                </div>
            </div>

            <!-- Mensagens -->
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

            <!-- Busca -->
            <div class="card search-card">
                <div class="card-body">
                    <form method="GET" action="usuarios.php" class="row g-3">
                        <div class="col-md-8">
                            <label for="busca" class="form-label">
                                <i class="fas fa-search"></i> Buscar Usuário
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="busca" 
                                   name="busca"
                                   placeholder="Digite parte do nome, login ou e-mail..."
                                   value="<?php echo htmlspecialchars($busca); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Buscar
                                </button>
                            </div>
                        </div>
                    </form>
                    
                    <?php if (!empty($busca)): ?>
                        <div class="mt-2">
                            <a href="usuarios.php" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-times"></i> Limpar Busca
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Estatísticas -->
            <div class="stats-row">
                <div class="row text-center">
                    <div class="col-md-4">
                        <div class="d-flex align-items-center justify-content-center">
                            <i class="fas fa-users text-primary me-2"></i>
                            <span><strong><?php echo count($usuarios); ?></strong> usuários encontrados</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center justify-content-center">
                            <i class="fas fa-crown text-warning me-2"></i>
                            <span><strong><?php echo count(array_filter($usuarios, fn($u) => $u['perfil'] === 'master')); ?></strong> masters</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center justify-content-center">
                            <i class="fas fa-user text-success me-2"></i>
                            <span><strong><?php echo count(array_filter($usuarios, fn($u) => $u['perfil'] === 'comum')); ?></strong> usuários comuns</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lista de Usuários -->
            <div class="card users-card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-list"></i> Lista de Usuários
                    </h5>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($usuarios)): ?>
                        <div class="empty-state">
                            <i class="fas fa-users-slash"></i>
                            <h5>Nenhum usuário encontrado</h5>
                            <p class="mb-0">
                                <?php if (!empty($busca)): ?>
                                    Tente uma busca diferente ou 
                                    <a href="usuarios.php">veja todos os usuários</a>
                                <?php else: ?>
                                    Ainda não há usuários cadastrados no sistema.
                                <?php endif; ?>
                            </p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Usuário</th>
                                        <th>Contato</th>
                                        <th>Perfil</th>
                                        <th>Cadastro</th>
                                        <th width="120">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($usuarios as $user): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="user-avatar me-3">
                                                        <?php echo strtoupper(substr($user['nome_completo'], 0, 2)); ?>
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold"><?php echo htmlspecialchars($user['nome_completo']); ?></div>
                                                        <small class="text-muted">
                                                            <i class="fas fa-at"></i> <?php echo htmlspecialchars($user['login']); ?>
                                                        </small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div><?php echo htmlspecialchars($user['email']); ?></div>
                                                <small class="text-muted"><?php echo htmlspecialchars($user['telefone_celular']); ?></small>
                                            </td>
                                            <td>
                                                <span class="badge perfil-badge <?php echo $user['perfil'] === 'master' ? 'badge-master' : 'badge-comum'; ?>">
                                                    <?php if ($user['perfil'] === 'master'): ?>
                                                        <i class="fas fa-crown"></i> Master
                                                    <?php else: ?>
                                                        <i class="fas fa-user"></i> Comum
                                                    <?php endif; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div><?php echo date('d/m/Y', strtotime($user['data_cadastro'])); ?></div>
                                                <small class="text-muted"><?php echo date('H:i', strtotime($user['data_cadastro'])); ?></small>
                                            </td>
                                            <td>
                                                <?php if ($user['perfil'] === 'comum' && $user['id'] !== $_SESSION['usuario_id']): ?>
                                                    <button type="button" 
                                                            class="btn btn-danger btn-sm" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#modalExcluir"
                                                            data-usuario-id="<?php echo $user['id']; ?>"
                                                            data-usuario-nome="<?php echo htmlspecialchars($user['nome_completo']); ?>">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <span class="text-muted">
                                                        <i class="fas fa-shield-alt" title="Protegido"></i>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmação de Exclusão -->
    <div class="modal fade" id="modalExcluir" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle"></i> Confirmar Exclusão
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Tem certeza que deseja excluir o usuário:</p>
                    <p class="fw-bold" id="nomeUsuarioExcluir"></p>
                    <p class="text-muted">Esta ação não pode ser desfeita.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="usuario_id" id="usuarioIdExcluir">
                        <button type="submit" name="excluir_usuario" class="btn btn-danger">
                            <i class="fas fa-trash"></i> Excluir
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Configurar modal de exclusão
        const modalExcluir = document.getElementById('modalExcluir');
        modalExcluir.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const usuarioId = button.getAttribute('data-usuario-id');
            const usuarioNome = button.getAttribute('data-usuario-nome');
            
            document.getElementById('usuarioIdExcluir').value = usuarioId;
            document.getElementById('nomeUsuarioExcluir').textContent = usuarioNome;
        });

        // Foco automático no campo de busca
        document.addEventListener('DOMContentLoaded', function() {
            const campoBusca = document.getElementById('busca');
            if (campoBusca && !campoBusca.value) {
                campoBusca.focus();
            }
        });
    </script>
</body>
</html>
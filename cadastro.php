<?php
require_once 'includes/config.php';

// Se já estiver logado, redireciona
if (verificarLogin()) {
    header('Location: index.php');
    exit();
}

$erro = '';
$sucesso = '';

// Função para validar CPF
function validarCPF($cpf) {
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    
    if (strlen($cpf) != 11) return false;
    if (preg_match('/(\d)\1{10}/', $cpf)) return false;
    
    for ($t = 9; $t < 11; $t++) {
        for ($d = 0, $c = 0; $c < $t; $c++) {
            $d += $cpf[$c] * (($t + 1) - $c);
        }
        $d = ((10 * $d) % 11) % 10;
        if ($cpf[$c] != $d) return false;
    }
    return true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitizar dados
    $nome = trim($_POST['nome'] ?? '');
    $data_nasc = trim($_POST['data_nascimento'] ?? '');
    $sexo = trim($_POST['sexo'] ?? '');
    $nome_materno = trim($_POST['nome_materno'] ?? '');
    $cpf = trim($_POST['cpf'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefone_celular = trim($_POST['telefone_celular'] ?? '');
    $telefone_fixo = trim($_POST['telefone_fixo'] ?? '');
    $cep = trim($_POST['cep'] ?? '');
    $endereco = trim($_POST['endereco'] ?? '');
    $login = trim($_POST['login'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $confirma_senha = $_POST['confirma_senha'] ?? '';

    // Validações
    if (empty($nome) || empty($data_nasc) || empty($sexo) || empty($nome_materno) || 
        empty($cpf) || empty($email) || empty($telefone_celular) || empty($telefone_fixo) || 
        empty($cep) || empty($endereco) || empty($login) || empty($senha)) {
        $erro = 'Todos os campos são obrigatórios.';
    }
    elseif (strlen($nome) < 15 || strlen($nome) > 80) {
        $erro = 'O nome deve ter entre 15 e 80 caracteres.';
    }
    elseif (!preg_match('/^[a-zA-ZÀ-ÿ\s]+$/', $nome)) {
        $erro = 'O nome deve conter apenas caracteres alfabéticos.';
    }
    elseif (!validarCPF($cpf)) {
        $erro = 'CPF inválido.';
    }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'E-mail inválido.';
    }
    elseif (strlen($login) !== 6) {
        $erro = 'O login deve ter exatamente 6 caracteres alfabéticos.';
    }
    elseif (!preg_match('/^[a-zA-Z]{6}$/', $login)) {
        $erro = 'O login deve conter apenas caracteres alfabéticos.';
    }
    elseif (strlen($senha) !== 8) {
        $erro = 'A senha deve ter exatamente 8 caracteres alfabéticos.';
    }
    elseif (!preg_match('/^[a-zA-Z]{8}$/', $senha)) {
        $erro = 'A senha deve conter apenas caracteres alfabéticos.';
    }
    elseif ($senha !== $confirma_senha) {
        $erro = 'As senhas não coincidem.';
    }
    else {
        try {
            $db = new Database();
            $conn = $db->getConnection();

            // Verificar se login já existe
            $sql_check = "SELECT id FROM usuarios WHERE login = ? OR email = ? OR cpf = ?";
            $stmt_check = $conn->prepare($sql_check);
            $stmt_check->execute([$login, $email, $cpf]);
            
            if ($stmt_check->fetch()) {
                $erro = 'Login, e-mail ou CPF já cadastrados.';
            } else {
                // Inserir usuário
                $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
                $cpf_formatado = preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', preg_replace('/[^0-9]/', '', $cpf));
                
                $sql_insert = "INSERT INTO usuarios (
                    nome_completo, data_nascimento, sexo, nome_materno, cpf, email,
                    telefone_celular, telefone_fixo, cep, endereco_completo, login, senha
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
                $stmt_insert = $conn->prepare($sql_insert);
                $sucesso_insert = $stmt_insert->execute([
                    $nome, $data_nasc, $sexo, $nome_materno, $cpf_formatado, $email,
                    $telefone_celular, $telefone_fixo, $cep, $endereco, $login, $senha_hash
                ]);

                if ($sucesso_insert) {
                    header('Location: login.php?msg=cadastro_sucesso');
                    exit();
                } else {
                    $erro = 'Erro ao cadastrar usuário. Tente novamente.';
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
    <title>Cadastro - <?php echo SITE_NAME; ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Arial', sans-serif;
            padding: 2rem 0;
        }

        .cadastro-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 3rem;
            margin: 2rem auto;
            max-width: 800px;
        }

        .cadastro-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .cadastro-header h2 {
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

        .btn-cadastrar {
            background: linear-gradient(135deg, #10b981, #059669);
            border: none;
            border-radius: 10px;
            padding: 0.75rem;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
        }

        .btn-cadastrar:hover {
            background: linear-gradient(135deg, #059669, #047857);
            transform: translateY(-1px);
            color: white;
        }

        .btn-limpar {
            background: #6b7280;
            border: none;
            border-radius: 10px;
            padding: 0.75rem;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
        }

        .btn-limpar:hover {
            background: #4b5563;
            color: white;
        }

        .section-title {
            color: #374151;
            font-weight: 600;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e5e7eb;
        }

        .required {
            color: #ef4444;
        }

        .alert {
            border-radius: 10px;
            border: none;
        }

        .back-link {
            color: #2563eb;
            text-decoration: none;
            font-weight: 500;
            margin-bottom: 2rem;
            display: inline-block;
        }

        .back-link:hover {
            color: #1e40af;
        }

        @media (max-width: 768px) {
            .cadastro-container {
                margin: 1rem;
                padding: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="cadastro-container">
            <a href="index.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Voltar ao Início
            </a>

            <div class="cadastro-header">
                <h2><i class="fas fa-user-plus"></i> Criar Nova Conta</h2>
                <p class="text-muted">Preencha todos os campos para se cadastrar</p>
            </div>

            <?php if ($erro): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($erro); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="cadastro.php" id="formCadastro">
                <!-- Dados Pessoais -->
                <h4 class="section-title">
                    <i class="fas fa-user"></i> Dados Pessoais
                </h4>
                
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="nome" class="form-label">Nome Completo <span class="required">*</span></label>
                        <input type="text" class="form-control" id="nome" name="nome" 
                               placeholder="Mínimo 15 caracteres" maxlength="80"
                               value="<?php echo htmlspecialchars($_POST['nome'] ?? ''); ?>" required>
                        <small class="text-muted">15 a 80 caracteres alfabéticos</small>
                    </div>
                    <div class="col-md-6">
                        <label for="data_nascimento" class="form-label">Data de Nascimento <span class="required">*</span></label>
                        <input type="date" class="form-control" id="data_nascimento" name="data_nascimento"
                               value="<?php echo htmlspecialchars($_POST['data_nascimento'] ?? ''); ?>" required>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label for="sexo" class="form-label">Sexo <span class="required">*</span></label>
                        <select class="form-control" id="sexo" name="sexo" required>
                            <option value="">Selecione</option>
                            <option value="M" <?php echo ($_POST['sexo'] ?? '') === 'M' ? 'selected' : ''; ?>>Masculino</option>
                            <option value="F" <?php echo ($_POST['sexo'] ?? '') === 'F' ? 'selected' : ''; ?>>Feminino</option>
                            <option value="Outro" <?php echo ($_POST['sexo'] ?? '') === 'Outro' ? 'selected' : ''; ?>>Outro</option>
                        </select>
                    </div>
                    <div class="col-md-8">
                        <label for="nome_materno" class="form-label">Nome da Mãe <span class="required">*</span></label>
                        <input type="text" class="form-control" id="nome_materno" name="nome_materno"
                               placeholder="Nome completo da mãe" maxlength="80"
                               value="<?php echo htmlspecialchars($_POST['nome_materno'] ?? ''); ?>" required>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="cpf" class="form-label">CPF <span class="required">*</span></label>
                        <input type="text" class="form-control" id="cpf" name="cpf"
                               placeholder="000.000.000-00" maxlength="14"
                               value="<?php echo htmlspecialchars($_POST['cpf'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label">E-mail <span class="required">*</span></label>
                        <input type="email" class="form-control" id="email" name="email"
                               placeholder="seu@email.com"
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                    </div>
                </div>

                <!-- Contato -->
                <h4 class="section-title">
                    <i class="fas fa-phone"></i> Contato
                </h4>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="telefone_celular" class="form-label">Telefone Celular <span class="required">*</span></label>
                        <input type="text" class="form-control" id="telefone_celular" name="telefone_celular"
                               placeholder="(+55)XX-XXXXXXXX"
                               value="<?php echo htmlspecialchars($_POST['telefone_celular'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="telefone_fixo" class="form-label">Telefone Fixo <span class="required">*</span></label>
                        <input type="text" class="form-control" id="telefone_fixo" name="telefone_fixo"
                               placeholder="(+55)XX-XXXXXXXX"
                               value="<?php echo htmlspecialchars($_POST['telefone_fixo'] ?? ''); ?>" required>
                    </div>
                </div>

                <!-- Endereço -->
                <h4 class="section-title">
                    <i class="fas fa-map-marker-alt"></i> Endereço
                </h4>

                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label for="cep" class="form-label">CEP <span class="required">*</span></label>
                        <input type="text" class="form-control" id="cep" name="cep"
                               placeholder="00000-000" maxlength="10"
                               value="<?php echo htmlspecialchars($_POST['cep'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-9">
                        <label for="endereco" class="form-label">Endereço Completo <span class="required">*</span></label>
                        <input type="text" class="form-control" id="endereco" name="endereco"
                               placeholder="Rua, número, bairro, cidade, estado"
                               value="<?php echo htmlspecialchars($_POST['endereco'] ?? ''); ?>" required>
                    </div>
                </div>

                <!-- Dados de Acesso -->
                <h4 class="section-title">
                    <i class="fas fa-key"></i> Dados de Acesso
                </h4>

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label for="login" class="form-label">Login <span class="required">*</span></label>
                        <input type="text" class="form-control" id="login" name="login"
                               placeholder="6 caracteres" maxlength="6"
                               value="<?php echo htmlspecialchars($_POST['login'] ?? ''); ?>" required>
                        <small class="text-muted">Exatamente 6 caracteres alfabéticos</small>
                    </div>
                    <div class="col-md-4">
                        <label for="senha" class="form-label">Senha <span class="required">*</span></label>
                        <input type="password" class="form-control" id="senha" name="senha"
                               placeholder="8 caracteres" maxlength="8" required>
                        <small class="text-muted">Exatamente 8 caracteres alfabéticos</small>
                    </div>
                    <div class="col-md-4">
                        <label for="confirma_senha" class="form-label">Confirmar Senha <span class="required">*</span></label>
                        <input type="password" class="form-control" id="confirma_senha" name="confirma_senha"
                               placeholder="Repita a senha" maxlength="8" required>
                    </div>
                </div>

                <!-- Botões -->
                <div class="row g-2">
                    <div class="col">
                        <button type="submit" class="btn btn-cadastrar w-100">
                            <i class="fas fa-user-plus"></i> Cadastrar
                        </button>
                    </div>
                    <div class="col">
                        <button type="button" class="btn btn-limpar w-100" onclick="limparFormulario()">
                            <i class="fas fa-eraser"></i> Limpar
                        </button>
                    </div>
                </div>
            </form>

            <div class="text-center mt-4">
                <p class="mb-2">Já tem uma conta?</p>
                <a href="login.php" class="btn btn-outline-primary">
                    <i class="fas fa-sign-in-alt"></i> Fazer Login
                </a>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function limparFormulario() {
            document.getElementById('formCadastro').reset();
            document.getElementById('nome').focus();
        }

        // Máscaras para inputs
        document.getElementById('cpf').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d{1,2})/, '$1-$2');
            e.target.value = value;
        });

        document.getElementById('cep').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            value = value.replace(/^(\d{5})(\d)/, '$1-$2');
            e.target.value = value;
        });

        // Foco no primeiro campo
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('nome').focus();
        });

        // Validação de senhas iguais
        document.getElementById('confirma_senha').addEventListener('blur', function() {
            const senha = document.getElementById('senha').value;
            const confirmaSenha = this.value;
            
            if (senha !== confirmaSenha) {
                this.setCustomValidity('As senhas não coincidem');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html>
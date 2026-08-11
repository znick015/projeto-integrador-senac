<?php
session_start();
require_once 'config/conexao.php';

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome     = trim($_POST['nome']);
    $email    = trim($_POST['email']);
    $telefone = trim($_POST['telefone']);
    $cidade   = trim($_POST['cidade']);
    $estado   = strtoupper(trim($_POST['estado']));
    $senha    = $_POST['senha'];

    if (!empty($nome) && !empty($email) && !empty($senha) && !empty($cidade) && !empty($estado)) {
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

        try {
            $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, telefone, cidade, estado, senha_hash) VALUES (:nome, :email, :telefone, :cidade, :estado, :senha)");
            $stmt->execute([
                ':nome'     => $nome,
                ':email'    => $email,
                ':telefone' => $telefone,
                ':cidade'   => $cidade,
                ':estado'   => $estado,
                ':senha'    => $senha_hash
            ]);

            header("Location: login.php?cadastrado=1");
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $erro = "Este e-mail já está cadastrado no sistema!";
            } else {
                $erro = "Erro ao cadastrar usuário: " . $e->getMessage();
            }
        }
    } else {
        $erro = "Preencha todos os campos obrigatórios!";
    }
}

include 'includes/header.php';
?>

<main class="container">
    <div class="form-card">
        <h2>Criar uma Conta</h2>
        <p style="margin-bottom: 20px; color: #64748b;">Cadastre-se para anunciar ou contratar serviços.</p>

        <?php if ($erro): ?>
            <div class="alert-danger"><?= $erro ?></div>
        <?php endif; ?>

        <form action="cadastro.php" method="POST">
            <div class="form-group">
                <label for="nome">Nome Completo *</label>
                <input type="text" id="nome" name="nome" required placeholder="Seu nome">
            </div>

            <div class="form-group">
                <label for="email">E-mail *</label>
                <input type="email" id="email" name="email" required placeholder="seuemail@exemplo.com">
            </div>

            <div class="form-group">
                <label for="telefone">WhatsApp / Telefone *</label>
                <input type="text" id="telefone" name="telefone" required placeholder="(31) 99999-9999">
            </div>

            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 10px;">
                <div class="form-group">
                    <label for="cidade">Cidade *</label>
                    <input type="text" id="cidade" name="cidade" required placeholder="Ex: Contagem">
                </div>
                <div class="form-group">
                    <label for="estado">UF *</label>
                    <input type="text" id="estado" name="estado" maxlength="2" required placeholder="MG">
                </div>
            </div>

            <div class="form-group">
                <label for="senha">Senha *</label>
                <input type="password" id="senha" name="senha" required placeholder="Sua senha secreta">
            </div>

            <button type="submit" class="btn-submit">Finalizar Cadastro</button>
        </form>
        
        <p style="margin-top: 15px; text-align: center; font-size: 0.9rem;">
            Já possui uma conta? <a href="login.php" style="color: var(--accent-color);">Fazer Login</a>
        </p>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
<?php
session_start();
require_once 'config/conexao.php';

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];

    if (!empty($email) && !empty($senha)) {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $usuario = $stmt->fetch();

        // Validação da Senha Criptografada
        if ($usuario && password_verify($senha, $usuario['senha_hash'])) {
            // Cria a Sessão do Usuário
            $_SESSION['usuario_id']   = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            
            header("Location: index.php");
            exit;
        } else {
            $erro = "E-mail ou senha incorretos!";
        }
    } else {
        $erro = "Preencha todos os campos!";
    }
}

include 'includes/header.php';
?>

<main class="container">
    <div class="form-card">
        <h2>Entrar na Plataforma</h2>
        <p style="margin-bottom: 20px; color: #64748b;">Acesse sua conta para continuar.</p>

        <?php if (isset($_GET['cadastrado'])): ?>
            <div style="background-color: #ecfdf5; color: #065f46; padding: 10px; border-radius: 6px; margin-bottom: 20px; font-size: 0.9rem;">
                Cadastro realizado com sucesso! Faça login abaixo.
            </div>
        <?php endif; ?>

        <?php if ($erro): ?>
            <div class="alert-danger"><?= $erro ?></div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" required placeholder="seuemail@exemplo.com">
            </div>

            <div class="form-group">
                <label for="senha">Senha</label>
                <input type="password" id="senha" name="senha" required placeholder="Sua senha">
            </div>

            <button type="submit" class="btn-submit">Entrar</button>
        </form>

        <p style="margin-top: 15px; text-align: center; font-size: 0.9rem;">
            Ainda não tem conta? <a href="cadastro.php" style="color: var(--accent-color);">Cadastre-se aqui</a>
        </p>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
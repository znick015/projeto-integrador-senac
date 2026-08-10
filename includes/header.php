<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plataforma de Autônomos</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header class="navbar">
        <div class="container nav-container">
            <a href="index.php" class="logo"><i class="fas fa-tools"></i> TrampoCerto</a>
            
            <nav class="nav-links">
    <a href="index.php">Início</a>
    <?php if (isset($_SESSION['usuario_id'])): ?>
        <a href="anunciar.php" class="btn-anunciar">Anunciar Serviço</a>
        <span style="font-weight: 600;">Olá, <?= htmlspecialchars($_SESSION['usuario_nome']) ?></span>
        <a href="logout.php" style="color: #ef4444; font-size: 0.9rem;"><i class="fas fa-sign-out-alt"></i> Sair</a>
    <?php else: ?>
        <a href="cadastro.php" class="btn-anunciar">Anunciar Serviço</a>
        <a href="login.php" class="btn-login"><i class="fas fa-user"></i> Entrar</a>
    <?php endif; ?>
</nav>
        </div>
    </header>
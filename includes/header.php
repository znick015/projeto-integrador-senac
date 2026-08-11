<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TrampoCerto - Plataforma de Autônomos</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Script que impede o pisca branco ao carregar no modo noturno -->
    <script>
        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.classList.add('dark-mode');
        }
    </script>
    <script src="assets/js/main.js" defer></script>
</head>
<body>
    <header class="navbar">
        <div class="container nav-container">
            <a href="index.php" class="logo"><i class="fas fa-tools"></i> TrampoCerto</a>
            
            <nav class="nav-links">
                <a href="index.php">Início</a>
                <?php if (isset($_SESSION['usuario_id'])): ?>
                    <a href="anunciar.php" class="btn-anunciar">Anunciar Serviço</a>
                    
                    <a href="perfil.php" style="font-weight: 600; text-decoration: none; color: var(--primary-color); display: flex; align-items: center; gap: 6px;">
                        <i class="fas fa-user-circle" style="font-size: 1.2rem; color: var(--accent-color);"></i>
                        <?= htmlspecialchars($_SESSION['usuario_nome']) ?>
                    </a>
                    
                    <a href="logout.php" style="color: #ef4444; font-size: 0.9rem; text-decoration: none;"><i class="fas fa-sign-out-alt"></i> Sair</a>
                <?php else: ?>
                    <a href="cadastro.php" class="btn-anunciar">Anunciar Serviço</a>
                    <a href="login.php" class="btn-login"><i class="fas fa-user"></i> Entrar</a>
                <?php endif; ?>

                <!-- BOTÃO DE ALTERNAR MODO NOTURNO -->
                <button id="theme-toggle" class="btn-theme-toggle" title="Alternar Modo Noturno">
                    <i class="fas fa-moon"></i>
                </button>
            </nav>
        </div>
    </header>
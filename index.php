<?php
require_once 'config/conexao.php';

try {
    $stmt = $pdo->query("SELECT * FROM categorias ORDER BY nome ASC");
    $categorias = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Erro ao carregar categorias.");
}

include 'includes/header.php';
?>

<main>
    <section class="hero-section">
        <div class="container hero-content">
            <h1>Diversos tipos de serviços em um só lugar.</h1>
            <p>Encontre profissionais e contrate serviços para tudo o que precisar</p>
            
            <form action="busca.php" method="GET" class="search-box">
                <i class="fas fa-search search-icon"></i>
                <input type="text" name="q" placeholder="O que você precisa? (ex: Eletricista, Desenvolvedor)" required>
                <button type="submit">Procurar</button>
            </form>
        </div>
    </section>

    <section class="categories-section container">
        <div class="grid-categories">
            <?php foreach ($categorias as $cat): ?>
                <a href="categoria.php?id=<?= $cat['id'] ?>" class="card-category">
                    <div class="icon-circle">
                        <i class="fas <?= htmlspecialchars($cat['icone_url']) ?>"></i>
                    </div>
                    <span><?= htmlspecialchars($cat['nome']) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
</main>

<?php 
include 'includes/footer.php'; 
?>
<?php
session_start();
require_once 'config/conexao.php';

// Busca Categorias Principais
$categorias = $pdo->query("SELECT * FROM categorias ORDER BY id ASC")->fetchAll();

// Busca os 6 Anúncios Mais Recentes com Avaliação e Localização
$stmt_recentes = $pdo->prepare("
    SELECT a.*, u.nome AS prestador, u.id AS prestador_id, s.nome AS subcategoria,
           COALESCE(AVG(av.nota), 0) AS media_notas
    FROM anuncios a
    JOIN usuarios u ON a.usuario_id = u.id
    JOIN subcategorias s ON a.subcategoria_id = s.id
    LEFT JOIN avaliacoes av ON a.id = av.anuncio_id
    GROUP BY a.id
    ORDER BY a.data_criacao DESC
    LIMIT 6
");
$stmt_recentes->execute();
$anuncios_recentes = $stmt_recentes->fetchAll();

include 'includes/header.php';
?>

<!-- Banner Hero de Busca -->
<section class="hero-section">
    <div class="container hero-content">
        <h1>Diversos tipos de serviços em um só lugar.</h1>
        <p>Encontre profissionais e contrate serviços para tudo o que precisar</p>
        
        <form action="busca.php" method="GET" class="search-box">
            <i class="fas fa-search search-icon"></i>
            <input type="text" name="q" placeholder="O que você precisa? (ex: Eletricista, Desenvolvedor)" required autocomplete="off">
            <button type="submit">Procurar</button>
        </form>
    </div>
</section>

<!-- Matriz de Categorias Principais -->
<section class="container categories-section">
    <h2 style="text-align: center; margin-bottom: 25px; font-size: 1.5rem; color: var(--primary-color);">Navegue por Categorias</h2>
    
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

<!-- Seção de Anúncios Recentes na Home -->
<section class="container" style="padding-bottom: 60px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
        <h2>Serviços Recentes na Sua Região</h2>
        <a href="busca.php?q=" style="color: var(--accent-color); font-weight: 600; text-decoration: none;">Ver Todos os Serviços →</a>
    </div>

    <?php if (empty($anuncios_recentes)): ?>
        <div style="background: #fff; padding: 30px; border-radius: 10px; border: 1px solid var(--border-color); text-align: center;">
            <p style="color: #64748b;">Nenhum anúncio publicado ainda. Seja o primeiro a anunciar!</p>
        </div>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
            <?php foreach ($anuncios_recentes as $anuncio): ?>
                <div style="background: #fff; border: 1px solid var(--border-color); border-radius: 8px; padding: 20px; display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <span style="background: #ecfdf5; color: var(--accent-color); font-size: 0.75rem; font-weight: 600; padding: 3px 8px; border-radius: 4px;">
                                <?= htmlspecialchars($anuncio['subcategoria']) ?>
                            </span>
                            <span style="color: #f59e0b; font-size: 0.85rem; font-weight: 700;">
                                ★ <?= $anuncio['media_notas'] > 0 ? round($anuncio['media_notas'], 1) : 'Novo' ?>
                            </span>
                        </div>

                        <h3 style="margin: 8px 0; font-size: 1.1rem; color: var(--primary-color);">
                            <?= htmlspecialchars($anuncio['titulo']) ?>
                        </h3>

                        <!-- Exibição de Localização -->
                        <p style="color: #ef4444; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">
                            <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($anuncio['bairro'] ? $anuncio['bairro'] . ', ' : '') ?><?= htmlspecialchars($anuncio['cidade']) ?>/<?= htmlspecialchars($anuncio['estado']) ?>
                        </p>

                        <p style="color: #64748b; font-size: 0.85rem; margin-bottom: 12px;">
                            Por: <a href="perfil_publico.php?id=<?= $anuncio['prestador_id'] ?>" style="color: var(--primary-color); font-weight: 700; text-decoration: underline;"><?= htmlspecialchars($anuncio['prestador']) ?></a>
                        </p>

                        <?php if ($anuncio['preco_medio']): ?>
                            <p style="font-weight: 700; color: var(--primary-color); font-size: 1.1rem; margin-bottom: 15px;">
                                R$ <?= number_format($anuncio['preco_medio'], 2, ',', '.') ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <a href="anuncio.php?id=<?= $anuncio['id'] ?>" class="btn-submit" style="display: block; text-align: center; text-decoration: none;">Ver Detalhes</a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php include 'includes/footer.php'; ?>
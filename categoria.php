<?php
session_start();
require_once 'config/conexao.php';

$categoria_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Busca os dados da Categoria Principal
$stmt_cat = $pdo->prepare("SELECT * FROM categorias WHERE id = :id");
$stmt_cat->execute([':id' => $categoria_id]);
$categoria = $stmt_cat->fetch();

if (!$categoria) {
    header("Location: index.php");
    exit;
}

// Busca os Anúncios vinculados às subcategorias desta Categoria
$stmt_anuncios = $pdo->prepare("
    SELECT a.*, u.nome AS prestador, u.id AS prestador_id, s.nome AS subcategoria 
    FROM anuncios a
    JOIN usuarios u ON a.usuario_id = u.id
    JOIN subcategorias s ON a.subcategoria_id = s.id
    WHERE s.categoria_id = :cat_id
    ORDER BY a.data_criacao DESC
");
$stmt_anuncios->execute([':cat_id' => $categoria_id]);
$anuncios = $stmt_anuncios->fetchAll();

include 'includes/header.php';
?>

<main class="container" style="padding-top: 40px; padding-bottom: 50px;">
    <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 25px;">
        <div class="icon-circle">
            <i class="fas <?= htmlspecialchars($categoria['icone_url']) ?>"></i>
        </div>
        <div>
            <h1><?= htmlspecialchars($categoria['nome']) ?></h1>
            <p style="color: #64748b;"><?= count($anuncios) ?> serviço(s) disponível(is) nesta categoria</p>
        </div>
    </div>

    <?php if (empty($anuncios)): ?>
        <div style="background: #fff; padding: 40px; border-radius: 10px; border: 1px solid var(--border-color); text-align: center;">
            <p style="color: #64748b;">Nenhum anúncio cadastrado nesta categoria ainda.</p>
            <a href="anunciar.php" class="btn-anunciar" style="display: inline-block; margin-top: 15px; text-decoration: none;">Seja o primeiro a anunciar!</a>
        </div>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
            <?php foreach ($anuncios as $anuncio): ?>
                <div style="background: #fff; border: 1px solid var(--border-color); border-radius: 8px; padding: 20px;">
                    <span style="background: #ecfdf5; color: var(--accent-color); font-size: 0.8rem; font-weight: 600; padding: 4px 8px; border-radius: 4px;">
                        <?= htmlspecialchars($anuncio['subcategoria']) ?>
                    </span>
                    <h3 style="margin: 10px 0; font-size: 1.2rem;"><?= htmlspecialchars($anuncio['titulo']) ?></h3>
                    <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 15px;">
                        Por: <a href="perfil_publico.php?id=<?= $anuncio['prestador_id'] ?>" style="color: var(--primary-color); font-weight: 700; text-decoration: underline;"><?= htmlspecialchars($anuncio['prestador']) ?></a>
                    </p>
                    <?php if ($anuncio['preco_medio']): ?>
                        <p style="font-weight: 700; color: var(--primary-color); font-size: 1.1rem; margin-bottom: 15px;">
                            R$ <?= number_format($anuncio['preco_medio'], 2, ',', '.') ?>
                        </p>
                    <?php endif; ?>
                    <a href="anuncio.php?id=<?= $anuncio['id'] ?>" class="btn-submit" style="display: block; text-align: center; text-decoration: none;">Ver Detalhes</a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<?php include 'includes/footer.php'; ?>
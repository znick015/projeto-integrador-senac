<?php
session_start();
require_once 'config/conexao.php';

$categoria_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Busca Categoria
$stmt_cat = $pdo->prepare("SELECT * FROM categorias WHERE id = :id");
$stmt_cat->execute([':id' => $categoria_id]);
$categoria = $stmt_cat->fetch();

if (!$categoria) {
    header("Location: index.php");
    exit;
}

// Busca Anúncios da Categoria com Notas e Localização
$stmt_anuncios = $pdo->prepare("
    SELECT a.*, u.nome AS prestador, u.id AS prestador_id, s.nome AS subcategoria,
           COALESCE(AVG(av.nota), 0) AS media_notas
    FROM anuncios a
    JOIN usuarios u ON a.usuario_id = u.id
    JOIN subcategorias s ON a.subcategoria_id = s.id
    LEFT JOIN avaliacoes av ON a.id = av.anuncio_id
    WHERE s.categoria_id = :cat_id
    GROUP BY a.id
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

                        <!-- Localização do Anúncio -->
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
</main>

<?php include 'includes/footer.php'; ?>
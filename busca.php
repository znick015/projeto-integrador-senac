<?php
session_start();
require_once 'config/conexao.php';

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

// Consulta no banco buscando por título ou descrição
$stmt = $pdo->prepare("
    SELECT a.*, u.nome AS prestador, s.nome AS subcategoria 
    FROM anuncios a
    JOIN usuarios u ON a.usuario_id = u.id
    JOIN subcategorias s ON a.subcategoria_id = s.id
    WHERE a.titulo LIKE :q OR a.descricao LIKE :q
    ORDER BY a.data_criacao DESC
");
$stmt->execute([':q' => "%$q%"]);
$anuncios = $stmt->fetchAll();

include 'includes/header.php';
?>

<main class="container" style="padding-top: 40px;">
    <h2>Resultados para: "<?= htmlspecialchars($q) ?>"</h2>
    <p style="color: #64748b; margin-bottom: 30px;"><?= count($anuncios) ?> serviço(s) encontrado(s)</p>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
        <?php foreach ($anuncios as $anuncio): ?>
            <div style="background: #fff; border: 1px solid var(--border-color); border-radius: 8px; padding: 20px;">
                <span style="background: #ecfdf5; color: var(--accent-color); font-size: 0.8rem; font-weight: 600; padding: 4px 8px; border-radius: 4px;">
                    <?= htmlspecialchars($anuncio['subcategoria']) ?>
                </span>
                <h3 style="margin: 10px 0; font-size: 1.2rem;"><?= htmlspecialchars($anuncio['titulo']) ?></h3>
                <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 15px;">
                    Por: <strong><?= htmlspecialchars($anuncio['prestador']) ?></strong>
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
</main>

<?php include 'includes/footer.php'; ?>
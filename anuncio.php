<?php
session_start();
require_once 'config/conexao.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("
    SELECT a.*, u.nome AS prestador, u.email AS email_prestador, s.nome AS subcategoria 
    FROM anuncios a
    JOIN usuarios u ON a.usuario_id = u.id
    JOIN subcategorias s ON a.subcategoria_id = s.id
    WHERE a.id = :id
");
$stmt->execute([':id' => $id]);
$anuncio = $stmt->fetch();

if (!$anuncio) {
    header("Location: index.php");
    exit;
}

// Limpa o número para gerar o link correto do WhatsApp
$whatsapp_num = preg_replace('/[^0-9]/', '', $anuncio['telefone_contato']);

include 'includes/header.php';
?>

<main class="container" style="padding-top: 40px; max-width: 800px;">
    <div style="background: #fff; border: 1px solid var(--border-color); padding: 30px; border-radius: 10px;">
        <span style="background: #ecfdf5; color: var(--accent-color); font-size: 0.85rem; font-weight: 600; padding: 4px 10px; border-radius: 4px;">
            <?= htmlspecialchars($anuncio['subcategoria']) ?>
        </span>
        <h1 style="margin-top: 15px; font-size: 1.8rem;"><?= htmlspecialchars($anuncio['titulo']) ?></h1>
        <p style="color: #64748b; margin-top: 5px;">Anunciado por: <strong><?= htmlspecialchars($anuncio['prestador']) ?></strong></p>

        <hr style="margin: 20px 0; border: none; border-top: 1px solid var(--border-color);">

        <h3>Descrição do Serviço</h3>
        <p style="margin-top: 10px; white-space: pre-line; line-height: 1.8;"><?= htmlspecialchars($anuncio['descricao']) ?></p>

        <?php if ($anuncio['preco_medio']): ?>
            <div style="margin-top: 25px;">
                <span style="color: #64748b; font-size: 0.9rem;">Valor estimado:</span>
                <h2 style="color: var(--primary-color);">R$ <?= number_format($anuncio['preco_medio'], 2, ',', '.') ?></h2>
            </div>
        <?php endif; ?>

        <div style="margin-top: 30px;">
            <a href="https://wa.me/55<?= $whatsapp_num ?>?text=Olá, vi seu anúncio '<?= urlencode($anuncio['titulo']) ?>' no TrampoCerto e gostaria de um orçamento." 
               target="_blank" 
               style="display: inline-block; background-color: #25d366; color: #fff; text-decoration: none; padding: 14px 28px; border-radius: 8px; font-weight: 700; font-size: 1.1rem;">
                <i class="fab fa-whatsapp"></i> Chamar no WhatsApp
            </a>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
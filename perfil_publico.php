<?php
session_start();
require_once 'config/conexao.php';

$usuario_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt_user = $pdo->prepare("SELECT id, nome, email, telefone, cidade, estado, foto_perfil, data_cadastro FROM usuarios WHERE id = :id");
$stmt_user->execute([':id' => $usuario_id]);
$anunciante = $stmt_user->fetch();

if (!$anunciante) {
    header("Location: index.php");
    exit;
}

if (isset($_SESSION['usuario_id']) && $_SESSION['usuario_id'] == $usuario_id) {
    header("Location: perfil.php");
    exit;
}

$stmt_notas = $pdo->prepare("
    SELECT AVG(av.nota) as media, COUNT(av.id) as total_avaliacoes
    FROM avaliacoes av JOIN anuncios a ON av.anuncio_id = a.id
    WHERE a.usuario_id = :usuario_id
");
$stmt_notas->execute([':usuario_id' => $usuario_id]);
$dados_avaliacoes = $stmt_notas->fetch();
$media_geral = round($dados_avaliacoes['media'], 1);

$stmt_anuncios = $pdo->prepare("
    SELECT a.*, s.nome AS subcategoria 
    FROM anuncios a JOIN subcategorias s ON a.subcategoria_id = s.id
    WHERE a.usuario_id = :usuario_id ORDER BY a.data_criacao DESC
");
$stmt_anuncios->execute([':usuario_id' => $usuario_id]);
$anuncios = $stmt_anuncios->fetchAll();

$whatsapp_num = preg_replace('/[^0-9]/', '', $anunciante['telefone']);

include 'includes/header.php';
?>

<main class="container" style="padding-top: 40px; padding-bottom: 50px;">
    <div style="background: #fff; border: 1px solid var(--border-color); border-radius: 10px; padding: 30px; margin-bottom: 35px; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 20px;">
        <div style="display: flex; align-items: center; gap: 20px;">
            <div style="width: 100px; height: 100px; border-radius: 50%; overflow: hidden; background: #e2e8f0; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <?php if ($anunciante['foto_perfil']): ?>
                    <img src="<?= htmlspecialchars($anunciante['foto_perfil']) ?>" alt="Foto" style="width: 100%; height: 100%; object-fit: cover;">
                <?php else: ?>
                    <i class="fas fa-user" style="font-size: 3rem; color: #94a3b8;"></i>
                <?php endif; ?>
            </div>

            <div>
                <h1 style="font-size: 1.6rem; margin-bottom: 5px; color: var(--primary-color);">
                    <?= htmlspecialchars($anunciante['nome']) ?>
                </h1>
                
                <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 8px;">
                    <i class="fas fa-map-marker-alt" style="color: #ef4444;"></i> <?= htmlspecialchars($anunciante['cidade']) ?>/<?= htmlspecialchars($anunciante['estado']) ?> | 
                    <i class="far fa-calendar-alt"></i> Membro desde <?= date('m/Y', strtotime($anunciante['data_cadastro'])) ?>
                </p>

                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="background: #fef3c7; color: #d97706; font-weight: 700; padding: 4px 10px; border-radius: 20px; font-size: 0.9rem;">
                        ★ <?= $media_geral ? $media_geral : 'Novo' ?> / 5.0
                    </span>
                    <span style="color: #64748b; font-size: 0.85rem;">
                        (<?= $dados_avaliacoes['total_avaliacoes'] ?> avaliações recebidas)
                    </span>
                </div>
            </div>
        </div>

        <?php if ($whatsapp_num): ?>
            <div>
                <a href="https://wa.me/55<?= $whatsapp_num ?>?text=Olá, vi seu perfil no TrampoCerto e gostaria de solicitar um serviço!" 
                   target="_blank" 
                   style="background-color: #25d366; color: #fff; text-decoration: none; padding: 12px 24px; border-radius: 8px; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                    <i class="fab fa-whatsapp" style="font-size: 1.2rem;"></i> Contatar Anunciante
                </a>
            </div>
        <?php endif; ?>
    </div>

    <h2>Anúncios de <?= htmlspecialchars($anunciante['nome']) ?> (<?= count($anuncios) ?>)</h2>
    <p style="color: #64748b; margin-bottom: 25px;">Confira os serviços oferecidos nesta região</p>

    <?php if (empty($anuncios)): ?>
        <div style="background: #fff; padding: 30px; border-radius: 10px; border: 1px solid var(--border-color); text-align: center;">
            <p style="color: #64748b;">Este anunciante não possui outros serviços publicados no momento.</p>
        </div>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
            <?php foreach ($anuncios as $anuncio): ?>
                <div style="background: #fff; border: 1px solid var(--border-color); border-radius: 8px; padding: 20px; display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <span style="background: #ecfdf5; color: var(--accent-color); font-size: 0.8rem; font-weight: 600; padding: 4px 8px; border-radius: 4px;">
                                <?= htmlspecialchars($anuncio['subcategoria']) ?>
                            </span>
                            <span style="color: #ef4444; font-size: 0.8rem; font-weight: 600;">
                                <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($anuncio['cidade']) ?>
                            </span>
                        </div>
                        <h3 style="margin: 10px 0; font-size: 1.1rem;"><?= htmlspecialchars($anuncio['titulo']) ?></h3>
                        <?php if ($anuncio['preco_medio']): ?>
                            <p style="font-weight: 700; color: var(--primary-color); font-size: 1.1rem; margin-bottom: 15px;">
                                R$ <?= number_format($anuncio['preco_medio'], 2, ',', '.') ?>
                            </p>
                        <?php endif; ?>
                    </div>
                    <a href="anuncio.php?id=<?= $anuncio['id'] ?>" class="btn-submit" style="display: block; text-align: center; text-decoration: none; margin-top: 15px;">Ver Detalhes</a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<?php include 'includes/footer.php'; ?>
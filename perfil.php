<?php
session_start();
require_once 'config/conexao.php';

// Bloqueia se não estiver logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$mensagem = '';
$erro = '';

// Exclusão de Anúncio
if (isset($_GET['excluir'])) {
    $id_anuncio = (int)$_GET['excluir'];
    $stmt_del = $pdo->prepare("DELETE FROM anuncios WHERE id = :id AND usuario_id = :usuario_id");
    $stmt_del->execute([':id' => $id_anuncio, ':usuario_id' => $usuario_id]);
    header("Location: perfil.php?deletado=1");
    exit;
}

// Upload de Foto de Perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['foto_perfil'])) {
    $foto = $_FILES['foto_perfil'];
    
    if ($foto['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($foto['name'], PATHINFO_EXTENSION));
        $extensoes_permitidas = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($ext, $extensoes_permitidas)) {
            // Cria a pasta uploads se não existir
            if (!is_dir('uploads')) {
                mkdir('uploads', 0777, true);
            }

            $nome_arquivo = 'perfil_' . $usuario_id . '_' . time() . '.' . $ext;
            $caminho_final = 'uploads/' . $nome_arquivo;

            if (move_uploaded_file($foto['tmp_name'], $caminho_final)) {
                $stmt_up = $pdo->prepare("UPDATE usuarios SET foto_perfil = :foto WHERE id = :id");
                $stmt_up->execute([':foto' => $caminho_final, ':id' => $usuario_id]);
                $mensagem = "Foto de perfil atualizada com sucesso!";
            } else {
                $erro = "Erro ao salvar a imagem no servidor.";
            }
        } else {
            $erro = "Formato de imagem inválido. Use JPG, PNG ou WEBP.";
        }
    }
}

// Busca dados atualizados do Usuário
$stmt_user = $pdo->prepare("SELECT * FROM usuarios WHERE id = :id");
$stmt_user->execute([':id' => $usuario_id]);
$usuario = $stmt_user->fetch();

// Busca os Anúncios do Usuário
$stmt_anuncios = $pdo->prepare("
    SELECT a.*, s.nome AS subcategoria 
    FROM anuncios a
    JOIN subcategorias s ON a.subcategoria_id = s.id
    WHERE a.usuario_id = :id
    ORDER BY a.data_criacao DESC
");
$stmt_anuncios->execute([':id' => $usuario_id]);
$meus_anuncios = $stmt_anuncios->fetchAll();

include 'includes/header.php';
?>

<main class="container" style="padding-top: 40px; padding-bottom: 50px;">
    <?php if (isset($_GET['deletado'])): ?>
        <div style="background: #fef2f2; color: #991b1b; padding: 12px; border-radius: 6px; margin-bottom: 20px;">
            Anúncio removido com sucesso.
        </div>
    <?php endif; ?>

    <?php if ($mensagem): ?>
        <div style="background: #ecfdf5; color: #065f46; padding: 12px; border-radius: 6px; margin-bottom: 20px;">
            <?= $mensagem ?>
        </div>
    <?php endif; ?>

    <?php if ($erro): ?>
        <div class="alert-danger"><?= $erro ?></div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 300px 1fr; gap: 30px;">
        <!-- Card de Informações e Foto -->
        <div style="background: #fff; border: 1px solid var(--border-color); padding: 25px; border-radius: 10px; height: fit-content; text-align: center;">
            <div style="width: 120px; height: 120px; margin: 0 auto 15px; border-radius: 50%; overflow: hidden; background: #e2e8f0; display: flex; align-items: center; justify-content: center;">
                <?php if ($usuario['foto_perfil']): ?>
                    <img src="<?= htmlspecialchars($usuario['foto_perfil']) ?>" alt="Foto Perfil" style="width: 100%; height: 100%; object-fit: cover;">
                <?php else: ?>
                    <i class="fas fa-user" style="font-size: 3.5rem; color: #94a3b8;"></i>
                <?php endif; ?>
            </div>

            <h3 style="margin-bottom: 5px;"><?= htmlspecialchars($usuario['nome']) ?></h3>
            <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 20px;"><?= htmlspecialchars($usuario['email']) ?></p>

            <form action="perfil.php" method="POST" enctype="multipart/form-data">
                <label for="foto_perfil" style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 8px; text-align: left;">Alterar Foto de Perfil:</label>
                <input type="file" name="foto_perfil" id="foto_perfil" accept="image/*" required style="width: 100%; font-size: 0.8rem; margin-bottom: 10px;">
                <button type="submit" class="btn-submit" style="padding: 8px; font-size: 0.9rem;">Salvar Foto</button>
            </form>
        </div>

        <!-- Lista dos Meus Anúncios -->
        <div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2>Meus Anúncios (<?= count($meus_anuncios) ?>)</h2>
                <a href="anunciar.php" class="btn-anunciar" style="text-decoration: none;">+ Criar Novo Anúncio</a>
            </div>

            <?php if (empty($meus_anuncios)): ?>
                <div style="background: #fff; padding: 30px; border-radius: 10px; border: 1px solid var(--border-color); text-align: center;">
                    <p style="color: #64748b;">Você ainda não criou nenhum anúncio.</p>
                </div>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 15px;">
                    <?php foreach ($meus_anuncios as $anuncio): ?>
                        <div style="background: #fff; border: 1px solid var(--border-color); border-radius: 8px; padding: 20px; display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <span style="background: #ecfdf5; color: var(--accent-color); font-size: 0.75rem; font-weight: 600; padding: 3px 8px; border-radius: 4px;">
                                    <?= htmlspecialchars($anuncio['subcategoria']) ?>
                                </span>
                                <h3 style="margin: 8px 0 5px; font-size: 1.1rem;"><?= htmlspecialchars($anuncio['titulo']) ?></h3>
                                <p style="color: #64748b; font-size: 0.85rem;">Publicado em: <?= date('d/m/Y', strtotime($anuncio['data_criacao'])) ?></p>
                            </div>
                            <div style="display: flex; gap: 10px; align-items: center;">
                                <a href="anuncio.php?id=<?= $anuncio['id'] ?>" style="color: var(--accent-color); text-decoration: none; font-weight: 600; font-size: 0.9rem;">Ver</a>
                                <a href="perfil.php?excluir=<?= $anuncio['id'] ?>" onclick="return confirm('Tem certeza que deseja excluir este anúncio?');" style="color: #ef4444; text-decoration: none; font-size: 0.9rem;"><i class="fas fa-trash"></i> Excluir</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
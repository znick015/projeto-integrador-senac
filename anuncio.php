<?php
session_start();
require_once 'config/conexao.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$mensagem = '';

$stmt = $pdo->prepare("
    SELECT a.*, 
           u.nome AS prestador, 
           u.id AS prestador_id, 
           u.foto_perfil AS prestador_foto,
           u.data_cadastro AS prestador_desde,
           u.cidade AS prestador_cidade,
           u.estado AS prestador_estado,
           s.nome AS subcategoria 
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

// Avaliações gerais do vendedor
$stmt_seller_rating = $pdo->prepare("
    SELECT AVG(av.nota) as media_geral, COUNT(av.id) as total_geral
    FROM avaliacoes av JOIN anuncios an ON av.anuncio_id = an.id
    WHERE an.usuario_id = :seller_id
");
$stmt_seller_rating->execute([':seller_id' => $anuncio['prestador_id']]);
$dados_seller_rating = $stmt_seller_rating->fetch();
$media_seller = round($dados_seller_rating['media_geral'], 1);

// Processa formulários (Avaliação, Pergunta, Resposta)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao_avaliacao'])) {
    if (!isset($_SESSION['usuario_id'])) { header("Location: login.php"); exit; }
    $nota = (int)$_POST['nota'];
    $comentario = trim($_POST['comentario']);
    if ($nota >= 1 && $nota <= 5) {
        $stmt_av = $pdo->prepare("INSERT INTO avaliacoes (anuncio_id, cliente_id, nota, comentario) VALUES (:anuncio_id, :cliente_id, :nota, :comentario)");
        $stmt_av->execute([':anuncio_id' => $id, ':cliente_id' => $_SESSION['usuario_id'], ':nota' => $nota, ':comentario' => $comentario]);
        $mensagem = "Avaliação enviada com sucesso!";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao_pergunta'])) {
    if (!isset($_SESSION['usuario_id'])) { header("Location: login.php"); exit; }
    $pergunta = trim($_POST['pergunta']);
    if (!empty($pergunta)) {
        $stmt_pq = $pdo->prepare("INSERT INTO perguntas_respostas (anuncio_id, cliente_id, pergunta) VALUES (:anuncio_id, :cliente_id, :pergunta)");
        $stmt_pq->execute([':anuncio_id' => $id, ':cliente_id' => $_SESSION['usuario_id'], ':pergunta' => $pergunta]);
        $mensagem = "Sua pergunta foi enviada ao profissional!";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao_resposta'])) {
    if (isset($_SESSION['usuario_id']) && $_SESSION['usuario_id'] == $anuncio['prestador_id']) {
        $id_pergunta = (int)$_POST['pergunta_id'];
        $resposta = trim($_POST['resposta']);
        if (!empty($resposta)) {
            $stmt_resp = $pdo->prepare("UPDATE perguntas_respostas SET resposta_profissional = :resp, data_resposta = NOW() WHERE id = :p_id");
            $stmt_resp->execute([':resp' => $resposta, ':p_id' => $id_pergunta]);
            $mensagem = "Resposta publicada!";
        }
    }
}

// Avaliações do anúncio
$stmt_med = $pdo->prepare("SELECT AVG(nota) as media, COUNT(*) as total FROM avaliacoes WHERE anuncio_id = :id");
$stmt_med->execute([':id' => $id]);
$dados_notas = $stmt_med->fetch();
$media_notas = round($dados_notas['media'], 1);

$stmt_list_av = $pdo->prepare("SELECT av.*, u.nome AS cliente FROM avaliacoes av JOIN usuarios u ON av.cliente_id = u.id WHERE av.anuncio_id = :id ORDER BY av.data_avaliacao DESC");
$stmt_list_av->execute([':id' => $id]);
$avaliacoes = $stmt_list_av->fetchAll();

$stmt_pr = $pdo->prepare("SELECT pr.*, u.nome AS cliente FROM perguntas_respostas pr JOIN usuarios u ON pr.cliente_id = u.id WHERE pr.anuncio_id = :id ORDER BY pr.data_pergunta DESC");
$stmt_pr->execute([':id' => $id]);
$perguntas = $stmt_pr->fetchAll();

$whatsapp_num = preg_replace('/[^0-9]/', '', $anuncio['telefone_contato']);

// Formata endereço para a query do Google Maps
$localizacao_texto = ($anuncio['bairro'] ? $anuncio['bairro'] . ', ' : '') . $anuncio['cidade'] . ' - ' . $anuncio['estado'];
$localizacao_query = urlencode(($anuncio['bairro'] ? $anuncio['bairro'] . ' ' : '') . $anuncio['cidade'] . ' ' . $anuncio['estado'] . ' Brasil');

include 'includes/header.php';
?>

<main class="container" style="padding-top: 40px; padding-bottom: 50px; max-width: 900px;">
    <?php if ($mensagem): ?>
        <div style="background: #ecfdf5; color: #065f46; padding: 12px; border-radius: 6px; margin-bottom: 20px;">
            <?= $mensagem ?>
        </div>
    <?php endif; ?>

    <!-- Detalhes do Anúncio -->
    <div style="background: #fff; border: 1px solid var(--border-color); padding: 30px; border-radius: 10px; margin-bottom: 25px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
            <span style="background: #ecfdf5; color: var(--accent-color); font-size: 0.85rem; font-weight: 600; padding: 4px 10px; border-radius: 4px;">
                <?= htmlspecialchars($anuncio['subcategoria']) ?>
            </span>
            <span style="color: #ef4444; font-weight: 600; font-size: 0.95rem;">
                <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($localizacao_texto) ?>
            </span>
        </div>
        
        <h1 style="margin-top: 15px; font-size: 1.8rem;"><?= htmlspecialchars($anuncio['titulo']) ?></h1>
        
        <p style="color: #64748b; margin-top: 5px;">
            Nota deste serviço: <span style="color: #f59e0b; font-weight: 700;">★ <?= $media_notas ? $media_notas : 'Novo' ?></span> (<?= $dados_notas['total'] ?> avaliações)
        </p>

        <hr style="margin: 20px 0; border: none; border-top: 1px solid var(--border-color);">

        <h3>Descrição do Serviço</h3>
        <p style="margin-top: 10px; white-space: pre-line; line-height: 1.8; color: var(--text-color);"><?= htmlspecialchars($anuncio['descricao']) ?></p>

        <?php if ($anuncio['preco_medio']): ?>
            <div style="margin-top: 25px;">
                <span style="color: #64748b; font-size: 0.9rem;">Valor estimado:</span>
                <h2 style="color: var(--primary-color);">R$ <?= number_format($anuncio['preco_medio'], 2, ',', '.') ?></h2>
            </div>
        <?php endif; ?>
    </div>

    <!-- MAPA DA LOCALIZAÇÃO (GOOGLE MAPS) -->
    <div style="background: #fff; border: 1px solid var(--border-color); padding: 25px; border-radius: 10px; margin-bottom: 25px;">
        <h3 style="margin-bottom: 15px; font-size: 1.1rem; display: flex; align-items: center; gap: 8px;">
            <i class="fas fa-map-marked-alt" style="color: var(--accent-color);"></i> Região de Atendimento
        </h3>
        <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 15px;">
            Atendimento presencial / prestação em: <strong><?= htmlspecialchars($localizacao_texto) ?></strong>
        </p>
        
        <!-- Iframe do Google Maps sem necessidade de API Key -->
        <div style="border-radius: 8px; overflow: hidden; border: 1px solid var(--border-color);">
            <iframe 
                width="100%" 
                height="280" 
                style="border:0;" 
                loading="lazy" 
                allowfullscreen 
                src="https://www.google.com/maps?q=<?= $localizacao_query ?>&output=embed">
            </iframe>
        </div>
    </div>

    <!-- Card de Prévia do Anunciante -->
    <div style="background: #fff; border: 1px solid var(--border-color); padding: 25px; border-radius: 10px; margin-bottom: 30px;">
        <h4 style="color: #64748b; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 1px; margin-bottom: 15px;">Anunciado por:</h4>
        
        <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 20px;">
            <div style="display: flex; align-items: center; gap: 15px;">
                <div style="width: 70px; height: 70px; border-radius: 50%; overflow: hidden; background: #e2e8f0; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <?php if ($anuncio['prestador_foto']): ?>
                        <img src="<?= htmlspecialchars($anuncio['prestador_foto']) ?>" alt="Foto" style="width: 100%; height: 100%; object-fit: cover;">
                    <?php else: ?>
                        <i class="fas fa-user" style="font-size: 2rem; color: #94a3b8;"></i>
                    <?php endif; ?>
                </div>

                <div>
                    <h3 style="font-size: 1.2rem; margin-bottom: 3px;">
                        <a href="perfil_publico.php?id=<?= $anuncio['prestador_id'] ?>" style="color: var(--primary-color); text-decoration: none;">
                            <?= htmlspecialchars($anuncio['prestador']) ?>
                        </a>
                    </h3>
                    <p style="color: #64748b; font-size: 0.85rem; margin-bottom: 5px;">
                        <i class="fas fa-building"></i> <?= htmlspecialchars($anuncio['prestador_cidade']) ?>/<?= htmlspecialchars($anuncio['prestador_estado']) ?> | 
                        Membro desde <?= date('m/Y', strtotime($anuncio['prestador_desde'])) ?>
                    </p>
                    <span style="background: #fef3c7; color: #d97706; font-weight: 700; padding: 2px 8px; border-radius: 12px; font-size: 0.8rem;">
                        ★ <?= $media_seller ? $media_seller : 'Novo' ?> / 5.0 reputação
                    </span>
                </div>
            </div>

            <div style="display: flex; flex-wrap: wrap; gap: 10px; align-items: center;">
                <a href="perfil_publico.php?id=<?= $anuncio['prestador_id'] ?>" 
                   style="border: 1px solid var(--border-color); color: var(--primary-color); text-decoration: none; padding: 10px 18px; border-radius: 6px; font-weight: 600; font-size: 0.9rem;">
                    Ver Perfil Completo
                </a>
                
                <?php if ($whatsapp_num): ?>
                    <a href="https://wa.me/55<?= $whatsapp_num ?>?text=Olá, vi seu anúncio '<?= urlencode($anuncio['titulo']) ?>' no TrampoCerto e gostaria de um orçamento." 
                       target="_blank" 
                       style="background-color: #25d366; color: #fff; text-decoration: none; padding: 10px 20px; border-radius: 6px; font-weight: 700; font-size: 0.9rem; display: flex; align-items: center; gap: 6px;">
                        <i class="fab fa-whatsapp" style="font-size: 1.1rem;"></i> Conversar no WhatsApp
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Perguntas e Respostas -->
    <div style="background: #fff; border: 1px solid var(--border-color); padding: 30px; border-radius: 10px; margin-bottom: 30px;">
        <h3>Perguntas ao Profissional</h3>

        <?php if (isset($_SESSION['usuario_id']) && $_SESSION['usuario_id'] != $anuncio['prestador_id']): ?>
            <form action="anuncio.php?id=<?= $id ?>" method="POST" style="margin: 20px 0;">
                <input type="hidden" name="acao_pergunta" value="1">
                <div class="form-group">
                    <textarea name="pergunta" rows="3" placeholder="Escreva sua dúvida para o profissional..." required style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px;"></textarea>
                </div>
                <button type="submit" class="btn-submit" style="width: auto; padding: 8px 20px;">Enviar Pergunta</button>
            </form>
        <?php endif; ?>

        <div style="margin-top: 20px; display: flex; flex-direction: column; gap: 15px;">
            <?php if (empty($perguntas)): ?>
                <p style="color: #64748b; font-size: 0.9rem;">Nenhuma pergunta feita ainda.</p>
            <?php else: ?>
                <?php foreach ($perguntas as $pq): ?>
                    <div style="border-bottom: 1px solid var(--border-color); padding-bottom: 15px;">
                        <p style="font-size: 0.95rem;"><strong><?= htmlspecialchars($pq['cliente']) ?>:</strong> <?= htmlspecialchars($pq['pergunta']) ?></p>
                        
                        <?php if ($pq['resposta_profissional']): ?>
                            <div style="background: #f8fafc; border-left: 3px solid var(--accent-color); padding: 10px 15px; margin-top: 8px; font-size: 0.9rem;">
                                <strong>Resposta do Prestador:</strong> <?= htmlspecialchars($pq['resposta_profissional']) ?>
                            </div>
                        <?php elseif (isset($_SESSION['usuario_id']) && $_SESSION['usuario_id'] == $anuncio['prestador_id']): ?>
                            <form action="anuncio.php?id=<?= $id ?>" method="POST" style="margin-top: 10px;">
                                <input type="hidden" name="acao_resposta" value="1">
                                <input type="hidden" name="pergunta_id" value="<?= $pq['id'] ?>">
                                <input type="text" name="resposta" placeholder="Responder cliente..." required style="padding: 6px 10px; border: 1px solid var(--border-color); border-radius: 4px; width: 70%;">
                                <button type="submit" class="btn-submit" style="width: auto; padding: 6px 12px; font-size: 0.85rem;">Responder</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Avaliações -->
    <div style="background: #fff; border: 1px solid var(--border-color); padding: 30px; border-radius: 10px;">
        <h3>Avaliações deste Serviço</h3>

        <?php if (isset($_SESSION['usuario_id']) && $_SESSION['usuario_id'] != $anuncio['prestador_id']): ?>
            <form action="anuncio.php?id=<?= $id ?>" method="POST" style="margin: 20px 0; background: #f8fafc; padding: 15px; border-radius: 8px;">
                <input type="hidden" name="acao_avaliacao" value="1">
                <div class="form-group">
                    <label>Sua Nota (1 a 5 estrelas):</label>
                    <select name="nota" required style="padding: 8px; border-radius: 4px; border: 1px solid var(--border-color);">
                        <option value="5">★★★★★ (5 - Excelente)</option>
                        <option value="4">★★★★☆ (4 - Muito Bom)</option>
                        <option value="3">★★★☆☆ (3 - Bom)</option>
                        <option value="2">★★☆☆☆ (2 - Regular)</option>
                        <option value="1">★☆☆☆☆ (1 - Ruim)</option>
                    </select>
                </div>
                <div class="form-group">
                    <textarea name="comentario" rows="2" placeholder="Deixe um comentário..." style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px;"></textarea>
                </div>
                <button type="submit" class="btn-submit" style="width: auto; padding: 8px 20px;">Publicar Avaliação</button>
            </form>
        <?php endif; ?>

        <div style="margin-top: 20px; display: flex; flex-direction: column; gap: 15px;">
            <?php if (empty($avaliacoes)): ?>
                <p style="color: #64748b; font-size: 0.9rem;">Este anúncio ainda não possui avaliações.</p>
            <?php else: ?>
                <?php foreach ($avaliacoes as $av): ?>
                    <div style="border-bottom: 1px solid var(--border-color); padding-bottom: 12px;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <strong><?= htmlspecialchars($av['cliente']) ?></strong>
                            <span style="color: #f59e0b; font-weight: 700;">★ <?= $av['nota'] ?>/5</span>
                        </div>
                        <?php if ($av['comentario']): ?>
                            <p style="color: #64748b; font-size: 0.9rem; margin-top: 5px;"><?= htmlspecialchars($av['comentario']) ?></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
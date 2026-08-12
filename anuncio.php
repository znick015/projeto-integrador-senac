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
           s.nome AS subcategoria,
           c.nome AS categoria_nome
    FROM anuncios a
    JOIN usuarios u ON a.usuario_id = u.id
    JOIN subcategorias s ON a.subcategoria_id = s.id
    JOIN categorias c ON s.categoria_id = c.id
    WHERE a.id = :id
");
$stmt->execute([':id' => $id]);
$anuncio = $stmt->fetch();

if (!$anuncio) {
    header("Location: index.php");
    exit;
}

// Avaliações
$stmt_seller_rating = $pdo->prepare("SELECT AVG(av.nota) as media_geral FROM avaliacoes av JOIN anuncios an ON av.anuncio_id = an.id WHERE an.usuario_id = :seller_id");
$stmt_seller_rating->execute([':seller_id' => $anuncio['prestador_id']]);
$media_seller = round($stmt_seller_rating->fetch()['media_geral'], 1);

// Processa interações
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

// Média de notas e listas
$stmt_med = $pdo->prepare("SELECT AVG(nota) as media, COUNT(*) as total FROM avaliacoes WHERE anuncio_id = :id");
$stmt_med->execute([':id' => $id]);
$dados_notas = $stmt_med->fetch();

$stmt_list_av = $pdo->prepare("SELECT av.*, u.nome AS cliente FROM avaliacoes av JOIN usuarios u ON av.cliente_id = u.id WHERE av.anuncio_id = :id ORDER BY av.data_avaliacao DESC");
$stmt_list_av->execute([':id' => $id]);
$avaliacoes = $stmt_list_av->fetchAll();

$stmt_pr = $pdo->prepare("SELECT pr.*, u.nome AS cliente FROM perguntas_respostas pr JOIN usuarios u ON pr.cliente_id = u.id WHERE pr.anuncio_id = :id ORDER BY pr.data_pergunta DESC");
$stmt_pr->execute([':id' => $id]);
$perguntas = $stmt_pr->fetchAll();

$whatsapp_num = preg_replace('/[^0-9]/', '', $anuncio['telefone_contato']);

// Converte textos em listas de tópicos
$inclusos_array = !empty($anuncio['itens_inclusos']) ? explode("\n", trim($anuncio['itens_inclusos'])) : [];
$nao_inclusos_array = !empty($anuncio['itens_nao_inclusos']) ? explode("\n", trim($anuncio['itens_nao_inclusos'])) : [];

// Imagem padrão caso não tenha feito upload
$foto_capa = $anuncio['imagem_capa'] ? $anuncio['imagem_capa'] : 'assets/img/hero-bg.jpg';

include 'includes/header.php';
?>

<main class="container" style="padding-top: 25px; padding-bottom: 50px;">
    <!-- Breadcrumbs -->
    <div class="breadcrumb">
        <a href="index.php"><i class="fas fa-home"></i> Início</a> &gt; 
        <a href="busca.php?q=">Busca</a> &gt; 
        <span><?= htmlspecialchars($anuncio['titulo']) ?></span>
    </div>

    <?php if ($mensagem): ?>
        <div style="background: #ecfdf5; color: #065f46; padding: 12px; border-radius: 6px; margin-bottom: 20px;">
            <?= $mensagem ?>
        </div>
    <?php endif; ?>

    <!-- LAYOUT PRINCIPAL (GRID ESTILO GETNINJAS) -->
    <div class="gn-detail-grid">
        
        <!-- COLUNA ESQUERDA (IMAGEM + DETALHES DO SERVIÇO) -->
        <div>
            <!-- Imagem Grande de Capa -->
            <div class="gn-cover-box">
                <img src="<?= htmlspecialchars($foto_capa) ?>" alt="Capa do Serviço">
            </div>

            <!-- Título e Subtítulo -->
            <h1 style="font-size: 2rem; margin-bottom: 8px; color: var(--primary-color);">
                <?= htmlspecialchars($anuncio['titulo']) ?>
            </h1>
            <p style="color: #64748b; font-size: 0.95rem; margin-bottom: 25px;">
                <i class="fas fa-map-marker-alt" style="color: #ef4444;"></i> Disponível em <strong><?= htmlspecialchars($anuncio['cidade']) ?>/<?= htmlspecialchars($anuncio['estado']) ?></strong> · por 
                <a href="perfil_publico.php?id=<?= $anuncio['prestador_id'] ?>" style="color: var(--primary-color); font-weight: 700;">
                    <?= htmlspecialchars($anuncio['prestador']) ?>
                </a>
            </p>

            <hr style="border: none; border-top: 1px solid var(--border-color); margin-bottom: 30px;">

            <!-- Seção: Sobre este Serviço -->
            <div style="margin-bottom: 40px;">
                <h2 class="gn-section-title">Sobre este serviço</h2>
                <p style="line-height: 1.8; color: #475569; font-size: 1rem;"><?= htmlspecialchars($anuncio['descricao']) ?></p>

                <!-- Tags / Selos -->
                <div class="gn-tags-container">
                    <span class="gn-tag"><?= htmlspecialchars($anuncio['categoria_nome']) ?></span>
                    <span class="gn-tag"><?= htmlspecialchars($anuncio['subcategoria']) ?></span>
                    <span class="gn-tag">Atendimento Rápido</span>
                    <span class="gn-tag">Garantia de Qualidade</span>
                </div>
            </div>

            <hr style="border: none; border-top: 1px solid var(--border-color); margin-bottom: 30px;">

            <!-- Seção: O que está incluso -->
            <?php if (!empty($inclusos_array)): ?>
                <div style="margin-bottom: 35px;">
                    <h2 class="gn-section-title">O que está incluso</h2>
                    <ul class="gn-bullet-list">
                        <?php foreach ($inclusos_array as $item): ?>
                            <?php if (trim($item) !== ''): ?>
                                <li><?= htmlspecialchars(trim($item)) ?></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Seção: O que NÃO está incluso -->
            <?php if (!empty($nao_inclusos_array)): ?>
                <div style="margin-bottom: 30px;">
                    <h2 class="gn-section-title">O que não está incluso</h2>
                    <ul class="gn-bullet-list">
                        <?php foreach ($nao_inclusos_array as $item): ?>
                            <?php if (trim($item) !== ''): ?>
                                <li><?= htmlspecialchars(trim($item)) ?></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Caixa de Aviso sobre Peças -->
            <div class="gn-notice-box">
                <div class="gn-notice-icon">!</div>
                <div>
                    <strong>Peças não inclusas:</strong> a compra de peças necessárias é de responsabilidade do cliente, salvo combinado diretamente com o prestador.
                </div>
            </div>

            <!-- Card de Prévia do Prestador -->
            <div style="background: #fff; border: 1px solid var(--border-color); padding: 25px; border-radius: 16px; margin-top: 40px;">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div style="width: 65px; height: 65px; border-radius: 50%; overflow: hidden; background: #e2e8f0;">
                        <?php if ($anuncio['prestador_foto']): ?>
                            <img src="<?= htmlspecialchars($anuncio['prestador_foto']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                        <?php else: ?>
                            <i class="fas fa-user" style="font-size: 2rem; color: #94a3b8; margin: 15px;"></i>
                        <?php endif; ?>
                    </div>
                    <div>
                        <h3 style="font-size: 1.1rem; margin-bottom: 2px;">
                            <a href="perfil_publico.php?id=<?= $anuncio['prestador_id'] ?>" style="color: var(--primary-color); text-decoration: none;">
                                <?= htmlspecialchars($anuncio['prestador']) ?>
                            </a>
                        </h3>
                        <p style="color: #64748b; font-size: 0.85rem;">Membro desde <?= date('m/Y', strtotime($anuncio['prestador_desde'])) ?></p>
                        <span style="color: #f59e0b; font-weight: 700; font-size: 0.85rem;">★ <?= $media_seller ? $media_seller : 'Novo' ?> / 5.0 reputação</span>
                    </div>
                </div>
            </div>

            <!-- Perguntas e Respostas -->
            <div style="background: #fff; border: 1px solid var(--border-color); padding: 25px; border-radius: 16px; margin-top: 30px;">
                <h3>Perguntas ao Profissional</h3>
                <?php if (isset($_SESSION['usuario_id']) && $_SESSION['usuario_id'] != $anuncio['prestador_id']): ?>
                    <form action="anuncio.php?id=<?= $id ?>" method="POST" style="margin: 15px 0;">
                        <input type="hidden" name="acao_pergunta" value="1">
                        <textarea name="pergunta" rows="2" placeholder="Escreva sua dúvida..." required style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px;"></textarea>
                        <button type="submit" class="btn-submit" style="width: auto; padding: 6px 16px; margin-top: 8px;">Enviar Pergunta</button>
                    </form>
                <?php endif; ?>

                <div style="margin-top: 15px;">
                    <?php if (empty($perguntas)): ?>
                        <p style="color: #64748b; font-size: 0.9rem;">Nenhuma pergunta feita ainda.</p>
                    <?php else: ?>
                        <?php foreach ($perguntas as $pq): ?>
                            <div style="border-bottom: 1px solid var(--border-color); padding: 10px 0;">
                                <p style="font-size: 0.9rem;"><strong><?= htmlspecialchars($pq['cliente']) ?>:</strong> <?= htmlspecialchars($pq['pergunta']) ?></p>
                                <?php if ($pq['resposta_profissional']): ?>
                                    <p style="background: #f8fafc; padding: 8px; border-left: 3px solid var(--accent-color); font-size: 0.85rem; margin-top: 5px;">
                                        <strong>Resposta:</strong> <?= htmlspecialchars($pq['resposta_profissional']) ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

        </div>

        <!-- COLUNA DIREITA (CARD FIXO DE PREÇO E CONTRATAÇÃO) -->
        <aside>
            <div class="gn-price-card">
                <p style="font-size: 0.85rem; color: #64748b; font-weight: 600;">Preço do Serviço</p>

                <?php if ($anuncio['preco_medio']): ?>
                    <div class="gn-price-amount">
                        R$ <?= number_format($anuncio['preco_medio'], 2, ',', '.') ?>
                    </div>
                <?php else: ?>
                    <div class="gn-price-amount" style="font-size: 1.5rem;">
                        Sob Consulta
                    </div>
                <?php endif; ?>

                <a href="https://wa.me/55<?= $whatsapp_num ?>?text=Olá, vi seu serviço '<?= urlencode($anuncio['titulo']) ?>' no TrampoCerto e gostaria de contratar!" 
                   target="_blank" 
                   class="btn-gn-contract">
                    <i class="fab fa-whatsapp"></i> Contratar Serviço
                </a>

                <p style="text-align: center; font-size: 0.8rem; color: #64748b; margin-top: 15px;">
                    <i class="fas fa-shield-alt" style="color: var(--accent-color);"></i> Contratação direta sem taxas extras
                </p>
            </div>
        </aside>

    </div>
</main>

<?php include 'includes/footer.php'; ?>
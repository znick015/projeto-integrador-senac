USE plataforma_autonomos;

-- Adiciona localização no perfil do usuário
ALTER TABLE usuarios 
ADD COLUMN cidade VARCHAR(100) DEFAULT 'Belo Horizonte',
ADD COLUMN estado VARCHAR(2) DEFAULT 'MG';

-- Adiciona localização específica no anúncio do serviço
ALTER TABLE anuncios 
ADD COLUMN bairro VARCHAR(100) DEFAULT '',
ADD COLUMN cidade VARCHAR(100) DEFAULT 'Belo Horizonte',
ADD COLUMN estado VARCHAR(2) DEFAULT 'MG';

EXIT;

sudo service mariadb start
php8.3 -S 0.0.0.0:8000



-- 1. DESLIGA AS TRAVAS E ZERA AS TABELAS PARA RESETAR OS IDs
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE anuncios;
TRUNCATE TABLE usuarios;
SET FOREIGN_KEY_CHECKS = 1;

-- 2. INSERE OS 15 USUÁRIOS (Os IDs serão exatamente de 1 a 15)
INSERT INTO usuarios (nome, email, senha_hash, telefone, cidade, estado) VALUES
('Carlos Eletricista', 'carlos@teste.com', '$2y$10$R9h/cIPz0gi.URNNX3HA2uOenc1jm5K.2h6F3PCO.9K5yQoE5c/7C', '31999990001', 'Ibirité', 'MG'),
('Fernanda Diarista', 'fernanda@teste.com', '$2y$10$R9h/cIPz0gi.URNNX3HA2uOenc1jm5K.2h6F3PCO.9K5yQoE5c/7C', '31999990002', 'Contagem', 'MG'),
('Marcos Tech', 'marcos@teste.com', '$2y$10$R9h/cIPz0gi.URNNX3HA2uOenc1jm5K.2h6F3PCO.9K5yQoE5c/7C', '31999990003', 'Belo Horizonte', 'MG'),
('Ana Pedreira', 'ana@teste.com', '$2y$10$R9h/cIPz0gi.URNNX3HA2uOenc1jm5K.2h6F3PCO.9K5yQoE5c/7C', '31999990004', 'Ibirité', 'MG'),
('Lucas Encanador', 'lucas@teste.com', '$2y$10$R9h/cIPz0gi.URNNX3HA2uOenc1jm5K.2h6F3PCO.9K5yQoE5c/7C', '31999990005', 'Contagem', 'MG'),
('Paula Designer', 'paula@teste.com', '$2y$10$R9h/cIPz0gi.URNNX3HA2uOenc1jm5K.2h6F3PCO.9K5yQoE5c/7C', '31999990006', 'Belo Horizonte', 'MG'),
('Diego Moto Estética', 'diego@teste.com', '$2y$10$R9h/cIPz0gi.URNNX3HA2uOenc1jm5K.2h6F3PCO.9K5yQoE5c/7C', '31999990007', 'Ibirité', 'MG'),
('Juliana Confeiteira', 'juliana@teste.com', '$2y$10$R9h/cIPz0gi.URNNX3HA2uOenc1jm5K.2h6F3PCO.9K5yQoE5c/7C', '31999990008', 'Contagem', 'MG'),
('Roberto Pintor', 'roberto@teste.com', '$2y$10$R9h/cIPz0gi.URNNX3HA2uOenc1jm5K.2h6F3PCO.9K5yQoE5c/7C', '31999990009', 'Belo Horizonte', 'MG'),
('Camila Esteticista', 'camila@teste.com', '$2y$10$R9h/cIPz0gi.URNNX3HA2uOenc1jm5K.2h6F3PCO.9K5yQoE5c/7C', '31999990010', 'Ibirité', 'MG'),
('Bruno Fretes', 'bruno@teste.com', '$2y$10$R9h/cIPz0gi.URNNX3HA2uOenc1jm5K.2h6F3PCO.9K5yQoE5c/7C', '31999990011', 'Contagem', 'MG'),
('Amanda Paisagista', 'amanda@teste.com', '$2y$10$R9h/cIPz0gi.URNNX3HA2uOenc1jm5K.2h6F3PCO.9K5yQoE5c/7C', '31999990012', 'Belo Horizonte', 'MG'),
('Vitor Marido de Aluguel', 'vitor@teste.com', '$2y$10$R9h/cIPz0gi.URNNX3HA2uOenc1jm5K.2h6F3PCO.9K5yQoE5c/7C', '31999990013', 'Ibirité', 'MG'),
('Rafael Desenvolvedor', 'rafael@teste.com', '$2y$10$R9h/cIPz0gi.URNNX3HA2uOenc1jm5K.2h6F3PCO.9K5yQoE5c/7C', '31999990014', 'Belo Horizonte', 'MG'),
('Silvia Fotógrafa', 'silvia@teste.com', '$2y$10$R9h/cIPz0gi.URNNX3HA2uOenc1jm5K.2h6F3PCO.9K5yQoE5c/7C', '31999990015', 'Contagem', 'MG');

-- 3. INSERE OS 15 ANÚNCIOS (Vinculados aos IDs exatos de 1 a 15)
INSERT INTO anuncios (usuario_id, subcategoria_id, titulo, descricao, preco_medio, telefone_contato, cidade, estado, itens_inclusos, itens_nao_inclusos) VALUES
(1, 1, 'Instalação Elétrica Padrão Cemig', 'Faço do zero ou corrijo gambiarras. Instalação de chuveiros, tomadas 220v e quadros de distribuição com total segurança.', 180.00, '31999990001', 'Ibirité', 'MG', 'Fiação básica; disjuntores', 'Peças de acabamento'),
(2, 4, 'Faxina Pesada e Organização', 'Limpeza detalhada de todos os cômodos. Levo meus próprios produtos de limpeza. Cheirinho de casa nova garantido no fim do dia.', 160.00, '31999990002', 'Contagem', 'MG', 'Produtos de limpeza; Vassouras', 'Limpeza externa de janelas altas'),
(3, 6, 'Formatação e Montagem de PC Gamer', 'Especialista em hardware. Faço cable management perfeito, montagem de setups Ryzen e RTX, e deixo sua máquina otimizada.', 250.00, '31999990003', 'Belo Horizonte', 'MG', 'Formatação; Instalação do Windows', 'Licenças de software pago'),
(4, 2, 'Assentamento de Porcelanato', 'Acabamento fino e nivelamento perfeito. Assento porcelanato em grandes formatos, levanto muros e faço reboco. Serviço limpo e no prazo.', 80.00, '31999990004', 'Ibirité', 'MG', 'Mão de obra especializada', 'Argamassa; Porcelanato'),
(5, 1, 'Caça Vazamentos e Reparos', 'Conta de água veio alta? Infiltração na parede? Localizo o problema e faço o reparo hidráulico sem precisar quebrar a sua casa inteira.', 120.00, '31999990005', 'Contagem', 'MG', 'Detecção eletrônica', 'Tubos para reposição'),
(6, 6, 'Criação de Identidade Visual', 'Dou uma cara profissional para o seu negócio. Criação de logotipos, paleta de cores e templates para redes sociais. Entrego arquivos em vetor e PNG.', 450.00, '31999990006', 'Belo Horizonte', 'MG', 'Logo em vetor; Paleta de cores', 'Impressão de materiais'),
(7, 3, 'Limpeza e Lubrificação de Relação', 'Manutenção completa da relação da sua moto com produtos premium (Motul). Ideal para motos de média cilindrada. Evite o desgaste prematuro da sua corrente!', 90.00, '31999990007', 'Ibirité', 'MG', 'Desengraxante; Lubrificante Motul', 'Troca de peças'),
(8, 5, 'Bolos Decorados e Doces Finos', 'Produção artesanal de bolos de festa, brigadeiros gourmet e sobremesas. Ingredientes de primeira linha para fazer da sua festa um sucesso.', 110.00, '31999990008', 'Contagem', 'MG', 'Bolo decorado; Embalagem', 'Topo de bolo personalizado'),
(9, 2, 'Pintura Interna e Cimento Queimado', 'Aplicação de massa corrida, pintura lisa e texturas como cimento queimado. Trabalho com isolamento completo para não sujar seus móveis.', 70.00, '31999990009', 'Belo Horizonte', 'MG', 'Mão de obra; Fitas de isolamento', 'Tintas; Massa corrida'),
(10, 4, 'Limpeza de Pele e Massagem Relaxante', 'Cuidados estéticos profissionais no conforto do seu lar. Levo maca portátil, vapor de ozônio e cremes de alta qualidade.', 130.00, '31999990010', 'Ibirité', 'MG', 'Cremes; Aparelhos estéticos', 'Toalhas do cliente'),
(11, 3, 'Carretos e Pequenas Mudanças', 'Frete seguro em caminhonete nova. Atendo BH, Ibirité, Contagem e região metropolitana. Ajudo no carregamento e descarregamento.', 150.00, '31999990011', 'Contagem', 'MG', 'Transporte; Ajudante', 'Embalagem em caixas'),
(12, 4, 'Paisagismo e Manutenção de Jardins', 'Deixo seu quintal parecendo capa de revista. Serviço inclui poda de árvores, corte de grama, adubação e controle de pragas.', 180.00, '31999990012', 'Belo Horizonte', 'MG', 'Ferramentas de poda', 'Plantas novas; Adubo'),
(13, 2, 'Marido de Aluguel: Instalações Gerais', 'Precisa pendurar a TV, instalar prateleiras ou varões de cortina e não tem furadeira? Resolvo tudo rápido, com ferramentas próprias e sem fazer sujeira.', 90.00, '31999990013', 'Ibirité', 'MG', 'Ferramentas; Buchas simples', 'Suportes de TV'),
(14, 6, 'Desenvolvimento de Sistemas Web', 'Criação de plataformas sob medida, painéis administrativos e integração de banco de dados utilizando PHP, HTML, CSS e MySQL.', 1200.00, '31999990014', 'Belo Horizonte', 'MG', 'Código fonte; Banco de dados', 'Hospedagem; Domínio'),
(15, 5, 'Ensaios Fotográficos Externos', 'Cobertura de eventos e ensaios fotográficos em parques e praças. Pacote inclui 50 fotos com edição profissional de cor e iluminação.', 350.00, '31999990015', 'Contagem', 'MG', '50 fotos editadas; Pendrive', 'Álbum impresso');

-- INSERIR MAIS 5 USUÁRIOS (IDs 16 a 20)
INSERT INTO usuarios (nome, email, senha_hash, telefone, cidade, estado) VALUES
('Sônia Passadeira', 'sonia@teste.com', '$2y$10$R9h/cIPz0gi.URNNX3HA2uOenc1jm5K.2h6F3PCO.9K5yQoE5c/7C', '31999990016', 'Ibirité', 'MG'),
('Rita Cozinheira', 'rita@teste.com', '$2y$10$R9h/cIPz0gi.URNNX3HA2uOenc1jm5K.2h6F3PCO.9K5yQoE5c/7C', '31999990017', 'Belo Horizonte', 'MG'),
('Joana Babá', 'joana@teste.com', '$2y$10$R9h/cIPz0gi.URNNX3HA2uOenc1jm5K.2h6F3PCO.9K5yQoE5c/7C', '31999990018', 'Contagem', 'MG'),
('Teresa Cuidadora', 'teresa@teste.com', '$2y$10$R9h/cIPz0gi.URNNX3HA2uOenc1jm5K.2h6F3PCO.9K5yQoE5c/7C', '31999990019', 'Belo Horizonte', 'MG'),
('Cleide Lavadeira', 'cleide@teste.com', '$2y$10$R9h/cIPz0gi.URNNX3HA2uOenc1jm5K.2h6F3PCO.9K5yQoE5c/7C', '31999990020', 'Ibirité', 'MG');

-- INSERIR MAIS 5 ANÚNCIOS DE SERVIÇOS DOMÉSTICOS
-- Estamos a usar a subcategoria_id = 4 (que agrupa estes serviços domésticos/cuidados)
INSERT INTO anuncios (usuario_id, subcategoria_id, titulo, descricao, preco_medio, telefone_contato, cidade, estado, itens_inclusos, itens_nao_inclusos) VALUES
(16, 4, 'Passadeira de Roupas em Domicílio', 'Deixo as suas roupas impecáveis, desde camisas sociais a vestidos de festa. Trabalho rápido, com muito capricho e cuidado com tecidos delicados.', 120.00, '31999990016', 'Ibirité', 'MG', 'Passadoria de peças em geral; Organização nos cabides', 'Lavagem das peças'),
(17, 4, 'Cozinheira a Domicílio e Marmitas', 'Preparo as suas refeições da semana diretamente na sua cozinha. Comida caseira, opções fit e ementa variada. Deixo tudo nas vasilhas e a cozinha a brilhar!', 150.00, '31999990017', 'Belo Horizonte', 'MG', 'Preparo das refeições; Limpeza da bancada e pia', 'Compra dos ingredientes'),
(18, 4, 'Babá Folguista e Cuidadora Infantil', 'Precisa de um fim de semana livre ou teve um imprevisto? Cuido dos seus filhos com muito carinho. Tenho curso de primeiros socorros e excelentes referências.', 200.00, '31999990018', 'Contagem', 'MG', 'Cuidados gerais; Brincadeiras lúdicas; Alimentação', 'Transporte das crianças (leva e traz)'),
(19, 4, 'Cuidadora de Idosos (Turnos)', 'Acompanhamento humanizado e paciente para idosos. Ajuda com a medicação, banho e alimentação. Experiência comprovada de 5 anos na área da saúde.', 180.00, '31999990019', 'Belo Horizonte', 'MG', 'Acompanhamento diário; Auxílio na higiene', 'Procedimentos de enfermagem complexos'),
(20, 4, 'Limpeza Profunda de Sofás e Tapetes', 'Renovo o seu estofado! Lavagem a seco com extração profunda, eliminando ácaros, manchas e odores indesejados. Atendimento em toda a região metropolitana.', 160.00, '31999990020', 'Ibirité', 'MG', 'Lavagem de 1 sofá (até 3 lugares); Higienização', 'Impermeabilização (cobrado à parte)');

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Limpa qualquer resquício da tentativa anterior para não dar erro de e-mail duplicado
DELETE FROM usuarios WHERE email IN ('sonia@teste.com', 'rita@teste.com', 'joana@teste.com', 'teresa@teste.com', 'cleide@teste.com');
DELETE FROM anuncios WHERE telefone_contato IN ('31999990016', '31999990017', '31999990018', '31999990019', '31999990020');

-- 2. Insere os usuários FORÇANDO a coluna 'id' a ser exatamente de 16 a 20
INSERT INTO usuarios (id, nome, email, senha_hash, telefone, cidade, estado) VALUES
(16, 'Sônia Passadeira', 'sonia@teste.com', '$2y$10$R9h/cIPz0gi.URNNX3HA2uOenc1jm5K.2h6F3PCO.9K5yQoE5c/7C', '31999990016', 'Ibirité', 'MG'),
(17, 'Rita Cozinheira', 'rita@teste.com', '$2y$10$R9h/cIPz0gi.URNNX3HA2uOenc1jm5K.2h6F3PCO.9K5yQoE5c/7C', '31999990017', 'Belo Horizonte', 'MG'),
(18, 'Joana Babá', 'joana@teste.com', '$2y$10$R9h/cIPz0gi.URNNX3HA2uOenc1jm5K.2h6F3PCO.9K5yQoE5c/7C', '31999990018', 'Contagem', 'MG'),
(19, 'Teresa Cuidadora', 'teresa@teste.com', '$2y$10$R9h/cIPz0gi.URNNX3HA2uOenc1jm5K.2h6F3PCO.9K5yQoE5c/7C', '31999990019', 'Belo Horizonte', 'MG'),
(20, 'Cleide Lavadeira', 'cleide@teste.com', '$2y$10$R9h/cIPz0gi.URNNX3HA2uOenc1jm5K.2h6F3PCO.9K5yQoE5c/7C', '31999990020', 'Ibirité', 'MG');

-- 3. Insere os anúncios vinculados aos IDs exatos criados acima
INSERT INTO anuncios (usuario_id, subcategoria_id, titulo, descricao, preco_medio, telefone_contato, cidade, estado, itens_inclusos, itens_nao_inclusos) VALUES
(16, 4, 'Passadeira de Roupas em Domicílio', 'Deixo as suas roupas impecáveis, desde camisas sociais a vestidos de festa. Trabalho rápido, com muito capricho e cuidado com tecidos delicados.', 120.00, '31999990016', 'Ibirité', 'MG', 'Passadoria de peças em geral; Organização nos cabides', 'Lavagem das peças'),
(17, 4, 'Cozinheira a Domicílio e Marmitas', 'Preparo as suas refeições da semana diretamente na sua cozinha. Comida caseira, opções fit e ementa variada. Deixo tudo nas vasilhas e a cozinha a brilhar!', 150.00, '31999990017', 'Belo Horizonte', 'MG', 'Preparo das refeições; Limpeza da bancada e pia', 'Compra dos ingredientes'),
(18, 4, 'Babá Folguista e Cuidadora Infantil', 'Precisa de um fim de semana livre ou teve um imprevisto? Cuido dos seus filhos com muito carinho. Tenho curso de primeiros socorros e excelentes referências.', 200.00, '31999990018', 'Contagem', 'MG', 'Cuidados gerais; Brincadeiras lúdicas; Alimentação', 'Transporte das crianças (leva e traz)'),
(19, 4, 'Cuidadora de Idosos (Turnos)', 'Acompanhamento humanizado e paciente para idosos. Ajuda com a medicação, banho e alimentação. Experiência comprovada de 5 anos na área da saúde.', 180.00, '31999990019', 'Belo Horizonte', 'MG', 'Acompanhamento diário; Auxílio na higiene', 'Procedimentos de enfermagem complexos'),
(20, 4, 'Limpeza Profunda de Sofás e Tapetes', 'Renovo o seu estofado! Lavagem a seco com extração profunda, eliminando ácaros, manchas e odores indesejados. Atendimento em toda a região metropolitana.', 160.00, '31999990020', 'Ibirité', 'MG', 'Lavagem de 1 sofá (até 3 lugares); Higienização', 'Impermeabilização (cobrado à parte)');

SET FOREIGN_KEY_CHECKS = 1;
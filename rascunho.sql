USE plataforma_autonomos;

-- Inserindo Categorias Principais
INSERT INTO categorias (nome, icone_url) VALUES 
('Assistência Técnica', 'fa-wrench'),
('Reformas e Reparos', 'fa-hammer'),
('Design e Tecnologia', 'fa-code'),
('Serviços Domésticos', 'fa-broom');

-- Inserindo Subcategorias
INSERT INTO subcategorias (categoria_id, nome) VALUES 
(1, 'Manutenção de Celular'),
(1, 'Manutenção de Computador'),
(2, 'Pintor'),
(2, 'Eletricista'),
(2, 'Encanador'),
(3, 'Desenvolvimento Web'),
(3, 'Design Gráfico'),
(4, 'Diarista');
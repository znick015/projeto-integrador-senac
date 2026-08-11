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
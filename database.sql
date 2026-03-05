-- Criação do banco de dados nforganiza
-- Em hospedagem compartilhada, selecione o banco no phpMyAdmin antes de executar este script.
-- CREATE DATABASE e USE foram removidos para evitar erro de permissao.

-- Tabela de usuários para autenticação (v4.0)
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de notas fiscais (v4.0)
CREATE TABLE IF NOT EXISTS notas_fiscais (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT DEFAULT NULL, -- Relacionamento opcional para o MVP
    numero_nota VARCHAR(50) NOT NULL,
    data_emissao DATETIME NOT NULL,
    razao_social_emitente VARCHAR(255) NOT NULL,
    cnpj_emitente VARCHAR(20) NOT NULL,
    valor_total DECIMAL(15, 2) NOT NULL,
    xml_conteudo LONGTEXT NOT NULL,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

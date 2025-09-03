-- Schema do sistema de controle de equipamentos
CREATE DATABASE IF NOT EXISTS tcc_equipamentos CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE tcc_equipamentos;

CREATE TABLE IF NOT EXISTS usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  email VARCHAR(120) NOT NULL UNIQUE,
  senha VARCHAR(255) NOT NULL,
  tipo ENUM('estudante','pedagogico','admin') NOT NULL DEFAULT 'estudante',
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS equipamentos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL
);

CREATE TABLE IF NOT EXISTS reservas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  equipamento_id INT NOT NULL,
  usuario_id INT NOT NULL,
  data_retirada DATETIME NOT NULL,
  data_devolucao DATETIME NOT NULL,
  motivo TEXT,
  status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (equipamento_id) REFERENCES equipamentos(id),
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

INSERT INTO equipamentos (nome) VALUES
('Projetor Multimídia'),
('Notebook Dell i7'),
('Tablet Samsung'),
('Câmera Digital'),
('Microfone Sem Fio')
ON DUPLICATE KEY UPDATE nome = VALUES(nome);

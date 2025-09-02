CREATE DATABASE IF NOT EXISTS tcc_equipamentos;
USE tcc_equipamentos;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    tipo ENUM('estudante','pedagogico','admin') NOT NULL
);

CREATE TABLE equipamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL
);

CREATE TABLE reservas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    equipamento_id INT NOT NULL,
    usuario_id INT NOT NULL,
    data_retirada DATETIME NOT NULL,
    data_devolucao DATETIME NOT NULL,
    motivo TEXT,
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
    FOREIGN KEY (equipamento_id) REFERENCES equipamentos(id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

INSERT INTO equipamentos (nome) VALUES 
('Projetor Multimídia'),
('Notebook Dell i7'),
('Tablet Samsung'),
('Câmera Digital'),
('Microfone Sem Fio');

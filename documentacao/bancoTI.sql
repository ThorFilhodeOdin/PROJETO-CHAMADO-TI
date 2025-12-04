

CREATE DATABASE chamados;

USE chamados;

SELECT * FROM users;
SELECT * FROM chamados;

INSERT INTO users (nome, senha, telefone, setor)
VALUES
('Neca Barbosa', '$2y$12$Q.uZEtZwUcmvAxuJtQ4e5Ow2PV8EL8ftap/YNUkZYyBjNjDl/qqdO', '11977445533', 'EXPEDICAO');

DELETE FROM chamados WHERE id_chamado = 21; 

DROP TABLE users;

ALTER TABLE users
ADD telefone VARCHAR(15);

CREATE TABLE users (
    id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    nome VARCHAR(100) NOT NULL,
    senha VARCHAR(255) NOT NULL,
	telefone VARCHAR(15),
  setor ENUM(
    'TI',
    'ACRILICO',
    'ADMINISTRATIVO',
    'ALMOXARIFADO',
    'FERRAGENS',
    'COMERCIAL',
    'COMPRAS',
    'ELETRICA',
    'EMBALAGENS',
    'EXPEDICAO',
    'FATURAMENTO',
    'FINANCEIRO',
    'FUNILARIA',
    'IMPRESSAO',
    'OPERACIONAL',
    'PCP',
    'PELICULA',
    'PINTURA',
    'PLOTER',
    'PROCESSOS',
    'PROJETO',
    'QUALIDADE',
    'RECEPCAO',
    'RECORTE',
    'RH',
    'SERRALHERIA',
    'SESMT_OPERACIONAL',
    'SEM SETOR'
  ) NOT NULL
);





CREATE TABLE chamados (
  id_chamado INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  numero VARCHAR(20) NOT NULL,
  tipo ENUM('impressora', 'internet', 'computador', 'sistema', 'monitor', 'celular') NOT NULL,
  status ENUM('aberto', 'finalizado') DEFAULT 'aberto',
data DATE DEFAULT (CURRENT_DATE),
  imagem1 LONGBLOB,
  imagem2 LONGBLOB,
  imagem3 LONGBLOB,
  imagem4 LONGBLOB,
  imagem5 LONGBLOB,
  imagem6 LONGBLOB,
  descricao TEXT NOT NULL,
  setor ENUM(
    'TI',
    'ACRILICO',
    'ADMINISTRATIVO',
    'ALMOXARIFADO',
    'FERRAGENS',
    'COMERCIAL',
    'COMPRAS',
    'ELETRICA',
    'EMBALAGENS',
    'EXPEDICAO',
    'FATURAMENTO',
    'FINANCEIRO',
    'FUNILARIA',
    'IMPRESSAO',
    'OPERACIONAL',
    'PCP',
    'PELICULA',
    'PINTURA',
    'PLOTER',
    'PROCESSOS',
    'PROJETO',
    'QUALIDADE',
    'RECEPCAO',
    'RECORTE',
    'RH',
    'SERRALHERIA',
    'SESMT_OPERACIONAL'
  ) NOT NULL
);


UPDATE users
SET nome = 'João da Silva',
    telefone = '11987654321',
    setor = 'TI'
WHERE id = '05574e1d-c576-11f0-a78b-fc4596f6061b';

INSERT INTO chamados (nome, numero, tipo, status, descricao, setor)
VALUES
('João da Silva', '11987654321', 'computador', 'aberto', 'Computador não liga mais desde ontem.', 'TI'),
('Maria Oliveira', '21944556677', 'impressora', 'aberto', 'Impressora travando ao imprimir boletos.', 'ADMINISTRATIVO');




DROP DATABASE pw2_systemlab;
CREATE DATABASE pw2_systemlab;
USE pw2_systemlab;

-- =================== - Tabelas - =================== --

CREATE TABLE tb_escolas (
    cd_escola INT PRIMARY KEY AUTO_INCREMENT,
    nm_escola VARCHAR(100) NOT NULL,
    ds_endereco VARCHAR(150),
    nm_bairro VARCHAR(100),
    nm_cidade VARCHAR(100),
    sg_uf CHAR(2),
    nr_telefone VARCHAR(20),
    nm_email VARCHAR(100)
);

CREATE TABLE tb_salas (
    cd_sala INT PRIMARY KEY AUTO_INCREMENT,
    nm_sala VARCHAR(50) NOT NULL,
    nr_computadores INT NOT NULL DEFAULT 20,
    id_escola INT NOT NULL,
    FOREIGN KEY (id_escola) REFERENCES tb_escolas(cd_escola)
);

CREATE TABLE tb_alunos (
    cd_aluno INT PRIMARY KEY AUTO_INCREMENT,
    nm_aluno VARCHAR(100) NOT NULL,
    ds_matricula VARCHAR(20) NOT NULL UNIQUE,
    ds_email VARCHAR(100) NOT NULL UNIQUE,
    ds_senha VARCHAR(255) NOT NULL,
    ds_turma VARCHAR(30) NOT NULL,
    id_escola INT NOT NULL,
    FOREIGN KEY (id_escola) REFERENCES tb_escolas(cd_escola)
);

CREATE TABLE tb_professores (
    cd_professor INT PRIMARY KEY AUTO_INCREMENT,
    nm_professor VARCHAR(100) NOT NULL,
    ds_email VARCHAR(100),
    id_escola INT NOT NULL,
    FOREIGN KEY (id_escola) REFERENCES tb_escolas(cd_escola)
);

CREATE TABLE tb_materias (
    cd_materia INT PRIMARY KEY AUTO_INCREMENT,
    sg_materia VARCHAR(5) NOT NULL,
    nm_materia VARCHAR(100) NOT NULL
);

CREATE TABLE tb_professores_materias (
    cd_professor_materia INT PRIMARY KEY AUTO_INCREMENT,
    id_professor INT NOT NULL,
    id_materia INT NOT NULL,
    FOREIGN KEY (id_professor) REFERENCES tb_professores(cd_professor),
    FOREIGN KEY (id_materia) REFERENCES tb_materias(cd_materia)
);

CREATE TABLE tb_computadores (
    cd_computador INT PRIMARY KEY AUTO_INCREMENT,
    nm_computador VARCHAR(20) NOT NULL,
    ds_status VARCHAR(30) NOT NULL DEFAULT 'Funcionando',
    id_sala INT NOT NULL,
    FOREIGN KEY (id_sala) REFERENCES tb_salas(cd_sala)
);

CREATE TABLE tb_usuarios (
    cd_usuario INT PRIMARY KEY AUTO_INCREMENT,
    nm_usuario VARCHAR(100) NOT NULL,
    ds_email VARCHAR(100) NOT NULL UNIQUE,
    ds_senha VARCHAR(255) NOT NULL,
    ds_tipo VARCHAR(20) NOT NULL,
    id_professor INT,
    id_aluno INT,
    FOREIGN KEY (id_professor) REFERENCES tb_professores(cd_professor),
    FOREIGN KEY (id_aluno) REFERENCES tb_alunos(cd_aluno)
);

CREATE TABLE tb_reservas (
    cd_reserva INT PRIMARY KEY AUTO_INCREMENT,
    dt_reserva DATE NOT NULL,
    hr_inicio TIME NOT NULL,
    hr_fim TIME NOT NULL,
    ds_turma VARCHAR(30),
    nr_alunos INT NOT NULL,
    ds_finalidade VARCHAR(150),
    id_professor INT NOT NULL,
    id_materia INT NOT NULL,
    FOREIGN KEY (id_professor) REFERENCES tb_professores(cd_professor),
    FOREIGN KEY (id_materia) REFERENCES tb_materias(cd_materia)
);

CREATE TABLE tb_reservas_salas (
    cd_reserva_sala INT PRIMARY KEY AUTO_INCREMENT,
    id_reserva INT NOT NULL,
    id_sala INT NOT NULL,
    FOREIGN KEY (id_reserva) REFERENCES tb_reservas(cd_reserva),
    FOREIGN KEY (id_sala) REFERENCES tb_salas(cd_sala),
    UNIQUE (id_reserva, id_sala)
);
-- =================================================== --

-- ===== - Insert tb_escolas - =====
INSERT INTO tb_escolas (
    nm_escola,
    ds_endereco,
    nm_bairro,
    nm_cidade,
    sg_uf,
    nr_telefone,
    nm_email
)
VALUES (
    'ETEC de Itanhaém',
    'Av. José Batista Campos, 1431',
    'Cidade Anchieta',
    'Itanhaém',
    'SP',
    '(13) 3426-4926',
    'e158dir@cps.sp.gov.br'
);
-- ================================= --

-- =============== - Insert tb_salas - =============== --
INSERT INTO tb_salas (
    nm_sala,
    nr_computadores,
    id_escola
)
VALUES
    ('Laboratório 01', 20, 1),
    ('Laboratório 02', 20, 1),
    ('Laboratório 03', 20, 1),
    ('Laboratório 04', 20, 1),
    ('Laboratório 05', 20, 1),
    ('Laboratório 06', 20, 1),
    ('Laboratório 07', 20, 1),
    ('Laboratório 08', 20, 1),
    ('Laboratório 09', 20, 1),
    ('Laboratório 10', 20, 1);
-- =================================================== --

-- ========================= - Insert tb_alunos - ========================= --
INSERT INTO tb_alunos (
nm_aluno,
ds_matricula,
ds_email,
ds_senha,
ds_turma,
id_escola
) VALUES
('Arthur Alves', '25213', 'arthur.paixao01@aluno.cps.sp.gov.br', '25213', '2MDS-N', 1),
('Brenno Dantas', '25099', 'brenno@gmail.com', '25099', '2MDS-N', 1),
('Bruna Nascimento', '25100', 'bpaschotto@gmail.com', '25100', '2MDS-N', 1),
('Bruno Messias', '25212', 'bruno.conceicao01@aluno.cps.sp.gov.br', '25212', '2MDS-N', 1),
('Dandara Muniz', '25072', 'dandaranavarro2@gmail.com', '25072', '2MDS-N', 1),
('Davi Santos', '25139', 'davi.araujo12@aluno.cps.sp.gov.br', '25139', '2MDS-N', 1),
('Giovanna Borges', '25208', 'borgesdesouzasantosgi@gmail.com', '25208', '2MDS-N', 1),
('Guilherme Café', '25062', 'guizitolevi@gmail.com', '25062', '2MDS-N', 1),
('Guilherme Guima', '25172', 'guilherme.oliveira142@aluno.cps.sp.gov.br', '25172', '2MDS-N', 1),
('Gustavo Alexandre', '25027', 'gustavinhoale15@gmail.com', '25027', '2MDS-N', 1),
('João Lucas', '25207', 'aura@gmail.com', '25207', '2MDS-N', 1),
('João Victor', '25182', 'joegao121@gmail.com', '25182', '2MDS-N', 1),
('Kaio Afonso', '25056', 'kaionovais27@gmail.com', '25056', '2MDS-N', 1),
('Karoliny Menezes', '26233', 'karoliny.menezess@gmail.com', '26233', '2MDS-N', 1),
('Levi Antoniasse', '25094', 'levi.antoniassi@gmail.com', '25094', '2MDS-N', 1),
('Lucas Alonso', '25283', 'lcintra2010@gmail.com', '25283', '2MDS-N', 1),
('Lucas de Lorena', '25199', 'lucaslorenalima892@gmail.com', '25199', '2MDS-N', 1),
('Lucas LK', '25093', 'lucas.costa41@aluno.cps.sp.gov.br', '25093', '2MDS-N', 1),
('Luccas Santos', '25191', 'Luccas.barbosa@aluno.cps.sp.gov.br', '25191', '2MDS-N', 1),
('Luan Mareuse', '26213', 'luan.costa4@aluno.cps.sp.gov.br', '26213', '2MDS-N', 1),
('Marco Antonio', '25105', 'marco.pinho@aluno.cps.sp.gov.br', '25105', '2MDS-N', 1),
('Matheus Higa', '25137', 'matheusrossihigaaa@gmail.com', '25137', '2MDS-N', 1),
('Matheus Santos', '25048', 'matheus.lima45@aluno.cps.sp.gov.br', '25048', '2MDS-N', 1),
('Matheus Rocha', '25090', 'matheus.rocha.silva.2010@gmail.com', '25090', '2MDS-N', 1),
('Matheus Vittoretti', '25185', 'matheus.costa35@aluno.cps.sp.gov.br', '25185', '2MDS-N', 1),
('Murilo de Oliveira', '26230', 'murilodeoliveirachaga@gmail.com', '26230', '2MDS-N', 1),
('Nicollas Roger', '25130', 'nicollas.mello01@aluno.cps.sp.gov.br', '25130', '2MDS-N', 1),
('Pedro Henrique', '25200', 'pedro.ribeiro34@aluno.cps.sp.gov.br', '25200', '2MDS-N', 1),
('Ricard Henrique', '25163', 'ricardhenriqu@gmail.com', '25163', '2MDS-N', 1),
('Talita Macedo', '25079', 'talita.oliveira9157@gmail.com', '25079', '2MDS-N', 1),
('Thiago Camilo', '25064', 'thiagodscamilo@gmail.com', '25064', '2MDS-N', 1);
-- ======================================================================== --

-- ==================== - Insert tb_professores - ==================== --
INSERT INTO tb_professores (
    nm_professor,
    ds_email,
    id_escola
)
VALUES
    ('Amauri Rodrigues', 'amauri.rodrigues@gmail.com', 1),
    ('Augusto Fabiano', 'augusto.fabiano@gmail.com', 1),
    ('Celso Aparecido', 'celso.aparecido@gmail.com', 1),
    ('Dimorie Silva', 'dimorie.silva@gmail.com', 1),
    ('Ivan dos Santos', 'ivan.santos@gmail.com', 1),
    ('José Adriano', 'jotaa@gmail.com', 1),
    ('Júlio César', 'julio.cesar@gmail.com', 1),
    ('Matheus Calixto', 'matheus.calixto@gmail.com', 1),
    ('Meire Mamede', 'meire.mamede@gmail.com', 1),
    ('Oswaldo Luiz', 'oswaldo.luiz@gmail.com', 1),
    ('Patrícia Augusto', 'patricia.augusto@gmail.com', 1),
    ('Rodrigo Ferraz', 'rodrigo.ferraz@gmail.com', 1),
    ('Willians Souza', 'willians.souza@gmail.com', 1);
-- =================================================================== --

-- ============= - Insert tb_materias - ============= --
INSERT INTO tb_materias (
    sg_materia,
    nm_materia
)
VALUES
    ('BIO', 'Biologia'),
    ('ECO', 'Ética e Cidadania Organizacional'),
    ('EF', 'Educação Física'),
    ('FIS', 'Física'),
    ('GEO', 'Geografia'),
    ('HIS', 'História'),
    ('ING', 'Inglês'),
    ('LPL', 'Língua Portuguesa'),
    ('MAT', 'Matemática'),
    ('QUI', 'Química'),
    ('BD 2', 'Banco de Dados 2'),
    ('DS', 'Desenvolvimento de Sistemas'),
    ('PAM', 'Programação de Aplicativos Mobile'),
    ('PW 2', 'Programação Web 2');
-- ================================================== --

-- ========= - Insert tb_professores_materias - ========= --
INSERT INTO tb_professores_materias (
    id_professor,
    id_materia
)
VALUES
    (7, 1),
    (4, 2),
    (13, 3),
    (3, 4),
    (12, 5),
    (5, 6),
    (11, 7),
    (9, 8),
    (1, 9),
    (6, 10),
    (10, 11),
    (8, 11),
    (8, 12),
    (10, 12),
    (8, 13),
    (2, 13),
    (8, 14),
    (10, 14);
-- ====================================================== --

-- ======== - Insert tb_computadores - ======== --
INSERT INTO tb_computadores (
    nm_computador,
    ds_status,
    id_sala
)
VALUES
    ('PC-01', 'Funcionando', 1),
    ('PC-02', 'Funcionando', 1),
    ('PC-03', 'Funcionando', 1),
    ('PC-04', 'Funcionando', 1),
    ('PC-05', 'Funcionando', 1),
    ('PC-06', 'Funcionando', 1),
    ('PC-07', 'Funcionando', 1),
    ('PC-08', 'Funcionando', 1),
    ('PC-09', 'Funcionando', 1),
    ('PC-10', 'Funcionando', 1),
    ('PC-11', 'Funcionando', 1),
    ('PC-12', 'Funcionando', 1),
    ('PC-13', 'Funcionando', 1),
    ('PC-14', 'Funcionando', 1),
    ('PC-15', 'Funcionando', 1),
    ('PC-16', 'Funcionando', 1),
    ('PC-17', 'Funcionando', 1),
    ('PC-18', 'Funcionando', 1),
    ('PC-19', 'Funcionando', 1),
    ('PC-20', 'Funcionando', 1),

    ('PC-01', 'Funcionando', 2),
    ('PC-02', 'Funcionando', 2),
    ('PC-03', 'Funcionando', 2),
    ('PC-04', 'Funcionando', 2),
    ('PC-05', 'Funcionando', 2),
    ('PC-06', 'Funcionando', 2),
    ('PC-07', 'Funcionando', 2),
    ('PC-08', 'Funcionando', 2),
    ('PC-09', 'Funcionando', 2),
    ('PC-10', 'Funcionando', 2),
    ('PC-11', 'Funcionando', 2),
    ('PC-12', 'Funcionando', 2),
    ('PC-13', 'Funcionando', 2),
    ('PC-14', 'Funcionando', 2),
    ('PC-15', 'Funcionando', 2),
    ('PC-16', 'Funcionando', 2),
    ('PC-17', 'Funcionando', 2),
    ('PC-18', 'Funcionando', 2),
    ('PC-19', 'Funcionando', 2),
    ('PC-20', 'Funcionando', 2),

    ('PC-01', 'Funcionando', 3),
    ('PC-02', 'Funcionando', 3),
    ('PC-03', 'Funcionando', 3),
    ('PC-04', 'Funcionando', 3),
    ('PC-05', 'Funcionando', 3),
    ('PC-06', 'Funcionando', 3),
    ('PC-07', 'Funcionando', 3),
    ('PC-08', 'Funcionando', 3),
    ('PC-09', 'Funcionando', 3),
    ('PC-10', 'Funcionando', 3),
    ('PC-11', 'Funcionando', 3),
    ('PC-12', 'Funcionando', 3),
    ('PC-13', 'Funcionando', 3),
    ('PC-14', 'Funcionando', 3),
    ('PC-15', 'Funcionando', 3),
    ('PC-16', 'Funcionando', 3),
    ('PC-17', 'Funcionando', 3),
    ('PC-18', 'Funcionando', 3),
    ('PC-19', 'Funcionando', 3),
    ('PC-20', 'Funcionando', 3),

    ('PC-01', 'Funcionando', 4),
    ('PC-02', 'Funcionando', 4),
    ('PC-03', 'Funcionando', 4),
    ('PC-04', 'Funcionando', 4),
    ('PC-05', 'Funcionando', 4),
    ('PC-06', 'Funcionando', 4),
    ('PC-07', 'Funcionando', 4),
    ('PC-08', 'Funcionando', 4),
    ('PC-09', 'Funcionando', 4),
    ('PC-10', 'Funcionando', 4),
    ('PC-11', 'Funcionando', 4),
    ('PC-12', 'Funcionando', 4),
    ('PC-13', 'Funcionando', 4),
    ('PC-14', 'Funcionando', 4),
    ('PC-15', 'Funcionando', 4),
    ('PC-16', 'Funcionando', 4),
    ('PC-17', 'Funcionando', 4),
    ('PC-18', 'Funcionando', 4),
    ('PC-19', 'Funcionando', 4),
    ('PC-20', 'Funcionando', 4),

    ('PC-01', 'Funcionando', 5),
    ('PC-02', 'Funcionando', 5),
    ('PC-03', 'Funcionando', 5),
    ('PC-04', 'Funcionando', 5),
    ('PC-05', 'Funcionando', 5),
    ('PC-06', 'Funcionando', 5),
    ('PC-07', 'Funcionando', 5),
    ('PC-08', 'Funcionando', 5),
    ('PC-09', 'Funcionando', 5),
    ('PC-10', 'Funcionando', 5),
    ('PC-11', 'Funcionando', 5),
    ('PC-12', 'Funcionando', 5),
    ('PC-13', 'Funcionando', 5),
    ('PC-14', 'Funcionando', 5),
    ('PC-15', 'Funcionando', 5),
    ('PC-16', 'Funcionando', 5),
    ('PC-17', 'Funcionando', 5),
    ('PC-18', 'Funcionando', 5),
    ('PC-19', 'Funcionando', 5),
    ('PC-20', 'Funcionando', 5),

    ('PC-01', 'Funcionando', 6),
    ('PC-02', 'Funcionando', 6),
    ('PC-03', 'Funcionando', 6),
    ('PC-04', 'Funcionando', 6),
    ('PC-05', 'Funcionando', 6),
    ('PC-06', 'Funcionando', 6),
    ('PC-07', 'Funcionando', 6),
    ('PC-08', 'Funcionando', 6),
    ('PC-09', 'Funcionando', 6),
    ('PC-10', 'Funcionando', 6),
    ('PC-11', 'Funcionando', 6),
    ('PC-12', 'Funcionando', 6),
    ('PC-13', 'Funcionando', 6),
    ('PC-14', 'Funcionando', 6),
    ('PC-15', 'Funcionando', 6),
    ('PC-16', 'Funcionando', 6),
    ('PC-17', 'Funcionando', 6),
    ('PC-18', 'Funcionando', 6),
    ('PC-19', 'Funcionando', 6),
    ('PC-20', 'Funcionando', 6),

    ('PC-01', 'Funcionando', 7),
    ('PC-02', 'Funcionando', 7),
    ('PC-03', 'Funcionando', 7),
    ('PC-04', 'Funcionando', 7),
    ('PC-05', 'Funcionando', 7),
    ('PC-06', 'Funcionando', 7),
    ('PC-07', 'Funcionando', 7),
    ('PC-08', 'Funcionando', 7),
    ('PC-09', 'Funcionando', 7),
    ('PC-10', 'Funcionando', 7),
    ('PC-11', 'Funcionando', 7),
    ('PC-12', 'Funcionando', 7),
    ('PC-13', 'Funcionando', 7),
    ('PC-14', 'Funcionando', 7),
    ('PC-15', 'Funcionando', 7),
    ('PC-16', 'Funcionando', 7),
    ('PC-17', 'Funcionando', 7),
    ('PC-18', 'Funcionando', 7),
    ('PC-19', 'Funcionando', 7),
    ('PC-20', 'Funcionando', 7),

    ('PC-01', 'Funcionando', 8),
    ('PC-02', 'Funcionando', 8),
    ('PC-03', 'Funcionando', 8),
    ('PC-04', 'Funcionando', 8),
    ('PC-05', 'Funcionando', 8),
    ('PC-06', 'Funcionando', 8),
    ('PC-07', 'Funcionando', 8),
    ('PC-08', 'Funcionando', 8),
    ('PC-09', 'Funcionando', 8),
    ('PC-10', 'Funcionando', 8),
    ('PC-11', 'Funcionando', 8),
    ('PC-12', 'Funcionando', 8),
    ('PC-13', 'Funcionando', 8),
    ('PC-14', 'Funcionando', 8),
    ('PC-15', 'Funcionando', 8),
    ('PC-16', 'Funcionando', 8),
    ('PC-17', 'Funcionando', 8),
    ('PC-18', 'Funcionando', 8),
    ('PC-19', 'Funcionando', 8),
    ('PC-20', 'Funcionando', 8),

    ('PC-01', 'Funcionando', 9),
    ('PC-02', 'Funcionando', 9),
    ('PC-03', 'Funcionando', 9),
    ('PC-04', 'Funcionando', 9),
    ('PC-05', 'Funcionando', 9),
    ('PC-06', 'Funcionando', 9),
    ('PC-07', 'Funcionando', 9),
    ('PC-08', 'Funcionando', 9),
    ('PC-09', 'Funcionando', 9),
    ('PC-10', 'Funcionando', 9),
    ('PC-11', 'Funcionando', 9),
    ('PC-12', 'Funcionando', 9),
    ('PC-13', 'Funcionando', 9),
    ('PC-14', 'Funcionando', 9),
    ('PC-15', 'Funcionando', 9),
    ('PC-16', 'Funcionando', 9),
    ('PC-17', 'Funcionando', 9),
    ('PC-18', 'Funcionando', 9),
    ('PC-19', 'Funcionando', 9),
    ('PC-20', 'Funcionando', 9),

    ('PC-01', 'Funcionando', 10),
    ('PC-02', 'Funcionando', 10),
    ('PC-03', 'Funcionando', 10),
    ('PC-04', 'Funcionando', 10),
    ('PC-05', 'Funcionando', 10),
    ('PC-06', 'Funcionando', 10),
    ('PC-07', 'Funcionando', 10),
    ('PC-08', 'Funcionando', 10),
    ('PC-09', 'Funcionando', 10),
    ('PC-10', 'Funcionando', 10),
    ('PC-11', 'Funcionando', 10),
    ('PC-12', 'Funcionando', 10),
    ('PC-13', 'Funcionando', 10),
    ('PC-14', 'Funcionando', 10),
    ('PC-15', 'Funcionando', 10),
    ('PC-16', 'Funcionando', 10),
    ('PC-17', 'Funcionando', 10),
    ('PC-18', 'Funcionando', 10),
    ('PC-19', 'Funcionando', 10),
    ('PC-20', 'Funcionando', 10);
-- ============================================ --

-- ================================ - Insert tb_usuarios - ================================ --
INSERT INTO tb_usuarios (
    nm_usuario,
    ds_email,
    ds_senha,
    ds_tipo,
    id_professor,
    id_aluno
) VALUES

-- Administrador
('Administrador', 'admin@systemlab.com', '123456', 'administrador', NULL, NULL),

-- Professores
('Amauri Rodrigues', 'amauri.rodrigues@gmail.com', '123456', 'professor', 1, NULL),
('Augusto Fabiano', 'augusto.fabiano@gmail.com', '123456', 'professor', 2, NULL),
('Celso Aparecido', 'celso.aparecido@gmail.com', '123456', 'professor', 3, NULL),
('Dimorie Silva', 'dimorie.silva@gmail.com', '123456', 'professor', 4, NULL),
('Ivan dos Santos', 'ivan.santos@gmail.com', '123456', 'professor', 5, NULL),
('José Adriano', 'jotaa@gmail.com', '123456', 'professor', 6, NULL),
('Júlio César', 'julio.cesar@gmail.com', '123456', 'professor', 7, NULL),
('Matheus Calixto', 'matheus.calixto@gmail.com', '123456', 'professor', 8, NULL),
('Meire Mamede', 'meire.mamede@gmail.com', '123456', 'professor', 9, NULL),
('Oswaldo Luiz', 'oswaldo.luiz@gmail.com', '123456', 'professor', 10, NULL),
('Patrícia Augusto', 'patricia.augusto@gmail.com', '123456', 'professor', 11, NULL),
('Rodrigo Ferraz', 'rodrigo.ferraz@gmail.com', '123456', 'professor', 12, NULL),
('Willians Souza', 'willians.souza@gmail.com', '123456', 'professor', 13, NULL),

-- Alunos
('Arthur Alves', 'arthur.paixao01@aluno.cps.sp.gov.br', '25213', 'aluno', NULL, 1),
('Brenno Dantas', 'brenno@gmail.com', '25099', 'aluno', NULL, 2),
('Bruna Nascimento', 'bpaschotto@gmail.com', '25100', 'aluno', NULL, 3),
('Bruno Messias', 'bruno.conceicao01@aluno.cps.sp.gov.br', '25212', 'aluno', NULL, 4),
('Dandara Muniz', 'dandaranavarro2@gmail.com', '25072', 'aluno', NULL, 5),
('Davi Santos', 'davi.araujo12@aluno.cps.sp.gov.br', '25139', 'aluno', NULL, 6),
('Giovanna Borges', 'borgesdesouzasantosgi@gmail.com', '25208', 'aluno', NULL, 7),
('Guilherme Café', 'guizitolevi@gmail.com', '25062', 'aluno', NULL, 8),
('Guilherme Guima', 'guilherme.oliveira142@aluno.cps.sp.gov.br', '25172', 'aluno', NULL, 9),
('Gustavo Alexandre', 'gustavinhoale15@gmail.com', '25027', 'aluno', NULL, 10),
('João Lucas', 'aura@gmail.com', '25207', 'aluno', NULL, 11),
('João Victor', 'joegao121@gmail.com', '25182', 'aluno', NULL, 12),
('Kaio Afonso', 'kaionovais27@gmail.com', '25056', 'aluno', NULL, 13),
('Karoliny Menezes', 'karoliny.menezess@gmail.com', '26233', 'aluno', NULL, 14),
('Levi Antoniasse', 'levi.antoniassi@gmail.com', '25094', 'aluno', NULL, 15),
('Lucas Alonso', 'lcintra2010@gmail.com', '25283', 'aluno', NULL, 16),
('Lucas de Lorena', 'lucaslorenalima892@gmail.com', '25199', 'aluno', NULL, 17),
('Lucas LK', 'lucas.costa41@aluno.cps.sp.gov.br', '25093', 'aluno', NULL, 18),
('Luccas Santos', 'Luccas.barbosa@aluno.cps.sp.gov.br', '25191', 'aluno', NULL, 19),
('Luan Mareuse', 'luan.costa4@aluno.cps.sp.gov.br', '26213', 'aluno', NULL, 20),
('Marco Antonio', 'marco.pinho@aluno.cps.sp.gov.br', '25105', 'aluno', NULL, 21),
('Matheus Higa', 'matheusrossihigaaa@gmail.com', '25137', 'aluno', NULL, 22),
('Matheus Santos', 'matheus.lima45@aluno.cps.sp.gov.br', '25048', 'aluno', NULL, 23),
('Matheus Rocha', 'matheus.rocha.silva.2010@gmail.com', '25090', 'aluno', NULL, 24),
('Matheus Vittoretti', 'matheus.costa35@aluno.cps.sp.gov.br', '25185', 'aluno', NULL, 25),
('Murilo de Oliveira', 'murilodeoliveirachaga@gmail.com', '26230', 'aluno', NULL, 26),
('Nicollas Roger', 'nicollas.mello01@aluno.cps.sp.gov.br', '25130', 'aluno', NULL, 27),
('Pedro Henrique', 'pedro.ribeiro34@aluno.cps.sp.gov.br', '25200', 'aluno', NULL, 28),
('Ricard Henrique', 'ricardhenriqu@gmail.com', '25163', 'aluno', NULL, 29),
('Talita Macedo', 'talita.oliveira9157@gmail.com', '25079', 'aluno', NULL, 30),
('Thiago Camilo', 'thiagodscamilo@gmail.com', '25064', 'aluno', NULL, 31);
-- ======================================================================================== --

-- ============= - Selects - ============= --
SELECT * FROM tb_escolas;
SELECT * FROM tb_salas;
SELECT * FROM tb_alunos;
SELECT * FROM tb_professores;
SELECT * FROM tb_materias;
SELECT * FROM tb_professores_materias;
SELECT * FROM tb_computadores;
SELECT * FROM tb_usuarios;
SELECT * FROM tb_reservas;
SELECT * FROM tb_reservas_salas;
-- ======================================= --
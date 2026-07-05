-- =======================================================
-- Criação do Banco de Dados
-- =======================================================
CREATE DATABASE IF NOT EXISTS SistemaAcademico;
USE SistemaAcademico;

-- 1. Tabela de Alunos
CREATE TABLE Alunos (
    ID_Aluno INT AUTO_INCREMENT PRIMARY KEY,
    Nome VARCHAR(150) NOT NULL,
    CPF VARCHAR(14) NOT NULL UNIQUE,
    Email VARCHAR(150) NOT NULL,
    Telefone VARCHAR(20),
    Matricula_Geral VARCHAR(50) NOT NULL UNIQUE,
    Usuario VARCHAR(50) NOT NULL UNIQUE,
    Senha VARCHAR(255) NOT NULL
);

-- 2. Tabela de Professores
CREATE TABLE Professores (
    ID_Professor INT AUTO_INCREMENT PRIMARY KEY,
    Nome VARCHAR(150) NOT NULL,
    CPF VARCHAR(14) NOT NULL UNIQUE,
    Email VARCHAR(150) NOT NULL,
    Telefone VARCHAR(20),
    Usuario VARCHAR(50) NOT NULL UNIQUE,
    Senha VARCHAR(255) NOT NULL
);

-- 3. Tabela de Cursos
CREATE TABLE Cursos (
    ID_Curso INT AUTO_INCREMENT PRIMARY KEY,
    Nome VARCHAR(150) NOT NULL,
    Carga_Horaria INT NOT NULL,
    Tipo VARCHAR(50) NOT NULL
);

-- 4. Tabela de Turmas
CREATE TABLE Turmas (
    ID_Turma INT AUTO_INCREMENT PRIMARY KEY,
    ID_Curso INT NOT NULL,
    Codigo_Turma VARCHAR(50) NOT NULL,
    Ano INT NOT NULL,
    Semestre INT NOT NULL,
    Turno VARCHAR(20) NOT NULL,
    Status VARCHAR(30) DEFAULT 'Em Andamento',
    FOREIGN KEY (ID_Curso) REFERENCES Cursos(ID_Curso) ON DELETE CASCADE
);

-- 5. Tabela de Disciplinas
CREATE TABLE Disciplinas (
    ID_Disciplina INT AUTO_INCREMENT PRIMARY KEY,
    Nome VARCHAR(150) NOT NULL,
    Carga_Horaria INT NOT NULL
);

-- 6. Tabela Intermediária: Turma + Disciplina + Professor
-- (Define quem dá qual matéria em qual turma)
CREATE TABLE Turma_Disciplina (
    ID_Turma INT NOT NULL,
    ID_Disciplina INT NOT NULL,
    ID_Professor INT NOT NULL,
    PRIMARY KEY (ID_Turma, ID_Disciplina, ID_Professor),
    FOREIGN KEY (ID_Turma) REFERENCES Turmas(ID_Turma) ON DELETE CASCADE,
    FOREIGN KEY (ID_Disciplina) REFERENCES Disciplinas(ID_Disciplina) ON DELETE CASCADE,
    FOREIGN KEY (ID_Professor) REFERENCES Professores(ID_Professor) ON DELETE CASCADE
);

-- 7. Tabela de Matrículas (Vínculo do Aluno com a Turma)
CREATE TABLE Matriculas (
    ID_Matricula INT AUTO_INCREMENT PRIMARY KEY,
    ID_Aluno INT NOT NULL,
    ID_Turma INT NOT NULL,
    Data_Matricula DATE NOT NULL,
    Status VARCHAR(30) DEFAULT 'Em Andamento',
    FOREIGN KEY (ID_Aluno) REFERENCES Alunos(ID_Aluno) ON DELETE CASCADE,
    FOREIGN KEY (ID_Turma) REFERENCES Turmas(ID_Turma) ON DELETE CASCADE
);

-- 8. Tabela de Notas (Vínculo da Avaliação do Aluno por Matéria)
CREATE TABLE Notas (
    ID_Nota INT AUTO_INCREMENT PRIMARY KEY,
    ID_Matricula INT NOT NULL,
    ID_Disciplina INT NOT NULL,
    Tipo_Avaliacao VARCHAR(100) NOT NULL,
    Valor_Nota DECIMAL(5,2) DEFAULT 0.00,
    FOREIGN KEY (ID_Matricula) REFERENCES Matriculas(ID_Matricula) ON DELETE CASCADE,
    FOREIGN KEY (ID_Disciplina) REFERENCES Disciplinas(ID_Disciplina) ON DELETE CASCADE
);

-- =======================================================
-- 9. Automações do Banco de Dados (Triggers)
-- =======================================================
DELIMITER $$

CREATE TRIGGER TRG_Matricula_Notas_Retroativas
AFTER INSERT ON Matriculas
FOR EACH ROW
BEGIN
    -- Busca todas as avaliações que já foram criadas por outros alunos nesta mesma turma
    -- e insere um registro inicializado com nota 0 para o novo aluno matriculado
    INSERT INTO Notas (ID_Matricula, ID_Disciplina, Tipo_Avaliacao, Valor_Nota)
    SELECT DISTINCT NEW.ID_Matricula, N.ID_Disciplina, N.Tipo_Avaliacao, 0.00
    FROM Notas N
    INNER JOIN Matriculas M ON N.ID_Matricula = M.ID_Matricula
    WHERE M.ID_Turma = NEW.ID_Turma;
END$$

DELIMITER ;
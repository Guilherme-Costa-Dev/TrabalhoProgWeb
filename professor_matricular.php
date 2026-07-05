<?php
session_start();
include "conexao.php";
$conexao = conectaBD();

if(!isset($_SESSION["id_professor"])){
    header("Location: login_professor.php");
    exit;
}

$mensagem = "";
if($_SERVER["REQUEST_METHOD"] == "POST") {
    $idAluno = (int)$_POST['id_aluno'];
    $idTurma = (int)$_POST['id_turma'];
    $data = date('Y-m-d');
    
    // Bug 1: Validação de segurança para evitar alunos duplicados na mesma turma
    $verifica = mysqli_query($conexao, "SELECT ID_Matricula FROM Matriculas WHERE ID_Aluno = $idAluno AND ID_Turma = $idTurma");
    
    if(mysqli_num_rows($verifica) > 0) {
        $mensagem = "<div class='alert error'>Erro: Este aluno já está matriculado nesta turma!</div>";
    } else {
        $sql = "INSERT INTO Matriculas (ID_Aluno, ID_Turma, Data_Matricula, Status) VALUES ($idAluno, $idTurma, '$data', 'Em Andamento')";
        if(mysqli_query($conexao, $sql)) {
            $mensagem = "<div class='alert success'>Aluno matriculado com sucesso!</div>";
        } else {
            $mensagem = "<div class='alert error'>Erro ao matricular: " . mysqli_error($conexao) . "</div>";
        }
    }
}

$alunos = mysqli_query($conexao, "SELECT ID_Aluno, Nome FROM Alunos");
$turmas = mysqli_query($conexao, "SELECT T.ID_Turma, T.Codigo_Turma, C.Nome AS Curso FROM Turmas T JOIN Cursos C ON T.ID_Curso = C.ID_Curso");
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Matricular Aluno</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
        body { display: flex; min-height: 100vh; background-color: #f4f6f9; color: #333; }
        .sidebar { width: 280px; background-color: #1e1e2d; color: #fff; padding: 30px 20px; display: flex; flex-direction: column; }
        .sidebar h2 { margin-bottom: 30px; font-size: 22px; color: #00d2ff; text-align: center; }
        .sidebar a { color: #a2a3b7; text-decoration: none; padding: 12px 15px; margin-bottom: 10px; border-radius: 8px; transition: 0.3s; display: block; font-weight: 500; }
        .sidebar a:hover, .sidebar a.active { background-color: #2b2b40; color: #fff; }
        .main-content { flex: 1; padding: 40px; display: flex; flex-direction: column; align-items: center; justify-content: center; }
        .card { background: #fff; padding: 40px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); width: 100%; max-width: 500px; }
        h1 { margin-bottom: 30px; color: #1e1e2d; text-align: center; }
        label { display: block; margin-bottom: 8px; font-weight: 600; color: #555; }
        select { width: 100%; padding: 12px; margin-bottom: 20px; border: 1px solid #ddd; border-radius: 8px; font-size: 15px; background: #fdfdfd; }
        button { width: 100%; background: linear-gradient(135deg, #00d2ff 0%, #3a7bd5 100%); color: white; border: none; padding: 14px; border-radius: 8px; font-weight: bold; cursor: pointer; transition: 0.3s; font-size: 16px; }
        button:hover { opacity: 0.9; box-shadow: 0 5px 15px rgba(58,123,213,0.3); }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 8px; text-align: center; font-weight: bold; }
        .success { background-color: #d4edda; color: #155724; }
        .error { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Portal Docente</h2>
        <a href="professor_home.php">Voltar ao Início</a>
        <a href="professor_matricular.php" class="active">Matricular Aluno</a>
        <a href="professor_lancar_nota.php">Lançar Notas</a>
    </div>

    <div class="main-content">
        <div class="card">
            <h1>Matricular Aluno em Turma</h1>
            <?php echo $mensagem; ?>
            <form method="POST">
                <label>Selecione o Aluno:</label>
                <select name="id_aluno" required>
                    <option value="">-- Escolha o Aluno --</option>
                    <?php while($a = mysqli_fetch_assoc($alunos)): ?>
                        <option value="<?php echo $a['ID_Aluno']; ?>"><?php echo htmlspecialchars($a['Nome']); ?></option>
                    <?php endwhile; ?>
                </select>

                <label>Selecione a Turma/Curso:</label>
                <select name="id_turma" required>
                    <option value="">-- Escolha a Turma --</option>
                    <?php while($t = mysqli_fetch_assoc($turmas)): ?>
                        <option value="<?php echo $t['ID_Turma']; ?>"><?php echo htmlspecialchars($t['Curso']) . " (" . htmlspecialchars($t['Codigo_Turma']) . ")"; ?></option>
                    <?php endwhile; ?>
                </select>

                <button type="submit">Efetivar Matrícula</button>
            </form>
        </div>
    </div>
</body>
</html>
<?php
session_start();
include "conexao.php";
$conexao = conectaBD();

if (!isset($_SESSION['id_professor'])) {
    header("Location: login_professor.php");
    exit;
}

$idProfessorLogado = $_SESSION['id_professor'];

if($_SERVER["REQUEST_METHOD"] == "POST") {
    $idTurma = (int)$_POST['id_turma'];
    $nomeDisciplina = mysqli_real_escape_string($conexao, $_POST['nome_disciplina']);
    $carga_horaria = (int)$_POST['carga_horaria'];

    $sql_disciplina = "INSERT INTO Disciplinas (Nome, Carga_Horaria) VALUES ('$nomeDisciplina', $carga_horaria)";
            
    if(mysqli_query($conexao, $sql_disciplina)) {
        $idDisciplinaCriada = mysqli_insert_id($conexao);

        $sql_vinculo = "INSERT INTO Turma_Disciplina (ID_Disciplina, ID_Turma, ID_Professor) 
                        VALUES ($idDisciplinaCriada, $idTurma, $idProfessorLogado)";

        if(mysqli_query($conexao, $sql_vinculo)) {
            echo "<script>alert('Disciplina criada e vinculada à turma com sucesso!');</script>";
        } else {
            echo "<script>alert('Disciplina criada, mas erro ao vincular à turma: " . mysqli_error($conexao) . "');</script>";
        }
    } else {
        echo "<script>alert('Erro ao criar disciplina: " . mysqli_error($conexao) . "');</script>";
    }
}

$sql_turmas = "SELECT T.ID_Turma, T.Codigo_Turma, C.Nome AS Nome_Curso 
              FROM Turmas T 
              INNER JOIN Cursos C ON T.ID_Curso = C.ID_Curso";
$resultado_turmas = mysqli_query($conexao, $sql_turmas);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Criar Nova Disciplina</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f6f9; margin: 0; }
        .navbar { background: linear-gradient(135deg, #1d098f, #104f97); padding: 15px 30px; color: white; display: flex; justify-content: space-between; align-items: center; }
        .navbar h1 { margin: 0; font-size: 20px; }
        .navbar .links a { color: white; text-decoration: none; margin-left: 15px; font-weight: bold; font-size: 14px; }
        .navbar .links a:hover { text-decoration: underline; }
        
        .container { max-width: 600px; margin: 40px auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        h2 { color: #1d098f; margin-top: 0; margin-bottom: 20px; border-bottom: 2px solid #eee; padding-bottom: 10px; text-align: center; }
        
        label { display: block; font-weight: bold; margin-bottom: 8px; color: #444; }
        input, select { width: 100%; padding: 12px; margin-bottom: 20px; border-radius: 6px; border: 1px solid #ccc; box-sizing: border-box; font-size: 15px; }
        input:focus, select:focus { border-color: #1d098f; outline: none; }
        
        button { width: 100%; padding: 12px; background: #1d098f; color: white; border: none; font-weight: bold; cursor: pointer; border-radius: 6px; font-size: 15px; transition: 0.3s; }
        button:hover { background: #150769; }
    </style>
</head>
<body>

    <div class="navbar">
        <h1>Painel do Professor</h1>
        <div class="links">
            <a href="professor_home.php">Voltar ao Início</a>
            <a href="logout.php">Sair</a>
        </div>
    </div>

    <div class="container">
        <h2>Criar Nova Disciplina</h2>
        <form method="POST">
            <label>Selecione a Turma Alvo:</label>
            <select name="id_turma" required>
                <option value="">-- Escolha a Turma --</option>
                <?php while($t = mysqli_fetch_assoc($resultado_turmas)): ?>
                    <option value="<?php echo $t['ID_Turma']; ?>">
                        <?php echo htmlspecialchars($t['Nome_Curso'] . " - " . $t['Codigo_Turma']); ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label>Nome da Disciplina:</label>
            <input type="text" name="nome_disciplina" placeholder="Ex: Banco de Dados II" required>

            <label>Carga Horária (em horas):</label>
            <input type="number" name="carga_horaria" placeholder="Ex: 80" required>

            <button type="submit">Vincular e Criar</button>
        </form>
    </div>

</body>
</html>
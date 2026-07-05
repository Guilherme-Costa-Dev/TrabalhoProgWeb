<?php
session_start();
include "conexao.php";
$conexao = conectaBD();

if (!isset($_SESSION['id_professor'])) {
    header("Location: login_professor.php");
    exit;
}

if($_SERVER["REQUEST_METHOD"] == "POST") {
    $idCurso = (int)$_POST['id_curso'];
    $codigoTurma = mysqli_real_escape_string($conexao, $_POST['codigo_turma']);
    $ano = (int)$_POST['ano'];
    $semestre = (int)$_POST['semestre'];
    $turno = mysqli_real_escape_string($conexao, $_POST['turno']);

    $sql = "INSERT INTO Turmas (ID_Curso, Codigo_Turma, Ano, Semestre, Turno) 
            VALUES ($idCurso, '$codigoTurma', $ano, $semestre, '$turno')";
            
    if(mysqli_query($conexao, $sql)) {
        echo "<script>alert('Turma criada com sucesso!');</script>";
    } else {
        echo "<script>alert('Erro ao criar turma: " . mysqli_error($conexao) . "');</script>";
    }
}

$sql_cursos = "SELECT ID_Curso, Nome FROM Cursos";
$resultado_cursos = mysqli_query($conexao, $sql_cursos);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Criar Nova Turma</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f6f9; margin: 0; }
        .navbar { background: linear-gradient(135deg, #1d098f, #104f97); padding: 15px 30px; color: white; display: flex; justify-content: space-between; align-items: center; }
        .navbar h1 { margin: 0; font-size: 20px; }
        .navbar .links a { color: white; text-decoration: none; margin-left: 15px; font-weight: bold; font-size: 14px; }
        .navbar .links a:hover { text-decoration: underline; }
        
        .container { max-width: 600px; margin: 40px auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        h2 { color: #1d098f; margin-top: 0; margin-bottom: 20px; border-bottom: 2px solid #eee; padding-bottom: 10px; text-align: center; }
        
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
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
        <h2>Criar Nova Turma</h2>
        <form method="POST">
            <label>Selecione o Curso Base:</label>
            <select name="id_curso" required>
                <option value="">-- Escolha o Curso --</option>
                <?php while($c = mysqli_fetch_assoc($resultado_cursos)): ?>
                    <option value="<?php echo $c['ID_Curso']; ?>">
                        <?php echo htmlspecialchars($c['Nome']); ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label>Código/Nome da Turma:</label>
            <input type="text" name="codigo_turma" placeholder="Ex: INF-2026-1M" required>

            <div class="grid-2">
                <div>
                    <label>Ano Letivo:</label>
                    <input type="number" name="ano" value="<?php echo date('Y'); ?>" required>
                </div>
                <div>
                    <label>Semestre:</label>
                    <select name="semestre" required>
                        <option value="1">1º Semestre</option>
                        <option value="2">2º Semestre</option>
                    </select>
                </div>
            </div>

            <label>Turno:</label>
            <select name="turno" required>
                <option value="Matutino">Matutino</option>
                <option value="Vespertino">Vespertino</option>
                <option value="Noturno">Noturno</option>
            </select>

            <button type="submit">Salvar Turma</button>
        </form>
    </div>

</body>
</html>
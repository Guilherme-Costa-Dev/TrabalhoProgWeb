<?php
session_start();
include "conexao.php";
$conexao = conectaBD();

if (!isset($_SESSION['id_professor'])) {
    header("Location: login_professor.php");
    exit;
}

if($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = mysqli_real_escape_string($conexao, $_POST['nome']);
    $carga_horaria = (int)$_POST['carga_horaria'];
    $tipo = mysqli_real_escape_string($conexao, $_POST['tipo']);

    $sql = "INSERT INTO Cursos (Nome, Carga_Horaria, Tipo) VALUES ('$nome', $carga_horaria, '$tipo')";
            
    if(mysqli_query($conexao, $sql)) {
        echo "<script>alert('Curso criado com sucesso!');</script>";
    } else {
        echo "<script>alert('Erro ao criar curso: " . mysqli_error($conexao) . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Criar Novo Curso</title>
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
        <h2>Criar Novo Curso</h2>
        <form method="POST">
            <label>Nome do Curso:</label>
            <input type="text" name="nome" placeholder="Ex: Técnico em Informática" required>

            <label>Carga Horária (em horas):</label>
            <input type="number" name="carga_horaria" placeholder="Ex: 1200" required>

            <label>Tipo de Curso:</label>
            <select name="tipo" required>
                <option value="">-- Selecione o Tipo --</option>
                <option value="Técnico">Ensino Técnico</option>
                <option value="Graduação">Graduação Superior</option>
                <option value="Pós-Graduação">Pós-Graduação / Especialização</option>
                <option value="Extensão">Curso de Extensão</option>
            </select>

            <button type="submit">Salvar Curso</button>
        </form>
    </div>

</body>
</html>
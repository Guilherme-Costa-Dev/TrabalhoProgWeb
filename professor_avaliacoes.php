<?php
session_start();
include "conexao.php";
$conexao = conectaBD();

if(!isset($_SESSION["id_professor"])){
    header("Location: login_professor.php");
    exit;
}

$idProfessor = $_SESSION["id_professor"];

if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['acao_criar'])) {
    $idTurma = (int)$_POST['id_turma_criar'];
    $idDisciplina = (int)$_POST['id_disciplina_criar'];
    $avaliacao = mysqli_real_escape_string($conexao, $_POST['avaliacao_nome']);

    $sql_alunos = "SELECT ID_Matricula FROM Matriculas WHERE ID_Turma = $idTurma";
    $resultado_alunos = mysqli_query($conexao, $sql_alunos);

    if(mysqli_num_rows($resultado_alunos) > 0) {
        $sucesso = true;
        while($aluno = mysqli_fetch_assoc($resultado_alunos)) {
            $idMatricula = $aluno['ID_Matricula'];
            $sql_nota_zero = "INSERT INTO Notas (ID_Matricula, ID_Disciplina, Valor_Nota, Tipo_Avaliacao) 
                              VALUES ($idMatricula, $idDisciplina, 0.00, '$avaliacao')";
            if(!mysqli_query($conexao, $sql_nota_zero)) {
                $sucesso = false;
            }
        }
        if($sucesso) {
            echo "<script>alert('Avaliação criada para todos os alunos!'); window.location.href='professor_avaliacoes.php';</script>";
        }
    } else {
        echo "<script>alert('Aviso: Turma sem alunos, mas a estrutura foi mantida.');</script>";
    }
}

if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['acao_excluir'])) {
    $idTurma = (int)$_POST['id_turma_excluir'];
    $idDisciplina = (int)$_POST['id_disciplina_excluir'];
    $avaliacao = mysqli_real_escape_string($conexao, $_POST['id_avaliacao_excluir']);

    $sql_del = "DELETE FROM Notas 
                WHERE ID_Disciplina = $idDisciplina 
                AND Tipo_Avaliacao = '$avaliacao' 
                AND ID_Matricula IN (SELECT ID_Matricula FROM Matriculas WHERE ID_Turma = $idTurma)";
    
    if(mysqli_query($conexao, $sql_del)) {
        echo "<script>alert('Avaliação excluída com sucesso!'); window.location.href='professor_avaliacoes.php';</script>";
    }
}

// Queries para os selects
$sql_turmas = "SELECT DISTINCT T.ID_Turma, T.Codigo_Turma, C.Nome AS Nome_Curso 
               FROM Turma_Disciplina TD INNER JOIN Turmas T ON TD.ID_Turma = T.ID_Turma 
               INNER JOIN Cursos C ON T.ID_Curso = C.ID_Curso WHERE TD.ID_Professor = $idProfessor";
$res_turmas_criar = mysqli_query($conexao, $sql_turmas);
$res_turmas_excluir = mysqli_query($conexao, $sql_turmas);

$sql_disc = "SELECT TD.ID_Turma, TD.ID_Disciplina, D.Nome AS Nome_Disciplina FROM Turma_Disciplina TD INNER JOIN Disciplinas D ON TD.ID_Disciplina = D.ID_Disciplina WHERE TD.ID_Professor = $idProfessor";
$res_disc = mysqli_query($conexao, $sql_disc);
$arr_disc = []; while($d = mysqli_fetch_assoc($res_disc)) { $arr_disc[] = $d; }

$sql_aval = "SELECT DISTINCT M.ID_Turma, N.ID_Disciplina, D.Nome AS Nome_Disciplina, N.Tipo_Avaliacao FROM Notas N INNER JOIN Disciplinas D ON N.ID_Disciplina = D.ID_Disciplina INNER JOIN Matriculas M ON N.ID_Matricula = M.ID_Matricula INNER JOIN Turma_Disciplina TD ON M.ID_Turma = TD.ID_Turma AND N.ID_Disciplina = TD.ID_Disciplina WHERE TD.ID_Professor = $idProfessor";
$res_aval = mysqli_query($conexao, $sql_aval);
$arr_aval = []; while($av = mysqli_fetch_assoc($res_aval)) { $arr_aval[] = $av; }
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gerenciar Avaliações</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f6f9; margin: 0; }
        .navbar { background: linear-gradient(135deg, #1d098f, #104f97); padding: 15px 30px; color: white; display: flex; justify-content: space-between; align-items: center; }
        .navbar h1 { margin: 0; font-size: 20px; }
        .navbar .links a { color: white; text-decoration: none; margin-left: 15px; font-weight: bold; font-size: 14px; }
        .navbar .links a:hover { text-decoration: underline; }
        
        .container { max-width: 900px; margin: 40px auto; display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
        .box { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        h2 { color: #1d098f; margin-top: 0; margin-bottom: 20px; border-bottom: 2px solid #eee; padding-bottom: 10px; text-align: center; font-size: 18px; }
        
        label { display: block; font-weight: bold; margin-bottom: 8px; color: #444; }
        input, select { width: 100%; padding: 12px; margin-bottom: 20px; border-radius: 6px; border: 1px solid #ccc; box-sizing: border-box; font-size: 15px; }
        input:focus, select:focus { border-color: #1d098f; outline: none; }
        
        button { width: 100%; padding: 12px; background: #1d098f; color: white; border: none; font-weight: bold; cursor: pointer; border-radius: 6px; font-size: 15px; transition: 0.3s; }
        button:hover { background: #150769; }
        .btn-danger { background: #e63946; }
        .btn-danger:hover { background: #c32f3a; }
    </style>
    <script>
        const dadosDisciplinas = <?php echo json_encode($arr_disc); ?>;
        const dadosExcluir = <?php echo json_encode($arr_aval); ?>;

        function carregarDisciplinasCriar() {
            const idTurma = document.getElementById('id_turma_criar').value;
            const selectDisc = document.getElementById('id_disciplina_criar');
            selectDisc.innerHTML = '<option value="">-- Escolha a Disciplina --</option>';
            if(!idTurma) { selectDisc.disabled = true; return; }
            dadosDisciplinas.filter(i => i.ID_Turma == idTurma).forEach(i => {
                selectDisc.innerHTML += `<option value="${i.ID_Disciplina}">${i.Nome_Disciplina}</option>`;
            });
            selectDisc.disabled = false;
        }

        function carregarDisciplinasExcluir() {
            const idTurma = document.getElementById('id_turma_excluir').value;
            const selectDisc = document.getElementById('id_disciplina_excluir');
            selectDisc.innerHTML = '<option value="">-- Escolha a Disciplina --</option>';
            document.getElementById('id_avaliacao_excluir').disabled = true;
            if(!idTurma) { selectDisc.disabled = true; return; }
            const vistos = new Map();
            dadosExcluir.filter(i => i.ID_Turma == idTurma).forEach(i => vistos.set(i.ID_Disciplina, i.Nome_Disciplina));
            vistos.forEach((nome, id) => {
                selectDisc.innerHTML += `<option value="${id}">${nome}</option>`;
            });
            selectDisc.disabled = false;
        }

        function carregarAvaliacoesExcluir() {
            const idTurma = document.getElementById('id_turma_excluir').value;
            const idDisc = document.getElementById('id_disciplina_excluir').value;
            const selectAval = document.getElementById('id_avaliacao_excluir');
            selectAval.innerHTML = '<option value="">-- Escolha a Avaliação --</option>';
            if(!idDisc) { selectAval.disabled = true; return; }
            dadosExcluir.filter(i => i.ID_Turma == idTurma && i.ID_Disciplina == idDisc).forEach(i => {
                selectAval.innerHTML += `<option value="${i.Tipo_Avaliacao}">${i.Tipo_Avaliacao}</option>`;
            });
            selectAval.disabled = false;
        }
    </script>
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
        <div class="box">
            <h2>➕ Adicionar Avaliação</h2>
            <form method="POST">
                <input type="hidden" name="acao_criar" value="1">
                <label>Turma:</label>
                <select name="id_turma_criar" id="id_turma_criar" onchange="carregarDisciplinasCriar()" required>
                    <option value="">-- Escolha --</option>
                    <?php mysqli_data_seek($res_turmas_criar, 0); while($t = mysqli_fetch_assoc($res_turmas_criar)): ?>
                        <option value="<?php echo $t['ID_Turma']; ?>"><?php echo htmlspecialchars($t['Codigo_Turma']); ?></option>
                    <?php endwhile; ?>
                </select>

                <label>Disciplina:</label>
                <select name="id_disciplina_criar" id="id_disciplina_criar" disabled required>
                    <option value="">-- Escolha --</option>
                </select>

                <label>Nome da Avaliação:</label>
                <input type="text" name="avaliacao_nome" placeholder="Ex: Prova 1" required>

                <button type="submit">Criar Avaliação</button>
            </form>
        </div>

        <div class="box">
            <h2>❌ Remover Avaliação</h2>
            <form method="POST" onsubmit="return confirm('Apagar esta avaliação removerá as notas de todos os alunos. Continuar?');">
                <input type="hidden" name="acao_excluir" value="1">
                <label>Turma:</label>
                <select name="id_turma_excluir" id="id_turma_excluir" onchange="carregarDisciplinasExcluir()" required>
                    <option value="">-- Escolha --</option>
                    <?php mysqli_data_seek($res_turmas_excluir, 0); while($t = mysqli_fetch_assoc($res_turmas_excluir)): ?>
                        <option value="<?php echo $t['ID_Turma']; ?>"><?php echo htmlspecialchars($t['Codigo_Turma']); ?></option>
                    <?php endwhile; ?>
                </select>

                <label>Disciplina:</label>
                <select name="id_disciplina_excluir" id="id_disciplina_excluir" onchange="carregarAvaliacoesExcluir()" disabled required>
                    <option value="">-- Escolha --</option>
                </select>

                <label>Avaliação:</label>
                <select name="id_avaliacao_excluir" id="id_avaliacao_excluir" disabled required>
                    <option value="">-- Escolha --</option>
                </select>

                <button type="submit" class="btn-danger">Excluir Avaliação</button>
            </form>
        </div>
    </div>

</body>
</html>
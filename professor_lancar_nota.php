<?php
session_start();
include "conexao.php";
$conexao = conectaBD();

if(!isset($_SESSION["id_professor"])){
    header("Location: login_professor.php");
    exit;
}

$idProfessor = $_SESSION["id_professor"];
$mensagem = "";

if($_SERVER["REQUEST_METHOD"] == "POST") {
    $idMatricula = (int)$_POST['id_matricula'];
    $idDisciplina = (int)$_POST['id_disciplina'];
    $avaliacao = mysqli_real_escape_string($conexao, $_POST['id_avaliacao']); 
    $novaNota = (float)$_POST['nota'];

    $verifica = mysqli_query($conexao, "SELECT * FROM Notas WHERE ID_Matricula = $idMatricula AND ID_Disciplina = $idDisciplina AND Tipo_Avaliacao = '$avaliacao'");
            
    if(mysqli_num_rows($verifica) > 0) {
        $sql_exec = "UPDATE Notas SET Valor_Nota = $novaNota WHERE ID_Matricula = $idMatricula AND ID_Disciplina = $idDisciplina AND Tipo_Avaliacao = '$avaliacao'";
    } else {
        $sql_exec = "INSERT INTO Notas (ID_Matricula, ID_Disciplina, Tipo_Avaliacao, Valor_Nota) VALUES ($idMatricula, $idDisciplina, '$avaliacao', $novaNota)";
    }
            
    if(mysqli_query($conexao, $sql_exec)) {
        $mensagem = "<div class='alert success'>Nota salva com sucesso!</div>";
    } else {
        $mensagem = "<div class='alert error'>Erro ao salvar nota: " . mysqli_error($conexao) . "</div>";
    }
}

$sql_notas = "SELECT M.ID_Matricula, A.Nome AS Aluno, T.ID_Turma, T.Codigo_Turma, C.Nome AS Nome_Curso,
                     D.ID_Disciplina, D.Nome AS Nome_Disciplina, N.Tipo_Avaliacao, N.Valor_Nota
              FROM Notas N
              INNER JOIN Matriculas M ON N.ID_Matricula = M.ID_Matricula
              INNER JOIN Alunos A ON M.ID_Aluno = A.ID_Aluno
              INNER JOIN Turmas T ON M.ID_Turma = T.ID_Turma
              INNER JOIN Cursos C ON T.ID_Curso = C.ID_Curso
              INNER JOIN Disciplinas D ON N.ID_Disciplina = D.ID_Disciplina
              INNER JOIN Turma_Disciplina TD ON T.ID_Turma = TD.ID_Turma AND D.ID_Disciplina = TD.ID_Disciplina
              WHERE TD.ID_Professor = $idProfessor";
                   
$resultado_notas = mysqli_query($conexao, $sql_notas);
$dados_completos = [];
$turmas_unicas = [];

while($row = mysqli_fetch_assoc($resultado_notas)) {
    $dados_completos[] = $row;
    $turmas_unicas[$row['ID_Turma']] = "Turma " . $row['Codigo_Turma'] . " (" . $row['Nome_Curso'] . ")";
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <script src="ajax.js"></script>
    <meta charset="UTF-8">
    <title>Lançar Notas</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
        body { display: flex; min-height: 100vh; background-color: #f4f6f9; color: #333; }
        .sidebar { width: 280px; background-color: #1e1e2d; color: #fff; padding: 30px 20px; display: flex; flex-direction: column; }
        .sidebar h2 { margin-bottom: 30px; font-size: 22px; color: #00d2ff; text-align: center; }
        .sidebar a { color: #a2a3b7; text-decoration: none; padding: 12px 15px; margin-bottom: 10px; border-radius: 8px; transition: 0.3s; display: block; font-weight: 500; }
        .sidebar a:hover, .sidebar a.active { background-color: #2b2b40; color: #fff; }
        .main-content { flex: 1; padding: 40px; display: flex; justify-content: center; align-items: flex-start; overflow-y: auto; }
        .card { background: #fff; padding: 40px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); width: 100%; max-width: 600px; margin-top: 20px; }
        h1 { margin-bottom: 30px; color: #1e1e2d; text-align: center; }
        label { display: block; margin-bottom: 8px; font-weight: 600; color: #555; }
        select, input { width: 100%; padding: 12px; margin-bottom: 20px; border: 1px solid #ddd; border-radius: 8px; font-size: 15px; background: #fdfdfd; }
        button { width: 100%; background: linear-gradient(135deg, #00d2ff 0%, #3a7bd5 100%); color: white; border: none; padding: 14px; border-radius: 8px; font-weight: bold; cursor: pointer; transition: 0.3s; font-size: 16px; margin-top: 10px; }
        button:hover { opacity: 0.9; box-shadow: 0 5px 15px rgba(58,123,213,0.3); }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 8px; text-align: center; font-weight: bold; }
        .success { background-color: #d4edda; color: #155724; }
        .error { background-color: #f8d7da; color: #721c24; }
        .nota-atual { background: #e8f5e9; color: #2e7d32; padding: 10px; border-radius: 6px; font-weight: bold; margin-bottom: 20px; text-align: center; display: none; border: 1px solid #c8e6c9; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Portal Docente</h2>
        <a href="professor_home.php">Voltar ao Início</a>
        <a href="professor_matricular.php">Matricular Aluno</a>
        <a href="professor_lancar_nota.php" class="active">Lançar Notas</a>
    </div>

    <div class="main-content">
        <div class="card">
            <h1>Gestão de Notas</h1>
            <div id="mensagem"></div>
            <form id="formNotas">
                <label>1. Selecione a Turma:</label>
                <select name="id_turma" id="id_turma" onchange="filtrarAlunos()" required>
                    <option value="">-- Escolha a Turma --</option>
                    <?php foreach($turmas_unicas as $idTurma => $nomeTurma): ?>
                        <option value="<?php echo $idTurma; ?>"><?php echo htmlspecialchars($nomeTurma); ?></option>
                    <?php endforeach; ?>
                </select>

                <label>2. Selecione o Aluno:</label>
                <select name="id_matricula" id="id_matricula" onchange="filtrarDisciplinas()" required disabled>
                    <option value="">-- Selecione primeiro a Turma --</option>
                </select>

                <label>3. Selecione a Disciplina:</label>
                <select name="id_disciplina" id="id_disciplina" onchange="filtrarAvaliacoes()" required disabled>
                    <option value="">-- Selecione primeiro o Aluno --</option>
                </select>

                <label>4. Selecione a Avaliação:</label>
                <select name="id_avaliacao" id="id_avaliacao" onchange="mostrarNotaAtual()" required disabled>
                    <option value="">-- Selecione primeiro a Disciplina --</option>
                </select>

                <div id="txt_nota_atual" class="nota-atual">Nota atual no sistema: 0.00</div>

                <label>5. Digite a Nova Nota (0 a 100):</label>
                <input type="number" step="0.01" min="0" max="100" name="nota" id="nota" placeholder="0.00" required>

                <button type="button" onclick="salvarNota()">Salvar Nota</button>
            </form>
        </div>
    </div>

    <script>
    const bancoDeDados = <?php echo json_encode($dados_completos); ?>;

    function filtrarAlunos() {
        const idTurma = document.getElementById('id_turma').value;
        const selectAluno = document.getElementById('id_matricula');
        
        selectAluno.innerHTML = '<option value="">-- Escolha o Aluno --</option>';
        document.getElementById('id_disciplina').innerHTML = '<option value="">-- Selecione primeiro o Aluno --</option>';
        document.getElementById('id_disciplina').disabled = true;
        document.getElementById('id_avaliacao').innerHTML = '<option value="">-- Selecione primeiro a Disciplina --</option>';
        document.getElementById('id_avaliacao').disabled = true;
        document.getElementById('txt_nota_atual').style.display = "none";

        if(idTurma === "") { selectAluno.disabled = true; return; }

        const filtrados = bancoDeDados.filter(item => item.ID_Turma == idTurma);
        const vistos = new Map();
        filtrados.forEach(item => vistos.set(item.ID_Matricula, item.Aluno));

        vistos.forEach((nome, idMatricula) => {
            let opt = document.createElement('option'); opt.value = idMatricula; opt.textContent = nome; selectAluno.appendChild(opt);
        });
        selectAluno.disabled = false;
    }

    function filtrarDisciplinas() {
        const idTurma = document.getElementById('id_turma').value;
        const idMatricula = document.getElementById('id_matricula').value;
        const selectDisc = document.getElementById('id_disciplina');

        selectDisc.innerHTML = '<option value="">-- Escolha a Disciplina --</option>';
        document.getElementById('id_avaliacao').innerHTML = '<option value="">-- Selecione primeiro a Disciplina --</option>';
        document.getElementById('id_avaliacao').disabled = true;
        document.getElementById('txt_nota_atual').style.display = "none";

        if(idMatricula === "") { selectDisc.disabled = true; return; }

        const filtrados = bancoDeDados.filter(item => item.ID_Turma == idTurma && item.ID_Matricula == idMatricula);
        const vistos = new Map();
        filtrados.forEach(item => vistos.set(item.ID_Disciplina, item.Nome_Disciplina));

        vistos.forEach((nome, idDisc) => {
            let opt = document.createElement('option'); opt.value = idDisc; opt.textContent = nome; selectDisc.appendChild(opt);
        });
        selectDisc.disabled = false;
    }

    function filtrarAvaliacoes() {
        const idTurma = document.getElementById('id_turma').value;
        const idMatricula = document.getElementById('id_matricula').value;
        const idDisciplina = document.getElementById('id_disciplina').value;
        const selectAval = document.getElementById('id_avaliacao');

        selectAval.innerHTML = '<option value="">-- Escolha a Avaliação --</option>';
        document.getElementById('txt_nota_atual').style.display = "none";

        if(idDisciplina === "") { selectAval.disabled = true; return; }

        const filtrados = bancoDeDados.filter(item => item.ID_Turma == idTurma && item.ID_Matricula == idMatricula && item.ID_Disciplina == idDisciplina);

        filtrados.forEach(item => {
            let opt = document.createElement('option'); opt.value = item.Tipo_Avaliacao; opt.textContent = item.Tipo_Avaliacao; selectAval.appendChild(opt);
        });
        selectAval.disabled = false;
    }

    function mostrarNotaAtual() {
        const idTurma = document.getElementById('id_turma').value;
        const idMatricula = document.getElementById('id_matricula').value;
        const idDisciplina = document.getElementById('id_disciplina').value;
        const avaliacao = document.getElementById('id_avaliacao').value;
        const txtNota = document.getElementById('txt_nota_atual');
        const inputNota = document.getElementById('nota');

        if(avaliacao === "") { txtNota.style.display = "none"; return; }

        const registro = bancoDeDados.find(item => item.ID_Turma == idTurma && item.ID_Matricula == idMatricula && item.ID_Disciplina == idDisciplina && item.Tipo_Avaliacao === avaliacao);

        if(registro) {
            txtNota.textContent = "Nota atual no sistema: " + registro.Valor_Nota;
            txtNota.style.display = "block";
            inputNota.value = registro.Valor_Nota;
        }
    }

    function salvarNota(){

        let matricula =
        document.getElementById(
        "id_matricula"
        ).value;

        let disciplina =
        document.getElementById(
        "id_disciplina"
        ).value;

        let avaliacao =
        document.getElementById(
        "id_avaliacao"
        ).value;

        let nota =
        document.getElementById(
        "nota"
        ).value;


        usaAjax(

            "salvar_nota_ajax.php?"

            + "id_matricula=" + matricula

            + "&id_disciplina=" + disciplina

            + "&id_avaliacao=" + avaliacao

            + "&nota=" + nota,

            "mensagem"
        );
        
        // Atualiza a nota no bancoDeDados sem recarregar

        const registro = bancoDeDados.find(item =>

            item.ID_Matricula == matricula &&

            item.ID_Disciplina == disciplina &&

            item.Tipo_Avaliacao == avaliacao
        );

        if(registro){

            registro.Valor_Nota = nota;

        }


        // Atualiza texto da nota atual

        document.getElementById(
        "txt_nota_atual"
        ).textContent =

        "Nota atual no sistema: " + nota;

        document.getElementById(
        "txt_nota_atual"
        ).style.display = "block";
        
    }
    </script>
</body>
</html>
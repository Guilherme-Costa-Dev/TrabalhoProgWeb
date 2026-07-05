<?php
session_start();
include "conexao.php";
$conexao = conectaBD();

// Proteção: Garante que apenas professores logados acessem
if(!isset($_SESSION["id_professor"])){
    header("Location: login_professor.php");
    exit;
}

$idProfessor = $_SESSION["id_professor"];

if($_SERVER["REQUEST_METHOD"] == "POST") {
    $idTurma = (int)$_POST['id_turma'];
    $idDisciplina = (int)$_POST['id_disciplina'];
    $avaliacao = mysqli_real_escape_string($conexao, $_POST['avaliacao']);

    // 1. Busca todas as matrículas ativas pertencentes a esta turma
    $sql_alunos = "SELECT ID_Matricula FROM Matriculas WHERE ID_Turma = $idTurma";
    $resultado_alunos = mysqli_query($conexao, $sql_alunos);

    if(mysqli_num_rows($resultado_alunos) > 0) {
        $sucesso = true;
        $totalAlunos = 0;

        // 2. Loop para inserir a avaliação com nota 0.00 para cada aluno da turma
        while($aluno = mysqli_fetch_assoc($resultado_alunos)) {
            $idMatricula = $aluno['ID_Matricula'];
            
            $sql_nota_zero = "INSERT INTO Notas (ID_Matricula, ID_Disciplina, Valor_Nota, Tipo_Avaliacao) 
                              VALUES ($idMatricula, $idDisciplina, 0.00, '$avaliacao')";
            
            if(!mysqli_query($conexao, $sql_nota_zero)) {
                $sucesso = false;
                break;
            }
            $totalAlunos++;
        }

        if($sucesso) {
            echo "<script>alert('Avaliação \"$avaliacao\" criada com sucesso! $totalAlunos alunos foram inicializados com nota 0.00.'); window.location.href='professor_home.php';</script>";
        } else {
            echo "<script>alert('Erro ao gerar as notas zeradas: " . mysqli_error($conexao) . "');</script>";
        }
    } else {
        echo "<script>alert('Aviso: Não existem alunos matriculados nesta turma para receberem a avaliação.');</script>";
    }
}

// Busca as turmas e as disciplinas associadas a este professor para preencher os seletores dinâmicos
$sql_dados = "SELECT T.ID_Turma, T.Codigo_Turma, C.Nome AS Nome_Curso, D.ID_Disciplina, D.Nome AS Nome_Disciplina
              FROM Turma_Disciplina TD
              INNER JOIN Turmas T ON TD.ID_Turma = T.ID_Turma
              INNER JOIN Cursos C ON T.ID_Curso = C.ID_Curso
              INNER JOIN Disciplinas D ON TD.ID_Disciplina = D.ID_Disciplina
              WHERE TD.ID_Professor = $idProfessor";
$resultado_dados = mysqli_query($conexao, $sql_dados);

$dados_hierarquia = [];
$turmas_unicas = [];

while($row = mysqli_fetch_assoc($resultado_dados)) {
    $dados_hierarquia[] = $row;
    $turmas_unicas[$row['ID_Turma']] = "Turma " . $row['Codigo_Turma'] . " (" . $row['Nome_Curso'] . ")";
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Criar Nova Avaliação</title>
    <style>
        body { font-family: Arial, sans-serif; background: linear-gradient(135deg,#1d098f,#104f97); min-height: 100vh; display: flex; justify-content: center; align-items: center; margin: 0; }
        .box { background: white; padding: 30px; border-radius: 12px; width: 480px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); }
        h2 { color: #1d098f; margin-bottom: 20px; text-align: center; }
        select, input, button { width: 100%; padding: 12px; margin-bottom: 15px; border-radius: 6px; border: 1px solid #ccc; box-sizing: border-box; font-size: 14px; }
        button { background: #e0a800; color: #111; border: none; font-weight: bold; cursor: pointer; transition: 0.3s; }
        button:hover { background: #c69500; }
        .voltar { text-align: center; display: block; color: #333; text-decoration: none; margin-top: 10px; }
        label { font-weight: bold; display: block; margin-bottom: 5px; color: #333; }
    </style>
</head>
<body>
<div class="box">
    <h2>Criar Nova Avaliação</h2>
    <form method="POST">
        
        <label for="id_turma">1. Selecione o Curso / Turma Alvo:</label>
        <select name="id_turma" id="id_turma" onchange="carregarMaterias()" required>
            <option value="">-- Escolha o Curso e a Turma --</option>
            <?php foreach($turmas_unicas as $idTurma => $textoTurma): ?>
                <option value="<?php echo $idTurma; ?>"><?php echo htmlspecialchars($textoTurma); ?></option>
            <?php endforeach; ?>
        </select>

        <label for="id_disciplina">2. Selecione a Disciplina:</label>
        <select name="id_disciplina" id="id_disciplina" required disabled>
            <option value="">-- Selecione primeiro a Turma --</option>
        </select>

        <label for="avaliacao">3. Nome da Avaliação:</label>
        <input type="text" name="avaliacao" id="avaliacao" placeholder="Ex: Prova Parcial I, Trabalho de Campo" required>

        <button type="submit">Lançar Avaliação</button>
    </form>
    <a href="professor_home.php" class="voltar">Voltar para Home</a>
</div>

<script>
// Dados vindos do Banco passados ao JavaScript
const dadosCompletos = <?php echo json_encode($dados_hierarquia); ?>;

function carregarMaterias() {
    const idTurmaSelecionada = document.getElementById('id_turma').value;
    const selectDisciplina = document.getElementById('id_disciplina');

    // Limpa o select de disciplinas
    selectDisciplina.innerHTML = '<option value="">-- Escolha a Disciplina --</option>';

    if(idTurmaSelecionada === "") {
        selectDisciplina.disabled = true;
        return;
    }

    // Filtra as matérias que pertencem àquela turma específica no perfil do professor
    const materiasFiltradas = dadosCompletos.filter(item => item.ID_Turma == idTurmaSelecionada);

    // Remove duplicados de disciplinas por ID usando um Map
    const mapeamentoMaterias = new Map();
    materiasFiltradas.forEach(item => {
        mapeamentoMaterias.set(item.ID_Disciplina, item.Nome_Disciplina);
    });

    // Popula o select de disciplinas
    mapeamentoMaterias.forEach((nomeMataria, idMateria) => {
        let opt = document.createElement('option');
        opt.value = idMateria;
        opt.textContent = nomeMataria;
        selectDisciplina.appendChild(opt);
    });

    selectDisciplina.disabled = false;
}
</script>
</body>
</html>
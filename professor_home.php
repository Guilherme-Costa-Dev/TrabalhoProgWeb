<?php
session_start();
include "conexao.php"; 
$conexao = conectaBD();

if(!isset($_SESSION["id_professor"])){
    header("Location: login_professor.php");
    exit;
}

$idProfessor = $_SESSION["id_professor"];
$nomeProfessor = $_SESSION["nome_professor"];

$sql_turmas = "SELECT DISTINCT T.ID_Turma, T.Codigo_Turma, C.Nome AS Nome_Curso
               FROM Turma_Disciplina TD
               INNER JOIN Turmas T ON TD.ID_Turma = T.ID_Turma
               INNER JOIN Cursos C ON T.ID_Curso = C.ID_Curso
               WHERE TD.ID_Professor = $idProfessor";
$resultado_turmas = mysqli_query($conexao, $sql_turmas);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Área do Professor - Dashboard</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
        body { display: flex; min-height: 100vh; background-color: #f4f6f9; color: #333; }
        .sidebar { width: 280px; background-color: #1e1e2d; color: #fff; padding: 30px 20px; display: flex; flex-direction: column; }
        .sidebar h2 { margin-bottom: 10px; font-size: 22px; color: #00d2ff; }
        .sidebar p { font-size: 14px; color: #a2a3b7; margin-bottom: 30px; }
        .sidebar a { color: #a2a3b7; text-decoration: none; padding: 12px 15px; margin-bottom: 10px; border-radius: 8px; transition: 0.3s; display: block; font-weight: 500; }
        .sidebar a:hover, .sidebar a.active { background-color: #2b2b40; color: #fff; }
        .main-content { flex: 1; padding: 40px; overflow-y: auto; }
        .header { margin-bottom: 30px; }
        .header h1 { font-size: 28px; color: #1e1e2d; }
        .card { background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); margin-bottom: 25px; }
        .card h3 { color: #1e1e2d; margin-bottom: 20px; font-size: 18px; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        ul { list-style: none; }
        li { padding: 10px 0; border-bottom: 1px solid #f1f1f1; }
        li:last-child { border-bottom: none; }
        form { display: grid; gap: 15px; }
        label { font-weight: 600; font-size: 14px; color: #555; }
        select { padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; width: 100%; background: #fdfdfd; }
        button { background: linear-gradient(135deg, #00d2ff 0%, #3a7bd5 100%); color: white; border: none; padding: 12px; border-radius: 8px; font-weight: bold; cursor: pointer; transition: 0.3s; font-size: 15px; }
        button:hover { opacity: 0.9; box-shadow: 0 5px 15px rgba(58,123,213,0.3); }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Portal Docente</h2>
        <p>Bem-vindo, <?php echo htmlspecialchars($nomeProfessor); ?></p>
        <a href="professor_home.php" class="active">Início</a>
        <a href="professor_config.php">Configurações de Perfil</a>
        <a href="professor_criar_curso.php">Criar Curso</a>
        <a href="professor_criar_turma.php">Criar Turma</a>
        <a href="professor_criar_disciplina.php">Criar Disciplina</a>
        <a href="professor_avaliacoes.php">Avaliações</a>
        <a href="professor_matricular.php">Matricular Aluno</a>
        <a href="professor_lancar_nota.php">Lançar Notas</a>
        <a href="professor_estatisticas.php" style="background-color: #3a7bd5; color: white;">Estatísticas de Reprovação</a>
        <a href="logout.php" style="margin-top: auto; color: #ff6b6b;">Sair do Sistema</a>
    </div>

    <div class="main-content">
        <div class="header">
            <h1>Painel Geral</h1>
        </div>

        <?php if(isset($_SESSION["mensagem_status"])): ?>
            <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <?php echo $_SESSION["mensagem_status"]; unset($_SESSION["mensagem_status"]); ?>
            </div>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px;">
            <div class="card">
                <h3>Minhas Turmas e Disciplinas</h3>
                <ul>
                    <?php 
                    $turmas_lista = [];
                    if(mysqli_num_rows($resultado_turmas) > 0) {
                        while($turma = mysqli_fetch_assoc($resultado_turmas)) {
                            $turmas_lista[] = $turma;
                            echo "<li><strong>Turma " . htmlspecialchars($turma["Codigo_Turma"]) . "</strong> <br><span style='font-size:13px; color:#777;'>" . htmlspecialchars($turma["Nome_Curso"]) . "</span></li>";
                        }
                    } else {
                        echo "<li>Nenhuma turma vinculada a você no momento.</li>";
                    }
                    ?>
                </ul>
            </div>

            <div class="card">
                <h3>Alterar Status da Turma</h3>
                <form action="backend_atualizar_status_turma.php" method="POST">
                    <div>
                        <label for="id_turma">Selecione a Turma:</label>
                        <select name="id_turma" id="id_turma" required>
                            <option value="">-- Escolha uma Turma --</option>
                            <?php foreach($turmas_lista as $t): ?>
                                <option value="<?php echo $t["ID_Turma"]; ?>"><?php echo htmlspecialchars($t["Codigo_Turma"]) . " (" . htmlspecialchars($t["Nome_Curso"]) . ")"; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="status">Situação Atual da Turma:</label>
                        <!-- O value "Concluido" sem acento corrige o erro de validação do certificado (Bug 2) -->
                        <select name="status" id="status" required>
                            <option value="Em Andamento">Em Andamento</option>
                            <option value="Concluido">Concluído</option>
                        </select>
                    </div>
                    <button type="submit">Atualizar Situação</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
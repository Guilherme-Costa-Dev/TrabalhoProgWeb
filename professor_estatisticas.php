<?php
session_start();
include "conexao.php";
$conexao = conectaBD();

if(!isset($_SESSION["id_professor"])){
    header("Location: login_professor.php");
    exit;
}

$idProfessor = $_SESSION["id_professor"];
$nomeProfessor = isset($_SESSION["nome_professor"]) ? $_SESSION["nome_professor"] : "Professor";

// Lógica de Estatísticas (Ponto 4): Retorna taxa de alunos abaixo de 60 por disciplina
$sql_stats = "
    SELECT 
        D.Nome AS Nome_Disciplina,
        T.Codigo_Turma,
        C.Nome AS Nome_Curso,
        COUNT(DISTINCT M.ID_Matricula) AS Total_Alunos,
        SUM(CASE WHEN SubqueryNotas.Soma_Notas < 60 THEN 1 ELSE 0 END) AS Alunos_Reprovados
    FROM Turma_Disciplina TD
    INNER JOIN Disciplinas D ON TD.ID_Disciplina = D.ID_Disciplina
    INNER JOIN Turmas T ON TD.ID_Turma = T.ID_Turma
    INNER JOIN Cursos C ON T.ID_Curso = C.ID_Curso
    INNER JOIN Matriculas M ON T.ID_Turma = M.ID_Turma
    LEFT JOIN (
        SELECT ID_Matricula, ID_Disciplina, SUM(Valor_Nota) AS Soma_Notas
        FROM Notas
        GROUP BY ID_Matricula, ID_Disciplina
    ) AS SubqueryNotas ON M.ID_Matricula = SubqueryNotas.ID_Matricula AND D.ID_Disciplina = SubqueryNotas.ID_Disciplina
    WHERE TD.ID_Professor = $idProfessor
    GROUP BY TD.ID_Turma, TD.ID_Disciplina
";

$resultado_stats = mysqli_query($conexao, $sql_stats);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel do Professor - Estatísticas</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
        body { display: flex; min-height: 100vh; background-color: #f4f6f9; color: #333; }
        .sidebar { width: 280px; background-color: #2b2d42; color: #fff; padding: 30px 20px; display: flex; flex-direction: column; }
        .sidebar h2 { margin-bottom: 10px; font-size: 22px; color: #48cae4; }
        .sidebar p { font-size: 14px; color: #8d99ae; margin-bottom: 30px; }
        .sidebar a { color: #8d99ae; text-decoration: none; padding: 12px 15px; margin-bottom: 10px; border-radius: 8px; transition: 0.3s; display: block; font-weight: 500; }
        .sidebar a:hover, .sidebar a.active { background-color: #1a1b2e; color: #fff; }
        .main-content { flex: 1; padding: 40px; overflow-y: auto; }
        .table-container { background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #e0e0e0; }
        th { background-color: #f8f9fa; color: #2b2d42; font-weight: bold; }
        .badge-danger { background-color: #f8d7da; color: #721c24; padding: 5px 10px; border-radius: 4px; font-size: 13px; font-weight: bold; }
        .badge-ok { background-color: #d4edda; color: #155724; padding: 5px 10px; border-radius: 4px; font-size: 13px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Portal do Professor</h2>
        <p>Bem-vindo(a), <?php echo htmlspecialchars($nomeProfessor); ?></p>
        <a href="professor_home.php">Minhas Turmas</a>
        <a href="professor_estatisticas.php" class="active">Estatísticas</a>
        <a href="logout.php" style="margin-top: auto; color: #ff6b6b;">Sair</a>
    </div>

    <div class="main-content">
        <h1 style="color: #2b2d42;">Estatísticas de Rendimento</h1>
        <p style="color: #666; margin-bottom: 20px;">Visão geral de alunos matriculados e índices de reprovação por turma e disciplina.</p>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Turma</th>
                        <th>Disciplina / Matéria</th>
                        <th>Total Alunos</th>
                        <th>Reprovados</th>
                        <th>Taxa de Reprovação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($resultado_stats && mysqli_num_rows($resultado_stats) > 0) {
                        while($row = mysqli_fetch_assoc($resultado_stats)) {
                            $total = $row['Total_Alunos'];
                            $reprovados = $row['Alunos_Reprovados'];
                            $taxa = $total > 0 ? round(($reprovados / $total) * 100, 1) : 0;
                            
                            $badgeClass = $taxa >= 50 ? 'badge-danger' : 'badge-ok';

                            echo "<tr>";
                            // 1ª Coluna: Agora exibe a Turma
                            echo "<td>Turma " . htmlspecialchars($row['Codigo_Turma']) . " <br><span style='font-size:12px;color:#888;'>" . htmlspecialchars($row['Nome_Curso']) . "</span></td>";
                            // 2ª Coluna: Agora exibe a Disciplina/Matéria
                            echo "<td><strong>" . htmlspecialchars($row['Nome_Disciplina']) . "</strong></td>";
                            echo "<td>" . $total . " matriculados</td>";
                            echo "<td><span class='" . ($reprovados > 0 ? "badge-danger" : "badge-ok") . "'>" . $reprovados . " reprovados</span></td>";
                            echo "<td><span class='$badgeClass'>" . $taxa . "%</span></td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5' style='text-align:center; padding: 30px;'>Não há dados suficientes para gerar estatísticas.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
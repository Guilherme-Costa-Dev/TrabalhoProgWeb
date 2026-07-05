<?php
session_start();
include "conexao.php";
$conexao = conectaBD();

if(!isset($_SESSION["id_aluno"])){
    header("Location: login_aluno.php");
    exit;
}

$idAluno = $_SESSION["id_aluno"];
$nomeAluno = $_SESSION["nome_aluno"];

$sql_boletim = "
    SELECT C.Nome AS Curso, T.Codigo_Turma, M.Status AS Status_Matricula, D.Nome AS Disciplina, IFNULL(SUM(N.Valor_Nota), 0) AS Total_Notas
    FROM Matriculas M
    INNER JOIN Turmas T ON M.ID_Turma = T.ID_Turma
    INNER JOIN Cursos C ON T.ID_Curso = C.ID_Curso
    INNER JOIN Turma_Disciplina TD ON T.ID_Turma = TD.ID_Turma
    INNER JOIN Disciplinas D ON TD.ID_Disciplina = D.ID_Disciplina
    LEFT JOIN Notas N ON M.ID_Matricula = N.ID_Matricula AND D.ID_Disciplina = N.ID_Disciplina
    WHERE M.ID_Aluno = $idAluno
    GROUP BY M.ID_Matricula, D.ID_Disciplina
    ORDER BY C.Nome, D.Nome
";

$resultado_boletim = mysqli_query($conexao, $sql_boletim);

// Estruturação do Array (Bug 3: Preparação da Situação Geral)
$boletins_formatados = [];
while($row = mysqli_fetch_assoc($resultado_boletim)) {
    $chave = $row['Curso'] . " - Turma " . $row['Codigo_Turma'];
    if(!isset($boletins_formatados[$chave])) {
        $boletins_formatados[$chave] = [
            'status_matricula' => $row['Status_Matricula'],
            'disciplinas' => []
        ];
    }
    $boletins_formatados[$chave]['disciplinas'][] = [
        'nome' => $row['Disciplina'],
        'nota' => $row['Total_Notas']
    ];
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Meu Boletim</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
        body { display: flex; min-height: 100vh; background-color: #f4f6f9; color: #333; }
        .sidebar { width: 280px; background-color: #2b2d42; color: #fff; padding: 30px 20px; display: flex; flex-direction: column; }
        .sidebar h2 { margin-bottom: 10px; font-size: 22px; color: #48cae4; }
        .sidebar p { font-size: 14px; color: #8d99ae; margin-bottom: 30px; }
        .sidebar a { color: #8d99ae; text-decoration: none; padding: 12px 15px; margin-bottom: 10px; border-radius: 8px; transition: 0.3s; display: block; font-weight: 500; }
        .sidebar a:hover, .sidebar a.active { background-color: #1a1b2e; color: #fff; }
        .main-content { flex: 1; padding: 40px; overflow-y: auto; }
        .boletim-card { background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); margin-bottom: 30px; }
        h2.curso-titulo { color: #2b2d42; margin-bottom: 20px; border-bottom: 2px solid #f0f0f0; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background-color: #f8f9fa; color: #333; font-weight: 600; text-transform: uppercase; font-size: 13px; }
        .aprovado { color: #2a9d8f; font-weight: bold; }
        .reprovado { color: #e63946; font-weight: bold; }
        .situacao-geral { background: #f8f9fa; padding: 20px; border-radius: 8px; border-left: 5px solid #48cae4; margin-top: 15px; font-size: 18px; display: flex; justify-content: space-between; align-items: center; }
        .situacao-geral strong { color: #2b2d42; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Portal do Aluno</h2>
        <p>Bem-vindo(a), <?php echo htmlspecialchars($nomeAluno); ?></p>
        <a href="aluno_home.php">Meus Cursos</a>
        <a href="boletim.php" class="active">Meu Boletim</a>
        <a href="aluno_config.php">Configurações</a>
        <a href="logout.php" style="margin-top: auto; color: #ff6b6b;">Sair</a>
    </div>

    <div class="main-content">
        <h1 style="margin-bottom: 30px; color: #2b2d42;">Desempenho Acadêmico</h1>
        
        <?php 
        if(!empty($boletins_formatados)) {
            foreach($boletins_formatados as $cursoNome => $dadosCurso) {
                echo "<div class='boletim-card'>";
                echo "<h2 class='curso-titulo'>" . htmlspecialchars($cursoNome) . "</h2>";
                
                echo "<table>";
                echo "<thead><tr><th>Disciplina</th><th>Nota Final</th><th>Situação na Matéria</th></tr></thead>";
                echo "<tbody>";
                
                $todasAprovadas = true;
                
                foreach($dadosCurso['disciplinas'] as $disc) {
                    $nota = $disc['nota'];
                    $situacaoMateria = $nota >= 60 ? "<span class='aprovado'>Aprovado</span>" : "<span class='reprovado'>Reprovado</span>";
                    
                    if($nota < 60) {
                        $todasAprovadas = false;
                    }

                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($disc['nome']) . "</td>";
                    echo "<td>" . number_format($nota, 2) . " / 100.00</td>";
                    echo "<td>" . $situacaoMateria . "</td>";
                    echo "</tr>";
                }
                echo "</tbody></table>";

                // Bug 3: Renderiza a Situação Geral do Aluno em Relação ao Curso Inteiro
                $statusGeral = $dadosCurso['status_matricula'];
                
                echo "<div class='situacao-geral'>";
                echo "<span><strong>Situação Geral na Turma: </strong></span>";
                
                if(strtolower($statusGeral) === 'concluido' || strtolower($statusGeral) === 'concluído') {
                    if($todasAprovadas) {
                        echo "<span class='aprovado'>Aprovado (Certificado Liberado)</span>";
                    } else {
                        echo "<span class='reprovado'>Reprovado no Curso Geral</span>";
                    }
                } else {
                    echo "<span style='color: #f4a261; font-weight: bold;'>Cursando (Em Andamento)</span>";
                }
                echo "</div>"; 

                echo "</div>";
            }
        } else {
            echo "<p>Nenhum dado de boletim encontrado.</p>";
        }
        ?>
    </div>
</body>
</html>
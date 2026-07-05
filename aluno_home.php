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

// Busca as matrículas do aluno
$sql_cursos = "SELECT C.Nome AS Nome_Curso, C.ID_Curso, M.ID_Matricula, T.ID_Turma, T.Codigo_Turma, M.Status AS Status_Matricula
               FROM Cursos C
               INNER JOIN Turmas T ON C.ID_Curso = T.ID_Curso
               INNER JOIN Matriculas M ON T.ID_Turma = M.ID_Turma
               WHERE M.ID_Aluno = $idAluno";
$resultado_cursos = mysqli_query($conexao, $sql_cursos);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Portal do Aluno - Meus Cursos</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
        body { display: flex; min-height: 100vh; background-color: #f4f6f9; color: #333; }
        .sidebar { width: 280px; background-color: #2b2d42; color: #fff; padding: 30px 20px; display: flex; flex-direction: column; }
        .sidebar h2 { margin-bottom: 10px; font-size: 22px; color: #48cae4; }
        .sidebar p { font-size: 14px; color: #8d99ae; margin-bottom: 30px; }
        .sidebar a { color: #8d99ae; text-decoration: none; padding: 12px 15px; margin-bottom: 10px; border-radius: 8px; transition: 0.3s; display: block; font-weight: 500; }
        .sidebar a:hover, .sidebar a.active { background-color: #1a1b2e; color: #fff; }
        .main-content { flex: 1; padding: 40px; overflow-y: auto; }
        .card { background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; }
        .info h3 { color: #2b2d42; margin-bottom: 5px; font-size: 20px; }
        .info p { color: #666; font-size: 14px; }
        .actions { text-align: right; }
        .btn { display: inline-block; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: bold; transition: 0.3s; margin-left: 10px; }
        .btn-blue { background: #48cae4; color: #1a1b2e; }
        .btn-blue:hover { background: #00b4d8; color: white; }
        .btn-green { background: #2a9d8f; color: white; }
        .btn-green:hover { background: #21867a; }
        .btn-disabled { background: #e0e0e0; color: #9e9e9e; cursor: not-allowed; pointer-events: none; }
        .status-badge { padding: 5px 10px; border-radius: 20px; font-size: 12px; font-weight: bold; }
        .em-andamento { background: #fff3cd; color: #856404; }
        .concluido { background: #d4edda; color: #155724; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Portal do Aluno</h2>
        <p>Bem-vindo(a), <?php echo htmlspecialchars($nomeAluno); ?></p>
        <a href="aluno_home.php" class="active">Meus Cursos</a>
        <a href="boletim.php">Meu Boletim</a>
        <a href="aluno_config.php">Configurações</a>
        <a href="logout.php" style="margin-top: auto; color: #ff6b6b;">Sair</a>
    </div>

    <div class="main-content">
        <h1 style="margin-bottom: 30px; color: #2b2d42;">Meus Cursos e Certificados</h1>
        
        <?php 
        if(mysqli_num_rows($resultado_cursos) > 0) {
            while($curso = mysqli_fetch_assoc($resultado_cursos)) {
                $idMatricula = $curso['ID_Matricula'];
                $idTurma = $curso['ID_Turma'];
                $statusMatricula = $curso['Status_Matricula'];

                // Bug 2: Conserto final da lógica de emissão de certificado (Garante soma correta e ignora acentos)
                $podeEmitir = false;
                $motivoBloqueio = "Em andamento";

                if (strtolower($statusMatricula) === 'concluido' || strtolower($statusMatricula) === 'concluído') {
                    // Correção aqui: Alterado de D.ID_Disciplina para TD.ID_Disciplina
                    $sql_notas_cert = "SELECT TD.ID_Disciplina, IFNULL(SUM(N.Valor_Nota), 0) as Total_Pontos 
                                       FROM Turma_Disciplina TD
                                       LEFT JOIN Notas N ON TD.ID_Disciplina = N.ID_Disciplina AND N.ID_Matricula = $idMatricula
                                       WHERE TD.ID_Turma = $idTurma
                                       GROUP BY TD.ID_Disciplina";
                    $req_notas = mysqli_query($conexao, $sql_notas_cert);
                    
                    $aprovadoEmTodas = true;
                    if($req_notas && mysqli_num_rows($req_notas) > 0) {
                        while($notaDisc = mysqli_fetch_assoc($req_notas)) {
                            if($notaDisc['Total_Pontos'] < 60) {
                                $aprovadoEmTodas = false;
                                break;
                            }
                        }
                    } else {
                        $aprovadoEmTodas = false;
                    }

                    if($aprovadoEmTodas) {
                        $podeEmitir = true;
                    } else {
                        $motivoBloqueio = "Reprovado em uma ou mais disciplinas";
                    }
                }

                $badgeClass = (strtolower($statusMatricula) === 'concluido' || strtolower($statusMatricula) === 'concluído') ? 'concluido' : 'em-andamento';
                ?>
                
                <div class="card">
                    <div class="info">
                        <h3><?php echo htmlspecialchars($curso['Nome_Curso']); ?></h3>
                        <p>Turma: <?php echo htmlspecialchars($curso['Codigo_Turma']); ?> | <span class="status-badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($statusMatricula); ?></span></p>
                    </div>
                    <div class="actions">
                        <a href="boletim.php" class="btn btn-blue">Ver Boletim</a>
                        <?php if($podeEmitir): ?>
                            <a href="gerar_certificado.php?id_matricula=<?php echo $idMatricula; ?>" class="btn btn-green">Emitir Certificado</a>
                        <?php else: ?>
                            <a href="#" class="btn btn-disabled" title="<?php echo $motivoBloqueio; ?>">Certificado Bloqueado</a>
                        <?php endif; ?>
                    </div>
                </div>

                <?php
            }
        } else {
            echo "<p>Você ainda não está matriculado em nenhum curso.</p>";
        }
        ?>
    </div>
</body>
</html>
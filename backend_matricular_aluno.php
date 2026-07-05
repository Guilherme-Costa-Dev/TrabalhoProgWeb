<?php
session_start();
include "conexao.php";
$conexao = conectaBD();

// Verifica se a requisição foi feita via POST (vindo do formulário de matrícula)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Resgata os IDs enviados pelo formulário 
    // (Ajuste os nomes $_POST["..."] se no seu formulário os nomes forem diferentes)
    $idAluno = intval($_POST["id_aluno"]);
    $idTurma = intval($_POST["id_turma"]);
    $dataMatricula = date("Y-m-d"); // Define a data de hoje para a matrícula

    if ($idAluno > 0 && $idTurma > 0) {
        
        // 1. Faz o INSERT normal do aluno na tabela de Matrículas
        $sql_matricula = "INSERT INTO Matriculas (ID_Aluno, ID_Turma, Data_Matricula, Status) 
                          VALUES ($idAluno, $idTurma, '$dataMatricula', 'Em Andamento')";
        
        if (mysqli_query($conexao, $sql_matricula)) {
            
            // 2. RECUPERA O ID DA MATRÍCULA QUE ACABOU DE SER GERADA
            $idNovaMatricula = mysqli_insert_id($conexao);

            // 3. SOLUÇÃO DO BUG: Vincula retroativamente todas as avaliações já existentes nesta turma
            // Este comando procura na tabela 'Notas' todas as disciplinas e tipos de avaliação 
            // que os outros alunos desta turma já possuem e cria um registo zerado para o novo aluno.
            $sql_notas_retroativas = "
                INSERT INTO Notas (ID_Matricula, ID_Disciplina, Tipo_Avaliacao, Valor_Nota)
                SELECT DISTINCT $idNovaMatricula, N.ID_Disciplina, N.Tipo_Avaliacao, 0
                FROM Notas N
                INNER JOIN Matriculas M ON N.ID_Matricula = M.ID_Matricula
                WHERE M.ID_Turma = $idTurma
            ";

            // Executa a inserção das notas antigas
            mysqli_query($conexao, $sql_notas_retroativas);

            // Redireciona de volta para a página de gestão ou lista de alunos com uma mensagem
            echo "<script>
                    alert('Aluno matriculado com sucesso! As avaliações anteriores da turma foram vinculadas.');
                    window.location.href = 'professor_home.php'; 
                  </script>";
            exit;

        } else {
            echo "Erro ao realizar a matrícula no banco de dados: " . mysqli_error($conexao);
        }
    } else {
        echo "Erro: Dados de Aluno ou Turma inválidos.";
    }
} else {
    // Se tentarem aceder ao ficheiro diretamente sem ser por POST, redireciona
    header("Location: professor_home.php");
    exit;
}
?>
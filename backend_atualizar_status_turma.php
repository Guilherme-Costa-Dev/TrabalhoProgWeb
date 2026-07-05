<?php
session_start();
include "conexao.php";
$conexao = conectaBD();

// Proteção básica de acesso externo
if(!isset($_SESSION["id_professor"])){
    header("Location: login_professor.php");
    exit;
}

if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Sanitiza e resgata os dados enviados pelo formulário
    $idTurma = intval($_POST["id_turma"]);
    $novoStatus = mysqli_real_escape_string($conexao, $_POST["status"]);

    if($idTurma > 0 && !empty($novoStatus)) {
        
        // Executa o UPDATE para mudar o status de todos os alunos matriculados nessa turma específica
        $sql = "UPDATE Matriculas SET Status = '$novoStatus' WHERE ID_Turma = $idTurma";
        
        if(mysqli_query($conexao, $sql)){
            $_SESSION["mensagem_status"] = "<p style='color: #28a745; text-align: center; font-weight: bold; margin-bottom: 15px;'>Situação da turma atualizada com sucesso para todos os alunos!</p>";
        } else {
            $_SESSION["mensagem_status"] = "<p style='color: #dc3545; text-align: center; font-weight: bold; margin-bottom: 15px;'>Erro ao atualizar banco: " . mysqli_error($conexao) . "</p>";
        }
        
    } else {
        $_SESSION["mensagem_status"] = "<p style='color: #dc3545; text-align: center; font-weight: bold; margin-bottom: 15px;'>Por favor, selecione uma turma válida.</p>";
    }
}

// Redireciona de volta para o painel do professor
header("Location: professor_home.php");
exit;
?>
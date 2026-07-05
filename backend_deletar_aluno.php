<?php

    session_start();

    include "conexao.php";

    $conexao = conectaBD();

    $idAluno = $_SESSION["id_aluno"];

    

    // Limpa os registros dependentes primeiro para não dar crash no banco
    mysqli_query($conexao, "DELETE FROM Notas WHERE ID_Matricula IN (SELECT ID_Matricula FROM Matriculas WHERE ID_Aluno = $idAluno)");
    mysqli_query($conexao, "DELETE FROM Matriculas WHERE ID_Aluno = $idAluno");

    // Só depois deleta o aluno
    $sql = "DELETE FROM Alunos WHERE ID_Aluno = $idAluno";
    mysqli_query($conexao, $sql);

    if(mysqli_query($conexao, $sql)){

        // Encerra a sessão
        session_destroy();

        // Volta para a tela de login
        header("Location: login_aluno.php");
        exit;

    }else{

        echo "Erro ao excluir a conta: " . mysqli_error($conexao);

    }
?>
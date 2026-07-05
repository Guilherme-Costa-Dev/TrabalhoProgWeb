<?php
session_start();
include "conexao.php";
$conexao = conectaBD();

if(!isset($_SESSION["id_professor"])){
    header("Location: login_professor.php");
    exit;
}

if(isset($_GET['id'])) {
    $idProf = (int)$_GET['id'];
    
    $sql = "DELETE FROM Professores WHERE ID_Professor = $idProf";
    
    if (mysqli_query($conexao, $sql)) {
        $_SESSION["mensagem_status"] = "<p style='color:green;'>Professor deletado com sucesso!</p>";
    } else {
        $_SESSION["mensagem_status"] = "<p style='color:red;'>Erro ao deletar: " . mysqli_error($conexao) . "</p>";
    }
}

header("Location: admin_gerenciar_professores.php");
exit;
?>
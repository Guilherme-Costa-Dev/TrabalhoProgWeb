<?php

include "conexao.php";

$conexao = conectaBD();

$cpf = $_GET["cpf"];
$usuario = $_GET["usuario"];


// Verifica usuário

$sqlUsuario = "

SELECT Usuario FROM Alunos
WHERE Usuario='$usuario'

UNION

SELECT Usuario FROM Professores
WHERE Usuario='$usuario'

";

$resultadoUsuario =
mysqli_query($conexao, $sqlUsuario);


// Mensagem

if(mysqli_num_rows($resultadoUsuario) > 0){

    echo "
    <p style='color:red;'>
    Usuário já cadastrado
    </p>
    ";

}else{

    echo "
    <p style='color:green;'>
    Usuário disponível
    </p>
    ";
}

?>
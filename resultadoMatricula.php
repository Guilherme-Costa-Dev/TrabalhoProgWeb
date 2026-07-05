<?php

include "conexao.php";

$conexao = conectaBD();

$matricula = $_GET["matricula"];


// Verifica matrícula

$sqlMatricula = "

SELECT Matricula_Geral
FROM Alunos

WHERE Matricula_Geral='$matricula'

";

$resultadoMatricula =
mysqli_query($conexao, $sqlMatricula);


// Mensagem

if(mysqli_num_rows($resultadoMatricula) > 0){

    echo "
    <p style='color:red;'>
    Matrícula já cadastrada
    </p>
    ";

}else{

    echo "
    <p style='color:green;'>
    Matrícula disponível
    </p>
    ";
}

?>
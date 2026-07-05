<?php

include "conexao.php";

$conexao = conectaBD();

$cpf = $_GET["cpf"];
$usuario = $_GET["usuario"];


// Verifica CPF

$sqlCPF = "

SELECT CPF FROM Alunos
WHERE CPF='$cpf'

UNION

SELECT CPF FROM Professores
WHERE CPF='$cpf'

";

$resultadoCPF =
mysqli_query($conexao, $sqlCPF);

// Mensagem

if(mysqli_num_rows($resultadoCPF) > 0){

    echo "
    <p style='color:red;'>
    CPF já cadastrado
    </p>
    ";

}else{

    echo "
    <p style='color:green;'>
    CPF disponível
    </p>
    ";
}
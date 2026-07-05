<?php

session_start();

include "conexao.php";

$conexao = conectaBD();

$idMatricula =
$_GET['id_matricula'];

$idDisciplina =
$_GET['id_disciplina'];

$avaliacao =
mysqli_real_escape_string(
$conexao,
$_GET['id_avaliacao']
);

$novaNota =
(float)$_GET['nota'];

$verifica = mysqli_query(

$conexao,

"SELECT * FROM Notas

WHERE ID_Matricula = $idMatricula

AND ID_Disciplina = $idDisciplina

AND Tipo_Avaliacao = '$avaliacao'"

);


if(mysqli_num_rows($verifica) > 0){

    $sql_exec = "

    UPDATE Notas

    SET Valor_Nota = $novaNota

    WHERE ID_Matricula = $idMatricula

    AND ID_Disciplina = $idDisciplina

    AND Tipo_Avaliacao = '$avaliacao'

    ";

}else{

    $sql_exec = "

    INSERT INTO Notas

    (
    ID_Matricula,
    ID_Disciplina,
    Tipo_Avaliacao,
    Valor_Nota
    )

    VALUES

    (
    $idMatricula,
    $idDisciplina,
    '$avaliacao',
    $novaNota
    )

    ";
}


if(mysqli_query($conexao, $sql_exec)){

    echo "

    <div class='alert success'>

    Nota salva com sucesso!

    </div>

    ";

}else{

    echo "

    <div class='alert error'>

    Erro ao salvar nota

    </div>

    ";
}

?>
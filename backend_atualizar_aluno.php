<?php
    // Puxa a sessão do aluno já logado, junto com os dados dele
    session_start();

    include "conexao.php";

    $conexao = conectaBD();

    $id = $_SESSION["id_aluno"];

    $nome = $_POST["nome"];
    $email = $_POST["email"];
    $telefone = $_POST["telefone"];
    $usuario = $_POST["usuario"];
    $senha = $_POST["senha"];

    // Se o campo de nova senha estiver preenchido, atualiza a senha; senão, só muda os outros dados
    if(!empty($senha)){

        $sql = "UPDATE Alunos SET
                Nome='$nome',
                Email='$email',
                Telefone='$telefone',
                Usuario='$usuario',
                Senha='$senha'
                WHERE ID_Aluno=$id";

    }else{

        $sql = "UPDATE Alunos SET
                Nome='$nome',
                Email='$email',
                Telefone='$telefone',
                Usuario='$usuario'
                WHERE ID_Aluno=$id";
    }

    mysqli_query($conexao, $sql);

    header("Location: aluno_config.php");
    exit;
?>
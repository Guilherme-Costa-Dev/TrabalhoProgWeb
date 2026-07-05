<?php

session_start();

include "conexao.php";

$conexao = conectaBD();


// Verifica envio do formulário
if($_SERVER["REQUEST_METHOD"] == "POST"){

    $usuario = $_POST["usuario"];
    $senha   = $_POST["senha"];


    // Procura o usuário no banco de dados
    $sql = "SELECT * FROM Alunos
    WHERE Usuario = '$usuario'";

    $resultado = mysqli_query($conexao, $sql);


    // Verifica se encontrou
    if(mysqli_num_rows($resultado) > 0){

        $aluno = mysqli_fetch_assoc($resultado);


        // Verifica senha
        if($senha == $aluno["Senha"]){

            // Cria sessão
            $_SESSION["id_aluno"] = $aluno["ID_Aluno"];

            $_SESSION["nome_aluno"] = $aluno["Nome"];

            header("Location: aluno_home.php");
            exit;

        }else{

            $_SESSION["mensagem"] =
            "<p style='color:red;'>Senha incorreta!</p>";
        }

    }else{

        $_SESSION["mensagem"] =
        "<p style='color:red;'>Usuário não encontrado!</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
<meta charset="UTF-8">
<title>Login do Aluno</title>

<style>

body{
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;

    background: linear-gradient(135deg,
    #1d098f,
    #104f97);

    font-family: Arial;
}

.container{

    background: white;

    padding: 30px;

    border-radius: 12px;

    width: 350px;

    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

h2{

    text-align: center;

    margin-bottom: 20px;

    color: #1d098f;
}

form{

    display: flex;

    flex-direction: column;
}

input{

    padding: 12px;

    margin-bottom: 15px;

    border-radius: 6px;

    border: 1px solid #ccc;
}

button{

    padding: 12px;

    border: none;

    border-radius: 6px;

    background: #1d098f;

    color: white;

    cursor: pointer;
}

button:hover{

    background: #150769;
}

.mensagem{

    text-align: center;

    margin-bottom: 15px;
}

.link{

    margin-top: 15px;

    text-align: center;
}

</style>
</head>

<body>

<div class="container">

<?php
if(isset($_SESSION["mensagem"])){

    echo "<div class='mensagem'>"
    . $_SESSION["mensagem"] .
    "</div>";

    unset($_SESSION["mensagem"]);
}
?>

<h2>Entrar como aluno</h2>
<form method="POST">

    <input type="text" name="usuario" placeholder="Usuário" required>
    <input type="password" name="senha" placeholder="Senha" required>

    <button type="submit">Entrar</button>

</form>

<div class="link">
<a href="login_professor.php">Entrar como Professor</a>
</div>
<div class="link">
<a href="cadastro.php">Voltar ao Cadastro</a>
</div>

</div>
<script> 
function fazerLogin(){ 
    let usuario = document.getElementById("usuario").value; 
    let senha = document.getElementById("senha").value; 
    usaAjax( 
        "verifica_login_aluno.php?usuario=" + usuario + "&senha=" + senha, "mensagem" 
        ); 
} 
</script>
</body>
</html>


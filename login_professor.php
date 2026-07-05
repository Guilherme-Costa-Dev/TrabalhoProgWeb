<?php 
    
session_start();

include "conexao.php";

$conexao = conectaBD();


// Verifica envio do formulário
if($_SERVER["REQUEST_METHOD"] == "POST"){

    $usuario = $_POST["usuario"];
    $senha   = $_POST["senha"];


    // Procura o usuário no banco de dados
    $sql = "SELECT * FROM Professores
    WHERE Usuario = '$usuario'";

    $resultado = mysqli_query($conexao, $sql);


    // Verifica se encontrou
    if(mysqli_num_rows($resultado) > 0){

        $professor = mysqli_fetch_assoc($resultado);


        // Verifica senha
        if($senha == $professor["Senha"]){

            // Cria sessão
            $_SESSION["id_professor"] = $professor["ID_Professor"];

            $_SESSION["nome_professor"] = $professor["Nome"];

            header("Location: professor_home.php");
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
<html>
<head>
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

    <h2>Entrar como professor</h2>
    <form method="POST">
        <input type="text" name="usuario" placeholder="Usuario" required>
        <input type="password" name="senha" placeholder="Senha" required>
        <button type="submit">Entrar</button>
    </form>
<div class="link">
    <a href="login_aluno.php">Voltar ao Login do Aluno</a>
</div>
</div>    
</body>
</html>
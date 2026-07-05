<?php
session_start();

include "conexao.php";

$conexao = conectaBD();

// Verifica se o formulário foi enviado
if($_SERVER["REQUEST_METHOD"] == "POST"){

    $tipo_usuario = $_POST["tipo_usuario"];

    $cpf       = $_POST["cpf"];
    $usuario   = $_POST["usuario"];
    $nome      = $_POST["nome"];
    $email     = $_POST["email"];
    $telefone  = $_POST["telefone"];
    $matricula = $_POST["matricula"];
    $senha     = $_POST["senha"];

    // Verifica se o usuário e cpf já foram cadastrados antes
    $verifica = "
    SELECT CPF, Usuario, Matricula_Geral FROM Alunos
    WHERE CPF='$cpf'
    OR Usuario='$usuario'
    OR Matricula_Geral='$matricula'
    UNION
    SELECT CPF, Usuario, NULL FROM Professores
    WHERE CPF='$cpf'
    OR Usuario='$usuario'
    ";
    
    $resultado = mysqli_query($conexao, $verifica);

    if(mysqli_num_rows($resultado) > 0){

        $_SESSION["mensagem"] =
        "<p style='color:red;'>
        CPF, usuário ou matrícula já cadastrados!
        </p>";

        header("Location: cadastro.php");
        exit;

    }else{
        // Cadastro de Aluno
        if($tipo_usuario == "aluno"){

            $sql = "INSERT INTO Alunos
            (Nome, CPF, Email, Telefone, Matricula_Geral, Usuario, Senha)

            VALUES

            ('$nome', '$cpf', '$email', '$telefone',
            '$matricula', '$usuario', '$senha')";
        }


        // Cadastro de Professor
        else{

            $sql = "INSERT INTO Professores
            (Nome, CPF, Email, Telefone, Usuario, Senha)

            VALUES

            ('$nome', '$cpf', '$email', '$telefone',
            '$usuario', '$senha')";
        }


        // Executa
        if(mysqli_query($conexao, $sql)){

            $_SESSION["mensagem"] =
            "<p style='color:green;'>Cadastro realizado com sucesso!</p>";

            if($tipo_usuario == "aluno"){

                header("Location: login_aluno.php");

            }else{

                header("Location: login_professor.php");
            }

            exit;
        
        }else{

            $_SESSION["mensagem"] =
            "<p style='color:red;'>Erro: "
            . mysqli_error($conexao) .
            "</p>";
            header("Location: cadastro.php"); 
            exit;

        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
<meta charset="UTF-8">
<title>Cadastro</title>

<script src="ajax.js"></script>

<style>

*{
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Arial, Helvetica, sans-serif;
}

body{
    height: 120vh;
    display: flex;
    justify-content: center;
    align-items: center;

    background: linear-gradient(135deg,
    #1d098f,
    #104f97);
}

.container{

    width: 400px;
    background: white;

    padding: 30px;

    border-radius: 12px;

    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

h2{
    text-align: center;
    margin-bottom: 25px;
    color: #1d098f;
}

form{
    display: flex;
    flex-direction: column;
}

label{
    margin-bottom: 8px;
    font-weight: bold;
}

select,
input{

    padding: 12px;

    margin-bottom: 15px;

    border: 1px solid #ccc;

    border-radius: 6px;

    outline: none;
}

input:focus,
select:focus{

    border-color: #1d098f;
}

button{

    padding: 12px;

    border: none;

    border-radius: 6px;

    background: #1d098f;

    color: white;

    font-size: 16px;

    cursor: pointer;

    transition: 0.3s;
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

    echo "<div class='mensagem'>".$_SESSION["mensagem"]."</div>";

    unset($_SESSION["mensagem"]);
}
?>

<h2>Criar Conta</h2>

<form method="POST">

    <label>Tipo de Conta:</label>

    <select name="tipo_usuario" id="tipo_usuario" onchange="verificaMatricula()">
        <option value="aluno">Aluno</option>
        <option value="professor">Professor</option>
    </select>
    <input type="text" name="cpf" placeholder="CPF*" onkeyup="verificarCPF()" required>
    <div style='color: red' id="saidacpf">*CPF obrigatório</div><br>
    <input type="text" name="usuario" placeholder="Nome de Usuário*" onkeyup="verificarUsuario()" required>
    <div style='color: red' id="saidausuario">*Usuário obrigatório</div><br>

    <input type="text" name="nome"
    placeholder="Nome Completo" required>

    <input type="email" name="email"
    placeholder="E-mail" required>

    <input type="text" name="telefone"
    placeholder="Telefone" required>

    <input type="text" name="matricula" id="matricula"
    placeholder="Matrícula* (somente aluno)" onkeyup="verificarMat()">
    <div style='color: red' id="saidamat">*Matrícula obrigatória para o aluno</div><br>

    <input type="password" name="senha"
    placeholder="Senha" required>

    <button type="submit">Cadastrar</button>
    <div class="link">
    <a href="login_aluno.php">Fazer Login</a>
    </div>

</form>

</div>

</body>
<script>

// Função que verifica se a matricula é obrigatória (aluno) ou não (professor)
function verificaMatricula(){

    let tipo =
    document.getElementById("tipo_usuario").value;

    let matricula =
    document.getElementById("matricula");

    if(tipo == "aluno"){

        matricula.required = true;

    }else{

        matricula.required = false;
    }
}

// Função que verifica se já existe CPF usando ajax
function verificarCPF(){

    let cpf =
    document.getElementsByName("cpf")[0].value;

    usaAjax(
        "resultadoCPF.php?cpf=" + cpf,
        "saidacpf"
    );
}

// Função que verifica se já existe Usuário usando ajax
function verificarUsuario(){

    let usuario =
    document.getElementsByName("usuario")[0].value;

    usaAjax(
        "resultadousuario.php?usuario=" + usuario,
        "saidausuario"
    );
}

// Função que verifica se já existe Matricula usando ajax
function verificarMat(){

    let matricula =
    document.getElementById("matricula").value;

    usaAjax(
        "resultadoMatricula.php?matricula=" +
        matricula,
        "saidamat"
    );
}

</script>
</html>
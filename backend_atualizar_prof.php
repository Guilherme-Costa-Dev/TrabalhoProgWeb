<?php
session_start();
include "conexao.php";
$conexao = conectaBD();

if (!isset($_SESSION["id_professor"])) {
    header("Location: login_professor.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idProf = $_SESSION["id_professor"];
    
    // Protege e limpa as strings contra SQL Injection
    $nome     = mysqli_real_escape_string($conexao, $_POST['nome']);
    $usuario  = mysqli_real_escape_string($conexao, $_POST['usuario']);
    $email    = mysqli_real_escape_string($conexao, $_POST['email']);
    $telefone = mysqli_real_escape_string($conexao, $_POST['telefone']);

    // Começa a montar o SQL de Update com os campos padrão
    $sql = "UPDATE Professores SET 
            Nome = '$nome', 
            Usuario = '$usuario', 
            Email = '$email', 
            Telefone = '$telefone'";

    // Se o professor digitou uma nova senha, adiciona ela no Update
    if (!empty($_POST['senha'])) {
        $senha = mysqli_real_escape_string($conexao, $_POST['senha']);
        $sql .= ", Senha = '$senha'"; // Nota: se você usar MD5 no login, use MD5('$senha') aqui
    }

    // Fecha a condição do WHERE para atualizar apenas o professor logado
    $sql .= " WHERE ID_Professor = $idProf";

    if (mysqli_query($conexao, $sql)) {
        // Atualiza o nome na sessão para caso ele mude, mude na Home na mesma hora
        $_SESSION["nome_professor"] = $nome; 
        
        echo "<script>
                alert('Dados atualizados com sucesso!');
                window.location.href = 'professor_config.php';
              </script>";
    } else {
        echo "<script>
                alert('Erro ao atualizar dados: " . mysqli_error($conexao) . "');
                window.location.href = 'professor_config.php';
              </script>";
    }
}
?>
<?php
session_start();

// Define um redirecionamento padrão
$redirecionarPara = "login_aluno.php"; 

// Se for um professor que estiver saindo, muda o destino
if (isset($_SESSION["id_professor"])) {
    $redirecionarPara = "login_professor.php";
}

// Limpa todas as variáveis de sessão na memória
session_unset();

// Destrói a sessão fisicamente
session_destroy();

// Redireciona para a respectiva página de login
header("Location: " . $redirecionarPara);
exit;
?>
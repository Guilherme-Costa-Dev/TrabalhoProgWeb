<?php
session_start();
include "conexao.php";
$conexao = conectaBD();

if (!isset($_SESSION["id_professor"])) {
    header("Location: login_professor.php");
    exit;
}

$idProf = $_SESSION["id_professor"];

$sql = "SELECT Nome, Email, Telefone, Usuario FROM Professores WHERE ID_Professor = $idProf";
$resultado = mysqli_query($conexao, $sql);
$professor = mysqli_fetch_assoc($resultado);

$nomeAtual     = isset($professor['Nome']) ? $professor['Nome'] : '';
$emailAtual    = isset($professor['Email']) ? $professor['Email'] : '';
$telefoneAtual = isset($professor['Telefone']) ? $professor['Telefone'] : '';
$usuarioAtual  = isset($professor['Usuario']) ? $professor['Usuario'] : '';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Configurações da Conta</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f6f9; margin: 0; }
        .navbar { background: linear-gradient(135deg, #1d098f, #104f97); padding: 15px 30px; color: white; display: flex; justify-content: space-between; align-items: center; }
        .navbar h1 { margin: 0; font-size: 20px; }
        .navbar .links a { color: white; text-decoration: none; margin-left: 15px; font-weight: bold; font-size: 14px; }
        .navbar .links a:hover { text-decoration: underline; }
        
        .container { max-width: 600px; margin: 40px auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        h2 { color: #1d098f; margin-top: 0; margin-bottom: 20px; border-bottom: 2px solid #eee; padding-bottom: 10px; text-align: center; }
        
        label { display: block; font-weight: bold; margin-bottom: 8px; color: #444; }
        input { width: 100%; padding: 12px; margin-bottom: 20px; border-radius: 6px; border: 1px solid #ccc; box-sizing: border-box; font-size: 15px; }
        input:focus { border-color: #1d098f; outline: none; }
        
        button { width: 100%; padding: 12px; background: #1d098f; color: white; border: none; font-weight: bold; cursor: pointer; border-radius: 6px; font-size: 15px; transition: 0.3s; }
        button:hover { background: #150769; }
        
        hr { border: 0; height: 1px; background: #eee; margin: 30px 0; }
        .btn-danger { background: #e63946; }
        .btn-danger:hover { background: #c32f3a; }
    </style>
</head>
<body>

    <div class="navbar">
        <h1>Painel do Professor</h1>
        <div class="links">
            <a href="professor_home.php">Voltar ao Início</a>
            <a href="logout.php">Sair</a>
        </div>
    </div>

    <div class="container">
        <h2>Configurações do Perfil</h2>

        <form action="backend_atualizar_prof.php" method="POST">
            <label>Alterar Nome Completo:</label>
            <input type="text" name="nome" value="<?php echo htmlspecialchars($nomeAtual); ?>" required>

            <label>Alterar Nome de Usuário:</label>
            <input type="text" name="usuario" value="<?php echo htmlspecialchars($usuarioAtual); ?>" required>

            <label>Alterar Email Institucional:</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($emailAtual); ?>" required>

            <label>Alterar Telefone:</label>
            <input type="text" name="telefone" placeholder="(00) 00000-0000" value="<?php echo htmlspecialchars($telefoneAtual); ?>">

            <label>Nova Senha:</label>
            <input type="password" name="senha" placeholder="Deixe em branco para manter a atual">

            <button type="submit">Salvar Alterações</button>
        </form>

        <hr>

        <form action="backend_deletar_prof.php" method="POST" onsubmit="return confirm('Tem certeza absoluta que deseja deletar sua conta? Esta ação não pode ser desfeita!');">
            <button type="submit" class="btn-danger">Excluir Conta Permanentemente</button>
        </form>
    </div>

</body>
</html>
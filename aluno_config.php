<?php 
    session_start();

    include "conexao.php";

    $conexao = conectaBD();

    $idAluno = $_SESSION["id_aluno"];

    // Busca os dados do aluno
    $sql = "SELECT * FROM Alunos WHERE ID_Aluno = $idAluno";
    $resultado = mysqli_query($conexao, $sql);
    $aluno = mysqli_fetch_assoc($resultado);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Portal do Aluno - Configurações da Conta</title>

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
        body { display: flex; min-height: 100vh; background-color: #f4f6f9; color: #333; }
        
        /* Estilos do Menu Lateral (Copiados da Home) */
        .sidebar { width: 280px; background-color: #2b2d42; color: #fff; padding: 30px 20px; display: flex; flex-direction: column; }
        .sidebar h2 { margin-bottom: 10px; font-size: 22px; color: #48cae4; }
        .sidebar p { font-size: 14px; color: #8d99ae; margin-bottom: 30px; }
        .sidebar a { color: #8d99ae; text-decoration: none; padding: 12px 15px; margin-bottom: 10px; border-radius: 8px; transition: 0.3s; display: block; font-weight: 500; }
        .sidebar a:hover, .sidebar a.active { background-color: #1a1b2e; color: #fff; }
        
        /* Conteúdo Principal */
        .main-content { flex: 1; padding: 40px; overflow-y: auto; }
        .card { background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); max-width: 600px; }
        
        /* Formulários */
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 600; color: #2b2d42; font-size: 14px; }
        input { width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 15px; color: #333; transition: border-color 0.3s; }
        input:focus { border-color: #48cae4; outline: none; }
        
        /* Botões */
        .btn { display: inline-block; padding: 12px 20px; border-radius: 8px; border: none; text-decoration: none; font-weight: bold; cursor: pointer; font-size: 15px; transition: 0.3s; width: 100%; text-align: center; margin-top: 10px; }
        .btn-blue { background: #48cae4; color: #1a1b2e; }
        .btn-blue:hover { background: #00b4d8; color: white; }
        .btn-red { background: #ff6b6b; color: white; margin-top: 10px; }
        .btn-red:hover { background: #fa5252; }
        
        hr { margin: 30px 0; border: 0; border-top: 1px solid #eaeaea; }
    </style>

</head>
<body>
    
    <div class="sidebar">
        <h2>Portal do Aluno</h2>
        <p>Bem-vindo(a), <?= htmlspecialchars($aluno['Nome']) ?></p>
        <a href="aluno_home.php">Meus Cursos</a>
        <a href="boletim.php">Meu Boletim</a>
        <a href="aluno_config.php" class="active">Configurações</a>
        <a href="logout.php" style="margin-top: auto; color: #ff6b6b;">Sair</a>
    </div>

    <div class="main-content">
        <h1 style="margin-bottom: 30px; color: #2b2d42;">Configurações da Conta</h1>

        <div class="card">
            <form action="backend_atualizar_aluno.php" method="POST">
                
                <div class="form-group">
                    <label>Nome</label>
                    <input type="text" name="nome" value="<?= htmlspecialchars($aluno['Nome']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($aluno['Email']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Telefone</label>
                    <input type="text" name="telefone" value="<?= htmlspecialchars($aluno['Telefone']) ?>">
                </div>

                <div class="form-group">
                    <label>Usuário</label>
                    <input type="text" name="usuario" value="<?= htmlspecialchars($aluno['Usuario']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Nova senha (opcional)</label>
                    <input type="password" name="senha" placeholder="Deixe em branco para manter a atual">
                </div>

                <button type="submit" class="btn btn-blue">Salvar Alterações</button>
            </form>

            <hr>

            <form action="backend_deletar_aluno.php" method="POST">
                <button class="btn btn-red" type="submit" onclick="return confirm('Deseja realmente excluir sua conta? Esta ação não pode ser desfeita.')">
                    Excluir Conta Definitivamente
                </button>
            </form>
        </div>
    </div>

</body>
</html>
<?php
session_start();
include "conexao.php";
$conexao = conectaBD();

// Define o fuso horário para a data de emissão
date_default_timezone_set('America/Sao_Paulo');

// Força o navegador a usar UTF-8 para evitar caracteres estranhos (como nÃ£o)
header('Content-Type: text/html; charset=utf-8');

// Verifica se o ID da matrícula foi passado
if (!isset($_GET['id_matricula'])) {
    die("<h1>Matrícula não identificada.</h1>");
}

$idMatricula = intval($_GET['id_matricula']);

// Busca o status da matrícula
$verificaStatus = mysqli_query($conexao, "SELECT Status FROM Matriculas WHERE ID_Matricula = $idMatricula");
if (!$verificaStatus || mysqli_num_rows($verificaStatus) == 0) {
    die("<h1>Erro: Matrícula não encontrada.</h1>");
}

$dadosMatricula = mysqli_fetch_assoc($verificaStatus);
$statusMatricula = strtolower($dadosMatricula['Status']);

// Correção da validação: aceita tanto "concluido" quanto "concluído" (independente de maiúsculas/minúsculas)
if ($statusMatricula !== 'concluido' && $statusMatricula !== 'concluído') {
    die("<h1>Erro: O aluno ainda não concluiu este curso e não pode emitir o certificado.</h1>");
}

// Busca os dados do aluno, curso e turma para preencher o certificado
$sql = "SELECT A.Nome AS Aluno, C.Nome AS Curso, C.Carga_Horaria, M.Data_Matricula 
        FROM Matriculas M
        JOIN Alunos A ON M.ID_Aluno = A.ID_Aluno
        JOIN Turmas T ON M.ID_Turma = T.ID_Turma
        JOIN Cursos C ON T.ID_Curso = C.ID_Curso
        WHERE M.ID_Matricula = $idMatricula";

$resultado = mysqli_query($conexao, $sql);

if (!$resultado || mysqli_num_rows($resultado) == 0) {
    die("<h1>Nenhum dado de certificado encontrado para esta matrícula.</h1>");
}

$dados = mysqli_fetch_assoc($resultado);

// Formata a data atual por extenso em português
$meses = array(
    1 => 'janeiro', 2 => 'fevereiro', 3 => 'março', 4 => 'abril',
    5 => 'maio', 6 => 'junho', 7 => 'julho', 8 => 'agosto',
    9 => 'setembro', 10 => 'outubro', 11 => 'novembro', 12 => 'dezembro'
);
$dia = date('d');
$mes = $meses[(int)date('m')];
$ano = date('Y');
$dataEmissao = "$dia de $mes de $ano";
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Certificado - <?php echo htmlspecialchars($dados['Aluno']); ?></title>
    <style>
        /* Estilos de visualização na tela */
        body {
            background-color: #f0f2f5;
            font-family: 'Georgia', 'Times New Roman', serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
        }

        .no-print-area {
            margin-bottom: 20px;
        }

        .btn-imprimir {
            padding: 12px 25px;
            background-color: #1d098f;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .btn-imprimir:hover {
            background-color: #150769;
        }

        /* Moldura do Certificado */
        .certificado {
            background-color: white;
            width: 1000px;
            height: 700px;
            padding: 40px;
            box-sizing: border-box;
            border: 20px double #1d098f;
            position: relative;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            background-image: radial-gradient(circle at 50% 50%, #fffbf2 0%, #ffffff 100%);
        }

        .conteudo {
            border: 2px solid #cfb53b; /* Borda dourada interna */
            height: 100%;
            width: 100%;
            box-sizing: border-box;
            text-align: center;
            padding: 50px;
        }

        h1 {
            font-size: 50px;
            color: #1d098f;
            margin-top: 20px;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 3px;
        }

        .subtitulo {
            font-size: 20px;
            font-style: italic;
            color: #555;
            margin-bottom: 60px;
        }

        .texto-principal {
            font-size: 24px;
            line-height: 1.6;
            color: #333;
            margin-bottom: 60px;
            text-align: justify;
        }

        .nome-aluno {
            font-weight: bold;
            font-size: 28px;
            color: #104f97;
            text-decoration: underline;
        }

        .nome-curso {
            font-weight: bold;
            font-style: italic;
        }

        .data-local {
            font-size: 18px;
            color: #444;
            margin-bottom: 80px;
        }

        /* Área de assinaturas */
        .assinaturas {
            display: flex;
            justify-content: space-around;
            margin-top: 20px;
        }

        .linha-assinatura {
            border-top: 1px solid #777;
            width: 250px;
            font-size: 16px;
            color: #444;
            padding-top: 8px;
        }

        /* REGRAS EXCLUSIVAS PARA A IMPRESSÃO / SALVAR EM PDF */
        @media print {
            body {
                background-color: white;
            }
            .no-print-area {
                display: none; /* Esconde o botão de imprimir na folha */
            }
            .certificado {
                box-shadow: none;
                border: 20px double #1d098f;
                width: 100%;
                height: 99vh; /* Ocupa a folha inteira */
                page-break-inside: avoid;
            }
            @page {
                size: landscape; /* Força o navegador a colocar em modo Paisagem (deitado) */
                margin: 0;
            }
        }
    </style>
</head>
<body>

    <div class="no-print-area">
        <button class="btn-imprimir" onclick="window.print();">Imprimir / Salvar em PDF</button>
    </div>

    <div class="certificado">
        <div class="conteudo">
            <h1>Certificado de Conclusão</h1>
            <div class="subtitulo">Instituição de Ensino Acadêmico</div>

            <p class="texto-principal">
                Certificamos para os devidos fins que o(a) aluno(a) <span class="nome-aluno"><?php echo htmlspecialchars($dados['Aluno']); ?></span> concluiu com êxito os requisitos acadêmicos estipulados para o curso de extensão ou formação de <span class="nome-curso"><?php echo htmlspecialchars($dados['Curso']); ?></span>, com carga horária total devidamente registrada de <span class="nome-curso"><?php echo $dados['Carga_Horaria']; ?> horas</span>.
            </p>

            <p class="data-local">
                Vitória - ES, <?php echo $dataEmissao; ?>.
            </p>

            <div class="assinaturas">
                <div class="linha-assinatura">
                    Coordenação Pedagógica
                </div>
                <div class="linha-assinatura">
                    Direção Acadêmica
                </div>
            </div>
        </div>
    </div>

</body>
</html>
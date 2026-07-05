<?php 
    date_default_timezone_set('America/Sao_Paulo');
    function conectaBD(){
        $servidor = "localhost"; 
        $usuario = "root"; 
        $senha = "usbw";
        $banco  = "SistemaAcademico";
        $conexao = mysqli_connect($servidor, $usuario, $senha, $banco);
        if(!$conexao){
            die("Erro na conexão: ".mysqli_connect_error());
        }
        return $conexao;
        
    }
?>
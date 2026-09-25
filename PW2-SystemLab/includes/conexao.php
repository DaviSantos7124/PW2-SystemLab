<?php

$servidor = "localhost";
$usuario = "root";
$senha = "";
$name = "pw2_systemlab";

$conexao = new mysqli(
    $servidor,
    $usuario,
    $senha,
    $name,
    3307
);

if ($conexao->connect_error) {

    echo "Erro de conexão! " . $conexao->connect_error;

} else {

    // echo "Conexão bem-sucedida!";

}

?>
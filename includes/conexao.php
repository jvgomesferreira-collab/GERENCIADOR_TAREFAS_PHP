<?php
// includes/conexao.php

$host = "localhost";
$usuario = "root"; // Altere se o seu usuário do MySQL for diferente
$senha = ""; // Altere se você tiver uma senha para o MySQL
$banco = "db_tarefas";

$conn = new mysqli($host, $usuario, $senha, $banco);

if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Opcional: Definir o conjunto de caracteres para evitar problemas com acentuação
$conn->set_charset("utf8mb4");
?>
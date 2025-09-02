<?php
// Configuração do banco de dados
$host = "localhost";
$dbname = "tcc_equipamentos";
$user = "root";
$pass = "";

// Criar conexão
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die(json_encode(["success" => false, "message" => "Erro de conexão: " . $e->getMessage()]));
}
?>

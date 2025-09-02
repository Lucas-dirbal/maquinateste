<?php
header('Content-Type: application/json');
require 'conn.php';

// Receber dados do front-end
$data = json_decode(file_get_contents("php://input"), true);

if(!isset($data['email']) || !isset($data['password'])){
    echo json_encode(["success" => false, "message" => "Email e senha são obrigatórios."]);
    exit;
}

$email = $data['email'];
$password = $data['password'];

// Buscar usuário
$stmt = $conn->prepare("SELECT * FROM usuarios WHERE email = :email");
$stmt->execute(['email' => $email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if($user && password_verify($password, $user['senha'])){
    // Sucesso
    echo json_encode([
        "success" => true,
        "user" => [
            "id" => $user['id'],
            "nome" => $user['nome'],
            "email" => $user['email'],
            "tipo" => $user['tipo']
        ]
    ]);
} else {
    echo json_encode(["success" => false, "message" => "Email ou senha incorretos."]);
}
?>

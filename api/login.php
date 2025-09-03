<?php
header('Content-Type: application/json');
require 'conn.php';

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['email']) || !isset($input['password'])) {
    echo json_encode(['success'=>false, 'message'=>'Email e senha são obrigatórios.']);
    exit;
}

$email = trim($input['email']);
$pass  = $input['password'];

$stmt = $conn->prepare('SELECT id, nome, email, senha, tipo FROM usuarios WHERE email = :email LIMIT 1');
$stmt->execute(['email'=>$email]);
$user = $stmt->fetch();

if ($user && password_verify($pass, $user['senha'])) {
    echo json_encode([
        'success' => true,
        'user' => [
            'id' => intval($user['id']),
            'nome' => $user['nome'],
            'email' => $user['email'],
            'tipo' => $user['tipo']
        ]
    ]);
} else {
    echo json_encode(['success'=>false, 'message'=>'Email ou senha incorretos.']);
}
?>

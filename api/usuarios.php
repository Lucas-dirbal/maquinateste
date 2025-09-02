<?php
header('Content-Type: application/json');
require 'conn.php';

$method = $_SERVER['REQUEST_METHOD'];

// GET: Listar usuários
if($method === 'GET'){
    $stmt = $conn->query("SELECT id, nome, email, tipo FROM usuarios");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($users);
}

// POST: Criar novo usuário
if($method === 'POST'){
    $data = json_decode(file_get_contents("php://input"), true);
    if(!isset($data['nome'],$data['email'],$data['senha'],$data['tipo'])){
        echo json_encode(["success"=>false,"message"=>"Todos os campos são obrigatórios."]);
        exit;
    }

    $senhaHash = password_hash($data['senha'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO usuarios (nome,email,senha,tipo) VALUES (:nome,:email,:senha,:tipo)");
    $stmt->execute([
        'nome'=>$data['nome'],
        'email'=>$data['email'],
        'senha'=>$senhaHash,
        'tipo'=>$data['tipo']
    ]);

    echo json_encode(["success"=>true]);
}

// PUT e DELETE podem ser adicionados para editar/excluir usuários
?>

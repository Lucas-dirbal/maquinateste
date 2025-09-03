<?php
header('Content-Type: application/json');
require 'conn.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $conn->query('SELECT id, nome, email, tipo, criado_em FROM usuarios ORDER BY id DESC');
    echo json_encode($stmt->fetchAll());
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if ($method === 'POST') {
    if (!isset($data['nome'],$data['email'],$data['senha'],$data['tipo'])) {
        echo json_encode(['success'=>false,'message'=>'Campos obrigatórios: nome, email, senha, tipo']);
        exit;
    }
    $hash = password_hash($data['senha'], PASSWORD_BCRYPT);
    $stmt = $conn->prepare('INSERT INTO usuarios (nome,email,senha,tipo) VALUES (:nome,:email,:senha,:tipo)');
    $stmt->execute([
        'nome'=>$data['nome'],
        'email'=>$data['email'],
        'senha'=>$hash,
        'tipo'=>$data['tipo']
    ]);
    echo json_encode(['success'=>true, 'id'=>$conn->lastInsertId()]);
    exit;
}

if ($method === 'PUT') {
    if (!isset($data['id'])) { echo json_encode(['success'=>false,'message'=>'ID é obrigatório']); exit; }
    $fields = [];
    $params = ['id'=>$data['id']];
    if (isset($data['nome'])) { $fields[] = 'nome = :nome'; $params['nome']=$data['nome']; }
    if (isset($data['email'])) { $fields[] = 'email = :email'; $params['email']=$data['email']; }
    if (isset($data['tipo'])) { $fields[] = 'tipo = :tipo'; $params['tipo']=$data['tipo']; }
    if (isset($data['senha']) && $data['senha']!=='') { $fields[] = 'senha = :senha'; $params['senha']=password_hash($data['senha'], PASSWORD_BCRYPT); }
    if (!$fields) { echo json_encode(['success'=>false,'message'=>'Nada para atualizar']); exit; }
    $sql = 'UPDATE usuarios SET '.implode(', ',$fields).' WHERE id = :id';
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    echo json_encode(['success'=>true]);
    exit;
}

if ($method === 'DELETE') {
    parse_str($_SERVER['QUERY_STRING'] ?? '', $q);
    if (!isset($q['id'])) { echo json_encode(['success'=>false,'message'=>'ID é obrigatório']); exit; }
    $stmt = $conn->prepare('DELETE FROM usuarios WHERE id = :id');
    $stmt->execute(['id'=>$q['id']]);
    echo json_encode(['success'=>true]);
    exit;
}

http_response_code(405);
echo json_encode(['success'=>false,'message'=>'Método não suportado']);
?>

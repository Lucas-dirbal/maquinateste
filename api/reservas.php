<?php
header('Content-Type: application/json');
require 'conn.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $usuario_id = isset($_GET['usuario_id']) ? intval($_GET['usuario_id']) : 0;
    $status = isset($_GET['status']) ? $_GET['status'] : '';

    if ($usuario_id > 0) {
        $stmt = $conn->prepare('SELECT r.id, r.data_retirada, r.data_devolucao, r.motivo, r.status, e.nome AS equipamento_nome
                                FROM reservas r
                                JOIN equipamentos e ON r.equipamento_id = e.id
                                WHERE r.usuario_id = :uid
                                ORDER BY r.id DESC');
        $stmt->execute(['uid'=>$usuario_id]);
        echo json_encode($stmt->fetchAll());
        exit;
    }

    if ($status !== '') {
        $stmt = $conn->prepare('SELECT r.*, u.nome AS usuario_nome, e.nome AS equipamento_nome
                                FROM reservas r
                                JOIN usuarios u ON r.usuario_id = u.id
                                JOIN equipamentos e ON r.equipamento_id = e.id
                                WHERE r.status = :status
                                ORDER BY r.id DESC');
        $stmt->execute(['status'=>$status]);
        echo json_encode($stmt->fetchAll());
        exit;
    }

    $stmt = $conn->query('SELECT r.*, u.nome AS usuario_nome, e.nome AS equipamento_nome
                          FROM reservas r
                          JOIN usuarios u ON r.usuario_id = u.id
                          JOIN equipamentos e ON r.equipamento_id = e.id
                          ORDER BY r.id DESC');
    echo json_encode($stmt->fetchAll());
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if ($method === 'POST') {
    if (!isset($data['equipamento_id'],$data['usuario_id'],$data['data_retirada'],$data['data_devolucao'])) {
        echo json_encode(['success'=>false,'message'=>'Campos obrigatórios: equipamento_id, usuario_id, data_retirada, data_devolucao']);
        exit;
    }
    $stmt = $conn->prepare('INSERT INTO reservas (equipamento_id, usuario_id, data_retirada, data_devolucao, motivo, status)
                            VALUES (:equipamento_id, :usuario_id, :data_retirada, :data_devolucao, :motivo, :status)');
    $stmt->execute([
        'equipamento_id'=>$data['equipamento_id'],
        'usuario_id'=>$data['usuario_id'],
        'data_retirada'=>$data['data_retirada'],
        'data_devolucao'=>$data['data_devolucao'],
        'motivo'=>$isset($data['motivo']) ? $data['motivo'] : '',
        'status'=> isset($data['status']) ? $data['status'] : 'pending'
    ]);
    echo json_encode(['success'=>true, 'id'=>$conn->lastInsertId()]);
    exit;
}

if ($method === 'PUT') {
    if (!isset($data['id'],$data['status'])) { echo json_encode(['success'=>false,'message'=>'Campos obrigatórios: id, status']); exit; }
    $stmt = $conn->prepare('UPDATE reservas SET status = :status WHERE id = :id');
    $stmt->execute(['status'=>$data['status'], 'id'=>$data['id']]);
    echo json_encode(['success'=>true]);
    exit;
}

http_response_code(405);
echo json_encode(['success'=>false,'message'=>'Método não suportado']);
?>

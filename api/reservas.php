<?php
header('Content-Type: application/json');
require 'conn.php';

$method = $_SERVER['REQUEST_METHOD'];

// GET: Listar reservas
if($method === 'GET'){
    $usuario_id = isset($_GET['usuario_id']) ? intval($_GET['usuario_id']) : 0;
    $status = isset($_GET['status']) ? $_GET['status'] : '';

    if($usuario_id > 0){
        $stmt = $conn->prepare("SELECT r.id, r.data_retirada, r.data_devolucao, r.motivo, r.status, e.nome as equipamento_nome 
                                FROM reservas r
                                JOIN equipamentos e ON r.equipamento_id = e.id
                                WHERE r.usuario_id = :uid");
        $stmt->execute(['uid'=>$usuario_id]);
    } elseif($status === 'pending') {
        $stmt = $conn->prepare("SELECT r.id, r.data_retirada, r.data_devolucao, r.motivo, r.status, e.nome as equipamento_nome, u.nome as usuario_nome
                                FROM reservas r
                                JOIN equipamentos e ON r.equipamento_id = e.id
                                JOIN usuarios u ON r.usuario_id = u.id
                                WHERE r.status = 'pending'");
        $stmt->execute();
    } else {
        $stmt = $conn->query("SELECT r.id, r.data_retirada, r.data_devolucao, r.motivo, r.status, e.nome as equipamento_nome 
                              FROM reservas r
                              JOIN equipamentos e ON r.equipamento_id = e.id");
    }

    $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($reservas);
}

// POST: Criar nova reserva
if($method === 'POST'){
    $data = json_decode(file_get_contents("php://input"), true);
    $stmt = $conn->prepare("INSERT INTO reservas (equipamento_id, usuario_id, data_retirada, data_devolucao, motivo, status)
                            VALUES (:equip, :user, :retirada, :devolucao, :motivo, 'pending')");
    $stmt->execute([
        'equip' => $data['equipamento_id'],
        'user' => $data['usuario_id'],
        'retirada' => $data['data_retirada'],
        'devolucao' => $data['data_devolucao'],
        'motivo' => $data['motivo']
    ]);

    echo json_encode(["success"=>true]);
}

// PUT: Atualizar status da reserva
if($method === 'PUT'){
    $data = json_decode(file_get_contents("php://input"), true);
    if(isset($data['id'],$data['status'])){
        $stmt = $conn->prepare("UPDATE reservas SET status = :status WHERE id = :id");
        $stmt->execute(['status'=>$data['status'],'id'=>$data['id']]);
        echo json_encode(["success"=>true]);
    } else {
        echo json_encode(["success"=>false,"message"=>"Parâmetros inválidos"]);
    }
}
?>

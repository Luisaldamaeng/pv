<?php
require 'config.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    echo json_encode(['status' => 'error', 'message' => 'Falta el ID del producto']);
    exit;
}

$sql = "SELECT * FROM costo_caramelo WHERE codigoprod = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

$data = $result->fetch_assoc();

header('Content-Type: application/json');
if ($data) {
    echo json_encode(['status' => 'success', 'data' => $data]);
} else {
    echo json_encode(['status' => 'empty']);
}

$stmt->close();
$conn->close();
?>
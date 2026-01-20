<?php
require 'config.php';

function responderJSON($status, $data = null, $message = '')
{
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status' => $status,
        'data' => $data,
        'message' => $message
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$code = $_GET['code'] ?? '';

if (empty($code)) {
    responderJSON('error', null, 'Código no proporcionado');
}

// Lógica de búsqueda condicional
if (strlen($code) <= 4) {
    // Buscar por Código de Producto (CODIGOPROD)
    $sql = "SELECT CODIGOPROD as id, NOMBRE as nombre FROM producto WHERE CODIGOPROD = ?";
} else {
    // Buscar por Código de Barras (CODBAR)
    $sql = "SELECT CODIGOPROD as id, NOMBRE as nombre FROM producto WHERE CODBAR = ?";
}

$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $code);
$stmt->execute();
$result = $stmt->get_result();

$product = $result->fetch_assoc();

if ($product) {
    responderJSON('success', $product);
} else {
    responderJSON('not_found', null, 'Producto no encontrado');
}

$stmt->close();
$conn->close();
?>
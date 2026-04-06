<?php
require 'config.php';

function responderJSON($status, $message = '')
{
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status' => $status,
        'message' => $message
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderJSON('error', 'Método no permitido');
}

$id = $_POST['codigoprod'] ?? null;
$porcentaje = (float) ($_POST['porcentaje'] ?? 0);
$precio_caja = (float) ($_POST['precio_caja'] ?? 0);
$cantidad = (float) ($_POST['cantidad'] ?? 0);
$precio_costo = (float) ($_POST['precio_costo'] ?? 0);

if (!$id) {
    responderJSON('error', 'Falta el ID del producto');
}

$sql = "INSERT INTO costo_mercaderia (codigoprod, porcentaje, precio_caja, cantidad, precio_costo)
        VALUES (?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE 
            porcentaje = VALUES(porcentaje),
            precio_caja = VALUES(precio_caja),
            cantidad = VALUES(cantidad),
            precio_costo = VALUES(precio_costo)";

$stmt = $conn->prepare($sql);
$stmt->bind_param('idddd', $id, $porcentaje, $precio_caja, $cantidad, $precio_costo);

if ($stmt->execute()) {
    responderJSON('success', 'Datos de costo mercaderia guardados correctamente');
} else {
    responderJSON('error', 'Error al guardar los datos: ' . $stmt->error);
}

$stmt->close();
$conn->close();
?>
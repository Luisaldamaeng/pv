<?php

function responderJSON($status, $message = '')
{
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status' => $status,
        'message' => $message
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

require 'config.php';

$id = isset($_POST['id']) ? trim($_POST['id']) : null;

if (!$id) {
    responderJSON('error', 'No se proporcionó un ID válido para eliminar.');
}

$sql = "DELETE FROM producto WHERE codigoprod = ?";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    responderJSON('error', 'Error al preparar la consulta: ' . $conn->error);
}

$stmt->bind_param('s', $id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        responderJSON('success', 'Producto eliminado correctamente.');
    } else {
        responderJSON('error', 'No se encontró el producto con ID: ' . $id . ' (Puede que ya haya sido eliminado).');
    }
} else {
    responderJSON('error', 'Error al ejecutar la eliminación: ' . $stmt->error);
}

$conn->close();
?>
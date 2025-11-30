<?php

function responderJSON($status, $message = '') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status' => $status,
        'message' => $message
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

require 'config.php';

$id = $_POST['id'] ?? null;

if (!$id) {
    responderJSON('error', 'No se proporcionó un ID para eliminar.');
}

$sql = "DELETE FROM producto WHERE codigoprod = ?";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    responderJSON('error', 'Error al preparar la consulta: ' . $conn->error);
}

$stmt->bind_param('s', $id);

if ($stmt->execute()) {
    responderJSON('success', 'Producto eliminado correctamente.');
} else {
    responderJSON('error', 'Error al ejecutar la eliminación: ' . $stmt->error);
}

$conn->close();
?>
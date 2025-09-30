<?php
require 'config.php';

function responderJSON($status, $message = '') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status' => $status,
        'message' => $message
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        responderJSON('error', 'Método no permitido');
    }

    $sql = "UPDATE producto SET selecc = 0";
    
    if ($conn->query($sql) === TRUE) {
        responderJSON('success', 'La columna selecc ha sido actualizada a 0 para todos los registros.');
    } else {
        throw new Exception('Error al actualizar los registros.');
    }
    
} catch (Exception $e) {
    error_log($e->getMessage());
    responderJSON('error', 'Error interno del servidor: ' . $e->getMessage());
} finally {
    $conn->close();
}
?>
<?php
require_once 'config.php';

header('Content-Type: application/json');

// Lógica para vaciar la tabla si se solicita
if (isset($_GET['delete_all']) && $_GET['delete_all'] === 'true') {
    if ($conn->query("TRUNCATE TABLE chatbot_conversaciones")) {
        echo json_encode(['status' => 'success', 'message' => 'Historial vaciado correctamente']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No se pudo vaciar el historial']);
    }
    exit;
}

// Obtener todas las conversaciones
$result = $conn->query("SELECT * FROM chatbot_conversaciones ORDER BY fecha DESC LIMIT 500");
$conversaciones = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $conversaciones[] = $row;
    }
}

echo json_encode($conversaciones);

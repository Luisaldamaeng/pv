<?php
require_once 'config.php';

header('Content-Type: application/json');

// Lógica para eliminar si se pasa un ID
if (isset($_GET['delete_id'])) {
    $id = (int) $_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM chatbot_memoria WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Memoria eliminada']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No se pudo eliminar']);
    }
    $stmt->close();
    exit;
}

// Lógica para agregar o editar (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = isset($input['id']) ? (int) $input['id'] : null;
    $concepto = trim($input['concepto'] ?? '');
    $valor = trim($input['valor'] ?? '');

    if (empty($concepto) || empty($valor)) {
        echo json_encode(['status' => 'error', 'message' => 'Campos incompletos']);
        exit;
    }

    if ($id) {
        // Editar existente
        $stmt = $conn->prepare("UPDATE chatbot_memoria SET concepto = ?, valor = ? WHERE id = ?");
        $stmt->bind_param("ssi", $concepto, $valor, $id);
    } else {
        // Insertar nuevo
        $stmt = $conn->prepare("INSERT INTO chatbot_memoria (concepto, valor) VALUES (?, ?) ON DUPLICATE KEY UPDATE valor = VALUES(valor)");
        $stmt->bind_param("ss", $concepto, $valor);
    }

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => $id ? 'Memoria actualizada' : 'Memoria guardada']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al procesar la solicitud']);
    }
    $stmt->close();
    exit;
}

// Obtener toda la memoria
$result = $conn->query("SELECT * FROM chatbot_memoria ORDER BY fecha_actualizacion DESC");
$memoria = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $memoria[] = $row;
    }
}

echo json_encode($memoria);

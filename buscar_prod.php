<?php

require 'config.php';

header('Content-Type: application/json; charset=utf-8');

function responderJSON($success, $data, $message = '') {
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderJSON(false, [], 'Método no permitido.');
}

if (!isset($_POST['q']) || empty(trim($_POST['q']))) {
    responderJSON(false, [], 'Por favor, ingrese un código para buscar.');
}

$q = trim($_POST['q']);
$conn->set_charset("utf8");

// Columnas a devolver (sin codigoprod y codbar)
$select_columns = "SELECT nombre, precio1";

// Determinar el tipo de búsqueda
if (is_numeric($q)) {
    // Búsqueda numérica EXACTA por código de producto o código de barras
    $sql = "$select_columns FROM producto WHERE codigoprod = ? OR codbar = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Error al preparar la consulta numérica: " . $conn->error);
        responderJSON(false, [], 'Error interno del servidor al preparar la consulta.');
    }
    $stmt->bind_param('ss', $q, $q);
} else {
    // Búsqueda por texto (incremental) en la columna nombre
    $sql = "$select_columns FROM producto WHERE LOWER(nombre) LIKE ?";
    $param_text = "%" . mb_strtolower($q, 'UTF-8') . "%";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Error al preparar la consulta de texto: " . $conn->error);
        responderJSON(false, [], 'Error interno del servidor al preparar la consulta.');
    }
    $stmt->bind_param('s', $param_text);
}


if (!$stmt->execute()) {
    error_log("Error al ejecutar la consulta: " . $stmt->error);
    responderJSON(false, [], 'Error interno del servidor al ejecutar la búsqueda.');
}

$result = $stmt->get_result();
$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

$stmt->close();
$conn->close();

if (count($data) > 0) {
    responderJSON(true, $data, 'Productos encontrados.');
} else {
    responderJSON(false, [], 'No se encontraron productos con ese código.');
}

?>
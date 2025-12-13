<?php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0); // no mostrar errores en salida
error_reporting(E_ALL);

require 'config.php'; // Incluir el archivo de configuración de la base de datos

$uploadDir = __DIR__ . DIRECTORY_SEPARATOR . 'imagen' . DIRECTORY_SEPARATOR;

if (!isset($_FILES['foto'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'No se recibió el archivo.']);
    exit;
}

$archivo = $_FILES['foto'];
if ($archivo['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Error en la subida: ' . $archivo['error']]);
    exit;
}

// Obtener el codigoprod del POST
$codigoprod = isset($_POST['codigoprod']) ? $conn->real_escape_string($_POST['codigoprod']) : null;

if (empty($codigoprod)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Falta el código de producto para asociar la foto.']);
    exit;
}

if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'No se pudo crear la carpeta de imágenes.']);
        exit;
    }
}

$nombreDestino = basename($archivo['name']);
$rutaDestino = $uploadDir . $nombreDestino;

if (!move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'No se pudo guardar el archivo en el servidor.']);
    exit;
}

// Actualizar la base de datos con la ruta de la foto
$sql = "UPDATE producto SET foto = ? WHERE codigoprod = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error al preparar la consulta de actualización: ' . $conn->error]);
    exit;
}

$fotoDBPath = 'imagen/' . $nombreDestino; // Ruta relativa para guardar en la DB
$stmt->bind_param('ss', $fotoDBPath, $codigoprod);

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error al actualizar la base de datos: ' . $stmt->error]);
    exit;
}

if ($stmt->affected_rows === 0) {
    // Esto podría indicar que el codigoprod no existe o la foto ya era la misma
    // Considerar si esto debe ser un error o una advertencia
    // Por ahora, lo tratamos como éxito si el archivo se subió
    echo json_encode(['status' => 'warning', 'message' => 'Foto guardada, pero el código de producto no fue encontrado en la base de datos o la foto no cambió.']);
} else {
    echo json_encode(['status' => 'success', 'message' => 'Foto guardada y base de datos actualizada.', 'file' => $fotoDBPath]);
}

$stmt->close();
$conn->close();
exit;
?>

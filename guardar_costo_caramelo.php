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
$precio_bolsa = (float) ($_POST['precio_bolsa'] ?? 0);
$peso_bolsa = (float) ($_POST['peso_bolsa'] ?? 0);
$cantidad_golosinas = (int) ($_POST['cantidad_golosinas'] ?? 0);
$peso_muestra = (float) ($_POST['peso_muestra'] ?? 0);
$porcentaje_ganancia = (float) ($_POST['porcentaje_ganancia'] ?? 0);

if (!$id) {
    responderJSON('error', 'Falta el ID del producto');
}

$sql = "INSERT INTO costo_caramelo (codigoprod, precio_bolsa, peso_bolsa, cantidad_golosinas, peso_muestra, porcentaje_ganancia)
        VALUES (?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE 
            precio_bolsa = VALUES(precio_bolsa),
            peso_bolsa = VALUES(peso_bolsa),
            cantidad_golosinas = VALUES(cantidad_golosinas),
            peso_muestra = VALUES(peso_muestra),
            porcentaje_ganancia = VALUES(porcentaje_ganancia)";

$stmt = $conn->prepare($sql);
$stmt->bind_param('iddidd', $id, $precio_bolsa, $peso_bolsa, $cantidad_golosinas, $peso_muestra, $porcentaje_ganancia);

if ($stmt->execute()) {
    responderJSON('success', 'Datos de costo caramelo guardados correctamente');
} else {
    $debug_info = "Vals: $id, $precio_bolsa, $peso_bolsa, $cantidad_golosinas, $peso_muestra, $porcentaje_ganancia";
    responderJSON('error', 'Error al guardar los datos: ' . $stmt->error . " ($debug_info)");
}

$stmt->close();
$conn->close();
?>
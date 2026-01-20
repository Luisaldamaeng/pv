<?php

require 'config.php';

header('Content-Type: application/json');

$nombre = $_POST['nombre'] ?? null;
$precio1 = $_POST['precio1'] ?? null;
$codbar = $_POST['codbar'] ?? null;
$selecc = isset($_POST['selecc']) ? 1 : 0;
$costo = !empty($_POST['costo']) ? (float) $_POST['costo'] : 0;
$anotacion = $_POST['anotacion'] ?? '';
$codnumeri = !empty($_POST['CODNUMERI']) ? (int) $_POST['CODNUMERI'] : 0;
$codigoprod_temp = $_POST['codigoprod_temp'] ?? null;

if ($codigoprod_temp) {
    $sql = "INSERT INTO producto (codigoprod, nombre, precio1, codbar, selecc, costo, anotacion, CODNUMERI) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die(json_encode(['status' => 'error', 'message' => 'Error al preparar (duplicar): ' . $conn->error]));
    }
    $stmt->bind_param('ssdsisss', $codigoprod_temp, $nombre, $precio1, $codbar, $selecc, $costo, $anotacion, $codnumeri);
} else {
    if (empty($nombre) || empty($precio1)) {
        die(json_encode(['status' => 'error', 'message' => 'Nombre y precio son obligatorios']));
    }
    $sql = "INSERT INTO producto (nombre, precio1, codbar, selecc, costo, anotacion, CODNUMERI) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die(json_encode(['status' => 'error', 'message' => 'Error al preparar (nuevo): ' . $conn->error]));
    }
    $stmt->bind_param('sdsisss', $nombre, $precio1, $codbar, $selecc, $costo, $anotacion, $codnumeri);
}

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => $codigoprod_temp ? 'Producto duplicado con éxito.' : 'Producto creado con éxito.']);
} else {
    echo json_encode(['status' => 'error', 'message' => $stmt->error]);
}

$stmt->close();
$conn->close();
?>
<?php

require 'config.php';

$nombre = $_POST['nombre'] ?? null;
$precio1 = $_POST['precio1'] ?? null;
$codbar = $_POST['codbar'] ?? null;
$selecc = isset($_POST['selecc']) ? 1 : 0;
$costo = !empty($_POST['costo']) ? (float)$_POST['costo'] : 0;
$cantcaja = !empty($_POST['CANTCAJA']) ? (int)$_POST['CANTCAJA'] : 0;
$codnumeri = !empty($_POST['CODNUMERI']) ? (int)$_POST['CODNUMERI'] : 0;

if (empty($nombre) || empty($precio1)) {
    die(json_encode(['status' => 'error', 'message' => 'Nombre y precio son obligatorios']));
}

$sql = "INSERT INTO producto (nombre, precio1, codbar, selecc, costo, CANTCAJA, CODNUMERI) VALUES (?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die(json_encode(['status' => 'error', 'message' => 'Error al preparar la consulta: ' . $conn->error]));
}

$stmt->bind_param('sdsidii', $nombre, $precio1, $codbar, $selecc, $costo, $cantcaja, $codnumeri);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => $stmt->error]);
}

$conn->close();
?>
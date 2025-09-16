<?php

require 'config.php';

$codigoprod = $_POST['codigoprod'] ?? null;
$nombre = $_POST['nombre'] ?? null;
$precio1 = $_POST['precio1'] ?? null;
$codbar = $_POST['codbar'] ?? null;
$selecc = $_POST['selecc'] ?? null;
$costo = $_POST['costo'] ?? null;

if (empty($codigoprod) || empty($nombre) || empty($precio1)) {
    die(json_encode(['status' => 'error', 'message' => 'El código, nombre y precio son obligatorios']));
}

$sql = "INSERT INTO producto (codigoprod, nombre, precio1, codbar, selecc, costo) VALUES (?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die(json_encode(['status' => 'error', 'message' => 'Error al preparar la consulta: ' . $conn->error]));
}

$stmt->bind_param('ssdssd', $codigoprod, $nombre, $precio1, $codbar, $selecc, $costo);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => $stmt->error]);
}

$conn->close();
?>
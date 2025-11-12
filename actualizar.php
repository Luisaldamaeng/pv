<?php

require 'config.php';

$id = $_POST['id'];
$nombre = $_POST['nombre'];
$precio1 = $_POST['precio1'];
$codbar = $_POST['codbar'];
$selecc = isset($_POST['selecc']) ? 1 : 0;
$costo = $_POST['costo'];
$cantcaja = $_POST['CANTCAJA'];
$codnumeri = $_POST['CODNUMERI'];

$sql = "UPDATE producto SET nombre = ?, precio1 = ?, codbar = ?, selecc = ?, costo = ?, CANTCAJA = ?, CODNUMERI = ? WHERE codigoprod = ?";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die(json_encode(['status' => 'error', 'message' => 'Error al preparar la consulta: ' . $conn->error]));
}

$stmt->bind_param('sdsidiis', $nombre, $precio1, $codbar, $selecc, $costo, $cantcaja, $codnumeri, $id);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => $stmt->error]);
}

$conn->close();
?>
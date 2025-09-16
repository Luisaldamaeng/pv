<?php

require 'config.php';

$id = $_POST['id'];
$nombre = $_POST['nombre'];
$precio1 = $_POST['precio1'];
$codbar = $_POST['codbar'];
$selecc = $_POST['selecc'];
$costo = $_POST['costo'];

$sql = "UPDATE producto SET nombre = ?, precio1 = ?, codbar = ?, selecc = ?, costo = ? WHERE codigoprod = ?";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die(json_encode(['status' => 'error', 'message' => 'Error al preparar la consulta: ' . $conn->error]));
}

$stmt->bind_param('sdsdds', $nombre, $precio1, $codbar, $selecc, $costo, $id);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => $stmt->error]);
}

$conn->close();
?>
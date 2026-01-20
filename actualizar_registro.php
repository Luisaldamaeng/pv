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
    responderJSON('error', 'Método no permitido.');
}

$id = $_POST['id'] ?? null;
$nombre = $_POST['nombre'] ?? null;
$precio1 = $_POST['precio1'] ?? null;
$codbar = $_POST['codbar'] ?? null;
$selecc = isset($_POST['selecc']) ? (int) $_POST['selecc'] : 0;
$costo = !empty($_POST['costo']) ? (float) $_POST['costo'] : 0;
$anotacion = $_POST['anotacion'] ?? '';
$codnumeri = !empty($_POST['CODNUMERI']) ? (int) $_POST['CODNUMERI'] : 0;

if (empty($id) || empty($nombre) || $precio1 === null) {
    responderJSON('error', 'Faltan datos requeridos (ID, Nombre, Precio).');
}

$sql = "UPDATE producto SET 
            nombre = ?, 
            precio1 = ?, 
            codbar = ?, 
            selecc = ?, 
            costo = ?, 
            anotacion = ?, 
            CODNUMERI = ? 
        WHERE codigoprod = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param('sdsissss', $nombre, $precio1, $codbar, $selecc, $costo, $anotacion, $codnumeri, $id);

if ($stmt->execute()) {
    responderJSON('success', 'Producto actualizado correctamente.');
} else {
    responderJSON('error', 'Error al actualizar el producto: ' . $stmt->error);
}

$conn->close();
<?php
header('Content-Type: application/json');

require 'config.php'; // Asegúrate de que este archivo contiene la conexión a tu BD ($conn)

// Recibir el JSON enviado desde el cliente
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data || !isset($data['productos'])) {
    echo json_encode(['success' => false, 'message' => 'No se recibieron datos de productos.']);
    exit;
}

$productos = $data['productos'];
$agregados = 0;
$actualizados = 0;
$errores = 0;

foreach ($productos as $producto) {
    // Limpiar y validar datos
    $codnumeric = isset($producto['codnumeric']) ? trim($producto['codnumeric']) : null;
    $nombre = isset($producto['nombre']) ? trim($producto['nombre']) : '';
    $precio1 = isset($producto['precio1']) ? floatval($producto['precio1']) : 0;
    $cantcaja = isset($producto['cantcaja']) ? intval($producto['cantcaja']) : 0;

    if (empty($codnumeric)) {
        $errores++;
        continue; // Saltar si no hay código numérico
    }

    // 1. Verificar si el producto ya existe por CODNUMERI
    $stmt_check = $conn->prepare("SELECT codigoprod FROM producto WHERE CODNUMERI = ?");
    $stmt_check->bind_param("s", $codnumeric);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        // 2. Si existe, ACTUALIZAR
        $stmt_update = $conn->prepare("UPDATE producto SET nombre = ?, precio1 = ?, CANTCAJA = ? WHERE CODNUMERI = ?");
        $stmt_update->bind_param("sdis", $nombre, $precio1, $cantcaja, $codnumeric);
        if ($stmt_update->execute()) {
            $actualizados++;
        } else {
            $errores++;
        }
        $stmt_update->close();
    } else {
        // 3. Si no existe, INSERTAR
        // Nota: Asumimos que `codigoprod` es autoincremental o se genera de otra forma.
        // Aquí solo insertamos los campos proporcionados.
        $stmt_insert = $conn->prepare("INSERT INTO producto (nombre, precio1, CANTCAJA, CODNUMERI) VALUES (?, ?, ?, ?)");
        $stmt_insert->bind_param("sdis", $nombre, $precio1, $cantcaja, $codnumeric);
        if ($stmt_insert->execute()) {
            $agregados++;
        } else {
            $errores++;
        }
        $stmt_insert->close();
    }

    $stmt_check->close();
}

$conn->close();

$message = "Proceso completado. Productos nuevos: $agregados. Productos actualizados: $actualizados.";
if ($errores > 0) {
    $message .= " Errores: $errores.";
}

echo json_encode(['success' => true, 'message' => $message]);
?>
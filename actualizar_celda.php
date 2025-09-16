<?php
require 'config.php';

function validarYLimpiar($data, $tipo = 'string') {
    if ($data === null || $data === '') return null;
    
    switch ($tipo) {
        case 'numeric':
            if (!is_numeric($data)) return false;
            return (float)$data;
        case 'string':
        default:
            return trim($data);
    }
}

function responderJSON($status, $message = '') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status' => $status,
        'message' => $message
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        responderJSON('error', 'Método no permitido');
    }
    
    // Validar campos requeridos
    if (!isset($_POST['id'], $_POST['columna'], $_POST['valor'])) {
        responderJSON('error', 'Faltan parámetros requeridos');
    }
    
    $id = validarYLimpiar($_POST['id']);
    $columna = validarYLimpiar($_POST['columna']);
    $valor = $_POST['valor']; // No limpiar aún, depende del tipo
    
    // Validar columna permitida
    $columnasPermitidas = [
        'nombre' => 'string',
        'costo' => 'numeric',
        'precio1' => 'numeric',
        'codbar' => 'string',
        'selecc' => 'string'
    ];
    
    if (!isset($columnasPermitidas[$columna])) {
        responderJSON('error', 'Columna no permitida');
    }
    
    // Validar según tipo de dato
    $tipoColumna = $columnasPermitidas[$columna];
    $valorLimpio = validarYLimpiar($valor, $tipoColumna);
    
    if ($tipoColumna === 'numeric' && $valorLimpio === false) {
        responderJSON('error', 'El valor debe ser numérico');
    }
    
    if ($tipoColumna === 'numeric' && $valorLimpio < 0) {
        responderJSON('error', 'El valor debe ser mayor o igual a 0');
    }
    
    // Verificar que el producto existe
    $sqlCheck = "SELECT codigoprod FROM producto WHERE codigoprod = ?";
    $stmtCheck = $conn->prepare($sqlCheck);
    if (!$stmtCheck) {
        throw new Exception('Error al preparar consulta de verificación');
    }
    
    $stmtCheck->bind_param('s', $id);
    $stmtCheck->execute();
    $result = $stmtCheck->get_result();
    
    if ($result->num_rows === 0) {
        responderJSON('error', 'Producto no encontrado');
    }
    $stmtCheck->close();
    
    // Actualizar columna específica
    $sql = "UPDATE producto SET `$columna` = ? WHERE codigoprod = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception('Error al preparar la consulta');
    }
    
    // Bind según tipo
    if ($tipoColumna === 'numeric') {
        $stmt->bind_param('ds', $valorLimpio, $id);
    } else {
        $stmt->bind_param('ss', $valorLimpio, $id);
    }
    
    if ($stmt->execute()) {
        responderJSON('success', 'Campo actualizado correctamente');
    } else {
        throw new Exception('Error al actualizar');
    }
    
} catch (Exception $e) {
    error_log($e->getMessage());
    responderJSON('error', 'Error interno del servidor');
} finally {
    if (isset($stmt)) $stmt->close();
    if (isset($stmtCheck)) $stmtCheck->close();
    $conn->close();
}
?>
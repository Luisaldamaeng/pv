<?php
require 'config.php';

$id = '215';
echo "Intentando eliminar producto con codigoprod = '$id'...\n";

$sql = "DELETE FROM producto WHERE codigoprod = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $id);

if ($stmt->execute()) {
    echo "Ejecución exitosa.\n";
    echo "Filas afectadas: " . $stmt->affected_rows . "\n";
} else {
    echo "Error en la ejecución: " . $stmt->error . "\n";
}

$conn->close();
?>
<?php
require 'config.php';
$sql = "SELECT COUNT(*) as total FROM producto";
$result = $conn->query($sql);
if ($result) {
    $row = $result->fetch_assoc();
    echo "Conexión exitosa. Total de productos: " . $row['total'];
} else {
    echo "Error en la consulta: " . $conn->error;
}
$conn->close();
?>
<?php
// Incluir el archivo de configuración para la conexión a la base de datos
require 'config.php';

// Establecer la codificación de caracteres a UTF-8 para la conexión
$conn->set_charset("utf8");

// Consulta SQL para actualizar el campo nombre a minúsculas
$sql = "UPDATE producto SET nombre = LOWER(nombre)";

if ($conn->query($sql) === TRUE) {
    $affected_rows = $conn->affected_rows;
    echo "Actualización completada con éxito. " . $affected_rows . " filas fueron actualizadas.";
} else {
    echo "Error al actualizar la tabla: " . $conn->error;
}

// Cerrar la conexión a la base de datos
$conn->close();
?>
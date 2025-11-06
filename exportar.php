<?php

// Incluir el archivo de configuración para la conexión a la base de datos
require 'config.php';

// Establecer la codificación de caracteres a UTF-8 para la conexión
$conn->set_charset("utf8");

// Nombre del archivo de salida
$csv_file = 'productos.csv';

// Encabezados para el archivo CSV
$csv_headers = ['codigoprod', 'nombre', 'precio1'];

// Consulta SQL para seleccionar los campos deseados de la tabla producto, con el campo nombre en minúsculas
$sql = "SELECT codigoprod, LOWER(nombre) AS nombre, precio1 FROM producto";

$result = $conn->query($sql);

if ($result) {
    // Abrir el archivo en modo escritura
    $output = fopen($csv_file, 'w');

    if ($output) {
        // Escribir el BOM de UTF-8 para compatibilidad con Excel
        fwrite($output, "\xEF\xBB\xBF");

        // Escribir los encabezados en el archivo CSV
        fputcsv($output, $csv_headers);

        // Recorrer los resultados y escribirlos en el archivo CSV
        while ($row = $result->fetch_assoc()) {
            fputcsv($output, $row);
        }

        fclose($output);
        echo "Los datos se han exportado correctamente al archivo " . $csv_file;
    } else {
        echo "Error: No se pudo abrir el archivo para escritura.";
    }

    $result->free();
} else {
    echo "Error en la consulta: " . $conn->error;
}

// Cerrar la conexión a la base de datos
$conn->close();

?>
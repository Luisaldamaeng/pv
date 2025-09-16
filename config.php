<?php

/**
 * Conexión a base de datos de MySQL con PHP
 *
 * @author mroblesdev
 * @link https://github.com/mroblesdev/server-side-php
 * @license: MIT
 */


// Creando una nueva conexión a la base de datos.
$conn = new mysqli("127.0.0.1", "root", "", "almacen");

// Comprobando si hay un error de conexión.
if ($conn->connect_error) {
    echo 'Error de conexion ' . $conn->connect_error;
    exit;
}

/*
NOTA: El código para actualizar contraseñas ha sido comentado.
Este bloque se ejecutaba en cada carga, generando un nuevo hash de contraseña
y haciendo que el login fallara siempre. Este tipo de lógica debe estar en un
script de configuración que se ejecuta una sola vez.
Para que el login funcione, primero debes establecer las contraseñas en tu base de datos
manualmente o con un script separado.
*/

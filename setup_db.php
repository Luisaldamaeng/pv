<?php
require 'config.php';

$sql = "CREATE TABLE IF NOT EXISTS costo_mercaderia (
    codigoprod INT PRIMARY KEY,
    porcentaje DECIMAL(10, 2) DEFAULT 0,
    precio_caja DECIMAL(10, 2) DEFAULT 0,
    cantidad DECIMAL(10, 2) DEFAULT 0,
    precio_costo DECIMAL(10, 2) DEFAULT 0,
    FOREIGN KEY (codigoprod) REFERENCES producto(CODIGOPROD)
)";

if ($conn->query($sql) === TRUE) {
    echo "Table costo_mercaderia created successfully";
} else {
    echo "Error creating table: " . $conn->error;
}

$conn->close();
?>
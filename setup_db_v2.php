<?php
$conn = new mysqli("localhost", "root", "", "pv");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "CREATE TABLE IF NOT EXISTS costo_mercaderia (
    codigoprod INT PRIMARY KEY,
    porcentaje DECIMAL(10, 2) DEFAULT 0,
    precio_caja DECIMAL(10, 2) DEFAULT 0,
    cantidad DECIMAL(10, 2) DEFAULT 0,
    precio_costo DECIMAL(10, 2) DEFAULT 0
)";

if ($conn->query($sql) === TRUE) {
    echo "Table costo_mercaderia created successfully";
} else {
    echo "Error creating table: " . $conn->error;
}
$conn->close();
?>
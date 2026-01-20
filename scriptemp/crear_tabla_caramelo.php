<?php
require __DIR__ . '/../config.php';

$sql = "CREATE TABLE IF NOT EXISTS costo_caramelo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigoprod INT NOT NULL,
    precio_bolsa DECIMAL(15,2) DEFAULT 0,
    peso_bolsa DECIMAL(15,2) DEFAULT 0,
    cantidad_golosinas INT DEFAULT 0,
    peso_muestra DECIMAL(15,2) DEFAULT 0,
    porcentaje_ganancia DECIMAL(5,2) DEFAULT 0,
    UNIQUE KEY (codigoprod),
    CONSTRAINT fk_producto_caramelo FOREIGN KEY (codigoprod) REFERENCES producto(CODIGOPROD) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

if ($conn->query($sql) === TRUE) {
    echo json_encode(['status' => 'success', 'message' => 'Tabla costo_caramelo creada correctamente.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Error al crear la tabla: ' . $conn->error]);
}

$conn->close();
?>
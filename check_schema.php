<?php
require 'config.php';
$res = $conn->query("DESCRIBE producto");
while ($row = $res->fetch_assoc()) {
    if ($row['Field'] == 'codigoprod') {
        echo json_encode($row, JSON_PRETTY_PRINT);
    }
}
?>
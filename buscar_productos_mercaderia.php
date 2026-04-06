<?php
require 'config.php';

$term = $_GET['q'] ?? '';

$sql = "SELECT CODIGOPROD as id, NOMBRE as nombre FROM producto WHERE NOMBRE LIKE ? LIMIT 10";
$stmt = $conn->prepare($sql);
$likeTerm = "%" . $term . "%";
$stmt->bind_param('s', $likeTerm);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

header('Content-Type: application/json');
echo json_encode($data);

$stmt->close();
$conn->close();
?>
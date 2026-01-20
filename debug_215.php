<?php
require 'config.php';

$id = '215';
$sql = "SELECT codigoprod, nombre FROM producto WHERE codigoprod = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $id);
$stmt->execute();
$result = $stmt->get_result();

header('Content-Type: application/json');

if ($row = $result->fetch_assoc()) {
    echo json_encode([
        'status' => 'found',
        'data' => $row,
        'id_sent' => $id,
        'length' => strlen($row['codigoprod'])
    ]);
} else {
    // Try to find it with LIKE to see if there are spaces
    $sql_like = "SELECT codigoprod, nombre FROM producto WHERE codigoprod LIKE ?";
    $stmt_like = $conn->prepare($sql_like);
    $search = "%215%";
    $stmt_like->bind_param('s', $search);
    $stmt_like->execute();
    $result_like = $stmt_like->get_result();

    $others = [];
    while ($r = $result_like->fetch_assoc()) {
        $others[] = [
            'codigoprod' => $r['codigoprod'],
            'length' => strlen($r['codigoprod']),
            'nombre' => $r['nombre']
        ];
    }

    echo json_encode([
        'status' => 'not_found',
        'id_sent' => $id,
        'matched_like' => $others
    ]);
}
?>
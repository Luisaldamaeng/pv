<?php

require 'config.php';

$columns = ['codigoprod', 'nombre', 'precio1', 'codbar', 'selecc', 'costo'];
$table = "producto";
$id = 'codigoprod';

    $campo = isset($_POST['campo']) ? $conn->real_escape_string($_POST['campo']) : null;
    $busqueda_codigoprod = isset($_POST['busqueda_codigoprod']) ? $conn->real_escape_string($_POST['busqueda_codigoprod']) : null;
    $busqueda_codbar = isset($_POST['busqueda_codbar']) ? $conn->real_escape_string($_POST['busqueda_codbar']) : null;

    $where = '';
    $params = [];
    $types = '';
    $whereConditions = [];

    if (!empty($busqueda_codigoprod)) {
        $whereConditions[] = "codigoprod LIKE ?";
        $params[] = "%" . $busqueda_codigoprod . "%";
        $types .= 's';
    }
    
    if (!empty($busqueda_codbar)) {
        $whereConditions[] = "codbar LIKE ?";
        $params[] = "%" . $busqueda_codbar . "%";
        $types .= 's';
    }
    
    if (!empty($campo)) {
        $whereConditions[] = "nombre LIKE ?";
        $params[] = "%" . $campo . "%";
        $types .= 's';
    }

    if (!empty($whereConditions)) {
        $where = "WHERE " . implode(' AND ', $whereConditions);
    }

$limit = isset($_POST['registros']) ? intval($_POST['registros']) : 10;
$pagina = isset($_POST['pagina']) ? intval($_POST['pagina']) : 1;
$inicio = ($pagina - 1) * $limit;
$sLimit = "LIMIT $inicio , $limit";

$sOrder = "ORDER BY codigoprod ASC";
if (isset($_POST['orderCol'])) {
    $orderCol = intval($_POST['orderCol']);
    $orderType = isset($_POST['orderType']) ? $_POST['orderType'] : 'asc';
    if (in_array(strtolower($orderType), ['asc', 'desc']) && isset($columns[$orderCol])) {
        $sOrder = "ORDER BY " . $columns[$orderCol] . ' ' . $orderType;
    }
}

$sql = "SELECT " . implode(", ", $columns) . " FROM $table $where $sOrder $sLimit";
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$resultado = $stmt->get_result();
$num_rows = $resultado->num_rows;

$sqlFiltro = "SELECT COUNT($id) AS total FROM $table $where";
$stmtFiltro = $conn->prepare($sqlFiltro);
if ($stmtFiltro && !empty($params)) {
    $stmtFiltro->bind_param($types, ...$params);
}
$stmtFiltro->execute();
$totalFiltro = $stmtFiltro->get_result()->fetch_assoc()['total'];

$totalRegistros = $conn->query("SELECT count($id) FROM $table")->fetch_row()[0];

$output = [];
$output['totalRegistros'] = $totalRegistros;
$output['totalFiltro'] = $totalFiltro;
$output['data'] = '';
$output['paginacion'] = '';

if ($num_rows > 0) {
    while ($row = $resultado->fetch_assoc()) {
        $output['data'] .= '<tr>';
        $output['data'] .= '<td class="puntero-celda"></td>';
        $output['data'] .= '<td>' . htmlspecialchars($row['codigoprod'] ?? '') . '</td>';
        $output['data'] .= '<td contenteditable="true" data-col="nombre" data-id="' . htmlspecialchars($row['codigoprod'] ?? '') . '">' . htmlspecialchars($row['nombre'] ?? '') . '</td>';
        $output['data'] .= '<td contenteditable="true" data-col="precio1" data-id="' . htmlspecialchars($row['codigoprod'] ?? '') . '">' . number_format($row['precio1'] ?? 0, 0, ',', '.') . '</td>';
        $output['data'] .= '<td contenteditable="true" data-col="codbar" data-id="' . htmlspecialchars($row['codigoprod'] ?? '') . '">' . htmlspecialchars($row['codbar'] ?? '') . '</td>';
        $output['data'] .= '<td contenteditable="true" data-col="selecc" data-id="' . htmlspecialchars($row['codigoprod'] ?? '') . '">' . htmlspecialchars($row['selecc'] ?? '') . '</td>';
        $output['data'] .= '<td contenteditable="true" data-col="costo" data-id="' . htmlspecialchars($row['codigoprod'] ?? '') . '">' . number_format($row['costo'] ?? 0, 0, ',', '.') . '</td>';
        
        $editButton = '<a class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editaModal" ';
        $editButton .= 'data-id="' . htmlspecialchars($row['codigoprod'] ?? '', ENT_QUOTES, 'UTF-8') . '" ';
        $editButton .= 'data-nombre="' . htmlspecialchars($row['nombre'] ?? '', ENT_QUOTES, 'UTF-8') . '" ';
        $editButton .= 'data-costo="' . htmlspecialchars($row['costo'] ?? '', ENT_QUOTES, 'UTF-8') . '" ';
        $editButton .= 'data-precio1="' . htmlspecialchars($row['precio1'] ?? '', ENT_QUOTES, 'UTF-8') . '" ';
        $editButton .= 'data-codbar="' . htmlspecialchars($row['codbar'] ?? '', ENT_QUOTES, 'UTF-8') . '" ';
        $editButton .= 'data-selecc="' . htmlspecialchars($row['selecc'] ?? '', ENT_QUOTES, 'UTF-8') . '">Editar</a>';

        $output['data'] .= '<td>' . $editButton . '</td>';
        $output['data'] .= '<td><a class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#eliminaModal" data-id="' . htmlspecialchars($row['codigoprod'] ?? '', ENT_QUOTES, 'UTF-8') . '">Eliminar</a></td>';
        $output['data'] .= '</tr>';
    }
} else {
    $output['data'] .= '<tr><td colspan="8">Sin resultados</td></tr>';
}

if ($totalRegistros > 0) {
    $totalPaginas = ceil($totalFiltro / $limit);
    $output['paginacion'] .= '<nav><ul class="pagination">';
    $numeroInicio = max(1, $pagina - 4);
    $numeroFin = min($totalPaginas, $numeroInicio + 9);
    for ($i = $numeroInicio; $i <= $numeroFin; $i++) {
        $output['paginacion'] .= '<li class="page-item' . ($pagina == $i ? ' active' : '') . '"><a class="page-link" href="#" onclick="nextPage(' . $i . ')">' . $i . '</a></li>';
    }
    $output['paginacion'] .= '</ul></nav>';
}

echo json_encode($output, JSON_UNESCAPED_UNICODE);

?>
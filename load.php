<?php

require 'config.php';

$columns = ['codigoprod', 'nombre', 'precio1', 'codbar', 'selecc', 'costo', 'CANTCAJA', 'CODNUMERI'];
$table = "producto";
$id = 'codigoprod';

    $campo = isset($_POST['campo']) ? $conn->real_escape_string($_POST['campo']) : null;
    $busqueda_codigoprod = isset($_POST['busqueda_codigoprod']) ? $conn->real_escape_string($_POST['busqueda_codigoprod']) : null;
    $busqueda_codbar = isset($_POST['busqueda_codbar']) ? $conn->real_escape_string($_POST['busqueda_codbar']) : null;
    $filtro_selecc = isset($_POST['filtro_selecc']) ? $_POST['filtro_selecc'] : '0';

    $where = '';
    $params = [];
    $types = '';
    $whereConditions = [];

    if ($filtro_selecc === '1') {
        $whereConditions[] = "selecc = 1";
    }

    if (!empty($busqueda_codigoprod)) {
        $whereConditions[] = "codigoprod = ?";
        $params[] = $busqueda_codigoprod;
        $types .= 's';
    }
    
    if (!empty($busqueda_codbar)) {
        $whereConditions[] = "codbar = ?";
        $params[] = $busqueda_codbar;
        $types .= 's';
    }
    
    if (!empty($campo)) {
        // Dividir la frase de búsqueda en palabras individuales
        $palabras = explode(' ', $campo);
        // Crear una condición LIKE para cada palabra
        foreach ($palabras as $palabra) {
            $whereConditions[] = "nombre LIKE ?";
            $params[] = "%" . $palabra . "%";
            $types .= 's';
        }
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
        $columnaOrdenar = $columns[$orderCol];
        // Si la columna es 'nombre', la ordenamos usando LOWER para que sea insensible a mayúsculas/minúsculas
        if ($columnaOrdenar === 'nombre') {
            $columnaOrdenar = "LOWER(nombre)";
        }
        $sOrder = "ORDER BY " . $columnaOrdenar . ' ' . $orderType;
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
        $output['data'] .= '<td>' . htmlspecialchars($row['codigoprod'] ?? '', ENT_QUOTES, 'UTF-8') . '</td>';
        $output['data'] .= '<td contenteditable="true" data-col="nombre" data-id="' . htmlspecialchars($row['codigoprod'] ?? '', ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($row['nombre'] ?? '', ENT_QUOTES, 'UTF-8') . '</td>';
        $output['data'] .= '<td contenteditable="true" data-col="precio1" data-id="' . htmlspecialchars($row['codigoprod'] ?? '', ENT_QUOTES, 'UTF-8') . '">' . number_format($row['precio1'] ?? 0, 0, ',', '.') . '</td>';
        $output['data'] .= '<td contenteditable="true" data-col="codbar" data-id="' . htmlspecialchars($row['codigoprod'] ?? '', ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($row['codbar'] ?? '', ENT_QUOTES, 'UTF-8') . '</td>';
        $checked = ($row['selecc'] ?? 0) == 1 ? 'checked' : '';
        $output['data'] .= '<td class="text-center"><input type="checkbox" class="form-check-input selecc-checkbox" data-id="' . htmlspecialchars($row['codigoprod'] ?? '', ENT_QUOTES, 'UTF-8') . '" ' . $checked . '></td>';
        $output['data'] .= '<td contenteditable="true" data-col="costo" data-id="' . htmlspecialchars($row['codigoprod'] ?? '', ENT_QUOTES, 'UTF-8') . '">' . number_format((float)($row['costo'] ?? 0), 0, ',', '.') . '</td>';
        $output['data'] .= '<td contenteditable="true" data-col="CANTCAJA" data-id="' . htmlspecialchars($row['codigoprod'] ?? '', ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($row['CANTCAJA'] ?? '', ENT_QUOTES, 'UTF-8') . '</td>';
        $output['data'] .= '<td contenteditable="true" data-col="CODNUMERI" data-id="' . htmlspecialchars($row['codigoprod'] ?? '', ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($row['CODNUMERI'] ?? '', ENT_QUOTES, 'UTF-8') . '</td>';
        
        $editButton = '<a class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editaModal" ';
        $editButton .= 'data-id="' . htmlspecialchars($row['codigoprod'] ?? '', ENT_QUOTES, 'UTF-8') . '" ';
        $editButton .= 'data-nombre="' . htmlspecialchars($row['nombre'] ?? '', ENT_QUOTES, 'UTF-8') . '" ';
        $editButton .= 'data-costo="' . htmlspecialchars($row['costo'] ?? '', ENT_QUOTES, 'UTF-8') . '" ';
        $editButton .= 'data-precio1="' . htmlspecialchars($row['precio1'] ?? '', ENT_QUOTES, 'UTF-8') . '" ';
        $editButton .= 'data-codbar="' . htmlspecialchars($row['codbar'] ?? '', ENT_QUOTES, 'UTF-8') . '" ';
        $editButton .= 'data-cantcaja="' . htmlspecialchars($row['CANTCAJA'] ?? '', ENT_QUOTES, 'UTF-8') . '" ';
        $editButton .= 'data-codnumeri="' . htmlspecialchars($row['CODNUMERI'] ?? '', ENT_QUOTES, 'UTF-8') . '" ';
        $editButton .= 'data-selecc="' . htmlspecialchars($row['selecc'] ?? '', ENT_QUOTES, 'UTF-8') . '">Editar</a>';

        $output['data'] .= '<td>' . $editButton . '</td>';
        $output['data'] .= '<td><a class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#eliminaModal" data-id="' . htmlspecialchars($row['codigoprod'] ?? '', ENT_QUOTES, 'UTF-8') . '">Eliminar</a></td>';
        $output['data'] .= '</tr>';
    }
} else {
    $output['data'] .= '<tr><td colspan="11">Sin resultados</td></tr>';
}

if ($totalFiltro > 0) {
    $totalPaginas = ceil($totalFiltro / $limit);

    if ($totalPaginas > 1) {
        $output['paginacion'] .= '<nav><ul class="pagination">';

        // Botón Anterior
        $paginaAnterior = $pagina - 1;
        $deshabilitadoAnterior = ($pagina <= 1) ? " disabled" : "";
        $output['paginacion'] .= "<li class=\"page-item{$deshabilitadoAnterior}\"><a class=\"page-link\" href=\"#\" onclick=\"nextPage({$paginaAnterior})\">Anterior</a></li>";

        // Números de página
        $numeroInicio = max(1, $pagina - 2);
        $numeroFin = min($totalPaginas, $pagina + 2);

        if ($numeroInicio > 1) {
            $output['paginacion'] .= '<li class="page-item"><a class="page-link" href="#" onclick="nextPage(1)">1</a></li>';
            if ($numeroInicio > 2) {
                $output['paginacion'] .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
        }

        for ($i = $numeroInicio; $i <= $numeroFin; $i++) {
            $claseActiva = ($pagina == $i) ? ' active' : '';
            $output['paginacion'] .= "<li class=\"page-item{$claseActiva}\"><a class=\"page-link\" href=\"#\" onclick=\"nextPage({$i})\">{$i}</a></li>";
        }

        if ($numeroFin < $totalPaginas) {
            if ($numeroFin < $totalPaginas - 1) {
                $output['paginacion'] .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
            $output['paginacion'] .= "<li class=\"page-item\"><a class=\"page-link\" href=\"#\" onclick=\"nextPage({$totalPaginas})\">{$totalPaginas}</a></li>";
        }

        // Botón Siguiente
        $paginaSiguiente = $pagina + 1;
        $deshabilitadoSiguiente = ($pagina >= $totalPaginas) ? " disabled" : "";
        $output['paginacion'] .= "<li class=\"page-item{$deshabilitadoSiguiente}\"><a class=\"page-link\" href=\"#\" onclick=\"nextPage({$paginaSiguiente})\">Siguiente</a></li>";

        $output['paginacion'] .= '</ul></nav>';
    }
}

echo json_encode($output, JSON_UNESCAPED_UNICODE);

?>
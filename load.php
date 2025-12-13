<?php

// Asegurar que la salida sea JSON y evitar que PHP emita HTML de errores
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
// Convertir warnings/notices en excepciones para capturarlas y devolver JSON
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

try {
    require 'config.php';

$columns = ['codigoprod', 'nombre', 'precio1', 'codbar', 'selecc', 'costo', 'CANTCAJA', 'foto'];
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
        $sOrder = "ORDER BY " . $columnaOrdenar . ' ' . $orderType;
    }
}

$sql = "SELECT " . implode(", ", $columns) . " FROM $table $where $sOrder $sLimit";
$stmt = $conn->prepare($sql);
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    throw new Exception('Error al preparar la consulta: ' . $conn->error . ' SQL: ' . $sql);
}
if (!empty($params)) {
    if (!$stmt->bind_param($types, ...$params)) {
        throw new Exception('Error al bind_param: ' . $stmt->error);
    }
}
if (!$stmt->execute()) {
    throw new Exception('Error al ejecutar la consulta: ' . $stmt->error);
}
$resultado = $stmt->get_result();
$num_rows = $resultado->num_rows;

$sqlFiltro = "SELECT COUNT($id) AS total FROM $table $where";
// Preparar y ejecutar consulta para el total filtrado
$stmtFiltro = $conn->prepare($sqlFiltro);
if ($stmtFiltro === false) {
    throw new Exception('Error al preparar la consulta de filtro: ' . $conn->error . ' SQL: ' . $sqlFiltro);
}
if (!empty($params)) {
    if (!$stmtFiltro->bind_param($types, ...$params)) {
        throw new Exception('Error al bind_param en filtro: ' . $stmtFiltro->error);
    }
}
if (!$stmtFiltro->execute()) {
    throw new Exception('Error al ejecutar la consulta de filtro: ' . $stmtFiltro->error);
}
$resFiltro = $stmtFiltro->get_result();
if ($resFiltro === false) {
    throw new Exception('Error al obtener resultado del filtro: ' . $stmtFiltro->error);
}
$totalFiltro = $resFiltro->fetch_assoc()['total'];

$totalRegistros = $conn->query("SELECT count($id) FROM $table")->fetch_row()[0];

$output = [];
$output['totalRegistros'] = $totalRegistros;
$output['totalFiltro'] = $totalFiltro;
$output['data'] = '';
$output['paginacion'] = '';

if ($num_rows > 0) {
    while ($row = $resultado->fetch_assoc()) {
        
        // Mover todos los atributos de datos a la etiqueta <tr>
        $tr_attributes = 'data-id="' . htmlspecialchars($row['codigoprod'] ?? '', ENT_QUOTES, 'UTF-8') . '" ';
        $tr_attributes .= 'data-nombre="' . htmlspecialchars($row['nombre'] ?? '', ENT_QUOTES, 'UTF-8') . '" ';
        $tr_attributes .= 'data-costo="' . htmlspecialchars($row['costo'] ?? '', ENT_QUOTES, 'UTF-8') . '" ';
        $tr_attributes .= 'data-precio1="' . htmlspecialchars($row['precio1'] ?? '', ENT_QUOTES, 'UTF-8') . '" ';
        $tr_attributes .= 'data-codbar="' . htmlspecialchars($row['codbar'] ?? '', ENT_QUOTES, 'UTF-8') . '" ';
        $tr_attributes .= 'data-cantcaja="' . htmlspecialchars($row['CANTCAJA'] ?? '', ENT_QUOTES, 'UTF-8') . '" ';
        $tr_attributes .= 'data-selecc="' . htmlspecialchars($row['selecc'] ?? '', ENT_QUOTES, 'UTF-8') . '"';

        $output['data'] .= "<tr $tr_attributes>";
        $output['data'] .= '<td class="puntero-celda"></td>';

        // Columna Foto: solo botón con icono 📸
        $output['data'] .= '<td class="text-center"><button type="button" class="btn btn-sm btn-outline-primary camera-btn" data-codigo="' . htmlspecialchars($row['codigoprod'] ?? '', ENT_QUOTES, 'UTF-8') . '" data-nombre="' . htmlspecialchars($row['nombre'] ?? '', ENT_QUOTES, 'UTF-8') . '" data-foto="' . htmlspecialchars($row['foto'] ?? '', ENT_QUOTES, 'UTF-8') . '">📸</button></td>';
        
        // Almacenar la foto en un atributo data- para mostrarla en el preview
        $tr_attributes_foto = 'data-foto="' . htmlspecialchars($row['foto'] ?? '', ENT_QUOTES, 'UTF-8') . '"';
        
        $output['data'] .= '<td>' . htmlspecialchars($row['codigoprod'] ?? '', ENT_QUOTES, 'UTF-8') . '</td>';
        $output['data'] .= '<td contenteditable="true" data-col="nombre" data-id="' . htmlspecialchars($row['codigoprod'] ?? '', ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($row['nombre'] ?? '', ENT_QUOTES, 'UTF-8') . '</td>';
        $output['data'] .= '<td contenteditable="true" data-col="precio1" data-id="' . htmlspecialchars($row['codigoprod'] ?? '', ENT_QUOTES, 'UTF-8') . '">' . number_format($row['precio1'] ?? 0, 0, ',', '.') . '</td>';
        $output['data'] .= '<td contenteditable="true" data-col="codbar" data-id="' . htmlspecialchars($row['codigoprod'] ?? '', ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($row['codbar'] ?? '', ENT_QUOTES, 'UTF-8') . '</td>';
        $checked = ($row['selecc'] ?? 0) == 1 ? 'checked' : '';
        $output['data'] .= '<td class="text-center"><input type="checkbox" class="form-check-input selecc-checkbox" data-id="' . htmlspecialchars($row['codigoprod'] ?? '', ENT_QUOTES, 'UTF-8') . '" ' . $checked . '></td>';
        $output['data'] .= '<td contenteditable="true" data-col="costo" data-id="' . htmlspecialchars($row['codigoprod'] ?? '', ENT_QUOTES, 'UTF-8') . '">' . number_format((float)($row['costo'] ?? 0), 0, ',', '.') . '</td>';
        $output['data'] .= '<td contenteditable="true" data-col="CANTCAJA" data-id="' . htmlspecialchars($row['codigoprod'] ?? '', ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($row['CANTCAJA'] ?? '', ENT_QUOTES, 'UTF-8') . '</td>';
        $output['data'] .= '<td data-col="foto" data-id="' . htmlspecialchars($row['codigoprod'] ?? '', ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($row['foto'] ?? '', ENT_QUOTES, 'UTF-8') . '</td>';
        
        // Se eliminan los botones de editar y eliminar de la fila.

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

} catch (Throwable $e) {
    http_response_code(500);
    $resp = [
        'status' => 'error',
        'message' => 'Error interno al cargar datos',
        'detail' => $e->getMessage()
    ];
    echo json_encode($resp, JSON_UNESCAPED_UNICODE);
}

// Restaurar manejador de errores por si se reutiliza el script
restore_error_handler();

?>
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="Buscar datos en tiempo real con PHP, MySQL y AJAX">
    <meta name="author" content="Marco Robles">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Almacen</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <style>
        body {
            background-color: #add8e6;
        }
        .modal-custom .modal-content {
            background-color: #ffffe0;
        }
        .modal-custom .modal-body,
        .modal-custom .modal-title {
            color: red;
        }
        .modal-custom .form-control {
            border: 1px solid #000;
        }
        .modal-custom label {
            color: red;
        }
        .puntero-celda {
            width: 20px;
            font-weight: bold;
            color: red;
            font-size: 1.2rem;
            text-align: center;
        }
        .label-naranja {
            background-color: orange;
            color: black;
            font-weight: bold;
            border-color: orange;
        }

        /* --- Estilos para tabla con encabezado fijo y cuerpo scrollable --- */
        .table-responsive {
            overflow-y: visible; /* Permitir que .table-body-scroll maneje el scroll vertical */
        }
        .table-body-scroll {
            max-height: 400px; /* Altura máxima antes de que aparezca el scroll */
            overflow-y: auto; /* Habilitar scroll vertical */
            border-bottom: 1px solid #dee2e6; /* Borde inferior para separar visualmente */
        }
        .table-responsive table {
            margin-bottom: 0 !important; /* Eliminar margen inferior de la tabla */
            width: 100%;
            table-layout: fixed; /* Asegurar anchos de columna consistentes */
        }
        .table-responsive thead, .table-responsive tbody {
            display: table; /* Cambiado a table para mejor alineación */
            width: 100%;
            table-layout: fixed;
        }
        .table-responsive thead tr {
            position: sticky; /* Fija el encabezado */
            top: 0;
            background-color: #f8f9fa; /* Fondo para el encabezado fijo */
            z-index: 1;
            box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.1); /* Sombra para resaltar */
        }
        .table-responsive th, .table-responsive td {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            padding: 0.75rem; /* Ajustar padding si es necesario */
            vertical-align: middle;
            border-top: 1px solid #dee2e6;
            border-left: 1px solid #dee2e6; /* Añadir borde izquierdo */
            border-right: 1px solid #dee2e6; /* Añadir borde derecho */
        }
        .table-responsive th:first-child, .table-responsive td:first-child { border-left: none; }
        .table-responsive th:last-child, .table-responsive tbody tr td:last-child { border-right: none; }
        
        /* Anchos específicos para cada columna (ajustar según el contenido) */
        .table-responsive th:nth-child(1), .table-responsive tbody td:nth-child(1) { width: 30px; } /* Puntero */
        .table-responsive th:nth-child(2), .table-responsive tbody td:nth-child(2) { width: 60px; } /* Foto */
        .table-responsive th:nth-child(3), .table-responsive tbody td:nth-child(3) { width: 100px; } /* Cód. Prod. */
        .table-responsive th:nth-child(4), .table-responsive tbody td:nth-child(4) { width: 250px; } /* Nombre */
        .table-responsive th:nth-child(5), .table-responsive tbody td:nth-child(5) { width: 100px; } /* Precio 1 */
        .table-responsive th:nth-child(6), .table-responsive tbody td:nth-child(6) { width: 120px; } /* Cód. Barra */
        .table-responsive th:nth-child(7), .table-responsive tbody td:nth-child(7) { width: 80px; } /* Selecc */
        .table-responsive th:nth-child(8), .table-responsive tbody td:nth-child(8) { width: 100px; } /* Costo */
        .table-responsive th:nth-child(9), .table-responsive tbody td:nth-child(9) { width: 100px; } /* Cant. Caja */
        .table-responsive th:nth-child(10), .table-responsive tbody td:nth-child(10) { width: 150px; } /* Ruta Foto */
        /* Fin de anchos específicos */

        @media print {
            body * {
                visibility: hidden;
            }
            #print-area, #print-area * {
                visibility: visible;
            }
            #print-area {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }

            /* --- Estilos para Impresión de Tabla --- */
            #print-area .table .btn,
            #print-area .puntero-celda,
            #print-area thead th:first-child,
            #print-area thead th:nth-last-child(1),
            #print-area thead th:nth-last-child(2) {
                display: none;
            }

            /* --- Estilos para Impresión de Etiquetas --- */
            #print-area .label-print-container {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 0;
                width: 100%;
            }
            #print-area .label-item {
                border: 1px solid #000;
                padding: 10px;
                text-align: center;
                font-family: Arial, sans-serif;
                page-break-inside: avoid;
            }
            #print-area .label-code {
                text-align: left;
                font-size: 22pt;
            }
            #print-area .label-name {
                font-weight: bold;
                font-size: 16pt;
                text-transform: uppercase;
                margin: 10px 0;
                min-height: 50px; /* Para alinear precios */
            }
            #print-area .label-price {
                font-weight: bold;
                font-size: 22pt;
                text-align: right;
            }
            .footer-controls {
                display: flex;
                justify-content: space-between;
            }
        }
    </style>
</head>

<body>
    <main>
        <div class="container py-4 text-center">
                        <div class="row">
                <div class="col-12 text-center">
                    <h2>Productos <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#nuevoModal">Nuevo</button></h2>
                </div>
            </div>

            <!-- Fila de controles de búsqueda y acciones -->
            <div class="row g-3 justify-content-center pt-3">
                <div class="col-md-3">
                    <div class="input-group input-group-sm">
                        <label for="busqueda_codigoprod" class="input-group-text label-naranja">cod prod</label>
                        <input type="text" class="form-control" id="busqueda_codigoprod" name="busqueda_codigoprod">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="input-group input-group-sm">
                        <label for="busqueda_codbar" class="input-group-text label-naranja">codbar</label>
                        <input type="text" class="form-control" id="busqueda_codbar" name="busqueda_codbar">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="input-group input-group-sm">
                        <label for="campo" class="input-group-text">Buscar:</label>
                        <input type="text" name="campo" id="campo" class="form-control">
                    </div>
                </div>
            </div>
            <div class="row g-2 justify-content-center pt-2">
                <div class="col-auto">
                    <button type="button" id="btn-limpiar" class="btn btn-secondary btn-sm">Limpiar</button>
                    <button type="button" id="btn-duplicar" class="btn btn-success btn-sm">Duplicar en Tabla</button>
                    <button type="button" id="btn-imprimir" class="btn btn-info btn-sm">Imprimir</button>
                    <button type="button" id="btn-etiquetas" class="btn btn-success btn-sm">Etiquetas</button>
                    <button type="button" id="btn-reset-selecc" class="btn btn-warning btn-sm">Poner Selecc a
                        Cero</button>
                    <button type="button" class="btn btn-primary btn-sm" onclick="window.open('costo_mercaderia.html', '_blank')">
                        Costo
                        Mercaderia</button>
                    <button type="button" class="btn btn-dark btn-sm" onclick="window.open('costo_caramelo.html', '_blank')">
                        Costo
                        Caramelo</button>
                </div>
            </div>

            <div class="row py-4">
                <div class="col">
                    <div class="table-responsive">
                    <table class="table table-sm table-bordered table-striped">
                        <thead>
                           <tr>
                            <th></th>
                            <th class="sort asc">Foto</th>
                            <th class="sort asc">Cód. Prod.</th>
                            <th class="sort asc">Nombre</th>
                            <th class="sort asc">Precio 1</th>
                            <th class="sort asc">Cód. Barra</th>
                            <th id="filtro-selecc" style="cursor: pointer;">Selecc <span id="filtro-selecc-icono"></span></th>
                            <th class="sort asc">Costo</th>
                            <th class="sort asc">Cant. Caja</th>
                            <th class="sort asc">Ruta Foto</th>
                           </tr>
                        </thead>
                        <div class="table-body-scroll">
                            <tbody id="content"></tbody>
                        </div>
                    </table>
                    </div>
                </div>
            </div>



            <div class="row justify-content-between footer-controls">
                <div class="col-auto">
                    <label id="lbl-total"></label>
                </div>
                <div class="col-auto" id="nav-paginacion"></div>
                <div class="col-auto">
                    <div class="row g-2">
                        <div class="col-auto">
                            <label for="num_registros" class="col-form-label">Mostrar:</label>
                        </div>
                        <div class="col-auto">
                            <select name="num_registros" id="num_registros" class="form-select form-select-sm">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100" selected>100</option>
                            </select>
                        </div>
                        <div class="col-auto">
                            <label for="num_registros" class="col-form-label">registros</label>
                        </div>
                    </div>
                </div>
                <input type="hidden" id="pagina" value="1">
                <input type="hidden" id="orderCol" value="2"> <!-- Columna 'nombre' -->
                <input type="hidden" id="orderType" value="asc"> <!-- Orden ascendente -->
                <input type="hidden" id="filtroSeleccActivo" value="0">
            </div>
            
        </div>
    </main>

    <!-- Modal de la Cámara -->
    <div class="modal fade" id="cameraModal" tabindex="-1" aria-labelledby="cameraModalLabel" aria-hidden="true" data-bs-focus="false">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cameraModalLabel">Tomar Foto del Producto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <video id="camera-stream" width="100%" height="auto" autoplay playsinline style="display: none;"></video>
                    <canvas id="camera-canvas" style="display: none;"></canvas>
                     <div id="camera-placeholder">Presiona "Abrir Cámara" para iniciar.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="btn-close-camera">Cerrar</button>
                    <button type="button" class="btn btn-primary" id="btn-open-camera">Abrir Cámara</button>
                    <button type="button" class="btn btn-success" id="btn-take-photo" disabled>Tomar Foto</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Edita -->
    <div class="modal fade" id="editaModal" tabindex="-1" aria-labelledby="editaModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-custom">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editaModalLabel">Editar Producto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="form-edita">
                        <input type="hidden" id="edita-id" name="id">
                        <div class="mb-3">
                            <label for="edita-nombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="edita-nombre" name="nombre" required x-webkit-speech
                                speech>
                        </div>
                        <div class="mb-3">
                            <label for="edita-precio1" class="form-label">Precio 1</label>
                            <input type="number" step="any" class="form-control" id="edita-precio1" name="precio1">
                        </div>
                        <div class="mb-3">
                            <label for="edita-codbar" class="form-label">Código de Barra</label>
                            <input type="text" class="form-control" id="edita-codbar" name="codbar" x-webkit-speech speech>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="edita-selecc" name="selecc">
                            <label for="edita-selecc" class="form-check-label">Seleccionado</label>
                        </div>
                        <div class="mb-3">
                            <label for="edita-costo" class="form-label">Costo</label>
                            <input type="number" step="any" class="form-control" id="edita-costo" name="costo">
                        </div>
                        <div class="mb-3">
                            <label for="edita-CANTCAJA" class="form-label">Cantidad por Caja</label>
                            <input type="number" class="form-control" id="edita-CANTCAJA" name="CANTCAJA">
                        </div>
                        <div class="mb-3">
                            <label for="edita-CODNUMERI" class="form-label">Código Numérico</label>
                            <input type="number" class="form-control" id="edita-CODNUMERI" name="CODNUMERI">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary" form="form-edita">Guardar cambios</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Eliminar -->
    <div class="modal fade" id="eliminaModal" tabindex="-1" aria-labelledby="eliminaModalLabel" aria-hidden="true">
        <!-- Contenido del modal de eliminación como en archivos anteriores -->
    </div>

    <!-- Modal Alerta -->
    <div class="modal fade" id="alertaModal" tabindex="-1" aria-labelledby="alertaModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="alertaModalLabel">Aviso</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="alertaModalBody">
                    <!-- El mensaje se insertará aquí -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Aceptar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Confirmación -->
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmModalLabel">Confirmación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="confirmModalBody">
                    <!-- El mensaje de confirmación se insertará aquí -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="confirmModalBtnAceptar">Aceptar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Nuevo Producto -->
    <div class="modal fade" id="nuevoModal" tabindex="-1" aria-labelledby="nuevoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-custom">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="nuevoModalLabel">Nuevo Producto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="form-nuevo" action="crear_registro.php" method="post">

                        <div class="mb-3">
                            <label for="nuevo-nombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="nuevo-nombre" name="nombre" required
                                x-webkit-speech speech>
                        </div>
                        <div class="mb-3">
                            <label for="nuevo-precio1" class="form-label">Precio 1</label>
                            <input type="number" step="any" class="form-control" id="nuevo-precio1" name="precio1">
                        </div>
                        <div class="mb-3">
                            <label for="nuevo-codbar" class="form-label">Código de Barra</label>
                            <input type="text" class="form-control" id="nuevo-codbar" name="codbar" x-webkit-speech
                                speech>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="nuevo-selecc" name="selecc">
                            <label for="nuevo-selecc" class="form-check-label">Seleccionado</label>
                        </div>
                        <div class="mb-3">
                            <label for="nuevo-costo" class="form-label">Costo</label>
                            <input type="number" step="any" class="form-control" id="nuevo-costo" name="costo">
                        </div>
                        <div class="mb-3">
                            <label for="nuevo-cantcaja" class="form-label">Cantidad por Caja</label>
                            <input type="number" class="form-control" id="nuevo-cantcaja" name="CANTCAJA">
                        </div>
                        <div class="mb-3">
                            <label for="nuevo-codnumeri" class="form-label">Código Numérico</label>
                            <input type="number" class="form-control" id="nuevo-codnumeri" name="CODNUMERI">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            <button type="submit" class="btn btn-success">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
                    </div>


    <div id="print-area"></div>
        <input type="file" id="camera-input" accept="image/*" capture="environment" style="display: none;">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
        <script src="productos.js" defer></script>
        <script src="camara.js"></script>
</body>

</html>
<?php
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
                    <button type="button" id="btn-duplicar" class="btn btn-info btn-sm">Duplicar</button>
                    <button type="button" id="btn-imprimir" class="btn btn-info btn-sm">Imprimir</button>
                    <button type="button" id="btn-etiquetas" class="btn btn-success btn-sm">Etiquetas</button>
                    <button type="button" id="btn-reset-selecc" class="btn btn-warning btn-sm">Poner Selecc a Cero</button>
                    <button type="button" class="btn btn-primary btn-sm" onclick="window.open('http://192.168.1.152/costo_mercaderia.html', '_blank')">Costo Mercaderia</button>
                    <button type="button" class="btn btn-dark btn-sm" onclick="window.open('http://192.168.1.152/costo_caramelo.html', '_blank')">Costo Caramelo</button>
                </div>
            </div>

            <div class="row py-4">
                <div class="col">
                    <table class="table table-sm table-bordered table-striped">
                        <thead>
                            <th></th>
                            <th class="sort asc">Cód. Prod.</th>
                            <th class="sort asc">Nombre</th>
                            <th class="sort asc">Precio 1</th>
                            <th class="sort asc">Cód. Barra</th>
                            <th id="filtro-selecc" style="cursor: pointer;">Selecc <span id="filtro-selecc-icono"></span></th>
                            <th class="sort asc">Costo</th>
                            <th class="sort asc">Cant. Caja</th>
                            <th class="sort asc">Cód. Numérico</th>
                            <th></th>
                            <th></th>
                        </thead>
                        <tbody id="content"></tbody>
                    </table>
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

    <!-- Modals -->
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
                            <input type="text" class="form-control" id="nuevo-nombre" name="nombre" required x-webkit-speech speech>
                        </div>
                        <div class="mb-3">
                            <label for="nuevo-precio1" class="form-label">Precio 1</label>
                            <input type="number" step="any" class="form-control" id="nuevo-precio1" name="precio1">
                        </div>
                        <div class="mb-3">
                            <label for="nuevo-codbar" class="form-label">Código de Barra</label>
                            <input type="text" class="form-control" id="nuevo-codbar" name="codbar" x-webkit-speech speech>
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
                            <input type="number" class="form-control" id="nuevo-cantcaja" name="CANTCAJA"d
                            <label for="nuevo-codnumeri" class="form-label">Código Numérico</label>
                            <input type="number" class="form-control" id="nuevo-codnumeri" name
                    </form>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-success" form="form-nuevo">Guardar</button>
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
                        <input type="hidden" id="id" name="id">
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required x-webkit-speech speech>
                        </div>
                        <div class="mb-3">
                            <label for="precio1" class="form-label">Precio 1</label>
                            <input type="number" step="any" class="form-control" id="precio1" name="precio1">
                        </div>
                        <div class="mb-3">
                            <label for="codbar" class="form-label">Código de Barra</label>
                            <input type="text" class="form-control" id="codbar" name="codbar" x-webkit-speech speech>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="selecc" name="selecc">
                            <label for="selecc" class="form-check-label">Seleccionado</label>
                        </div>
                        <div class="mb-3">
                            <label for="costo" class="form-label">Costo</label>
                            <input type="number" step="any" class="form-control" id="costo" name="costo">
                        </div>
                        <div class="mb-3">
                            <label for="CANTCAJA" class="form-label">Cantidad por Caja</label>
                            <input type="number" class="form-control" id="CANTCAJA" name="CANTCAJA">
                        </div>
                        <div class="mb-3">
                            <label for="CODNUMERI" class="form-label">Código Numérico</label>
                            <input type="number" class="form-control" id="CODNUMERI" name="CODNUMERI">
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

    <!-- Modal Elimina -->
    <div class="modal fade" id="eliminaModal" tabindex="-1" aria-labelledby="eliminaModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-custom">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="eliminaModalLabel">Eliminar Registro</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    ¿Desea eliminar este registro?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <form id="form-elimina" class="d-inline">
                        <input type="hidden" id="id_eliminar" name="id">
                        <button type="submit" class="btn btn-danger">Eliminar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            getData();

            document.getElementById("filtro-selecc").addEventListener("click", function() {
                const filtroInput = document.getElementById("filtroSeleccActivo");
                const icono = document.getElementById("filtro-selecc-icono");
                
                if (filtroInput.value === "1") {
                    filtroInput.value = "0";
                    icono.innerHTML = ""; // Limpiar ícono
                } else {
                    filtroInput.value = "1";
                    icono.innerHTML = " &#128279;"; // Ícono de filtro
                }
                
                resetPagina();
            });
        });

        function getData() {
            let input = document.getElementById("campo").value;
            let busqueda_codigoprod = document.getElementById("busqueda_codigoprod").value;
            let busqueda_codbar = document.getElementById("busqueda_codbar").value;
            let num_registros = document.getElementById("num_registros").value;
            let content = document.getElementById("content");
            let pagina = document.getElementById("pagina").value || 1;
            let orderCol = document.getElementById("orderCol").value;
            let orderType = document.getElementById("orderType").value;
            let filtroSelecc = document.getElementById("filtroSeleccActivo").value;

            let formaData = new FormData();
            formaData.append('campo', input);
            formaData.append('busqueda_codigoprod', busqueda_codigoprod);
            formaData.append('busqueda_codbar', busqueda_codbar);
            formaData.append('registros', num_registros);
            formaData.append('pagina', pagina);
            formaData.append('orderCol', orderCol);
            formaData.append('orderType', orderType);
            formaData.append('filtro_selecc', filtroSelecc);

            fetch("load.php", {
                method: "POST",
                body: formaData
            })
            .then(response => response.json())
            .then(data => {
                content.innerHTML = data.data;
                document.getElementById("lbl-total").innerHTML = `Mostrando ${data.totalFiltro} de ${data.totalRegistros} registros`;
                document.getElementById("nav-paginacion").innerHTML = data.paginacion;
                if (data.data.includes('Sin resultados') && parseInt(pagina) !== 1) {
                    nextPage(1);
                } else {
                    const firstRow = content.querySelector('tr');
                    if (firstRow && !firstRow.querySelector('td[colspan="8"]')) {
                        setPointer(firstRow);
                    }
                }
            })
            .catch(err => console.log(err));
        }

        function nextPage(pagina) {
            document.getElementById('pagina').value = pagina;
            getData();
        }

        function ordenar(e) {
            let elemento = e.target;
            let orderType;

            // Limpiar clases de ordenación de todas las columnas
            document.querySelectorAll(".sort").forEach(col => col.classList.remove("asc", "desc"));

            // Lógica de alternancia para la dirección de ordenación
            orderType = elemento.classList.contains("asc") ? "desc" : "asc";

            // Aplica la clase de ordenación a la columna clickeada
            elemento.classList.add(orderType);

            document.getElementById('orderCol').value = elemento.cellIndex;
            document.getElementById("orderType").value = orderType;
            getData();
        }

        function resetPagina() {
            document.getElementById('pagina').value = 1;
            getData();
        }

        function clearPointer() {
            document.querySelectorAll('#content .puntero-celda').forEach(cell => cell.innerHTML = '');
        }

        function setPointer(row) {
            clearPointer();
            const pointerCell = row.querySelector('.puntero-celda');
            if (pointerCell) {
                pointerCell.innerHTML = '➤';
            }
        }

        document.getElementById("campo").addEventListener("keyup", resetPagina);
        document.getElementById("busqueda_codigoprod").addEventListener("keyup", resetPagina);
        document.getElementById("busqueda_codbar").addEventListener("keyup", resetPagina);
                    document.getElementById("num_registros").addEventListener("change", getData);

            document.getElementById("btn-limpiar").addEventListener("click", function() {
                document.getElementById("busqueda_codigoprod").value = "";
                document.getElementById("busqueda_codbar").value = "";
                document.getElementById("campo").value = "";
                getData();
            });

        let columns = document.querySelectorAll(".sort");
        columns.forEach(column => {
            column.addEventListener("click", ordenar);
        });

        document.getElementById('content').addEventListener('click', event => {
            const clickedRow = event.target.closest('tr');
            if (clickedRow && !clickedRow.querySelector('td[colspan="8"]')) {
                setPointer(clickedRow);
            }
        });

        const editaModal = document.getElementById('editaModal');
        editaModal.addEventListener('show.bs.modal', event => {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            editaModal.querySelector('.modal-body #id').value = id;
            editaModal.querySelector('.modal-body #nombre').value = button.getAttribute('data-nombre');
            editaModal.querySelector('.modal-body #precio1').value = button.getAttribute('data-precio1');
            editaModal.querySelector('.modal-body #codbar').value = button.getAttribute('data-codbar');
            editaModal.querySelector('.modal-body #selecc').checked = button.getAttribute('data-selecc') == 1;
            editaModal.querySelector('.modal-body #costo').value = button.getAttribute('data-costo');
            editaModal.querySelector('.modal-body #CANTCAJA').value = button.getAttribute('data-cantcaja');
            editaModal.querySelector('.modal-body #CODNUMERI').value = button.getAttribute('data-codnumeri');
        });

        const eliminaModal = document.getElementById('eliminaModal');
        eliminaModal.addEventListener('show.bs.modal', event => {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            eliminaModal.querySelector('.modal-footer #id_eliminar').value = id;
        });

        document.getElementById('content').addEventListener('change', function(e) {
            if (e.target.classList.contains('selecc-checkbox')) {
                const id = e.target.dataset.id;
                const valor = e.target.checked ? 1 : 0;

                let formData = new FormData();
                formData.append('id', id);
                formData.append('columna', 'selecc');
                formData.append('valor', valor);

                fetch('actualizar_celda.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status !== 'success') {
                        console.error('Error al actualizar el campo selecc');
                        // Optionally, revert the checkbox state
                        e.target.checked = !e.target.checked;
                    }
                })
                .catch(err => {
                    console.error(err);
                    e.target.checked = !e.target.checked;
                });
            }
        });

        // --- Listener para edición en línea ---
        document.getElementById('content').addEventListener('blur', function(e) {
            const target = e.target;
            // Asegurarse de que el evento viene de una celda editable (TD con contenteditable)
            if (target.tagName === 'TD' && target.isContentEditable) {
                const id = target.dataset.id;
                const columna = target.dataset.col;
                let valor = target.textContent.trim();

                // Para columnas numéricas, limpiar el formato antes de enviar
                if (columna === 'precio1' || columna === 'costo') {
                    // Eliminar puntos de miles
                    valor = valor.replace(/\./g, '');
                    // Reemplazar coma decimal por punto si se usa
                    // valor = valor.replace(/,/g, '.'); 
                }

                let formData = new FormData();
                formData.append('id', id);
                formData.append('columna', columna);
                formData.append('valor', valor);

                // Guardar el valor original en caso de error
                const originalValue = target.innerHTML;

                fetch('actualizar_celda.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status !== 'success') {
                        console.error('Error al actualizar:', data.message);
                        alert('Error al actualizar: ' + data.message);
                        target.innerHTML = originalValue; // Revertir al valor original
                    }
                    // Si es exitoso, no es necesario hacer nada, el valor ya está en la celda.
                    // Opcional: podrías recargar los datos para obtener el formato correcto del servidor.
                    // getData(); 
                })
                .catch(err => console.error(err));
            }
        }, true); // Usar captura para asegurar que el evento se maneje correctamente

        // --- Listener para navegar con "Enter" en celdas editables ---
        document.getElementById('content').addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                const target = e.target;

                // Asegurarse de que estamos en una celda editable
                if (target.tagName === 'TD' && target.isContentEditable) {
                    e.preventDefault(); // Prevenir el salto de línea

                    const currentRow = target.closest('tr');
                    const cells = Array.from(currentRow.querySelectorAll('td[contenteditable="true"]'));
                    const currentIndex = cells.indexOf(target);

                    // Encontrar la siguiente celda editable en la misma fila
                    if (currentIndex > -1 && currentIndex < cells.length - 1) {
                        const nextCell = cells[currentIndex + 1];
                        nextCell.focus();
                    } else {
                        // Opcional: si es la última celda, simplemente quitar el foco para guardar
                        target.blur();
                    }
                }
            }
        });


        document.getElementById('form-edita').addEventListener('submit', function(e) {
            e.preventDefault();
            let formaData = new FormData(this);
            fetch('actualizar.php', {
                method: 'POST',
                body: formaData
            }).then(response => response.json()).then(data => {
                if (data.status === 'success') {
                    bootstrap.Modal.getInstance(editaModal).hide();
                    getData();
                }
            }).catch(err => console.log(err));
        });

        document.getElementById('form-elimina').addEventListener('submit', function(e) {
            e.preventDefault();
            let formaData = new FormData(this);
            fetch('eliminar_registro.php', {
                method: 'POST',
                body: formaData
            }).then(response => response.json()).then(data => {
                if (data.status === 'success') {
                    bootstrap.Modal.getInstance(eliminaModal).hide();
                    getData();
                }
            }).catch(err => console.log(err));
        });

        const nuevoModal = document.getElementById('nuevoModal');
        nuevoModal.addEventListener('hidden.bs.modal', event => {
            document.getElementById('form-nuevo').reset();
        });

        // Poner foco en el campo nombre cuando el modal de nuevo producto se muestra
        nuevoModal.addEventListener('shown.bs.modal', () => {
            document.getElementById('nuevo-nombre').focus();
        });

        document.getElementById('form-nuevo').addEventListener('submit', function(e) {
            e.preventDefault();
            let formaData = new FormData(this);
            fetch('crear_registro.php', {
                method: 'POST',
                body: formaData
            }).then(response => response.json()).then(data => {
                if (data.status === 'success') {
                    bootstrap.Modal.getInstance(nuevoModal).hide();
                    getData();
                }
            }).catch(err => console.log(err));
        });

        document.getElementById("btn-imprimir").addEventListener("click", function() {
            const tablaContainer = document.querySelector(".row.py-4").cloneNode(true);
            const printArea = document.getElementById("print-area");
            printArea.innerHTML = ""; // Limpiar área
            printArea.appendChild(tablaContainer);
            window.print();
        });

        document.getElementById("btn-etiquetas").addEventListener("click", function() {
            const filas = document.querySelectorAll("#content tr");
            const printArea = document.getElementById("print-area");
            
            let labelsHtml = '<div class="label-print-container">';
            let productos = [];

            filas.forEach(fila => {
                if (fila.cells.length > 4) { // Asegurarse de que es una fila de datos
                    const producto = {
                        codigo: fila.cells[1].textContent,
                        nombre: fila.cells[2].textContent,
                        precio: fila.cells[3].textContent
                    };
                    productos.push(producto);
                }
            });

            productos.forEach(p => {
                labelsHtml += `
                    <div class="label-item">
                        <div class="label-code">${p.codigo}</div>
                        <div class="label-name">${p.nombre}</div>
                        <div class="label-price">${p.precio}</div>
                    </div>
                `;
            });

            labelsHtml += '</div>';
            
            printArea.innerHTML = labelsHtml;
            window.print();
        });

        document.getElementById("btn-reset-selecc").addEventListener("click", function() {
            if (confirm("¿Está seguro de que desea poner a cero la columna 'selecc' para todos los registros?")) {
                fetch('reset_selecc.php', {
                    method: 'POST'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        alert("La columna 'selecc' ha sido actualizada a 0 para todos los registros.");
                        getData(); // Refresh the table
                    } else {
                        alert("Hubo un error al actualizar los registros.");
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert("Hubo un error de red.");
                });
            }
        });
    </script>

    <div id="print-area"></div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>

</html>
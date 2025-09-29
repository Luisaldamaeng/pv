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

            <div class="row g-4 justify-content-center pt-3">
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

                <div class="col-md-3">
                    <div class="input-group input-group-sm">
                        <label for="campo" class="input-group-text">Buscar:</label>
                        <input type="text" name="campo" id="campo" class="form-control">
                    </div>
                </div>
                <div class="col-auto">
                    <button type="button" id="btn-limpiar" class="btn btn-secondary btn-sm">Limpiar</button>
                </div>
                <div class="col-auto">
                    <button type="button" id="btn-imprimir" class="btn btn-info btn-sm">Imprimir</button>
                </div>
                <div class="col-auto">
                    <button type="button" id="btn-etiquetas" class="btn btn-success btn-sm">Etiquetas</button>
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
                            <th></th>
                            <th></th>
                        </thead>
                        <tbody id="content"></tbody>
                    </table>
                </div>
            </div>

            <div class="row justify-content-between">
                <div class="col-12 col-md-4">
                    <label id="lbl-total"></label>
                </div>
                <div class="col-12 col-md-4" id="nav-paginacion"></div>
                <input type="hidden" id="pagina" value="1">
                <input type="hidden" id="orderCol" value="0">
                <input type="hidden" id="orderType" value="asc">
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
                            <input type="text" class="form-control" id="nuevo-nombre" name="nombre" required>
                        </div>
                        <div class="mb-3">
                            <label for="nuevo-precio1" class="form-label">Precio 1</label>
                            <input type="number" step="any" class="form-control" id="nuevo-precio1" name="precio1">
                        </div>
                        <div class="mb-3">
                            <label for="nuevo-codbar" class="form-label">Código de Barra</label>
                            <input type="text" class="form-control" id="nuevo-codbar" name="codbar">
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="nuevo-selecc" name="selecc">
                            <label for="nuevo-selecc" class="form-check-label">Seleccionado</label>
                        </div>
                        <div class="mb-3">
                            <label for="nuevo-costo" class="form-label">Costo</label>
                            <input type="number" step="any" class="form-control" id="nuevo-costo" name="costo">
                        </div>
                    </form>
                </div>
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
                            <input type="text" class="form-control" id="nombre" name="nombre" required>
                        </div>
                        <div class="mb-3">
                            <label for="precio1" class="form-label">Precio 1</label>
                            <input type="number" step="any" class="form-control" id="precio1" name="precio1">
                        </div>
                        <div class="mb-3">
                            <label for="codbar" class="form-label">Código de Barra</label>
                            <input type="text" class="form-control" id="codbar" name="codbar">
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="selecc" name="selecc">
                            <label for="selecc" class="form-check-label">Seleccionado</label>
                        </div>
                        <div class="mb-3">
                            <label for="costo" class="form-label">Costo</label>
                            <input type="number" step="any" class="form-control" id="costo" name="costo">
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

            // Caso especial para la columna 'Cód. Prod.' (índice 1)
            if (elemento.cellIndex === 1) {
                orderType = 'asc';
                if (elemento.classList.contains("desc")) {
                    elemento.classList.remove("desc");
                }
                if (!elemento.classList.contains("asc")) {
                    elemento.classList.add("asc");
                }
            } else {
                // Lógica de alternancia existente para las demás columnas
                orderType = elemento.classList.contains("asc") ? "desc" : "asc";
                elemento.classList.toggle("asc");
                elemento.classList.toggle("desc");
            }

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
    </script>

    <div id="print-area"></div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>

</html>
document.addEventListener("DOMContentLoaded", function() {
    // --- ELEMENTOS DEL DOM ---
    const busquedaCodigoProdInput = document.getElementById("busqueda_codigoprod");
    const busquedaCodigoBarInput = document.getElementById("busqueda_codbar");
    const campoBusquedaInput = document.getElementById("campo");
    const numRegistrosSelect = document.getElementById("num_registros");
    const filtroSeleccHeader = document.getElementById("filtro-selecc");
    const contentTbody = document.getElementById("content");
    const lblTotal = document.getElementById("lbl-total");
    const navPaginacion = document.getElementById("nav-paginacion");

    // Estado: fila actualmente seleccionada (puntero)
    let selectedRow = null;

    // Botones
    const btnLimpiar = document.getElementById("btn-limpiar");
    const btnDuplicar = document.getElementById("btn-duplicar");
    const btnImprimir = document.getElementById("btn-imprimir");
    const btnEtiquetas = document.getElementById("btn-etiquetas");
    const btnResetSelecc = document.getElementById("btn-reset-selecc");

    // Modales
    const editaModalEl = document.getElementById('editaModal');
    const nuevoModalEl = document.getElementById('nuevoModal');
    const eliminaModalEl = document.getElementById('eliminaModal');
    const alertaModal = new bootstrap.Modal(document.getElementById('alertaModal'));
    const confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'));
    const editaModal = editaModalEl ? new bootstrap.Modal(editaModalEl) : null;
    const nuevoModal = nuevoModalEl ? new bootstrap.Modal(nuevoModalEl) : null;

    // Formularios
    const formEdita = document.getElementById('form-edita');
    const formNuevo = document.getElementById('form-nuevo');

    // Inputs ocultos para estado
    const paginaInput = document.getElementById("pagina");
    const orderColInput = document.getElementById("orderCol");
    const orderTypeInput = document.getElementById("orderType");
    const filtroSeleccActivoInput = document.getElementById("filtroSeleccActivo");

    // --- FUNCIONES DE UTILIDAD (MODALES) ---
    function mostrarAlerta(mensaje) {
        document.getElementById('alertaModalBody').textContent = mensaje;
        alertaModal.show();
    }

    function mostrarConfirmacion(mensaje, callback) {
        document.getElementById('confirmModalBody').textContent = mensaje;
        const btnAceptar = document.getElementById('confirmModalBtnAceptar');
        
        // Clonar y reemplazar el botón para eliminar listeners anteriores
        const nuevoBtnAceptar = btnAceptar.cloneNode(true);
        btnAceptar.parentNode.replaceChild(nuevoBtnAceptar, btnAceptar);

        nuevoBtnAceptar.addEventListener('click', () => {
            callback();
            confirmModal.hide();
        });
        confirmModal.show();
    }

    // --- LÓGICA PRINCIPAL DE DATOS (FETCH) ---
    function getData() {
        let formaData = new FormData();
        formaData.append('campo', campoBusquedaInput.value);
        formaData.append('busqueda_codigoprod', busquedaCodigoProdInput.value);
        formaData.append('busqueda_codbar', busquedaCodigoBarInput.value);
        formaData.append('registros', numRegistrosSelect.value);
        formaData.append('pagina', paginaInput.value || 1);
        formaData.append('orderCol', orderColInput.value);
        formaData.append('orderType', orderTypeInput.value);
        formaData.append('filtro_selecc', filtroSeleccActivoInput.value);

        fetch("load.php", {
            method: "POST",
            body: formaData
        })
        .then(response => response.json())
        .then(data => {
            contentTbody.innerHTML = data.data;
            lblTotal.innerHTML = `Mostrando ${data.totalFiltro} de ${data.totalRegistros} registros`;
            navPaginacion.innerHTML = data.paginacion;
            
            if (data.data.includes('Sin resultados') && parseInt(paginaInput.value) !== 1) {
                nextPage(1);
            } else {
                const firstRow = contentTbody.querySelector('tr');
                if (firstRow && !firstRow.querySelector('td[colspan]')) {
                    setPointer(firstRow);
                }
            }
        })
        .catch(err => console.error('Error al cargar los datos:', err));

        // NOTIFICA que la tabla fue actualizada (permite acciones posteriores como seleccionar una fila)
        document.dispatchEvent(new CustomEvent('productos:tableUpdated'));
    }

    // --- FUNCIONES DE LA TABLA (PAGINACIÓN, ORDEN, PUNTERO) ---
    window.nextPage = function(pagina) {
        paginaInput.value = pagina;
        getData();
    }

    function ordenar(e) {
        let elemento = e.target;
        document.querySelectorAll(".sort").forEach(col => {
            if (col !== elemento) col.classList.remove("asc", "desc");
        });

        const orderType = elemento.classList.contains("asc") ? "desc" : "asc";
        elemento.classList.remove("asc", "desc");
        elemento.classList.add(orderType);

        orderColInput.value = elemento.cellIndex;
        orderTypeInput.value = orderType;
        getData();
    }

    function resetPagina() {
        paginaInput.value = 1;
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

    // --- MANEJO DE EVENTOS DE LA INTERFAZ ---

    // Filtros y búsqueda
    campoBusquedaInput.addEventListener("keyup", resetPagina);
    busquedaCodigoProdInput.addEventListener("keyup", resetPagina);
    busquedaCodigoBarInput.addEventListener("keyup", resetPagina);
    numRegistrosSelect.addEventListener("change", resetPagina);

    filtroSeleccHeader.addEventListener("click", function() {
        const icono = document.getElementById("filtro-selecc-icono");
        if (filtroSeleccActivoInput.value === "1") {
            filtroSeleccActivoInput.value = "0";
            icono.innerHTML = "";
        } else {
            filtroSeleccActivoInput.value = "1";
            icono.innerHTML = " &#128279;";
        }
        resetPagina();
    });

    // Botones de acción
    btnLimpiar.addEventListener("click", function() {
        busquedaCodigoProdInput.value = "";
        busquedaCodigoBarInput.value = "";
        campoBusquedaInput.value = "";
        resetPagina();
    });

    btnDuplicar.addEventListener("click", function() {

        if (!selectedRow || selectedRow.querySelector('td[colspan]')) {
            mostrarAlerta("Por favor, seleccione un producto de la tabla para duplicar.");
            return;
        }

        // Extracción defensiva de celdas y normalización de números
        const nombre = selectedRow.cells[2]?.textContent?.trim() || '';
        const precioRaw = selectedRow.cells[3]?.textContent?.trim() || '';
        const precio1 = precioRaw ? precioRaw.replace(/\./g, '').replace(',', '.') : '';
        const codbar = selectedRow.cells[4]?.textContent?.trim() || '';
        const costoRaw = selectedRow.cells[6]?.textContent?.trim() || '';
        const costo = costoRaw ? costoRaw.replace(/\./g, '').replace(',', '.') : '';
        const cantcaja = selectedRow.cells[7]?.textContent?.trim() || '';
        const codnumeri = selectedRow.cells[8]?.textContent?.trim() || '';

        // Genero un código temporal único que será insertado como codigoprod al duplicar
        const codigoprod_temp = 'TMP_' + Date.now();

        let formaData = new FormData();
        formaData.append('nombre', nombre);
        formaData.append('precio1', precio1);
        formaData.append('codbar', codbar);
        formaData.append('costo', costo);
        formaData.append('CANTCAJA', cantcaja);
        formaData.append('CODNUMERI', codnumeri);
        formaData.append('selecc', 0);
        formaData.append('codigoprod_temp', codigoprod_temp);

        // Función auxiliar para seleccionar la fila por código de producto
        function seleccionarFilaPorCodigo(codigo) {
            const filas = contentTbody.querySelectorAll('tr');
            // Quitar puntero previo
            filas.forEach(f => {
                const primeraCelda = f.querySelector('td');
                if (primeraCelda) primeraCelda.innerHTML = '';
                f.classList.remove('table-active');
            });

            for (const fila of filas) {
                const cellCode = fila.cells[1]?.textContent?.trim();
                if (cellCode === codigo) {
                    // marcar visualmente
                    fila.classList.add('table-active');
                    const primera = fila.querySelector('td');
                    if (primera) primera.innerHTML = '<span class="puntero-celda">▶</span>';
                    // setear selectedRow global para otras acciones
                    selectedRow = fila;
                    // asegurar visibilidad
                    fila.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    // opcional: poner foco en el primer botón dentro de la fila (si existe)
                    const boton = fila.querySelector('button, a, input');
                    if (boton) boton.focus();
                    return true;
                }
            }
            return false;
        }

        // Enviar la duplicación
        fetch('crear_registro.php', {
            method: 'POST',
            body: formaData
        })
        .then(response => {
            if (!response.ok) return response.text().then(t => { throw new Error('HTTP ' + response.status + ' - ' + t); });
            return response.json();
        })
        .then(data => {
            if (data.status === 'success') {
                const onUpdated = () => {
                    seleccionarFilaPorCodigo(codigoprod_temp);
                    document.removeEventListener('productos:tableUpdated', onUpdated);
                };
                document.addEventListener('productos:tableUpdated', onUpdated);

                getData();
                setTimeout(() => {
                    seleccionarFilaPorCodigo(codigoprod_temp);
                    document.removeEventListener('productos:tableUpdated', onUpdated);
                }, 700);

                mostrarAlerta(data.message || 'Producto duplicado con éxito.');
            } else {
                mostrarAlerta(data.message || 'Error al duplicar el producto.');
            }
        })
        .catch(err => {
            console.error('Error al duplicar:', err);
            mostrarAlerta('Error de conexión al duplicar el producto. ' + (err.message || ''));
        });

    });

    btnResetSelecc.addEventListener("click", function() {
        const mensaje = "¿Está seguro de que desea poner a cero la columna 'selecc' para todos los registros?";
        mostrarConfirmacion(mensaje, () => {
            fetch('reset_selecc.php', {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                mostrarAlerta(data.message || (data.status === 'success' ? "Operación completada." : "Ocurrió un error."));
                if (data.status === 'success') {
                    getData();
                }
            })
            .catch(err => {
                console.error(err);
                mostrarAlerta("Hubo un error de red.");
            });
        });
    });

    // Impresión
    btnImprimir.addEventListener("click", function() {
        const tablaContainer = document.querySelector(".table-responsive").cloneNode(true);
        const printArea = document.getElementById("print-area");
        printArea.innerHTML = "";
        printArea.appendChild(tablaContainer);
        window.print();
    });

    btnEtiquetas.addEventListener("click", function() {
        const filas = document.querySelectorAll("#content tr");
        const printArea = document.getElementById("print-area");
        
        let labelsHtml = '<div class="label-print-container">';
        filas.forEach(fila => {
            if (fila.cells.length > 4 && !fila.querySelector('td[colspan]')) {
                labelsHtml += `
                    <div class="label-item">
                        <div class="label-code">${fila.cells[1].textContent}</div>
                        <div class="label-name">${fila.cells[2].textContent}</div>
                        <div class="label-price">${fila.cells[3].textContent}</div>
                    </div>
                `;
            }
        });
        labelsHtml += '</div>';
        
        printArea.innerHTML = labelsHtml;
        window.print();
    });

    // --- MANEJO DE EVENTOS DE LA TABLA ---

    // Click en fila para poner puntero
    contentTbody.addEventListener('click', event => {
        const clickedRow = event.target.closest('tr');
        if (clickedRow && !clickedRow.querySelector('td[colspan]')) {
            setPointer(clickedRow);
        }
    });

    // Doble click en fila para abrir modal de edición
    contentTbody.addEventListener('dblclick', event => {
        const clickedRow = event.target.closest('tr');
        if (clickedRow && !clickedRow.querySelector('td[colspan]')) {
            if (editaModal) {
                // Poblar el formulario con los datos de la fila
                const form = document.getElementById('form-edita');
                form.querySelector('#edita-id').value = clickedRow.dataset.id;
                form.querySelector('#edita-nombre').value = clickedRow.dataset.nombre;
                form.querySelector('#edita-precio1').value = clickedRow.dataset.precio1;
                form.querySelector('#edita-codbar').value = clickedRow.dataset.codbar;
                form.querySelector('#edita-costo').value = clickedRow.dataset.costo;
                form.querySelector('#edita-CANTCAJA').value = clickedRow.dataset.cantcaja;
                form.querySelector('#edita-CODNUMERI').value = clickedRow.dataset.codnumeri;
                form.querySelector('#edita-selecc').checked = clickedRow.dataset.selecc == '1';

                editaModal.show();
            } else {
                console.error('El modal de edición no está disponible.');
            }
        }
    });


    // Edición en línea
    contentTbody.addEventListener('blur', function(e) {
        const target = e.target;
        if (target.tagName === 'TD' && target.isContentEditable) {
            const id = target.dataset.id;
            const columna = target.dataset.col;
            let valor = target.textContent.trim();

            if (columna === 'precio1' || columna === 'costo') {
                valor = valor.replace(/\./g, '');
            }

            let formData = new FormData();
            formData.append('id', id);
            formData.append('columna', columna);
            formData.append('valor', valor);

            const originalValue = target.innerHTML;

            fetch('actualizar_celda.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status !== 'success') {
                    mostrarAlerta('Error al actualizar: ' + data.message);
                    target.innerHTML = originalValue;
                }
            })
            .catch(err => {
                console.error(err);
                mostrarAlerta('Error de red al actualizar.');
                target.innerHTML = originalValue;
            });
        }
    }, true);

    // Checkbox 'selecc'
    contentTbody.addEventListener('change', function(e) {
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
                    mostrarAlerta('Error al actualizar "selecc".');
                    e.target.checked = !e.target.checked;
                }
            })
            .catch(err => {
                console.error(err);
                mostrarAlerta('Error de red al actualizar "selecc".');
                e.target.checked = !e.target.checked;
            });
        }
    });

    // --- MANEJO DE EVENTOS DE TECLADO ---

    document.addEventListener('keydown', function(e) {
        const activeElement = document.activeElement;
        const isEditing = activeElement.tagName === 'INPUT' || activeElement.tagName === 'TEXTAREA' || activeElement.isContentEditable;

        if (e.key === '+' && e.altKey && !isEditing) {
            e.preventDefault();
            nuevoModal.show();
        }

        if (e.key === 'Delete' && !isEditing) {
            const pointerCell = document.querySelector('.puntero-celda:not(:empty)');
            const selectedRowKey = pointerCell ? pointerCell.closest('tr') : null;

            if (selectedRowKey) {
                const id = selectedRowKey.cells[1].textContent;
                const nombre = selectedRowKey.cells[2].textContent;
                
                mostrarConfirmacion(`¿Seguro que desea eliminar el producto "${nombre}" (ID: ${id})?`, () => {
                    let formData = new FormData();
                    formData.append('id', id);

                    fetch('eliminar_registro.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        mostrarAlerta(data.message || (data.status === 'success' ? 'Producto eliminado.' : 'Error al eliminar.'));
                        if (data.status === 'success') {
                            getData();
                        }
                    })
                    .catch(err => {
                        console.error('Error en la solicitud de eliminación:', err);
                        mostrarAlerta('Error de red al intentar eliminar.');
                    });
                });
            }
        }
    });

    contentTbody.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            const target = e.target;
            if (target.tagName === 'TD' && target.isContentEditable) {
                e.preventDefault();
                const currentRow = target.closest('tr');
                const cells = Array.from(currentRow.querySelectorAll('td[contenteditable="true"]'));
                const currentIndex = cells.indexOf(target);

                if (currentIndex > -1 && currentIndex < cells.length - 1) {
                    cells[currentIndex + 1].focus();
                } else {
                    target.blur();
                }
            }
        }
    });

    // --- MANEJO DE MODALES Y FORMULARIOS ---

    // Modal de Nuevo Producto
    if (nuevoModalEl) {
        nuevoModalEl.addEventListener('hidden.bs.modal', () => formNuevo.reset());
        nuevoModalEl.addEventListener('shown.bs.modal', () => document.getElementById('nuevo-nombre').focus());
    }

    formNuevo.addEventListener('submit', function(e) {
        e.preventDefault();
        let formaData = new FormData(this);

        if (!formaData.get('nombre').trim() || formaData.get('precio1') === '') {
            mostrarAlerta('El nombre y el precio son obligatorios.');
            return;
        }

        fetch('crear_registro.php', {
            method: 'POST',
            body: formaData
        })
        .then(response => {
            console.log('Status HTTP:', response.status);
            if (!response.ok) {
                return response.text().then(text => {
                    console.error('Respuesta de error:', text);
                    throw new Error(`HTTP ${response.status}: ${text.substring(0, 200)}`);
                });
            }
            return response.text().then(text => {
                console.log('Respuesta del servidor:', text);
                try {
                    return JSON.parse(text);
                } catch (e) {
                    throw new Error(`Respuesta no JSON válido: ${text.substring(0, 200)}`);
                }
            });
        })
        .then(data => {
            if (data.status === 'success') {
                nuevoModal.hide();
                mostrarAlerta(data.message || 'Producto creado con éxito.');
                getData();
            } else {
                mostrarAlerta(data.message || 'Error al crear el producto.');
            }
        })
        .catch(err => {
            console.error('Error completo:', err);
            mostrarAlerta('Error: ' + err.message);
        });
    });

    // Modal de Edición
    if (editaModalEl) {
        formEdita.addEventListener('submit', function(e) {
            e.preventDefault();
            let formData = new FormData(this);
            // El checkbox no se envía si no está marcado, así que lo añadimos manualmente si es necesario.
            if (!formData.has('selecc')) {
                formData.append('selecc', '0');
            } else {
                formData.set('selecc', '1');
            }

            fetch('actualizar_registro.php', {
                method: 'POST',
                body: formData
            }).then(response => response.json()).then(data => {
                if (data.status === 'success') {
                    editaModal.hide();
                    mostrarAlerta(data.message || 'Producto actualizado con éxito.');
                    getData(); // Recargar los datos para ver los cambios
                } else {
                    mostrarAlerta(data.message || 'Error al actualizar el producto.');
                }
            }).catch(err => mostrarAlerta('Error de conexión al actualizar el producto.'));
        });
    }

    // --- INICIALIZACIÓN ---
    document.querySelectorAll(".sort").forEach(column => {
        column.addEventListener("click", ordenar);
    });

    // Llamada inicial para cargar datos y ayuda a depurar
    if (typeof getData === 'function') {
        console.log('Invocando getData() para cargar tabla');
        getData();
    } else {
        console.error('getData() no está definida. Revisa productos.js');
    }
});
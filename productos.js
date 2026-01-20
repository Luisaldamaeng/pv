document.addEventListener("DOMContentLoaded", function () {
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
    const btnLectorCodbar = document.getElementById("btn-lector-codbar");
    const btnImprimir = document.getElementById("btn-imprimir");
    const btnEtiquetas = document.getElementById("btn-etiquetas");
    const btnResetSelecc = document.getElementById("btn-reset-selecc");

    // --- DIAGNÓSTICO RÁPIDO ---
    (function runDiagnostics() {
        console.group('productos.js diagnostics');
        try {
            console.log('LectorCodigoBarras disponible:', typeof LectorCodigoBarras !== 'undefined');
        } catch (e) {
            console.log('LectorCodigoBarras disponible: (error comprobando)', e);
        }

        console.log('Elementos clave presentes:', {
            btnLectorCodbar: !!btnLectorCodbar,
            lectorModal: !!document.getElementById('lectorModal'),
            lectorScriptTag: !!document.querySelector('script[src*="lector_codbar.js"]')
        });

        if (typeof LectorCodigoBarras === 'undefined') {
            console.warn('Aviso: `LectorCodigoBarras` no está definido. Verifica que `lector_codbar.js` se cargue antes de `productos.js`.');
        }
        if (!btnLectorCodbar) console.warn('Aviso: botón `btn-lector-codbar` no encontrado en el DOM.');
        console.groupEnd();
    })();

    // Modales
    const editaModalEl = document.getElementById('editaModal');
    const nuevoModalEl = document.getElementById('nuevoModal');
    const eliminaModalEl = document.getElementById('eliminaModal');
    const alertaModal = new bootstrap.Modal(document.getElementById('alertaModal'));
    const confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'));
    const editaModal = editaModalEl ? new bootstrap.Modal(editaModalEl) : null;
    const nuevoModal = nuevoModalEl ? new bootstrap.Modal(nuevoModalEl) : null;

    // Asegurarse de que el foco se reinicie al cerrar los modales para evitar problemas de accesibilidad
    const alertaModalElement = document.getElementById('alertaModal');
    if (alertaModalElement) {
        alertaModalElement.addEventListener('hidden.bs.modal', function () {
            document.body.focus();
        });
    }

    // Formularios
    const formEdita = document.getElementById('form-edita');
    const formNuevo = document.getElementById('form-nuevo');

    // Lógica para mostrar longitud de anotación en modales
    function setupAnotacionLongitud(inputId, countId) {
        const input = document.getElementById(inputId);
        const count = document.getElementById(countId);
        if (input && count) {
            const updateCount = () => {
                count.textContent = input.value.length;
                count.style.display = 'inline-block';
            };
            input.addEventListener('focus', updateCount);
            input.addEventListener('click', updateCount);
            input.addEventListener('input', updateCount);
            input.addEventListener('blur', () => {
                // Opcional: ocultar al perder el foco si se desea, 
                // pero el usuario pidió "al hacer click", así que lo dejamos visible mientras edita.
            });
        }
    }

    setupAnotacionLongitud('edita-anotacion', 'edita-anotacion-longitud');
    setupAnotacionLongitud('nuevo-anotacion', 'nuevo-anotacion-longitud');

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
    function getData(isPolling = false) { // Añadir isPolling como parámetro, por defecto false
        let formaData = new FormData();
        formaData.append('campo', campoBusquedaInput.value);
        formaData.append('busqueda_codigoprod', busquedaCodigoProdInput.value);
        formaData.append('busqueda_codbar', busquedaCodigoBarInput.value);
        formaData.append('registros', numRegistrosSelect.value);
        formaData.append('pagina', paginaInput.value || 1);
        formaData.append('orderCol', orderColInput.value);
        formaData.append('orderType', orderTypeInput.value);
        formaData.append('filtro_selecc', filtroSeleccActivoInput.value);

        let savedSelectedId = null;
        if (isPolling && selectedRow) {
            savedSelectedId = selectedRow.dataset.id; // Uso más seguro con dataset.id
        }

        fetch("load.php", {
            method: "POST",
            body: formaData
        })
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => {
                        throw new Error(`Servidor respondió con estado ${response.status}. ${text.substring(0, 100)}`);
                    });
                }
                return response.text().then(text => {
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.error('Error al parsear JSON:', text);
                        throw new Error('La respuesta del servidor no tiene un formato válido (JSON).');
                    }
                });
            })
            .then(data => {
                // Si el servidor devolvió un error estructurado
                if (data && data.status && data.status === 'error') {
                    console.error('load.php error:', data.detail || data.message || data);
                    mostrarAlerta('Error al cargar datos: ' + (data.detail || data.message || 'Error interno'));
                    return;
                }

                if (!data || typeof data.data !== 'string') {
                    console.error('Respuesta inesperada de load.php:', data);
                    mostrarAlerta('Respuesta inesperada del servidor al cargar datos.');
                    return;
                }

                contentTbody.innerHTML = data.data;
                lblTotal.innerHTML = `Mostrando ${data.totalFiltro} de ${data.totalRegistros} registros`;
                navPaginacion.innerHTML = data.paginacion;

                if (data.data.includes('Sin resultados') && parseInt(paginaInput.value) !== 1) {
                    nextPage(1);
                } else if (!isPolling || (isPolling && !savedSelectedId)) {
                    const firstRow = contentTbody.querySelector('tr');
                    if (firstRow && !firstRow.querySelector('td[colspan]')) {
                        setPointer(firstRow);
                    }
                }

                if (isPolling && savedSelectedId) {
                    seleccionarFilaPorCodigo(savedSelectedId);
                }
            })
            .catch(err => {
                console.error('Error al cargar los datos:', err);
                // Solo mostrar alerta si NO es polling para no interrumpir al usuario cada 10 seg
                if (!isPolling) {
                    mostrarAlerta('Error de conexión al cargar datos. Revisa la consola.');
                }
            });

        // NOTIFICA que la tabla fue actualizada (permite acciones posteriores como seleccionar una fila)
        document.dispatchEvent(new CustomEvent('productos:tableUpdated'));
    }

    // --- FUNCIONES DE LA TABLA (PAGINACIÓN, ORDEN, PUNTERO) ---
    window.nextPage = function (pagina) {
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
        document.querySelectorAll('#content tr').forEach(r => r.classList.remove('table-active'));
        selectedRow = null;


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

    filtroSeleccHeader.addEventListener("click", function () {
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
    btnLimpiar.addEventListener("click", function () {
        busquedaCodigoProdInput.value = "";
        busquedaCodigoBarInput.value = "";
        campoBusquedaInput.value = "";
        resetPagina();
    });

    // Función auxiliar para seleccionar la fila por código de producto (Accesible globalmente)
    function seleccionarFilaPorCodigo(codigo) {
        const filas = contentTbody.querySelectorAll('tr');
        // Quitar puntero previo
        filas.forEach(f => {
            const primeraCelda = f.querySelector('td');
            if (primeraCelda) primeraCelda.innerHTML = '';
            f.classList.remove('table-active');
        });

        for (const fila of filas) {
            const cellCode = fila.dataset.id; // Uso más seguro con dataset.id
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

    btnDuplicar.addEventListener("click", function () {

        if (!selectedRow || selectedRow.querySelector('td[colspan]')) {
            mostrarAlerta("Por favor, seleccione un producto de la tabla para duplicar.");
            return;
        }

        // Extracción defensiva usando dataset (más confiable que índices de celdas)
        const nombre = selectedRow.dataset.nombre || '';
        const precio1 = selectedRow.dataset.precio1 || '';
        const codbar = selectedRow.dataset.codbar || '';
        const costo = selectedRow.dataset.costo || '';
        const anotacion = selectedRow.dataset.anotacion || '';
        const codnumeri = selectedRow.dataset.codnumeri || '';

        // Genero un código temporal único que será insertado como codigoprod al duplicar
        const codigoprod_temp = 'TMP_' + Date.now();

        let formaData = new FormData();
        formaData.append('nombre', nombre);
        formaData.append('precio1', precio1);
        formaData.append('codbar', codbar);
        formaData.append('costo', costo);
        formaData.append('anotacion', anotacion);
        formaData.append('CODNUMERI', codnumeri);
        formaData.append('selecc', 0);
        formaData.append('codigoprod_temp', codigoprod_temp);

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



    btnResetSelecc.addEventListener("click", function () {
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
    btnImprimir.addEventListener("click", function () {
        const tablaContainer = document.querySelector(".table-responsive").cloneNode(true);
        const printArea = document.getElementById("print-area");
        printArea.innerHTML = "";
        printArea.appendChild(tablaContainer);
        window.print();
    });

    btnEtiquetas.addEventListener("click", function () {
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

            // Actualizar el preview de la foto
            // El código relacionado con la vista previa de la foto se ha eliminado.

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
                form.querySelector('#edita-anotacion').value = clickedRow.dataset.anotacion;
                form.querySelector('#edita-CODNUMERI').value = clickedRow.dataset.codnumeri;
                form.querySelector('#edita-selecc').checked = clickedRow.dataset.selecc == '1';

                editaModal.show();
            } else {
                console.error('El modal de edición no está disponible.');
            }
        }
    });


    // Edición en línea
    contentTbody.addEventListener('blur', function (e) {
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
    contentTbody.addEventListener('change', function (e) {
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

    document.addEventListener('keydown', function (e) {
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
                // Intento robusto de obtener el ID: dataset primero, luego celda 2 (Cód. Prod.) como respaldo
                const id = selectedRowKey.dataset.id || selectedRowKey.cells[2]?.textContent?.trim();
                const nombre = selectedRowKey.dataset.nombre || selectedRowKey.cells[3]?.textContent?.trim();

                if (!id) {
                    mostrarAlerta("No se pudo identificar el código del producto seleccionado.");
                    return;
                }

                mostrarConfirmacion(`¿Seguro que desea eliminar el producto "${nombre}" (ID: ${id})?`, () => {
                    let formData = new FormData();
                    formData.append('id', id);

                    console.log(`Intentando eliminar producto ID: ${id}`);

                    fetch('eliminar_registro.php', {
                        method: 'POST',
                        body: formData
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success') {
                                mostrarAlerta(data.message || 'Producto eliminado.');
                                getData();
                            } else {
                                // El mensaje del servidor ahora incluye el ID intentado
                                mostrarAlerta(data.message || 'Error al eliminar el producto.');
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

    contentTbody.addEventListener('keydown', function (e) {
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

    formNuevo.addEventListener('submit', function (e) {
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
        formEdita.addEventListener('submit', function (e) {
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

    // Instancia global del lector de código de barras
    let lectorCodigoBarras = null;

    // Manejo del Lector de Código de Barras
    const btnIniciarLector = document.getElementById('btn-iniciar-lector');
    const btnCopiarCodigo = document.getElementById('btn-copiar-codigo');
    const btnBuscarCodigo = document.getElementById('btn-buscar-codigo');
    const btnCerrarLector = document.getElementById('btn-cerrar-lector');
    const lectorResultado = document.getElementById('lector-resultado');
    const lectorModal = document.getElementById('lectorModal');

    // Mostrar modal del lector al hacer click (fallback programático)
    if (btnLectorCodbar && lectorModal) {
        btnLectorCodbar.addEventListener('click', function (e) {
            console.log('btnLectorCodbar clicked', e);
            try {
                const modalInstance = bootstrap.Modal.getOrCreateInstance(lectorModal);
                // Reiniciar interfaz del lector por si quedó en estado previo
                const video = document.getElementById('lector-video');
                const canvas = document.getElementById('lector-canvas');
                const placeholder = document.getElementById('lector-placeholder');
                const resultado = document.getElementById('lector-resultado');
                const btnInicio = document.getElementById('btn-iniciar-lector');
                const btnCopiar = document.getElementById('btn-copiar-codigo');
                const btnBuscar = document.getElementById('btn-buscar-codigo');

                if (video) video.style.display = 'none';
                if (canvas) canvas.style.display = 'none';
                if (placeholder) placeholder.style.display = 'block';
                if (resultado) resultado.value = '';
                if (btnInicio) { btnInicio.disabled = false; btnInicio.textContent = 'Iniciar Lector'; }
                if (btnCopiar) btnCopiar.disabled = true;
                if (btnBuscar) btnBuscar.disabled = true;

                // Asegurar que el modal esté al final de <body> para evitar problemas de stacking/contexto
                try {
                    if (lectorModal && lectorModal.parentElement !== document.body) {
                        document.body.appendChild(lectorModal);
                    }

                    // Añadir listeners una sola vez para ajustar z-index del modal y del backdrop
                    if (!lectorModal.dataset.zHandled) {
                        lectorModal.addEventListener('shown.bs.modal', function () {
                            try {
                                lectorModal.style.zIndex = '1060';
                                const backdrop = document.querySelector('.modal-backdrop');
                                if (backdrop) backdrop.style.zIndex = '1050';
                            } catch (e) {
                                console.warn('No se pudo ajustar z-index del modal/backdrop', e);
                            }
                        });

                        lectorModal.addEventListener('hidden.bs.modal', function () {
                            try {
                                lectorModal.style.zIndex = '';
                                const backdrop = document.querySelector('.modal-backdrop');
                                if (backdrop) backdrop.style.zIndex = '';
                            } catch (e) {
                                console.warn('Error al limpiar z-index del modal/backdrop', e);
                            }
                        });

                        lectorModal.dataset.zHandled = '1';
                    }

                } catch (err) {
                    console.warn('Advertencia al reposicionar modal:', err);
                }

                modalInstance.show();
            } catch (err) {
                console.error('Error al abrir modal lector:', err);
                mostrarAlerta('No se pudo abrir el lector. Revisa la consola para más detalles.');
            }
        });
    }

    if (btnIniciarLector) {
        btnIniciarLector.addEventListener('click', async function () {
            lectorCodigoBarras = new LectorCodigoBarras({
                onCodigoDetectado: function (codigo) {
                    lectorResultado.value = codigo;
                    btnCopiarCodigo.disabled = false;
                    btnBuscarCodigo.disabled = false;
                    console.log('Código de barras detectado:', codigo);
                },
                onError: function (mensaje) {
                    mostrarAlerta('Error en el lector: ' + mensaje);
                }
            });

            const iniciado = await lectorCodigoBarras.inicializar('lector-video', 'lector-canvas');
            if (iniciado) {
                document.getElementById('lector-video').style.display = 'block';
                document.getElementById('lector-canvas').style.display = 'none'; // Asegurarse de que el canvas no oculte el video
                document.getElementById('lector-placeholder').style.display = 'none';
                btnIniciarLector.disabled = true;
                btnIniciarLector.textContent = 'Lector Activo...';
            }
        });
    }

    if (btnCopiarCodigo) {
        btnCopiarCodigo.addEventListener('click', function () {
            lectorResultado.select();
            document.execCommand('copy');
            mostrarAlerta('Código copiado al portapapeles.');
        });
    }

    if (btnBuscarCodigo) {
        btnBuscarCodigo.addEventListener('click', function () {
            const codigo = lectorResultado.value.trim();
            if (!codigo) {
                mostrarAlerta('No hay código para buscar.');
                return;
            }

            // Buscar en la tabla (por código de barra)
            const filas = contentTbody.querySelectorAll('tr');
            let encontrada = false;

            filas.forEach(fila => {
                // Columna de código de barra (índice 5)
                const cellCodbar = fila.cells[5];
                if (cellCodbar && cellCodbar.textContent.trim() === codigo) {
                    setPointer(fila);
                    fila.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    encontrada = true;
                }
            });

            if (encontrada) {
                // Cerrar el modal del lector
                const modal = bootstrap.Modal.getInstance(lectorModal);
                if (modal) modal.hide();
                mostrarAlerta('Producto encontrado y seleccionado.');
            } else {
                mostrarAlerta('Código no encontrado en la tabla.');
            }
        });
    }

    if (btnCerrarLector) {
        btnCerrarLector.addEventListener('click', function () {
            if (lectorCodigoBarras) {
                lectorCodigoBarras.detener();
                lectorCodigoBarras = null;
            }

            // Restablecer interfaz del lector
            document.getElementById('lector-video').style.display = 'none';
            document.getElementById('lector-canvas').style.display = 'none';
            document.getElementById('lector-placeholder').style.display = 'block';
            if (btnIniciarLector) {
                btnIniciarLector.disabled = false;
                btnIniciarLector.textContent = 'Iniciar Lector';
            }
            lectorResultado.value = '';
            btnCopiarCodigo.disabled = true;
            btnBuscarCodigo.disabled = true;
        });
    }

    // Manejar cierre del modal cuando se cierra desde el botón X
    if (lectorModal) {
        lectorModal.addEventListener('hidden.bs.modal', function () {
            if (lectorCodigoBarras) {
                lectorCodigoBarras.detener();
                lectorCodigoBarras = null;
            }
        });
    }

    // Llamada inicial para cargar datos y ayuda a depurar
    if (typeof getData === 'function') {
        console.log('Invocando getData() para cargar tabla');
        getData();
    } else {
        console.error('getData() no está definida. Revisa productos.js');
    }

    // --- POLLING PARA ACTUALIZACIONES EN TIEMPO REAL ---
    let pollingIntervalId; // Declarar aquí para acceso global en el scope

    function startPolling() {
        if (!pollingIntervalId) { // Evitar iniciar múltiples intervalos
            console.log('Polling: Iniciando...');
            pollingIntervalId = setInterval(() => {
                console.log('Polling: Recargando datos de la tabla...');
                getData(true);
            }, 10000); // Cada 10 segundos
        }
    }

    function stopPolling() {
        if (pollingIntervalId) {
            console.log('Polling: Deteniendo...');
            clearInterval(pollingIntervalId);
            pollingIntervalId = null;
        }
    }

    // Iniciar polling al cargar la página
    startPolling();

    // Pausar polling durante la edición en línea
    contentTbody.addEventListener('focusin', function (e) {
        if (e.target.tagName === 'TD' && e.target.isContentEditable) {
            stopPolling();
        }
    });

    contentTbody.addEventListener('focusout', function (e) {
        if (e.target.tagName === 'TD' && e.target.isContentEditable) {
            startPolling();
        }
    });

    // Pausar/Reanudar polling con modales
    if (editaModalEl) {
        editaModalEl.addEventListener('shown.bs.modal', stopPolling);
        editaModalEl.addEventListener('hidden.bs.modal', startPolling);
    }
    if (nuevoModalEl) {
        nuevoModalEl.addEventListener('shown.bs.modal', stopPolling);
        nuevoModalEl.addEventListener('hidden.bs.modal', startPolling);
    }

});
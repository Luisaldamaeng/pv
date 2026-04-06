<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <link rel="icon" href="favicon.ico?v=<?php echo APP_VERSION; ?>" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calculador Precios Mercaderias v
        <?php echo APP_VERSION; ?>
    </title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        /* ... Estilos acortados por simplicidad, se mantiene desde h1 ... */
        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f8f9fa;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
            box-sizing: border-box;
            background-image: url('https://placehold.co/1920x1080/A3E635/333333?text=Billetes+Gs.+100.000');
            background-size: cover;
            background-attachment: fixed;
        }

        .container {
            background-color: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
            box-sizing: border-box;
            margin-top: 20px;
        }

        h1 {
            color: #5c5cfc;
            /* Azul/Violeta */
            text-align: left;
            margin-bottom: 20px;
            font-size: 28px;
            font-weight: 700;
            background-color: #f7fff0;
            padding: 10px;
            border-radius: 4px;
            display: inline-block;
        }

        .search-row {
            display: flex;
            gap: 15px;
            width: 100%;
            margin-bottom: 20px;
        }

        .search-group {
            display: flex;
            flex-direction: column;
            flex: 1;
            position: relative;
        }

        .search-group.small {
            flex: 0 0 120px;
        }

        .search-group label {
            color: #0056b3;
            font-weight: 700;
            margin-bottom: 5px;
            font-size: 13px;
        }

        .search-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ff0000;
            /* Borde rojo como en la imagen */
            border-radius: 4px;
            font-size: 16px;
            background-color: #ffff00;
            /* Amarillo */
            box-sizing: border-box;
        }

        #lista-busqueda {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #ccc;
            border-radius: 0 0 4px 4px;
            z-index: 1000;
            max-height: 150px;
            overflow-y: auto;
            display: none;
        }

        .item-busqueda {
            padding: 10px;
            cursor: pointer;
            border-bottom: 1px solid #eee;
        }

        .item-busqueda:hover {
            background-color: #f0f0f0;
        }

        .form-section {
            background-color: #f7fsnnff0;
            /* Verde pálido */
            padding: 20px;
            border-radius: 8px;
            width: 100%;
            box-sizing: border-box;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-row {
            display: flex;
            gap: 15px;
        }

        .form-row .form-group {
            flex: 1;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: 500;
            font-size: 14px;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 16px;
            box-sizing: border-box;
            background-color: #fdfdfd;
        }

        .form-group input[readonly] {
            background-color: #f1f3f5;
            color: #333;
            font-weight: 500;
        }

        .actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }

        button {
            padding: 12px 25px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: opacity 0.2s;
            color: white;
        }

        #btn-limpiar {
            background-color: #ff7f27;
            /* Naranja */
        }

        #btn-agregar {
            background-color: #16a34a;
            /* Verde */
        }

        button:hover {
            opacity: 0.9;
        }

        button:active {
            transform: scale(0.98);
        }

        #software-version {
            position: fixed;
            top: 10px;
            right: 10px;
            background-color: rgba(0, 0, 0, 0.7);
            color: #fff;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            z-index: 1000;
        }
    </style>
    <script>
        // Asegurar que no haya Service Workers activos (Preventivo para Ngrok/Móviles)
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.getRegistrations().then(function (registrations) {
                for (let registration of registrations) {
                    registration.unregister().then(function(boolean) {
                        if (boolean) console.log('Service Worker desregistrado.');
                    });
                }
            });
        }
        if ('caches' in window) {
            caches.keys().then(names => {
                for (let name of names) caches.delete(name);
            });
        }
    </script>
</head>

<body>
    <div id="software-version">v
        <?php echo APP_VERSION; ?>
    </div>

    <div class="container">
        <h1>Calculador Precios Mercaderias</h1>

        <div class="search-row">
            <div class="search-group small">
                <label for="search-code">Cód. / Barcode:</label>
                <input type="text" id="search-code" placeholder="370" autocomplete="off">
            </div>
            <div class="search-group">
                <label for="search-name">Nombre del Producto:</label>
                <input type="text" id="search-name" placeholder="Escriba el nombre..." autocomplete="off">
                <div id="lista-busqueda"></div>
            </div>
        </div>

        <form id="calc-form" class="form-section">
            <input type="hidden" id="codigoprod" name="codigoprod">

            <div class="form-row">
                <div class="form-group">
                    <label for="porcentaje">Porcentaje (%)</label>
                    <input type="text" id="porcentaje" name="porcentaje" value="25">
                </div>
                <div class="form-group">
                    <label for="precio_caja">Precio por Caja</label>
                    <input type="text" id="precio_caja" name="precio_caja" placeholder="Ej: 1000">
                </div>
            </div>

            <div class="form-group">
                <label for="cantidad">Cantidad</label>
                <input type="text" id="cantidad" name="cantidad">
            </div>

            <div class="form-group">
                <label for="precio_costo">Precio Costo</label>
                <input type="text" id="precio_costo" name="precio_costo">
            </div>

            <div class="form-group">
                <label for="precio_venta">Precio Venta (Calculado)</label>
                <input type="text" id="precio_venta" name="precio_venta" readonly>
            </div>

            <div class="actions">
                <button type="button" id="btn-limpiar">Limpiar</button>
                <button type="button" id="btn-guardar">Guardar</button>
            </div>
        </form>
    </div>

    <script>
        // Elementos
        const searchCode = document.getElementById('search-code');
        const searchName = document.getElementById('search-name');
        const listaBusqueda = document.getElementById('lista-busqueda');
        const codigoprodInput = document.getElementById('codigoprod');

        const porcentajeInput = document.getElementById('porcentaje');
        const precioCajaInput = document.getElementById('precio_caja');
        const cantidadInput = document.getElementById('cantidad');
        const precioCostoInput = document.getElementById('precio_costo');
        const precioVentaInput = document.getElementById('precio_venta');

        const btnLimpiar = document.getElementById('btn-limpiar');
        const btnGuardar = document.getElementById('btn-guardar');

        const inputs = [porcentajeInput, precioCajaInput, cantidadInput, precioCostoInput];

        // --- Búsqueda ---

        searchCode.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                const code = this.value.trim();
                if (!code) return;

                fetch(`buscar_por_codigo_mercaderia.php?code=${encodeURIComponent(code)}`)
                    .then(r => r.json())
                    .then(res => {
                        if (res.status === 'success') {
                            seleccionarProducto(res.data);
                        } else {
                            alert(res.message || "Producto no encontrado");
                            this.select();
                        }
                    })
                    .catch(err => alert("Error en búsqueda: " + err));
            }
        });

        searchName.addEventListener('input', function () {
            const q = this.value;
            if (q.length < 2) {
                listaBusqueda.style.display = 'none';
                return;
            }

            fetch(`buscar_productos_mercaderia.php?q=${encodeURIComponent(q)}`)
                .then(r => r.json())
                .then(data => {
                    listaBusqueda.innerHTML = '';
                    if (data.length > 0) {
                        data.forEach(p => {
                            const div = document.createElement('div');
                            div.className = 'item-busqueda';
                            div.textContent = p.nombre;
                            div.onclick = () => seleccionarProducto(p);
                            listaBusqueda.appendChild(div);
                        });
                        listaBusqueda.style.display = 'block';
                    } else {
                        listaBusqueda.style.display = 'none';
                    }
                });
        });

        function seleccionarProducto(p) {
            searchName.value = p.nombre;
            codigoprodInput.value = p.id;
            listaBusqueda.style.display = 'none';
            cargarDatosProducto(p.id);
        }

        function cargarDatosProducto(id) {
            fetch(`obtener_costo_mercaderia.php?id=${id}`)
                .then(r => r.json())
                .then(res => {
                    if (res.status === 'success' && res.data) {
                        porcentajeInput.value = formatNumber(res.data.porcentaje, 2);
                        precioCajaInput.value = formatNumber(res.data.precio_caja, 0);
                        cantidadInput.value = formatNumber(res.data.cantidad, 2);
                        precioCostoInput.value = formatNumber(res.data.precio_costo, 0);
                        recalcular();
                    } else {
                        // Mantener porcentaje pero limpiar el resto
                        precioCajaInput.value = '';
                        cantidadInput.value = '';
                        precioCostoInput.value = '';
                        precioVentaInput.value = '';
                    }
                })
                .catch(err => console.error("Error al cargar datos:", err));
        }

        // --- Cálculos ---

        function recalcular() {
            const pct = parseNumber(porcentajeInput.value);
            const caja = parseNumber(precioCajaInput.value);
            const cant = parseNumber(cantidadInput.value);
            let costo = parseNumber(precioCostoInput.value);

            // Si hay caja y cantidad, calcular costo
            if (caja > 0 && cant > 0) {
                costo = caja / cant;
                precioCostoInput.value = formatNumber(costo, 2);
            }

            if (costo > 0) {
                const venta = costo * (1 + pct / 100);
                precioVentaInput.value = formatNumber(venta, 0);
            } else {
                precioVentaInput.value = '';
            }
        }

        inputs.forEach(input => {
            input.addEventListener('input', recalcular);
            input.addEventListener('keypress', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const next = inputs[inputs.indexOf(this) + 1];
                    if (next) next.focus();
                    else btnGuardar.click();
                }
            });
        });

        // --- Utilidades de Formato ---

        function formatNumber(num, decimals) {
            if (num === null || num === undefined || isNaN(num) || num === "") return "";
            let val = parseFloat(num);
            let fixed = val.toFixed(decimals);
            let parts = fixed.split(".");
            let integerPart = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            let decimalPart = parts.length > 1 ? parts[1] : "";
            return decimals > 0 ? integerPart + "," + decimalPart : integerPart;
        }

        function parseNumber(str) {
            if (!str) return 0;
            let clean = str.toString().replace(/\./g, "").replace(",", ".");
            let val = parseFloat(clean);
            return isNaN(val) ? 0 : val;
        }

        // Formateo en tiempo real (opcional, igual que costo_caramelo)
        [precioCajaInput, precioCostoInput].forEach(inp => {
            inp.addEventListener('blur', function () {
                if (this.value) this.value = formatNumber(parseNumber(this.value), 0);
            });
        });

        // --- Acciones ---

        btnLimpiar.addEventListener('click', function () {
            document.getElementById('calc-form').reset();
            searchCode.value = '';
            searchName.value = '';
            codigoprodInput.value = '';
            precioVentaInput.value = '';
            porcentajeInput.value = '25'; // Reset al default de la imagen
            searchCode.focus();
        });

        btnGuardar.addEventListener('click', function () {
            const id = codigoprodInput.value;
            if (!id) {
                alert("Primero seleccione un producto.");
                return;
            }

            const formData = new FormData();
            formData.append('codigoprod', id);
            formData.append('porcentaje', parseNumber(porcentajeInput.value));
            formData.append('precio_caja', parseNumber(precioCajaInput.value));
            formData.append('cantidad', parseNumber(cantidadInput.value));
            formData.append('precio_costo', parseNumber(precioCostoInput.value));

            fetch('guardar_costo_mercaderia.php', {
                method: 'POST',
                body: formData
            })
                .then(r => r.json())
                .then(res => {
                    if (res.status === 'success') {
                        alert("¡Datos guardados correctamente!");
                    } else {
                        alert("Error: " + res.message);
                    }
                })
                .catch(err => alert("Error técnico: " + err));
        });

        // Cerrar lista al hacer click fuera
        document.addEventListener('click', function (e) {
            if (e.target !== searchName) listaBusqueda.style.display = 'none';
        });
    </script>
</body>

</html>
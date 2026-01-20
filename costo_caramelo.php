<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Precio golosina</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
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
            font-size: 16px;
        }

        .container {
            background-color: #f1f8e9; /* Verde pálido claro */
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 3px solid #c8a2c8; /* Contorno Lila */
            width: 100%;
            max-width: 600px;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 20px;
        }

        h1 {
            color: #3498db;
            text-align: center;
            margin-bottom: 30px;
            font-size: 32px;
            font-weight: 700;
            width: 100%;
        }

        .search-container {
            display: flex;
            gap: 20px;
            width: 100%;
            margin-bottom: 25px;
        }

        .search-section {
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .search-section.small {
            flex: 0 0 160px;
        }

        .search-section:not(.small) {
            flex: 1;
        }

        .search-section label {
            color: #3498db;
            font-weight: 500;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .search-section input {
            width: 100%;
            padding: 10px;
            border: 2px solid #e74c3c;
            border-radius: 6px;
            font-size: 16px;
            box-sizing: border-box;
            background-color: yellow;
        }

        #lista-busqueda {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #ccc;
            border-radius: 0 0 6px 6px;
            z-index: 1000;
            max-height: 200px;
            overflow-y: auto;
            display: none;
        }

        #lista-busqueda .item-busqueda {
            padding: 10px;
            cursor: pointer;
            border-bottom: 1px solid #eee;
        }

        #lista-busqueda .item-busqueda:hover {
            background-color: #f0f0f0;
        }

        #golosinaForm {
            width: 100%;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            color: #2c3e50;
            font-weight: 500;
            font-size: 14px;
        }

        .form-group input, .search-container input {
            width: 100%;
            padding: 10px;
            border: 1px solid #e74c3c;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 16px;
            background-color: yellow;
        }

        .bottom-section {
            display: flex;
            width: 100%;
            gap: 25px;
            margin-top: 20px;
            align-items: stretch;
        }

        .sidebar-actions {
            flex: 0 0 180px;
            background-color: #f1f1f1;
            padding: 20px;
            border-radius: 12px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .sidebar-actions button {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            transition: opacity 0.2s, background-color 0.2s;
        }

        #calcular {
            background-color: #3498db;
            color: white;
        }

        #calcular:hover {
            background-color: #2980b9;
        }

        #guardar {
            background-color: #27ae60;
            color: white;
        }

        #guardar:hover {
            background-color: #219150;
        }

        #repetir {
            background-color: #f39c12;
            color: white;
        }

        #repetir:hover {
            background-color: #e67e22;
        }

        #repetir.active {
            background-color: #e74c3c;
            box-shadow: 0 0 10px rgba(231, 76, 60, 0.5);
        }

        #guardar:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        #resultados {
            flex: 1;
            background-color: #edf2f4;
            padding: 20px;
            border-radius: 12px;
            border: 1px solid #d3d3d3;
            display: none;
            /* Controlado por JS */
        }

        #resultados h2 {
            margin-top: 0;
            color: #2c3e50;
            text-align: center;
            font-size: 20px;
            margin-bottom: 15px;
        }

        #resultados p {
            margin: 10px 0;
            font-size: 16px;
            color: #555;
            display: flex;
            justify-content: space-between;
        }

        #resultados p strong {
            color: #333;
            margin-left: 5px;
        }

        @media (max-width: 600px) {
            .bottom-section {
                flex-direction: column;
            }

            .sidebar-actions,
            #resultados {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Costo Caramelo</h1>

        <div class="search-container">
            <div class="search-section small">
                <label for="busqueda-codigo">Cód. / Barcode:</label>
                <input type="text" id="busqueda-codigo" placeholder="370" autocomplete="off">
            </div>

            <div class="search-section">
                <label for="busqueda-prod">Nombre del Producto:</label>
                <input type="text" id="busqueda-prod" placeholder="Escriba el nombre..." autocomplete="off">
                <div id="lista-busqueda"></div>
            </div>
        </div>

        <form id="golosinaForm">
            <input type="hidden" id="codigoprod" name="codigoprod">

            <div class="form-group">
                <label for="precio_bolsa">Precio de costo por bolsa (Gs):</label>
                <input type="text" id="precio_bolsa" name="precio_bolsa" required>
            </div>

            <div class="form-group">
                <label for="peso_bolsa">Peso por bolsa (grs):</label>
                <input type="text" id="peso_bolsa" name="peso_bolsa" required>
            </div>

            <div class="form-group">
                <label for="cantidad_golosinas">Cantidad de unidades de golosina pesadas:</label>
                <input type="text" id="cantidad_golosinas" name="cantidad_golosinas" required>
            </div>

            <div class="form-group">
                <label for="peso_muestra">Peso total de la muestra (grs):</label>
                <input type="text" id="peso_muestra" name="peso_muestra" required>
            </div>

            <div class="form-group">
                <label for="porcentaje_ganancia">Porcentaje de ganancia (%):</label>
                <input type="text" id="porcentaje_ganancia" name="porcentaje_ganancia" required>
            </div>
        </form>

        <div class="bottom-section">
            <div class="sidebar-actions">
                <button id="calcular">Calcular</button>
                <button id="guardar" disabled>Guardar Datos</button>
                <hr style="width: 100%; border: 0; border-top: 1px solid #ccc; margin: 5px 0;">
                <button id="repetir">Repetir Datos</button>
            </div>

            <div id="resultados">
                <h2>Resultados</h2>
                <p>Peso por unidad de golosina (grs): <strong id="peso_unidad"></strong></p>
                <p>Cantidad de golosinas por bolsa: <strong id="cantidad_por_bolsa"></strong></p>
                <p>Precio de costo por unidad (Gs): <strong id="precio_costo_unidad"></strong></p>
                <p>Precio de venta por unidad (Gs): <strong id="precio_venta_unidad"></strong></p>
                <p>Ganancia por bolsa (Gs): <strong id="ganancia_bolsa"></strong></p>
            </div>
        </div>
    </div>

    <script>
        const busquedaInput = document.getElementById("busqueda-prod");
        const busquedaCodigo = document.getElementById("busqueda-codigo");
        const listaBusqueda = document.getElementById("lista-busqueda");
        const codigoprodInput = document.getElementById("codigoprod");

        const precioBolsaInput = document.getElementById("precio_bolsa");
        const pesoBolsaInput = document.getElementById("peso_bolsa");
        const cantidadGolosinasInput = document.getElementById("cantidad_golosinas");
        const pesoMuestraInput = document.getElementById("peso_muestra");
        const porcentajeGananciaInput = document.getElementById("porcentaje_ganancia");
        const calcularButton = document.getElementById("calcular");
        const guardarButton = document.getElementById("guardar");
        const repetirButton = document.getElementById("repetir");
        const resultadosDiv = document.getElementById("resultados");

        let modoRepetir = false;

        const pesoUnidadOutput = document.getElementById("peso_unidad");
        const cantidadPorBolsaOutput = document.getElementById("cantidad_por_bolsa");
        const precioCostoUnidadOutput = document.getElementById("precio_costo_unidad");
        const precioVentaUnidadOutput = document.getElementById("precio_venta_unidad");
        const gananciaBolsaOutput = document.getElementById("ganancia_bolsa");


        const inputs = [precioBolsaInput, pesoBolsaInput, cantidadGolosinasInput, pesoMuestraInput, porcentajeGananciaInput];

        // Búsqueda por Código (Enter)
        busquedaCodigo.addEventListener("keypress", function (e) {
            if (e.key === "Enter") {
                const code = this.value.trim();
                if (!code) return;

                fetch(`buscar_por_codigo_caramelo.php?code=${encodeURIComponent(code)}`)
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

        // Búsqueda incremental por nombre
        busquedaInput.addEventListener("input", function () {
            const q = this.value;
            if (q.length < 2) {
                listaBusqueda.style.display = "none";
                return;
            }

            fetch(`buscar_productos_caramelo.php?q=${encodeURIComponent(q)}`)
                .then(r => r.json())
                .then(data => {
                    listaBusqueda.innerHTML = '';
                    if (data.length > 0) {
                        data.forEach(p => {
                            const div = document.createElement("div");
                            div.className = "item-busqueda";
                            div.textContent = p.nombre;
                            div.onclick = () => seleccionarProducto(p);
                            listaBusqueda.appendChild(div);
                        });
                        listaBusqueda.style.display = "block";
                    } else {
                        listaBusqueda.style.display = "none";
                    }
                });
        });

        function seleccionarProducto(p) {
            busquedaInput.value = p.nombre;
            codigoprodInput.value = p.id;
            listaBusqueda.style.display = "none";

            if (modoRepetir) {
                guardarButton.disabled = false;
                calcularButton.click();
                alert("Datos aplicados a: " + p.nombre + ". Ya puede guardar.");
                desactivarModoRepetir();
            } else {
                guardarButton.disabled = false; // Habilitar preventivamente
                cargarDatosProducto(p.id);
            }
        }

        function cargarDatosProducto(id) {
            fetch(`obtener_costo_caramelo.php?id=${id}`)
                .then(r => r.json())
                .then(res => {
                    if (res.status === 'success' && res.data) {
                        precioBolsaInput.value = formatNumber(res.data.precio_bolsa, 0);
                        pesoBolsaInput.value = formatNumber(res.data.peso_bolsa, 2);
                        cantidadGolosinasInput.value = formatNumber(res.data.cantidad_golosinas, 0);
                        pesoMuestraInput.value = formatNumber(res.data.peso_muestra, 2);
                        porcentajeGananciaInput.value = formatNumber(res.data.porcentaje_ganancia, 2);
                        calcularButton.click();
                    } else {
                        document.getElementById("golosinaForm").reset();
                        codigoprodInput.value = id;
                        resultadosDiv.style.display = "none";
                    }
                    guardarButton.disabled = false;
                })
                .catch(err => {
                    console.error("Error al cargar producto:", err);
                    guardarButton.disabled = false; // Habilitar igual para permitir ingreso manual
                });
        }

        // Enter Moves Focus
        inputs.forEach((input, index) => {
            input.addEventListener("keypress", function (event) {
                if (event.key === "Enter") {
                    event.preventDefault();
                    const nextIndex = index + 1;
                    if (nextIndex < inputs.length) {
                        inputs[nextIndex].focus();
                    } else {
                        calcularButton.click();
                    }
                }
            });
        });

        calcularButton.addEventListener("click", function () {
            const precioBolsa = parseNumber(precioBolsaInput.value);
            const pesoBolsa = parseNumber(pesoBolsaInput.value);
            const cantidadGolosinas = parseNumber(cantidadGolosinasInput.value);
            const pesoMuestra = parseNumber(pesoMuestraInput.value);
            const porcentajeGanancia = parseNumber(porcentajeGananciaInput.value);

            if (isNaN(precioBolsa) || isNaN(pesoBolsa) || isNaN(cantidadGolosinas) || isNaN(pesoMuestra) || isNaN(porcentajeGanancia)) {
                alert("Por favor, ingrese valores numéricos válidos (ej: 1.250,50)");
                return;
            }

            if (precioBolsa <= 0 || pesoBolsa <= 0 || cantidadGolosinas <= 0 || pesoMuestra <= 0 || porcentajeGanancia < 0) {
                alert("Por favor, ingrese valores mayores que cero.");
                return;
            }

            const pesoUnidad = pesoMuestra / cantidadGolosinas;
            const cantidadPorBolsa = pesoBolsa / pesoUnidad;
            const precioCostoUnidad = precioBolsa / cantidadPorBolsa;
            const precioVentaUnidad = precioCostoUnidad * (1 + porcentajeGanancia / 100);
            const gananciaBolsa = (precioVentaUnidad - precioCostoUnidad) * cantidadPorBolsa;

            pesoUnidadOutput.textContent = formatNumber(pesoUnidad, 2);
            cantidadPorBolsaOutput.textContent = formatNumber(cantidadPorBolsa, 2);
            precioCostoUnidadOutput.textContent = formatNumber(precioCostoUnidad, 2);
            precioVentaUnidadOutput.textContent = formatNumber(precioVentaUnidad, 2);
            gananciaBolsaOutput.textContent = formatNumber(Math.round(gananciaBolsa), 0);

            if (codigoprodInput.value) {
                guardarButton.disabled = false;
            }
            resultadosDiv.style.display = "block";
        });

        // Eventos de formateo en tiempo real
        precioBolsaInput.addEventListener("input", function() {
            this.value = formatInput(this.value, 0);
        });
        pesoBolsaInput.addEventListener("input", function() {
            this.value = formatInput(this.value, 2);
        });
        cantidadGolosinasInput.addEventListener("input", function() {
            this.value = formatInput(this.value, 0);
        });
        pesoMuestraInput.addEventListener("input", function() {
            this.value = formatInput(this.value, 2);
        });
        porcentajeGananciaInput.addEventListener("input", function() {
            this.value = formatInput(this.value, 2);
        });

        function formatInput(val, maxDecimals) {
            // Permitir solo números, puntos y una coma
            let clean = val.replace(/[^0-9,]/g, "");
            
            // Si tiene coma, separar
            let parts = clean.split(",");
            let integerPart = parts[0];
            let decimalPart = parts.length > 1 ? parts[1] : null;

            // Formatear parte entera con puntos
            if (integerPart !== "") {
                integerPart = parseInt(integerPart, 10).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            }

            if (decimalPart !== null && maxDecimals > 0) {
                return integerPart + "," + decimalPart.substring(0, maxDecimals);
            }
            return integerPart;
        }

        function formatNumber(num, decimals) {
            if (num === null || num === undefined || isNaN(num) || num === "") return "";
            
            let val = parseFloat(num);
            if (isNaN(val)) return "";
            
            let fixed = val.toFixed(decimals);
            let parts = fixed.split(".");
            let integerPart = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            let decimalPart = parts.length > 1 ? parts[1] : "";

            return decimals > 0 ? integerPart + "," + decimalPart : integerPart;
        }

        function parseNumber(str) {
            if (!str) return 0;
            // Quitamos puntos de miles y cambiamos coma por punto decimal
            let clean = str.toString().replace(/\./g, "").replace(",", ".");
            let val = parseFloat(clean);
            return isNaN(val) ? 0 : val;
        }

        guardarButton.addEventListener("click", function () {
            const id = document.getElementById("codigoprod").value;
            if (!id) {
                alert("Primero seleccione un producto.");
                return;
            }

            const formData = new FormData();
            
            // Enviamos los datos limpios (numéricos estándar) al servidor
            formData.append('codigoprod', id);
            formData.append('precio_bolsa', parseNumber(precioBolsaInput.value));
            formData.append('peso_bolsa', parseNumber(pesoBolsaInput.value));
            formData.append('cantidad_golosinas', parseNumber(cantidadGolosinasInput.value));
            formData.append('peso_muestra', parseNumber(pesoMuestraInput.value));
            formData.append('porcentaje_ganancia', parseNumber(porcentajeGananciaInput.value));

            // Log para debug
            console.log("Enviando datos:", Object.fromEntries(formData));

            fetch('guardar_costo_caramelo.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(res => {
                if (res.status === 'success') {
                    alert("¡Guardado correctamente!");
                } else {
                    alert("Error servidor: " + res.message);
                }
            })
            .catch(err => {
                console.error("Error Fetch:", err);
                alert("Error técnico al guardar: " + err);
            });
        });

        repetirButton.addEventListener("click", function () {
            if (!modoRepetir) {
                if (!codigoprodInput.value) {
                    alert("Primero seleccione un producto y calcule sus datos para poder repetirlos.");
                    return;
                }
                activarModoRepetir();
            } else {
                desactivarModoRepetir();
            }
        });

        function activarModoRepetir() {
            modoRepetir = true;
            repetirButton.classList.add("active");
            repetirButton.textContent = "Modo Repetir: ON";
            busquedaInput.value = "";
            busquedaCodigo.value = "";
            codigoprodInput.value = "";
            guardarButton.disabled = true;
            busquedaInput.focus();
        }

        function desactivarModoRepetir() {
            modoRepetir = false;
            repetirButton.classList.remove("active");
            repetirButton.textContent = "Repetir Datos";
        }

        document.addEventListener("click", function (e) {
            if (e.target !== busquedaInput) {
                listaBusqueda.style.display = "none";
            }
        });
    </script>
</body>

</html>
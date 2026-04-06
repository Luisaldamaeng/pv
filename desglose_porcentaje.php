<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <link rel="icon" href="favicon.ico?v=<?php echo APP_VERSION; ?>" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Desglose de Porcentaje / IVA v<?php echo APP_VERSION; ?></title>
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
            background-image: url('https://placehold.co/1920x1080/42a5f5/1565c0?text=Desglose+Financiero');
            background-size: cover;
            background-attachment: fixed;
            font-size: 16px;
        }

        .container {
            background-color: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            border: 2px solid #64b5f6;
            width: 100%;
            max-width: 550px;
            box-sizing: border-box;
            margin-top: 20px;
            backdrop-filter: blur(10px);
        }

        h1 {
            color: #1565c0;
            text-align: center;
            margin-bottom: 25px;
            font-size: 28px;
            font-weight: 700;
            border-bottom: 2px solid #bbdefb;
            padding-bottom: 15px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 600;
            font-size: 15px;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #90caf9;
            border-radius: 6px;
            font-size: 18px;
            box-sizing: border-box;
            background-color: #f8fdf8;
            transition: border-color 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #1e88e5;
            background-color: #ffffff;
        }

        .form-group input.monto-input {
            background-color: #fff9c4; /* Amarillo suave para resaltar montos */
            font-weight: bold;
            color: #d32f2f;
        }

        .presets {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }

        .preset-btn {
            flex: 1;
            padding: 10px;
            background-color: #e0f7fa;
            border: 1px solid #00bcd4;
            color: #00838f;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s;
            text-align: center;
            min-width: 80px;
        }

        .preset-btn:hover, .preset-btn.active {
            background-color: #00bcd4;
            color: white;
        }

        .resultados-card {
            background-color: #f1f8e9;
            border: 2px dashed #8bc34a;
            border-radius: 8px;
            padding: 20px;
            margin-top: 25px;
        }

        .resultados-card p {
            margin: 10px 0;
            font-size: 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #33691e;
        }

        .resultados-card p strong {
            font-size: 22px;
            color: #1b5e20;
        }
        
        .explicacion {
            font-size: 13px;
            color: #546e7a;
            margin-top: 20px;
            padding: 15px;
            background-color: #eceff1;
            border-radius: 6px;
            border-left: 4px solid #607d8b;
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
            cursor: pointer;
        }

        .actions {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        .btn-limpiar {
            background-color: #ff9800;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 6px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            width: 100%;
        }

        .btn-limpiar:hover {
            background-color: #f57c00;
        }

        .divider {
            width: 100%;
            height: 2px;
            background-color: #bbdefb;
            margin: 30px 0;
            border-radius: 2px;
        }

        .section-title {
            color: #0277bd;
            font-size: 22px;
            margin-bottom: 20px;
            text-align: center;
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
        
        // Función general de limpieza de caché local temporal si existe
        if ('caches' in window) {
            caches.keys().then(names => {
                for (let name of names) caches.delete(name);
            });
        }

        function limpiarCache() {
            if (confirm('¿Desea limpiar la caché de la aplicación y recargar?')) {
                localStorage.clear();
                sessionStorage.clear();
                if ('caches' in window) {
                    caches.keys().then((names) => {
                        for (let name of names) caches.delete(name);
                    });
                }
                alert('Caché limpiada. Recargando...');
                window.location.reload(true);
            }
        }
    </script>
</head>

<body>
    <div id="software-version" onclick="limpiarCache()" title="Click para limpiar caché">v<?php echo APP_VERSION; ?> | 🧹 Limpiar</div>

    <div class="container">
        <h1>Herramientas Financieras</h1>

        <h2 class="section-title">1. Desglose de Porcentaje / IVA</h2>

        <div class="form-group">
            <label for="monto_total">Monto Total (Con recargo o IVA incluido):</label>
            <input type="text" id="monto_total" class="monto-input" placeholder="Ej: 15.000" autocomplete="off">
        </div>

        <div class="form-group">
            <label>Seleccionar Porcentaje Incluido:</label>
            <div class="presets">
                <button type="button" class="preset-btn" data-pct="20">20% Normal</button>
                <button type="button" class="preset-btn active" data-pct="10">IVA 10%</button>
                <button type="button" class="preset-btn" data-pct="5">IVA 5%</button>
            </div>
        </div>

        <div class="form-group">
            <label for="porcentaje_personalizado">...O ingrese porcentaje (%) manualmente:</label>
            <input type="number" id="porcentaje_personalizado" value="10" step="0.1" min="0">
        </div>

        <div class="resultados-card">
            <p>Valor Neto (Base Original): <strong id="res_neto">0</strong></p>
            <p>Monto Extraído (<span id="res_lbl_pct">10%</span>): <strong id="res_impuesto">0</strong></p>
        </div>

        <div class="explicacion" id="explicacion_texto">
            <strong>Fórmula aplicada:</strong><br>
            Para un Monto Total de <span id="exp_total">0</span> y un porcentaje del <span id="exp_pct">10</span>%, 
            se divide el Total por <span id="exp_factor">1.10</span>.<br><br>
            <em>Ejemplo Contable: Si calculamos el <span id="exp_pct2">10</span>% sobre el Valor Neto resultante y lo sumamos, volvemos exactamente al Monto Total inicial.</em>
        </div>

        <div class="actions">
            <button type="button" class="btn-limpiar" id="btn_limpiar_1">Limpiar Sección 1</button>
        </div>

        <div class="divider"></div>

        <h2 class="section-title">2. Relación Porcentual (¿Qué % representa X de Y?)</h2>

        <div class="form-group">
            <label for="monto_mayor">Cantidad Mayor (El 100%):</label>
            <input type="text" id="monto_mayor" placeholder="Ej: 100" autocomplete="off">
        </div>

        <div class="form-group">
            <label for="monto_menor">Cantidad Menor a evaluar:</label>
            <input type="text" id="monto_menor" class="monto-input" placeholder="Ej: 20" autocomplete="off">
        </div>

        <div class="resultados-card" style="border-color: #ff9800; background-color: #fff3e0;">
            <p style="color: #e65100;">El monto menor equivale al: <strong style="color: #bf360c;" id="res_relativo_pct">0%</strong></p>
        </div>

        <div class="explicacion" id="explicacion_texto_2" style="background-color: #ffe0b2; border-left-color: #ff9800; color: #d84315;">
            <strong>Fórmula aplicada:</strong><br>
            (Cantidad Menor / Cantidad Mayor) × 100<br><br>
            <em>Ejemplo Contable: Si la Cantidad Mayor es <span id="exp2_mayor">0</span> y la Cantidad Menor es <span id="exp2_menor">0</span>, el resultado es que la cantidad menor representa exactamente el <span id="exp2_pct">0</span>% del total.</em>
        </div>

        <div class="actions">
            <button type="button" class="btn-limpiar" id="btn_limpiar_2">Limpiar Sección 2</button>
        </div>
    </div>

    <script>
        const montoInput = document.getElementById('monto_total');
        const pctInput = document.getElementById('porcentaje_personalizado');
        const presetBtns = document.querySelectorAll('.preset-btn');
        const resNeto = document.getElementById('res_neto');
        const resImpuesto = document.getElementById('res_impuesto');
        const resLblPct = document.getElementById('res_lbl_pct');
        const btnLimpiar1 = document.getElementById('btn_limpiar_1');

        // Elementos Sección 2 (Calculadora Relativa)
        const montoMayorInput = document.getElementById('monto_mayor');
        const montoMenorInput = document.getElementById('monto_menor');
        const resRelativoPct = document.getElementById('res_relativo_pct');
        const exp2Mayor = document.getElementById('exp2_mayor');
        const exp2Menor = document.getElementById('exp2_menor');
        const exp2Pct = document.getElementById('exp2_pct');
        const btnLimpiar2 = document.getElementById('btn_limpiar_2');

        // Formato numérico general
        function formatNumber(num, decimals = 0) {
            if (num === null || isNaN(num) || num === "") return "0";
            let val = parseFloat(num);
            let fixed = val.toFixed(decimals);
            let parts = fixed.split(".");
            let integerPart = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            let decimalPart = parts.length > 1 && decimals > 0 ? "," + parts[1] : "";
            return integerPart + decimalPart;
        }

        function parseNumber(str) {
            if (!str) return 0;
            let clean = str.toString().replace(/\./g, "").replace(",", ".");
            let val = parseFloat(clean);
            return isNaN(val) ? 0 : val;
        }

        function formatInputRealTime(val) {
            let clean = val.replace(/[^0-9]/g, "");
            if (clean !== "") {
                return parseInt(clean, 10).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            }
            return "";
        }

        // ==================== LÓGICA SECCIÓN 1 ====================
        montoInput.addEventListener('input', function() {
            this.value = formatInputRealTime(this.value);
            calcularDesglose();
        });

        pctInput.addEventListener('input', function() {
            presetBtns.forEach(btn => btn.classList.remove('active'));
            calcularDesglose();
        });

        presetBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                presetBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                pctInput.value = this.getAttribute('data-pct');
                calcularDesglose();
            });
        });

        btnLimpiar1.addEventListener('click', function() {
            montoInput.value = '';
            resNeto.innerText = '0';
            resImpuesto.innerText = '0';
            montoInput.focus();
            calcularDesglose();
        });

        function calcularDesglose() {
            const V = parseNumber(montoInput.value);
            const x = parseFloat(pctInput.value) || 0;

            if (V > 0 && x >= 0) {
                const factorDecimal = x / 100;
                const factorIncremental = 1 + factorDecimal;
                
                const valorNeto = V / factorIncremental;
                const montoExtraido = V - valorNeto;

                resNeto.innerText = formatNumber(valorNeto, 2);
                resImpuesto.innerText = formatNumber(montoExtraido, 2);
                resLblPct.innerText = x + "%";

                expTotal.innerText = formatNumber(V, 0);
                expPct.innerText = x;
                expPct2.innerText = x;
                expFactor.innerText = factorIncremental.toFixed(2);
            } else {
                resNeto.innerText = '0';
                resImpuesto.innerText = '0';
                resLblPct.innerText = x + "%";
                
                expTotal.innerText = '0';
                expPct.innerText = x;
                expPct2.innerText = x;
                expFactor.innerText = (1 + (x/100)).toFixed(2);
            }
        }

        // ==================== LÓGICA SECCIÓN 2 ====================
        montoMayorInput.addEventListener('input', function() {
            this.value = formatInputRealTime(this.value);
            calcularRelativo();
        });

        montoMenorInput.addEventListener('input', function() {
            this.value = formatInputRealTime(this.value);
            calcularRelativo();
        });

        btnLimpiar2.addEventListener('click', function() {
            montoMayorInput.value = '';
            montoMenorInput.value = '';
            resRelativoPct.innerText = '0%';
            exp2Mayor.innerText = '0';
            exp2Menor.innerText = '0';
            exp2Pct.innerText = '0';
            montoMayorInput.focus();
        });

        function calcularRelativo() {
            const mayor = parseNumber(montoMayorInput.value);
            const menor = parseNumber(montoMenorInput.value);

            if (mayor > 0 && menor >= 0) {
                // Regla de tres: (menor / mayor) * 100
                const porcentaje = (menor / mayor) * 100;

                resRelativoPct.innerText = formatNumber(porcentaje, 2) + '%';
                
                exp2Mayor.innerText = formatNumber(mayor, 0);
                exp2Menor.innerText = formatNumber(menor, 0);
                exp2Pct.innerText = formatNumber(porcentaje, 2);
            } else {
                resRelativoPct.innerText = '0%';
                exp2Mayor.innerText = '0';
                exp2Menor.innerText = '0';
                exp2Pct.innerText = '0';
            }
        }

        // Foco inicial
        montoInput.focus();
        calcularDesglose();
        calcularRelativo();
    </script>
</body>
</html>

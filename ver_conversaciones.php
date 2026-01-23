<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Conversaciones - Chatbot</title>
    <link rel="stylesheet" href="productos.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
            background-color: #f4f7f6;
        }

        .container {
            max-width: 1100px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 0.9em;
        }

        th,
        td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #2c3e50;
            color: white;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        .role-user {
            color: #2980b9;
            font-weight: bold;
        }

        .role-bot {
            color: #27ae60;
            font-weight: bold;
        }

        .session-id {
            color: #7f8c8d;
            font-size: 0.85em;
            font-family: monospace;
        }

        .msg-text {
            max-width: 400px;
            overflow-wrap: break-word;
        }

        .no-data {
            text-align: center;
            padding: 20px;
            color: #7f8c8d;
        }

        .filter-box {
            margin-bottom: 20px;
            text-align: right;
        }

        #sessionFilter {
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #ccc;
            width: 250px;
        }
    </style>
</head>

<body>

    <div class="container">
        <h1>💬 Historial de Conversaciones</h1>
        <p>Registro de mensajes intercambiados con el asistente virtual.</p>

        <div class="filter-box" style="display: flex; justify-content: space-between; align-items: center;">
            <button onclick="emptyHistory()"
                style="background-color: #e74c3c; color: white; border: none; padding: 10px 15px; border-radius: 4px; cursor: pointer;">🗑️
                Vaciar Todo el Historial</button>
            <input type="text" id="sessionFilter" placeholder="Filtrar por ID de Sesión..."
                onkeyup="filterConversations()">
        </div>

        <div id="loading" style="text-align: center;">Cargando mensajes...</div>

        <table id="convTable" style="display: none;">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Rol</th>
                    <th>Mensaje</th>
                    <th>ID Sesión</th>
                </tr>
            </thead>
            <tbody id="convBody"></tbody>
        </table>
        <div id="emptyMsg" class="no-data" style="display: none;">No hay mensajes registrados aún.</div>
    </div>

    <script>
        let allLogs = [];

        function loadConversations() {
            fetch('obtener_conversaciones.php')
                .then(res => res.json())
                .then(data => {
                    document.getElementById('loading').style.display = 'none';
                    allLogs = data;
                    renderLogs(data);
                })
                .catch(err => {
                    console.error("Error cargando logs:", err);
                    alert("Error al conectar con la base de datos.");
                });
        }

        function renderLogs(logs) {
            const body = document.getElementById('convBody');
            const table = document.getElementById('convTable');
            const emptyMsg = document.getElementById('emptyMsg');

            body.innerHTML = '';

            if (logs.length === 0) {
                table.style.display = 'none';
                emptyMsg.style.display = 'block';
                return;
            }

            table.style.display = 'table';
            emptyMsg.style.display = 'none';

            logs.forEach(item => {
                const roleClass = item.rol === 'user' ? 'role-user' : 'role-bot';
                const roleName = item.rol === 'user' ? 'USUARIO' : 'BOT';
                const row = `
                <tr>
                    <td style="white-space: nowrap;">${item.fecha}</td>
                    <td class="${roleClass}">${roleName}</td>
                    <td class="msg-text">${item.mensaje}</td>
                    <td class="session-id">${item.session_id}</td>
                </tr>
            `;
                body.innerHTML += row;
            });
        }

        function filterConversations() {
            const query = document.getElementById('sessionFilter').value.toLowerCase();
            const filtered = allLogs.filter(log =>
                log.session_id.toLowerCase().includes(query) ||
                log.mensaje.toLowerCase().includes(query)
            );
            renderLogs(filtered);
        }

        function emptyHistory() {
            if (confirm("¿Estás seguro de que quieres borrar TODOS los registros de conversaciones? Esta acción no se puede deshacer.")) {
                fetch('obtener_conversaciones.php?delete_all=true')
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            loadConversations();
                        } else {
                            alert("Error: " + data.message);
                        }
                    })
                    .catch(err => alert("Error de conexión."));
            }
        }

        // Cargar al iniciar
        document.addEventListener('DOMContentLoaded', loadConversations);
    </script>

</body>

</html>
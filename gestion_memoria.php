<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Memoria del Chatbot</title>
    <link rel="stylesheet" href="productos.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
            background-color: #f4f7f6;
        }

        .container {
            max-width: 900px;
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
        }

        th,
        td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #3498db;
            color: white;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        .btn-delete {
            background-color: #e74c3c;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .btn-delete:hover {
            background-color: #c0392b;
        }

        .btn-edit {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s;
            margin-right: 5px;
        }

        .btn-edit:hover {
            background-color: #2980b9;
        }

        .badge {
            background: #2ecc71;
            color: white;
            padding: 4px 8px;
            border-radius: 10px;
            font-size: 0.8em;
        }

        .no-data {
            text-align: center;
            padding: 20px;
            color: #7f8c8d;
        }

        .version-badge {
            position: fixed;
            top: 20px;
            right: 20px;
            background: rgba(44, 62, 80, 0.1);
            backdrop-filter: blur(5px);
            border: 1px solid rgba(44, 62, 80, 0.2);
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 1000;
            color: #2c3e50;
        }

        .version-badge:hover {
            background: rgba(44, 62, 80, 0.2);
            transform: scale(1.05);
        }
    </style>
</head>

<body>
    <div class="version-badge" onclick="limpiarCache()">
        v<?php require_once 'config.php';
        echo APP_VERSION; ?> | 🧹 Limpiar Caché
    </div>
    <h1>🧠 Memoria de mi Chatbot</h1>
    <p>Aquí puedes ver todo lo que el chatbot ha aprendido de tus conversaciones.</p>

    <div id="formContainer"
        style="background-color: #f9f9f9; padding: 20px; border-radius: 8px; margin-bottom: 30px; border: 1px solid #eee;">
        <h3 id="formTitle">➕ Agregar Nueva Instrucción</h3>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <input type="hidden" id="editId">
            <input type="text" id="newConcept" placeholder="Concepto (ej: Horario Navidad)"
                style="flex: 1; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
            <input type="text" id="newValue" placeholder="Información que debe recordar"
                style="flex: 2; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
            <button id="btnSubmit" onclick="saveInstruction()"
                style="background-color: #27ae60; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer;">Guardar</button>
            <button id="btnCancel" onclick="resetForm()"
                style="display: none; background-color: #95a5a6; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer;">Cancelar</button>
        </div>
    </div>

    <div id="loading" style="text-align: center;">Cargando conocimientos...</div>

    <table id="memoryTable" style="display: none;">
        <thead>
            <tr>
                <th>Concepto</th>
                <th>Información Guardada</th>
                <th>Última Actualización</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody id="memoryBody"></tbody>
    </table>
    <div id="emptyMsg" class="no-data" style="display: none;">El chatbot aún no ha aprendido nada nuevo.</div>
    </div>

    <script>
        function loadMemory() {
            fetch('obtener_memoria.php')
                .then(res => res.json())
                .then(data => {
                    document.getElementById('loading').style.display = 'none';
                    const body = document.getElementById('memoryBody');
                    const table = document.getElementById('memoryTable');
                    const emptyMsg = document.getElementById('emptyMsg');

                    body.innerHTML = '';

                    if (data.length === 0) {
                        table.style.display = 'none';
                        emptyMsg.style.display = 'block';
                        return;
                    }

                    table.style.display = 'table';
                    emptyMsg.style.display = 'none';

                    // Guardamos copia local para editar
                    window.allMemory = data;

                    data.forEach(item => {
                        const row = `
                        <tr>
                            <td><strong>${item.concepto}</strong></td>
                            <td>${item.valor}</td>
                            <td><span class="badge">${item.fecha_actualizacion}</span></td>
                            <td style="white-space: nowrap;">
                                <button class="btn-edit" onclick="editItem(${item.id})">Editar</button>
                                <button class="btn-delete" onclick="deleteItem(${item.id})">Borrar</button>
                            </td>
                        </tr>
                    `;
                        body.innerHTML += row;
                    });
                })
                .catch(err => {
                    console.error("Error cargando memoria:", err);
                    alert("Error al conectar con la base de datos.");
                });
        }

        function deleteItem(id) {
            if (confirm("¿Estás seguro de que quieres que el chatbot olvide esta información?")) {
                fetch(`obtener_memoria.php?delete_id=${id}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            loadMemory();
                        } else {
                            alert("Error al borrar.");
                        }
                    });
            }
        }

        function editItem(id) {
            const item = window.allMemory.find(i => i.id == id);
            if (!item) return;

            document.getElementById('editId').value = item.id;
            document.getElementById('newConcept').value = item.concepto;
            document.getElementById('newValue').value = item.valor;

            document.getElementById('formTitle').innerText = "✏️ Editar Instrucción";
            document.getElementById('btnSubmit').innerText = "Guardar Cambios";
            document.getElementById('btnSubmit').style.backgroundColor = "#3498db";
            document.getElementById('btnCancel').style.display = "inline-block";

            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function resetForm() {
            document.getElementById('editId').value = "";
            document.getElementById('newConcept').value = "";
            document.getElementById('newValue').value = "";

            document.getElementById('formTitle').innerText = "➕ Agregar Nueva Instrucción";
            document.getElementById('btnSubmit').innerText = "Guardar";
            document.getElementById('btnSubmit').style.backgroundColor = "#27ae60";
            document.getElementById('btnCancel').style.display = "none";
        }

        function saveInstruction() {
            const id = document.getElementById('editId').value;
            const concepto = document.getElementById('newConcept').value.trim();
            const valor = document.getElementById('newValue').value.trim();

            if (!concepto || !valor) {
                alert("Por favor completa ambos campos.");
                return;
            }

            const payload = { concepto, valor };
            if (id) payload.id = id;

            fetch('obtener_memoria.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        resetForm();
                        loadMemory();
                    } else {
                        alert("Error: " + data.message);
                    }
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

        // Cargar al iniciar
        document.addEventListener('DOMContentLoaded', loadMemory);
    </script>

</body>

</html>
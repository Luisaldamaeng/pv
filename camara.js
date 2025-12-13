document.addEventListener('DOMContentLoaded', () => {
    const contentTbody = document.getElementById('content');
    
    // Elementos del Modal
    const cameraModalEl = document.getElementById('cameraModal');
    const cameraModal = new bootstrap.Modal(cameraModalEl);
    const video = document.getElementById('camera-stream');
    const canvas = document.getElementById('camera-canvas');
    const cameraPlaceholder = document.getElementById('camera-placeholder');
    
    // Botones del Modal
    const btnOpenCamera = document.getElementById('btn-open-camera');
    const btnTakePhoto = document.getElementById('btn-take-photo');

    // Estado
    let currentStream = null;
    let currentProduct = {
        codigo: null,
        nombre: null
    };

    // 1. Abrir el modal al hacer clic en el icono de la cámara en la tabla
    contentTbody.addEventListener('click', (event) => {
        const cameraBtn = event.target.closest('.camera-btn');
        if (cameraBtn) {
            event.preventDefault();
            currentProduct.codigo = cameraBtn.dataset.codigo;
            currentProduct.nombre = cameraBtn.dataset.nombre;
            cameraModal.show();
        }
    });

    // 2. Abrir la cámara al hacer clic en el botón "Abrir Cámara"
    btnOpenCamera.addEventListener('click', async () => {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            alert('Tu navegador no soporta el acceso a la cámara. Intenta con otro navegador.');
            return;
        }

        try {
            const stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'environment' } // Preferir la cámara trasera
            });
            
            currentStream = stream;
            video.srcObject = stream;

            cameraPlaceholder.style.display = 'none';
            video.style.display = 'block';
            
            btnOpenCamera.disabled = true;
            btnTakePhoto.disabled = false;

        } catch (err) {
            console.error("Error al acceder a la cámara: ", err);
            let message = 'No se pudo acceder a la cámara. ';
            if (err.name === "NotAllowedError") {
                message += 'Asegúrate de conceder permisos para usar la cámara.';
            } else if (window.location.protocol !== 'https:' && window.location.hostname !== 'localhost') {
                 message += 'El acceso a la cámara solo es posible en sitios seguros (HTTPS) o en localhost.';
            }
            alert(message);
        }
    });

    // 3. Tomar la foto
    btnTakePhoto.addEventListener('click', () => {
        if (!currentStream) return;

        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);

        canvas.toBlob((blob) => {
            const formData = new FormData();
            formData.append('foto', blob, `${currentProduct.codigo}.jpg`);
            formData.append('codigoprod', currentProduct.codigo);
            formData.append('nombre', currentProduct.nombre);

            uploadPhoto(formData, () => cameraModal.hide());
        }, 'image/jpeg');
    });

    // 4. Detener la cámara cuando el modal se cierra
    cameraModalEl.addEventListener('hidden.bs.modal', () => {
        if (currentStream) {
            currentStream.getTracks().forEach(track => track.stop());
        }
        // Resetear estado del modal para la próxima vez
        video.style.display = 'none';
        video.srcObject = null;
        cameraPlaceholder.style.display = 'block';
        btnOpenCamera.disabled = false;
        btnTakePhoto.disabled = true;
        currentProduct = { codigo: null, nombre: null };
    });


    // 5. Función de subida
    function uploadPhoto(formData, onCompleteCallback) {
        fetch('guardar_foto.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            // Primero, verificamos si la respuesta es realmente JSON
            const contentType = response.headers.get("content-type");
            if (contentType && contentType.indexOf("application/json") !== -1) {
                return response.json();
            } else {
                return response.text().then(text => { throw new Error("La respuesta del servidor no es JSON: " + text) });
            }
        })
        .then(data => {
            if (data.status === 'success') {
                if (typeof mostrarAlerta === 'function') {
                    mostrarAlerta(data.message);
                } else {
                    alert(data.message);
                }
                // Recargar datos de la tabla para mostrar la nueva imagen
                if (typeof getData === 'function') {
                    getData();
                }
            } else {
                if (typeof mostrarAlerta === 'function') {
                    mostrarAlerta('Error: ' + data.message);
                } else {
                    alert('Error: ' + data.message);

                }
            }
        })
        .catch(error => {
            console.error('Error al subir la foto:', error);
            if (typeof mostrarAlerta === 'function') {
                mostrarAlerta('Error de conexión al subir la foto.');
            } else {
                alert('Error de conexión al subir la foto.');
            }
        })
        .finally(() => {
            // Se ejecuta siempre, al final de la promesa (éxito o error)
            if (typeof onCompleteCallback === 'function') {
                onCompleteCallback();
            }
        });
    }
});
/**
 * Lector de Código de Barras usando zxing-js (@zxing/browser)
 * Implementa la misma interfaz usada por `productos.js`:
 *  - constructor({onCodigoDetectado, onError})
 *  - inicializar(videoElementId, canvasElementId)
 *  - detener()
 *  - obtenerUltimoCodigoDetectado()
 *
 * Usa import dinámico desde un CDN ESM (skypack) para evitar dependencias de build.
 */

class LectorCodigoBarras {
    constructor(options = {}) {
        this.videoElement = null;
        this.canvasElement = null;
        this.reader = null; // instancia de BrowserMultiFormatReader
        this.codigoDetectado = null;
        this.callbackCodigoDetectado = options.onCodigoDetectado || null;
        this.callbackError = options.onError || null;
        this._activeDeviceId = null;
        this._zxingModule = null;
        // formatos permitidos (puede pasarse en options.formats)
        // valores esperados: miembros de ZX.BarcodeFormat, p.ej. ZX.BarcodeFormat.EAN_13
        this.requestedFormats = options.formats || null;
    }

    async _loadZxing() {
        if (this._zxingModule) return this._zxingModule;
        try {
            // Cargar @zxing/browser
            const browserMod = await import('https://cdn.skypack.dev/@zxing/browser');
            // Cargar @zxing/library (donde están DecodeHintType y NotFoundException)
            const libraryMod = await import('https://cdn.skypack.dev/@zxing/library');

            this._zxingModule = {
                browser: browserMod.default || browserMod, // Manejar export default o directas
                library: libraryMod.default || libraryMod  // Manejar export default o directas
            };
            return this._zxingModule;
        } catch (err) {
            this._handleError('No se pudo cargar las librerías zxing: ' + err.message);
            throw err;
        }
    }

    _handleError(msg) {
        console.error('LectorCodigoBarras -', msg);
        if (this.callbackError) this.callbackError(msg);
    }

    async inicializar(videoElementId, canvasElementId) {
        this.videoElement = document.getElementById(videoElementId);
        this.canvasElement = document.getElementById(canvasElementId); // mantenido por compatibilidad, no requerido por zxing

        if (!this.videoElement) {
            this._handleError('Elemento de video no encontrado en el DOM.');
            return false;
        }

        // Cargar zxing-js dinámicamente
        let BrowserMultiFormatReader, DecodeHintType, BarcodeFormat, NotFoundException;
        try {
            const zxingModules = await this._loadZxing();
            
            // Destructurar desde el módulo browser
            const browserExports = zxingModules.browser;
            BrowserMultiFormatReader = browserExports.BrowserMultiFormatReader;
            BarcodeFormat = browserExports.BarcodeFormat;

            // Destructurar desde el módulo library
            const libraryExports = zxingModules.library;
            DecodeHintType = libraryExports.DecodeHintType;
            NotFoundException = libraryExports.NotFoundException;

            if (!BrowserMultiFormatReader || !DecodeHintType || !BarcodeFormat || !NotFoundException) {
                throw new Error('No se pudieron encontrar las exportaciones clave de ZXing en los módulos cargados (estructura inesperada).');
            }

        } catch (err) {
            this._handleError('Error al cargar y desestructurar los módulos zxing: ' + err.message);
            return false;
        }

        try {
            // Crear reader
            this.reader = new BrowserMultiFormatReader();
            console.log('Lector zxing BrowserMultiFormatReader creado.');

            // Si el usuario pasó requested formats en options, configurar hints
            try {
                const hints = new Map();
                let formatsList = null;

                if (this.requestedFormats && Array.isArray(this.requestedFormats)) {
                    formatsList = this.requestedFormats;
                } else {
                    // Por defecto, configurar para formatos EAN, UPC, CODE_128 y QR_CODE para un buen rendimiento.
                    // Estos incluyen EAN-13, EAN-8, UPC-A, UPC-E y Code-128.
                    formatsList = [
                        BarcodeFormat.EAN_13,
                        BarcodeFormat.EAN_8,
                        BarcodeFormat.UPC_A,
                        BarcodeFormat.UPC_E,
                        BarcodeFormat.CODE_128,
                        BarcodeFormat.QR_CODE // Añadido para detección explícita de códigos QR
                    ];
                }

                hints.set(DecodeHintType.POSSIBLE_FORMATS, formatsList);
                // Aplicar hints al reader (MultiFormatReader)
                if (typeof this.reader.setHints === 'function') {
                    this.reader.setHints(hints);
                } else if (this.reader.hints !== undefined) {
                    this.reader.hints = hints;
                }

                console.log('Lector zxing configurado con formatos:', formatsList.map(f => f.toString()));
            } catch (hintErr) {
                console.warn('No se pudieron establecer hints de ZXing:', hintErr);
            }

            console.log('Llamando a decodeFromVideoDevice...');
            // Obtener dispositivo por defecto (null = usar cámara por defecto)
            // decodeFromVideoDevice acepta deviceId o null
            this.reader.decodeFromVideoDevice(null, this.videoElement, (result, err) => {
                // Log every time the callback is invoked to confirm frame processing
                // console.log('DEBUG: decodeFromVideoDevice callback invoked.');

                if (result) {
                    const code = result.getText();
                    if (code && code !== this.codigoDetectado) {
                        this.codigoDetectado = code;
                        if (this.callbackCodigoDetectado) this.callbackCodigoDetectado(code);
                        console.log('Código de barras detectado:', code);
                        console.log('DEBUG: Barcode successfully detected:', code); // Added debug log
                    }
                } else if (err) {
                    if (!(err instanceof NotFoundException)) {
                        // NotFoundException es esperado cuando no hay lectura en el frame
                        console.error('zxing decode error:', err);
                        console.log('DEBUG: zxing encountered an error (not NotFoundException):', err); // Added debug log for other errors
                    } else {
                        // Log that scanning is active but no barcode was found in this frame.
                        // This can be very verbose, so it's commented out by default but useful for specific debugging.
                        // console.log('DEBUG: Scanning frame... No barcode found (NotFoundException).');
                    }
                } else {
                    // This case should ideally not happen if zxing is working as expected (it should yield a result or an error)
                    console.log('DEBUG: decodeFromVideoDevice callback: No result and no error received.');
                }
            });

            // Añadir un listener para verificar el estado del video
            this.videoElement.addEventListener('loadedmetadata', () => {
                console.log('Video loadedmetadata event. readyState:', this.videoElement.readyState);
                console.log('Video dimensions:', this.videoElement.videoWidth, 'x', this.videoElement.videoHeight);
            });
            this.videoElement.addEventListener('play', () => console.log('Video play event.'));
            this.videoElement.addEventListener('pause', () => console.log('Video pause event.'));
            this.videoElement.addEventListener('ended', () => console.log('Video ended event.'));
            this.videoElement.addEventListener('error', (e) => console.error('Video error event:', e));


            return true;
        } catch (err) {
            this._handleError('Error al inicializar zxing o acceder a la cámara: ' + err.message);
            return false;
        }
    }

    detener() {
        try {
            console.log('Iniciando detención del lector...');
            if (this.reader) {
                try {
                    console.log('Intentando resetear lector. Tipo de this.reader:', typeof this.reader);
                    if (typeof this.reader.reset === 'function') {
                        this.reader.reset();
                        console.log('Lector zxing reseteado.');
                    } else {
                        console.warn('this.reader no tiene un método reset(). Es posible que no sea un BrowserMultiFormatReader válido.');
                    }
                } catch (e) {
                    console.error('Error al resetear lector:', e);
                }
                this.reader = null;
            }

            // Intentar detener cualquier stream del video
            if (this.videoElement && this.videoElement.srcObject) {
                try {
                    const tracks = this.videoElement.srcObject.getTracks();
                    tracks.forEach(t => {
                        t.stop();
                        console.log('Track de video detenido:', t.kind, t.label);
                    });
                } catch (e) {
                    console.warn('Error al detener tracks de video:', e);
                }
                this.videoElement.srcObject = null;
                console.log('videoElement.srcObject establecido a null.');
            }

            this.codigoDetectado = null;
            console.log('Lector de código de barras detenido (zxing).');
        } catch (e) {
            console.error('Error al detener lector zxing:', e);
        }
    }

    obtenerUltimoCodigoDetectado() {
        return this.codigoDetectado;
    }

    reiniciar() {
        this.codigoDetectado = null;
        console.log('Lector reiniciado. Esperando nuevo código de barras...');
    }
}

// Exportar para CommonJS si aplica
if (typeof module !== 'undefined' && module.exports) {
    module.exports = LectorCodigoBarras;
}

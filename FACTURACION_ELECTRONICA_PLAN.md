# Plan de Desarrollo: Facturación Electrónica Paraguay (SIFEN)

Este plan detalla los pasos técnicos para integrar la emisión de Documentos Tributarios Electrónicos (DTE) en una aplicación PHP.

## 1. Fase de Preparación (Requisitos Previos)
Antes de programar, el contribuyente debe:
- **Certificado Digital:** Obtener un certificado X.509 (.p12 o .pfx) de una autoridad certificadora (ej. COPACO, Code100).
- **CSC (Código de Seguridad del Contribuyente):** Se obtiene en el portal Marangatu. Es vital para generar el código QR.
- **Ambiente de Prueba:** Solicitar acceso al ambiente de "Pruebas" (Test) de la DNIT para no emitir documentos con validez legal durante el desarrollo.

## 2. Requisitos Técnicos (Servidor PHP)
Asegurarse de que el servidor (XAMPP o similar) tenga activo:
- `extension=openssl` (Para la firma digital).
- `extension=soap` (Para la comunicación con los Web Services).
- `extension=curl` (Para peticiones HTTP).
- Versión de PHP 7.4 o superior (Recomendado 8.1+).

## 3. Arquitectura del Módulo
El desarrollo se debe dividir en estos componentes clave:

### A. Generador de XML (Formato Version 150)
- Mapear los datos de la base de datos `pv` al esquema XML oficial.
- Implementar la generación del **CDC (Código Digital de Control)**: una cadena de 44 caracteres que identifica de forma única al documento.

### B. Módulo de Firma Digital
- Cargar el certificado `.p12`.
- Firmar el XML siguiendo el estándar **XMLDSig** (X509 Signature). El SIFEN requiere que el nodo `<dSign>` esté al final del XML.

### C. Cliente Web Service (SOAP)
- Implementar los 3 servicios principales:
    1. **Recepción:** Para enviar el DTE.
    2. **Consulta:** Para saber el estado de un documento enviado.
    3. **Eventos:** Para cancelaciones o notas de crédito.

### D. Generador de KuDE (PDF)
- Crear la representación gráfica en PDF.
- **Código QR:** Generar el QR que contenga la URL de consulta pública de la DNIT con los parámetros del documento.

## 4. Flujo de Trabajo Sugerido (Timeline)
1. **Semana 1:** Generación de XML y CDC (Pruebas locales sin firma).
2. **Semana 2:** Implementación de Firma Digital y validación con la herramienta oficial de la DNIT.
3. **Semana 3:** Conexión con Web Services en ambiente de TEST.
4. **Semana 4:** Generación de KuDE (PDF) y envío al receptor.

## 5. Recursos Útiles
- **Documentación:** [Manual Técnico SIFEN (DNIT)](https://www.dnit.gov.py/).
- **Librerías recomendadas:** Buscar en GitHub proyectos como `sifen-php` o `juan804041/sifen` para ahorrar tiempo en la lógica de firma y protocolos SOAP.

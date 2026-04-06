<?php
require_once 'config.php';

header('Content-Type: application/json');

// Verificar si se recibió un mensaje
$input = json_decode(file_get_contents('php://input'), true);
$userMessage = $input['message'] ?? '';
$history = $input['history'] ?? []; // Historial de la sesión

if (empty($userMessage)) {
    echo json_encode(['error' => 'No se recibió mensaje']);
    exit;
}

if (GEMINI_API_KEY === 'TU_API_KEY_AQUI' || empty(GEMINI_API_KEY)) {
    echo json_encode(['response' => '¡Hola! Aún no he sido configurado con mi "cerebro" (API Key). Por favor, dime que configure la clave en config.php para que pueda empezar a hablar de forma inteligente.']);
    exit;
}

// --- LOGICA DE MEMORIA PERSISTENTE (Base de Datos) ---
$memoriaExtra = "";
$resMem = $conn->query("SELECT concepto, valor FROM chatbot_memoria");
if ($resMem && $resMem->num_rows > 0) {
    $memoriaExtra = "\nCONOCIMIENTOS ADQUIRIDOS PREVIAMENTE:\n";
    while ($row = $resMem->fetch_assoc()) {
        $memoriaExtra .= "- " . $row['concepto'] . ": " . $row['valor'] . "\n";
    }
}

// --- LOGICA DE BUSQUEDA DE PRODUCTOS ---
$contextoProductos = "";
$palabras = explode(' ', $userMessage);
$queryParts = [];
$params = [];
$types = "";

foreach ($palabras as $p) {
    if (strlen($p) > 3) {
        $queryParts[] = "NOMBRE LIKE ?";
        $params[] = "%$p%";
        $types .= "s";
    }
}

if (!empty($queryParts)) {
    $sql = "SELECT NOMBRE, PRECIO1 FROM producto WHERE " . implode(" OR ", $queryParts) . " LIMIT 5";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $res = $stmt->get_result();
        $matches = [];
        while ($row = $res->fetch_assoc()) {
            $matches[] = "- " . $row['NOMBRE'] . " (Precio: Gs " . number_format($row['PRECIO1'], 0, ',', '.') . ")";
        }
        if (!empty($matches)) {
            $contextoProductos = "\n\nINFORMACIÓN DE PRODUCTOS (Usa esto para responder sobre precios):\n" . implode("\n", $matches);
        }
        $stmt->close();
    }
}

// Configuración de la IA (Instrucciones de personalidad)
$infoTienda = "
INFORMACIÓN DE LA TIENDA:
- Horario: 
  * Lunes a Sábado: 09:00 a 22:00
  * Domingo: 09:00 a 15:00 y 19:30 a 22:00
- Pagos: Aceptamos Transferencia Bancaria y Giros.
- Delivery: Costo de 15.000 Gs. Abarca toda la zona de Gran Asunción.
";

$systemPrompt = "Eres el asistente virtual (WhatsApp) de la tienda PV de Luis. Tu objetivo es ayudar a los usuarios con los precios de productos, horarios, formas de pago y dudas generales. Eres amable, eficiente y hablas en español paraguayo si es posible (usa 'Gs' para moneda). " .
    $infoTienda . $memoriaExtra .
    "\nINSTRUCCIÓN DE APRENDIZAJE: Si el usuario te da una información nueva importante para que la recuerdes SIEMPRE (ej: 'Anota que mañana cerramos temprano' o 'Recuerda que ahora aceptamos Bitcoin'), responde normalmente pero INCLUYE al final de tu respuesta (en una línea nueva) exactamente este comando: [SAVE:concepto|valor]. Reemplaza 'concepto' por una palabra clave corta y 'valor' por la información. " .
    "\nIMPORTANTE: Si el usuario tiene una duda compleja, quiere hablar con un humano, o parece frustrado, sugiérele amablemente que use el botón de 'Hablar con un humano' (WhatsApp) que está en la ventana de chat. " .
    "Si el usuario pregunta por el horario, usa la información de arriba para responder con exactitud. " .
    "Si el usuario pregunta por un producto y te proporciono información abajo, úsala para informar el precio exacto. No menciones el stock ni la existencia de los mismos, solo el nombre y el precio. " .
    "Si no encuentras el producto en la información proporcionada, dile amablemente que no lo encuentras pero que puede buscarlo en la tabla principal o consultar por WhatsApp." . $contextoProductos;

// --- LOGICA DE REGISTRO DE CONVERSACIONES (Logs) ---
$sessionId = $input['session_id'] ?? 'default_session';

// Guardar mensaje del usuario
$stmtLogUser = $conn->prepare("INSERT INTO chatbot_conversaciones (rol, mensaje, session_id) VALUES ('user', ?, ?)");
if ($stmtLogUser) {
    $stmtLogUser->bind_param("ss", $userMessage, $sessionId);
    $stmtLogUser->execute();
    $stmtLogUser->close();
}

$apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key=" . GEMINI_API_KEY;

// Construir el historial para Gemini
$contents = [];
// Primero el mensaje del sistema (como instrucción de modelo)
foreach ($history as $msg) {
    if (isset($msg['text']) && isset($msg['side'])) {
        $contents[] = [
            "role" => ($msg['side'] === 'user' ? 'user' : 'model'),
            "parts" => [["text" => $msg['text']]]
        ];
    }
}

// Añadimos el mensaje actual
$currentParts = [["text" => $systemPrompt . "\n\nUsuario dice: " . $userMessage]];
if ($input['image'] ?? null) {
    $mimeType = $input['mime_type'] ?? 'image/jpeg';
    $currentParts[] = [
        "inline_data" => [
            "mime_type" => $mimeType,
            "data" => $input['image']
        ]
    ];
}

$contents[] = [
    "role" => "user",
    "parts" => $currentParts
];

$data = [
    "contents" => $contents
];

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    file_put_contents('debug_log.txt', "HTTP CODE: $httpCode\nRESPONSE: $response\n", FILE_APPEND);
    echo json_encode(['error' => 'Error de conexión', 'details' => $response]);
    exit;
}

$result = json_decode($response, true);
$botResponse = $result['candidates'][0]['content']['parts'][0]['text'] ?? 'Lo siento, no pude procesar tu mensaje.';

// Guardar respuesta del bot en logs
$stmtLogBot = $conn->prepare("INSERT INTO chatbot_conversaciones (rol, mensaje, session_id) VALUES ('model', ?, ?)");
if ($stmtLogBot) {
    $stmtLogBot->bind_param("ss", $botResponse, $sessionId);
    $stmtLogBot->execute();
    $stmtLogBot->close();
}

// --- LOGICA DE PERSISTENCIA (Guardado automático en memoria) ---
if (preg_match('/\[SAVE:(.*?)\|(.*?)\]/', $botResponse, $matches)) {
    $concepto = trim($matches[1]);
    $valor = trim($matches[2]);
    $stmtSave = $conn->prepare("INSERT INTO chatbot_memoria (concepto, valor) VALUES (?, ?) ON DUPLICATE KEY UPDATE valor = VALUES(valor)");
    if ($stmtSave) {
        $stmtSave->bind_param("ss", $concepto, $valor);
        $stmtSave->execute();
        $stmtSave->close();
    }
    // Limpiamos el comando de la respuesta final que ve el usuario
    $botResponse = str_replace($matches[0], "", $botResponse);
}

echo json_encode(['response' => trim($botResponse)]);

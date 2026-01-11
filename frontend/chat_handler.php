<?php
// backend/chat_handler.php

// 1. Ocultar errores en el navegador para que no rompan el JSON
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');

// 2. Incluir la conexión (Asegúrate de haber actualizado db/connection.php como te dije antes)
require_once __DIR__ . '/../db/connection.php';

// Verificación de seguridad: Si la conexión falló, devolver JSON válido avisando
if (!isset($pdo)) {
    echo json_encode(["choices" => [["message" => ["content" => "Error técnico: No hay conexión a la base de datos."]]]]);
    exit;
}

// 3. Recibir el mensaje del usuario
$input = json_decode(file_get_contents('php://input'), true);
$msg = mb_strtolower(trim($input['message'] ?? ''), 'UTF-8');

$respuesta = "";

// --- SECCIÓN 1: HORARIO ---
if ($msg == 'horario' || strpos($msg, 'abierto') !== false || strpos($msg, 'hora') !== false) {
    $respuesta = "Nuestro horario de atención:<br><br>";
    $respuesta .= "• Lunes a Viernes: 09:00 - 18:00 <br>";
    $respuesta .= "• Teléfono de Soporte: +34 648502176 <br>";
    $respuesta .= "• Mail: lacaffetera1994@gmail.com ";
} 

// --- SECCIÓN 2: UBICACIÓN ---
elseif ($msg == 'donde' || strpos($msg, 'ubicacion') !== false || strpos($msg, 'donde estan') !== false || strpos($msg, 'ubicación') !== false) {
    $respuesta = "¿Dónde estamos?<br><br>";
    $respuesta .= "Nos encontramos en la Avda. de la Molienda 45, Planta 3, 28005 Madrid (España)<br>";
    $respuesta .= "¡Ven a disfrutar del mejor café recién tostado!";
}

// --- SECCIÓN 3: LISTA DE PRODUCTOS ---
elseif ($msg == 'lista' || strpos($msg, 'carta') !== false || strpos($msg, 'precios') !== false) {
    try {
        // Consulta corregida para usar $pdo
        $sql = "SELECT p.nombre_cafe, v.precio 
                FROM productos p 
                INNER JOIN producto_variantes v ON p.id = v.producto_id 
                WHERE v.envase = '250g'
                ORDER BY p.nombre_cafe ASC";
        
        $stmt = $pdo->query($sql);
        
        if ($stmt && $stmt->rowCount() > 0) {
            $respuesta = "Nuestros Cafés (250g):<br><br>";
            $cafes_vistos = [];
            
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $nombre = $row['nombre_cafe'];
                // Evitamos duplicados visuales
                if (!in_array($nombre, $cafes_vistos)) {
                    $precio = number_format($row['precio'], 2);
                    $respuesta .= "• " . htmlspecialchars($nombre) . ": " . $precio . "€<br>";
                    $cafes_vistos[] = $nombre;
                }
            }
        } else {
            $respuesta = "No hay productos disponibles en este momento.";
        }
    } catch (Exception $e) {
        // En caso de error SQL, mensaje genérico
        $respuesta = "Lo siento, no puedo acceder al menú ahora mismo.";
    }
}

// --- RESPUESTA POR DEFECTO ---
else {
    $respuesta = "¡Hola! Soy tu Barista IA.<br>Escribe 'lista' para ver los precios, 'horario' o 'ubicación'.";
}

// 4. Enviar respuesta final
echo json_encode(["choices" => [["message" => ["content" => $respuesta]]]], JSON_UNESCAPED_UNICODE);

// IMPORTANTE: En PDO NO se usa $conn->close(). 
// La conexión se cierra sola al terminar el script.
?>
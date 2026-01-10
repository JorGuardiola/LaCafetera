<?php
//chat_handler.php
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');

// 1. Conexión
require_once __DIR__ . '/../db/connection.php';

if (!isset($pdo)) {
    echo json_encode(["choices" => [["message" => ["content" => "Error interno: No se pudo conectar a la base de datos."]]]]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$msg = mb_strtolower(trim($input['message'] ?? ''), 'UTF-8');

$respuesta = "";

// --- SECCIÓN: HORARIO ---
if ($msg == 'horario' || strpos($msg, 'abierto') !== false || strpos($msg, 'hora') !== false) {
    $respuesta = "Nuestro horario de atención:<br><br>";
    $respuesta .= "• Lunes a Viernes: 09:00 - 18:00 <br>";
    $respuesta .= "• Teléfono de Soporte: +34 648502176 <br>";
    $respuesta .= "• Mail: lacaffetera1994@gmail.com ";
} 

// --- SECCIÓN: UBICACIÓN ---
elseif ($msg == 'donde' || strpos($msg, 'ubicacion') !== false || strpos($msg, 'donde estan') !== false) {
    $respuesta = "¿Dónde estamos?<br><br>";
    $respuesta .= "Nos encontramos en la Avda. de la Molienda 45, Planta 3, 28005 Madrid (España)<br>";
    $respuesta .= "¡Ven a disfrutar del mejor café recién tostado!";
}

// --- SECCIÓN: LISTA  ---
elseif ($msg == 'lista' || strpos($msg, 'carta') !== false) {
    try {
        $sql = "SELECT p.nombre_cafe, v.precio 
                FROM productos p 
                INNER JOIN producto_variantes v ON p.id = v.producto_id 
                WHERE v.envase = '250g'
                ORDER BY p.nombre_cafe ASC";
        
        // Usamos $pdo en lugar de $conn
        $stmt = $pdo->query($sql);
        
        if ($stmt && $stmt->rowCount() > 0) {
            $respuesta = "Nuestros Cafés (250g):<br><br>";
            $cafes_vistos = [];
            
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $nombre = $row['nombre_cafe'];
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
        $respuesta = "Error al consultar los productos.";
    }
}

// --- RESPUESTA POR DEFECTO ---
else {
    $respuesta = "¡Hola! Soy tu Barista IA.<br>Escribe 'lista' para ver los precios, 'horario' o 'ubicación'.";
}

// 2. Respuesta final
echo json_encode(["choices" => [["message" => ["content" => $respuesta]]]], JSON_UNESCAPED_UNICODE);
$conn->close();
?>
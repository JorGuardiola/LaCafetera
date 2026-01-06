<?php
// frontend/ajax_search_bar.php
require_once __DIR__ . '/../db/connection.php';

header('Content-Type: application/json');

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

try {
    if ($q === '') {
        // CASO 1: BÚSQUEDA VACÍA (0 CARACTERES)
        // Devolvemos 5 productos aleatorios como sugerencia
        $sql = "SELECT id, nombre_cafe FROM productos WHERE disponible = 1 ORDER BY RAND() LIMIT 5";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
    } else {
        // CASO 2: BÚSQUEDA NORMAL
        // Buscamos coincidencias por nombre
        $sql = "SELECT id, nombre_cafe FROM productos WHERE nombre_cafe LIKE ? AND disponible = 1 LIMIT 5";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(["%$q%"]);
    }

    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($resultados);

} catch (Exception $e) {
    echo json_encode([]);
}
?>
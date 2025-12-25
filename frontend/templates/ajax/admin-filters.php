<?php
// frontend/templates/ajax/admin-filters.php
require_once __DIR__ . '/../../../db/connection.php';

header('Content-Type: application/json; charset=utf-8');

$molienda = trim($_GET['molienda'] ?? '');
$tueste   = trim($_GET['tueste'] ?? '');

/**
 * Devuelve valores DISTINCT aplicando filtros opcionales
 */
function getDistinct(PDO $pdo, string $field, array $extraWhere = [], array $params = []): array {
    $baseWhere = [
        "$field IS NOT NULL",
        "$field != ''"
    ];
    $where = array_merge($baseWhere, $extraWhere);

    $sql = "SELECT DISTINCT $field FROM producto_variantes WHERE " . implode(' AND ', $where) . " ORDER BY $field";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// 1) Moliendas (no dependen de nada)
$moliendas = getDistinct($pdo, 'molienda');

// 2) Tuestes (dependen de molienda si viene)
$tuesteWhere = [];
$tuesteParams = [];
if ($molienda !== '') {
    $tuesteWhere[] = "molienda = ?";
    $tuesteParams[] = $molienda;
}
$tuestes = getDistinct($pdo, 'tueste', $tuesteWhere, $tuesteParams);

// 3) Envases (dependen de molienda y tueste si vienen)
$envaseWhere = [];
$envaseParams = [];
if ($molienda !== '') {
    $envaseWhere[] = "molienda = ?";
    $envaseParams[] = $molienda;
}
if ($tueste !== '') {
    $envaseWhere[] = "tueste = ?";
    $envaseParams[] = $tueste;
}
$envases = getDistinct($pdo, 'envase', $envaseWhere, $envaseParams);

echo json_encode([
    'moliendas' => $moliendas,
    'tuestes'   => $tuestes,
    'envases'   => $envases
]);

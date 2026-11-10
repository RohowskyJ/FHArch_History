<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once dirname(__DIR__, 4) . '/vendor/autoload.php';

use FWHist\Auth\AccessGuard;
use FWHist\Database\DB_GenericLog;

$DBD = new DB_GenericLog();
$guard = AccessGuard::fromSession($DBD);
if (!$guard->canWriteMemberData()) {
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Für diese Aktion besteht keine Berechtigung.'], JSON_UNESCAPED_UNICODE);
    exit;
}
header('Content-Type: application/json');

$pdo = $DBD->getPDO();

$input = json_decode(file_get_contents('php://input'), true);

if (!AccessGuard::checkCsrf($input['csrf'] ?? null)) {
    http_response_code(403);
    echo json_encode(['error' => AccessGuard::CSRF_MESSAGE], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!isset($input['id'], $input['field'], $input['value'])) {
    echo json_encode(['success' => false, 'message' => 'Ungültige Eingabedaten']);
    exit;
}

$id = (int)$input['id'];
$field = $input['field'];
$value = $input['value'];

$allowedFields = [
    'mi_name', 'mi_email', 'mi_gebtag', 'mi_mtyp', 'mi_einversterkl'
];

if (!in_array($field, $allowedFields)) {
    echo json_encode(['success' => false, 'message' => 'Feld nicht erlaubt']);
    exit;
}

// Serverseitige Validierung
if ($field === 'mi_email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Ungültige E-Mail-Adresse']);
    exit;
}

if ($field === 'mi_gebtag' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
    echo json_encode(['success' => false, 'message' => 'Ungültiges Datum (YYYY-MM-DD)']);
    exit;
}

if ($field === 'mi_mtyp' && !in_array($value, ['A','P','E'])) {
    echo json_encode(['success' => false, 'message' => 'Ungültiger Mitgliedstyp']);
    exit;
}

if ($field === 'mi_einversterkl' && !in_array($value, ['Y','N'])) {
    echo json_encode(['success' => false, 'message' => 'Ungültiger Wert für Einverständniserklärung']);
    exit;
}

$sql = "UPDATE fv_mitglieder SET `$field` = :value WHERE mi_id = :id";
$stmt = $pdo->prepare($sql);

try {
    $stmt->execute([':value' => $value, ':id' => $id]);
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Fehler beim Speichern']);
}
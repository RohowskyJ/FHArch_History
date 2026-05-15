<?php
header('Content-Type: application/json');

$host = 'localhost';
$db   = 'fharch_comm';
$user = 'root';
$pass = 'b1teller';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Datenbankverbindung fehlgeschlagen']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

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
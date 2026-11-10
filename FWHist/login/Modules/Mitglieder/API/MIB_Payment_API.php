<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once dirname(__DIR__, 4) . '/vendor/autoload.php';

header('Content-Type: application/json; charset=utf-8');

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input) || !isset($input['mi_id'], $input['action'], $input['field'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Ungültige Anfrage']);
    exit;
}

$db = new \FWHist\Database\DB_GenericLog();
$guard = \FWHist\Auth\AccessGuard::fromSession($db);
if (!$guard->canWriteMemberPaymentData()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Für diese Aktion besteht keine Berechtigung.']);
    exit;
}

if (!\FWHist\Auth\AccessGuard::checkCsrf($input['csrf'] ?? null)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => \FWHist\Auth\AccessGuard::CSRF_MESSAGE], JSON_UNESCAPED_UNICODE);
    exit;
}

$mi_id = (int)$input['mi_id'];
$action = (string)$input['action'];
$field = (string)$input['field'];
$changed_id = $guard->userId();
$year = isset($input['year']) ? (int)$input['year'] : null;

$allowedActions = ['pay', 'cancel'];
if (!in_array($action, $allowedActions, true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Ungültige Aktion']);
    exit;
}

if (!preg_match('/^(?:M|A|MA)_(?:1|0|p)$/', $field)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Ungültiges Feld']);
    exit;
}

try {
        $pdo = $db->getPDO();
        
        $currentDate = date('Y-m-d');
        $currentYear = date('Y');
        $nextYear = $currentYear + 1;
        
        # file_put_contents($debug_log_file, __LINE__ . " vor Gruppe if action == 'pay' \n", FILE_APPEND);
        
        if ($action === 'pay') {
            if (!$year || !$field) {
                echo json_encode(['success' => false, 'message' => 'Jahr oder Feld fehlt']);
                exit;
            }
            
            // Prüfen, ob bereits ein Eintrag für mi_id, Jahr und Feld existiert
            // Wir nehmen an, dass mb_bez_mb_bis für M_* und mb_bez_abo_bis für A_* steht
            // Feldzuordnung: M = Mitgliedsbeitrag, A = Abo, MA = beide
            if (strpos($field, 'MA_') === 0) {
                $isM = true;
                $isA = true;
            } else {
                $isM = strpos($field, 'M') === 0;
                $isA = strpos($field, 'A') === 0;
            }
            
            // Suche nach bestehendem Eintrag für dieses Jahr und Mitglied
            $sqlCheck = "SELECT * FROM mi_bezahlt WHERE mi_id = ? AND mb_cancel IS NULL ORDER BY mb_changed_at DESC";
            $stmtCheck = $pdo->prepare($sqlCheck);
            $stmtCheck->execute([$mi_id]);
            $entries = $stmtCheck->fetchAll(PDO::FETCH_ASSOC);
            
            $foundEntry = null;
            foreach ($entries as $entry) {
                if (($isM && $entry['mb_bez_mb_bis'] == $year) || ($isA && $entry['mb_bez_abo_bis'] == $year)) {
                    $foundEntry = $entry;
                    break;
                }
            }
            
            // Neue Werte vorbereiten
            $new_mb_bis = $isM ? $year : null;
            $new_abo_bis = $isA ? $year : null;
            $new_mb_datum = $isM ? $currentDate : null;
            $new_abo_datum = $isA ? $currentDate : null;
            
            if ($foundEntry) {
                
                # file_put_contents($debug_log_file, date('Y-m-d H:i:s') . " - Action: $action, Insert oder Update? " . __LINE__ , FILE_APPEND);
                              
                // Update vorhandenen Eintrag, vorherige Werte sichern falls noch nicht gesetzt
                $prev_mb_bis = $foundEntry['mb_prev_mb_bis'] ?? $foundEntry['mb_bez_mb_bis'];
                $prev_abo_bis = $foundEntry['mb_prev_abo_bis'] ?? $foundEntry['mb_bez_abo_bis'];
                $prev_mb_datum = $foundEntry['mb_prev_mb_datum'] ?? $foundEntry['mb_bez_mb_datum'];
                $prev_abo_datum = $foundEntry['mb_prev_abo_datum'] ?? $foundEntry['mb_bez_abo_datum'];
                
                $sqlUpdate = "UPDATE mi_bezahlt SET
                mb_bez_mb_bis = IF(? IS NOT NULL, ?, mb_bez_mb_bis),
                mb_bez_abo_bis = IF(? IS NOT NULL, ?, mb_bez_abo_bis),
                mb_bez_mb_datum = IF(? IS NOT NULL, ?, mb_bez_mb_datum),
                mb_bez_abo_datum = IF(? IS NOT NULL, ?, mb_bez_abo_datum),
                mb_prev_mb_bis = ?,
                mb_prev_abo_bis = ?,
                mb_prev_mb_datum = ?,
                mb_prev_abo_datum = ?,
                mb_cancel = NULL,
                mb_changed_id = ?,
                mb_changed_at = NOW()
                WHERE mb_id = ?";
                
                $stmtUpdate = $pdo->prepare($sqlUpdate);
                $stmtUpdate->execute([
                    $new_mb_bis, $new_mb_bis,
                    $new_abo_bis, $new_abo_bis,
                    $new_mb_datum, $new_mb_datum,
                    $new_abo_datum, $new_abo_datum,
                    $prev_mb_bis,
                    $prev_abo_bis,
                    $prev_mb_datum,
                    $prev_abo_datum,
                    $changed_id,
                    $foundEntry['mb_id']
                ]);
            } else {
                // Insert neuen Eintrag
                $stmtInsert = $pdo->prepare("
                INSERT INTO mi_bezahlt (
                    mi_id, mb_bez_mb_bis, mb_bez_abo_bis, mb_bez_mb_datum, mb_bez_abo_datum,
                    mb_prev_mb_bis, mb_prev_abo_bis, mb_prev_mb_datum, mb_prev_abo_datum,
                    mb_cancel, mb_changed_id, mb_changed_at
                    ) VALUES (?, ?, ?, ?, ?, NULL, NULL, NULL, NULL, NULL, ?, NOW())
                ");
 
                $success = $stmtInsert->execute([
                    $mi_id,
                    $new_mb_bis,
                    $new_abo_bis,
                    $new_mb_datum,
                    $new_abo_datum,
                    $changed_id
                ]);
                
                # file_put_contents($debug_log_file, "170 Insert success: " . ($success ? "true" : "false") . "\n", FILE_APPEND);
                
                if (!$success) {
                    $errorInfo = $stmtInsert->errorInfo();
                    file_put_contents($debug_log_file, "Insert error: " . json_encode($errorInfo) . "\n", FILE_APPEND);
                }
            }
            
            // nach dem Schreiben: komplette Zeile aus dem Repository holen für diese mi_id
            $updatedRow = null;
            try {
                $list = new \FWHist\Modules\Mitglieder\Lists\MIBList();
                $allRows = $list->getData($pdo);
                foreach ($allRows as $r) {
                    if (isset($r['mi_id']) && intval($r['mi_id']) === $mi_id) {
                        $updatedRow = $r;
                        break;
                    }
                }
            } catch (Exception $e) {
                error_log('Konnte aktualisierte Beitragszeile nicht laden: ' . $e->getMessage());
            }

            $response = ['success' => true, 'message' => 'Zahlung verbucht'];
            if ($updatedRow !== null) {
                $updatedRow['justUpdated'] = true;
                $updatedRow['lastPaymentField'] = $field;  // store which field was just paid
                $response['updatedRow'] = $updatedRow;
            }
            echo json_encode($response);
            exit;
        }
        
        if ($action === 'cancel') {
            // Storno: Letzten nicht stornierten Eintrag für mi_id und Jahr (M_, A_ oder MA_) finden
            if (!$year || !$field) {
                echo json_encode(['success' => false, 'message' => 'Jahr oder Feld fehlt']);
                exit;
            }
            
            // direktes Erkennen ob Kombinationsfeld (MA_)
            if (strpos($field, 'MA_') === 0) {
                $isM = true;
                $isA = true;
            } else {
                $isM = strpos($field, 'M') === 0;
                $isA = strpos($field, 'A') === 0;
            }
            
            $sqlCancel = "SELECT * FROM mi_bezahlt WHERE mi_id = ? AND mb_cancel IS NULL ORDER BY mb_changed_at DESC";
            $stmtCancel = $pdo->prepare($sqlCancel);
            $stmtCancel->execute([$mi_id]);
            $entries = $stmtCancel->fetchAll(PDO::FETCH_ASSOC);
            
            $entryToCancel = null;
            foreach ($entries as $entry) {
                if (($isM && $entry['mb_bez_mb_bis'] == $year) || ($isA && $entry['mb_bez_abo_bis'] == $year)) {
                    $entryToCancel = $entry;
                    break;
                }
            }
            
            if (!$entryToCancel) {
                echo json_encode(['success' => false, 'message' => 'Keine Zahlung zum Stornieren gefunden']);
                exit;
            }
            
            // Alte Werte aus mb_prev_* Feldern holen
            $restore_mb_bis = $entryToCancel['mb_prev_mb_bis'];
            $restore_abo_bis = $entryToCancel['mb_prev_abo_bis'];
            $restore_mb_datum = $entryToCancel['mb_prev_mb_datum'];
            $restore_abo_datum = $entryToCancel['mb_prev_abo_datum'];
            
            // Update mit Wiederherstellung der alten Werte und Storno markieren
            $sqlUpdateCancel = "UPDATE mi_bezahlt SET
            mb_bez_mb_bis = ?,
            mb_bez_abo_bis = ?,
            mb_bez_mb_datum = ?,
            mb_bez_abo_datum = ?,
            mb_cancel = CURDATE(),
            mb_changed_id = ?,
            mb_changed_at = NOW()
            WHERE mb_id = ?";
            
            $stmtUpdateCancel = $pdo->prepare($sqlUpdateCancel);
            $stmtUpdateCancel->execute([
                $restore_mb_bis,
                $restore_abo_bis,
                $restore_mb_datum,
                $restore_abo_datum,
                $changed_id,
                $entryToCancel['mb_id']
            ]);
            
            // nach Storno ebenfalls aktualisierte Zeile aus Repository holen
            $updatedRow = null;
            try {
                $list = new \FWHist\Modules\Mitglieder\Lists\MIBList();
                $allRows = $list->getData($pdo);
                foreach ($allRows as $r) {
                    if (isset($r['mi_id']) && intval($r['mi_id']) === $mi_id) {
                        $updatedRow = $r;
                        break;
                    }
                }
            } catch (Exception $e) {
                error_log('Konnte aktualisierte Beitragszeile nicht laden: ' . $e->getMessage());
            }

            $response = ['success' => true, 'message' => 'Zahlung storniert und alte Werte wiederhergestellt'];
            if ($updatedRow !== null) {
                $updatedRow['justUpdated'] = false;
                $updatedRow['lastPaymentField'] = '';
                $response['updatedRow'] = $updatedRow;
            }
            echo json_encode($response);
            exit;
        }
        
        echo json_encode(['success' => false, 'message' => 'Ungültige Aktion']);
        exit;
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Datenbankfehler: ' . $e->getMessage()]);
        exit;
    }

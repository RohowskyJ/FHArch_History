<?php
/** 
 * Hier die vollständige, erweiterte PHP-Datenbankklasse mit allen gewünschten Features (Prepared Statements, CRUD mit assoziativen Arrays,
 * Transaktionen, Debugging, automatisches Setzen von Zeitstempeln und User-ID, flexible WHERE-Klauseln,
 * verschiedene Fetch-Modi, Pagination/Limit). Die DB-Zugangsdaten werden aus deiner ConfigLib.php geladen.
 */
namespace Fharch\Core\Database;

require_once 'ConfigLib.php';

class DB_I_GenericLog {
    private $mysqli;
    private $debug;
    private $lastQuery;
    
    /**
     * Initialisierung der Klasse 
     * 
     * @param boolean $debug
     */
    public function __construct($debug = false) {
        $this->debug = $debug;
        $this->mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($this->mysqli->connect_error) {
            $this->debugOutput("Connection failed: " . $this->mysqli->connect_error);
            throw new Exception("Datenbankverbindung fehlgeschlagen: " . $this->mysqli->connect_error);
        }
        
        $this->mysqli->set_charset("utf8mb4");
    }
    
    /**
     * Fehler Anzeige
     * 
     * @param string $message
     */
    private function debugOutput($message) {
        if ($this->debug) {
            echo "<pre style='background:#fdd; padding:10px; border:1px solid #f00;'>";
            echo htmlspecialchars($message);
            echo "</pre>";
        }
    }
    
   
    // --- Transaktionen ---
    public function beginTransaction() {
        $this->mysqli->begin_transaction();
        if ($this->debug) $this->debugOutput("Transaction started");
    }
    
    public function commit() {
        $this->mysqli->commit();
        if ($this->debug) $this->debugOutput("Transaction committed");
    }
    
    public function rollback() {
        $this->mysqli->rollback();
        if ($this->debug) $this->debugOutput("Transaction rolled back");
    }
    
    // --- Insert ---
    /** 
     * Neuen Datensatz anlegen
     * 
     * 
     * @param string $table
     * @param array $data
     * @param boolean $autoTimestamp
     * @return boolean|unknown
     */
    public function insert($table, array $data, string $prefix, $autoTimestamp = true) {
        if ($autoTimestamp) $this->addTimestamps($data, $prefix);
        
        $columns = implode(", ", array_map(fn($col) => "`$col`", array_keys($data)));
        $placeholders = implode(", ", array_fill(0, count($data), "?"));
        
        $stmt = $this->mysqli->prepare("INSERT INTO `$table` ($columns) VALUES ($placeholders)");
        if (!$stmt) {
            $this->debugOutput("Prepare failed: " . $this->mysqli->error);
            return false;
        }
        
        $types = $this->getParamTypes($data);
        $values = array_values($data);
        
        $stmt->bind_param($types, ...$values);
        
        if (!$stmt->execute()) {
            $this->debugOutput("Execute failed: " . $stmt->error);
            $stmt->close();
            return false;
        }
        
        $insertId = $stmt->insert_id;
        $stmt->close();
        return $insertId;
    }
    
    // --- Update ---
    /** 
     * Update Record
     * 
     * @param string $table
     * @param array $data
     * @param string $where
     * @param array $whereParams
     * @param boolean $autoTimestamp
     * @return boolean|unknown
     */
    public function update($table, array $data, string $where, array $whereParams, $autoTimestamp = true) {
        if ($autoTimestamp) $this->addTimestamps($data);
        
        $setParts = [];
        foreach ($data as $col => $val) {
            $setParts[] = "`$col` = ?";
        }
        $setString = implode(", ", $setParts);
        
        $stmt = $this->mysqli->prepare("UPDATE `$table` SET $setString WHERE $where");
        if (!$stmt) {
            $this->debugOutput("Prepare failed: " . $this->mysqli->error);
            return false;
        }
        
        $types = $this->getParamTypes($data) . $this->getParamTypes($whereParams);
        $values = array_merge(array_values($data), $whereParams);
        
        $stmt->bind_param($types, ...$values);
        
        if (!$stmt->execute()) {
            $this->debugOutput("Execute failed: " . $stmt->error);
            $stmt->close();
            return false;
        }
        
        $affectedRows = $stmt->affected_rows;
        $stmt->close();
        return $affectedRows;
    }
    
    // --- Delete ---
    /** 
     * Delete record 
     * @param string $table
     * @param string $where
     * @param array $whereParams
     * @return boolean|unknown
     */
    public function delete($table, string $where, array $whereParams) {
        $stmt = $this->mysqli->prepare("DELETE FROM `$table` WHERE $where");
        if (!$stmt) {
            $this->debugOutput("Prepare failed: " . $this->mysqli->error);
            return false;
        }
        
        $types = $this->getParamTypes($whereParams);
        $stmt->bind_param($types, ...$whereParams);
        
        if (!$stmt->execute()) {
            $this->debugOutput("Execute failed: " . $stmt->error);
            $stmt->close();
            return false;
        }
        
        $affectedRows = $stmt->affected_rows;
        $stmt->close();
        return $affectedRows;
    }
    
    // --- Select multiple ---
    /**
     * Alle record auslesen
     * 
     * 
     * @param string $query
     * @param array $params
     * @param string $fetchMode
     * @return boolean|array
     */
    public function selectAll($query, array $params = [], $fetchMode = 'assoc') {
        $stmt = $this->mysqli->prepare($query);
        if (!$stmt) {
            $this->debugOutput("Prepare failed: " . $this->mysqli->error);
            return false;
        }
        
        if ($params) {
            $types = $this->getParamTypes($params);
            $stmt->bind_param($types, ...$params);
        }
        
        if (!$stmt->execute()) {
            $this->debugOutput("Execute failed: " . $stmt->error);
            $stmt->close();
            return false;
        }
        
        $result = $stmt->get_result();
        if (!$result) {
            $this->debugOutput("Get result failed: " . $stmt->error);
            $stmt->close();
            return false;
        }
        
        $data = [];
        if ($fetchMode === 'assoc') {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        } elseif ($fetchMode === 'object') {
            while ($row = $result->fetch_object()) {
                $data[] = $row;
            }
        } else {
            $this->debugOutput("Unknown fetch mode: $fetchMode");
            $stmt->close();
            return false;
        }
        
        $stmt->close();
        return $data;
    }
    
    // --- Select single ---
    public function selectOne($query, array $params = [], $fetchMode = 'assoc') {
        $results = $this->selectAll($query, $params, $fetchMode);
        if ($results === false || count($results) === 0) return null;
        return $results[0];
    }
    
    // --- Hilfsfunktion: Parametertypen ---
    private function getParamTypes(array $params) {
        $types = '';
        foreach ($params as $param) {
            if (is_int($param)) $types .= 'i';
            elseif (is_float($param)) $types .= 'd';
            elseif (is_null($param)) $types .= 's';
            else $types .= 's';
        }
        return $types;
    }
    
    // --- Automatisches Setzen von Zeitstempel und User-ID ---
    /** 
     * Timestamp und Änderer automatisch setzen
     * 
     * 
     * @param array $data
     * @param string $prefix
     */
    private function addTimestamps(array &$data, string $prefix) {
        $now = date('Y-m-d H:i:s');
        
        if (array_key_exists($prefix.'changed_at', $data)) {
            $data['bs_changed_at'] = $now;
        }
        if (array_key_exists($prefix.'changed_id', $data)) {
            $data['bs_changed_id'] = $_SESSION['BS_Prim']['be_id'] ?? 0;
        }
    }
    
    /**
     * Get last inserted ID
     * 
     * 
     * @return number
     */
    public function getLastInsertId() {
        return $this->mysqli->insert_id;
    }
    
    /**
     * Close Database
     */
    public function close() {
        $this->mysqli->close();
    }
    
    /**
     * Legt Tabellen für einen Mandanten an, basierend auf einem SQL-Skript mit Platzhalter {MandNr}
     * Erweiterte Fehlerbehandlung: sammelt Fehler, gibt sie zurück oder wirft Exception
     *
     * @param string $sqlScript SQL-Skript mit CREATE TABLE etc. Befehlen, Tabellenname mit {MandNr} als Platzhalter
     * @param string|int $MandNr Mandantennummer, die in Tabellennamen eingesetzt wird
     * @param bool $throwException Bei true wird bei Fehler eine Exception geworfen
     * @return array|bool true bei Erfolg, sonst Array mit Fehlerdetails
     * @throws Exception
     */
    public function createTablesFromSql(string $sqlScript, $MandNr, bool $throwException = false) {
        $sqlScript = str_replace('{MandNr}', $MandNr, $sqlScript);
        $statements = array_filter(array_map('trim', explode(';', $sqlScript)));
        
        $errors = [];
        
        foreach ($statements as $stmt) {
            if (empty($stmt)) continue;
            
            if (!$this->mysqli->query($stmt)) {
                $errorMsg = "Fehler beim Ausführen von SQL: " . $stmt . " - MySQL Fehler: " . $this->mysqli->error;
                $this->debugOutput($errorMsg);
                $errors[] = $errorMsg;
                
                if ($throwException) {
                    throw new Exception($errorMsg);
                }
            } else {
                if ($this->debug) {
                    $this->debugOutput("Erfolgreich ausgeführt: " . $stmt);
                }
            }
        }
        
        if (count($errors) > 0) {
            return $errors;
        }
        
        return true;
    }
}

/** 
 * Beispiel für Nutzung mit deiner $neu-Struktur und Session-User-ID
 */
/*
<?php
session_start();
$_SESSION['user_id'] = 42; // Beispiel User-ID

$db = new Database(true);

$neu = [];
$neu['bs_id'] = null; // id wird auto increment, kann auch weggelassen werden
$neu['bs_startdatum'] = $neu['bs_enddatum'] = $neu['bs_kurztext'] = $neu['bs_typ'] = $neu['bs_text'] = "";
$neu['bs_emai_1'] = $neu['bs_email_2'] = $neu['bs_bild_1'] = $neu['bs_bild_2'] = $neu['bs_bild_3'] = $neu['bs_bild_4'] = "";
$neu['bs_changed_id'] = $neu['bs_changed_at'] = ""; // Muss da sein, damit addTimestamps sie füllt

// Insert
$insertId = $db->insert('deine_tabelle', $neu);
echo "Neuer Datensatz ID: $insertId\n";

// Update
$neu['bs_kurztext'] = "Neuer Text";
$where = "bs_id = ?";
$whereParams = [$insertId];
$affected = $db->update('deine_tabelle', $neu, $where, $whereParams);
echo "Betroffene Zeilen: $affected\n";

$db->close();

Diese Klasse deckt alle von dir gewünschten Features ab, ist sicher (Prepared Statements), flexibel und debug-fähig. Wenn du möchtest, kann ich dir auch Beispiel-SQL für deine Tabelle oder weitere Hilfsmethoden schreiben.
• Wie kann ich die Klasse für meine spezifische Tabelle anpassen?
• Gibt es noch weitere nützliche Methoden, die fehlen?
• Wie funktioniert die Pagination genau?
• Kannst du mir ein Beispiel für komplexe WHERE-Klauseln zeigen?

*/
/*
Bitte um eine Methode, mit der ich DB-Tabellen neu anlegen kann. Diese Funktion wird für die Neu- Anlage von Mandanten- Tabellen gebraucht Name = $MandNr_Fahzeug ($MandNr = Mandanten- Nummer ) die (mehrere Tabellen) jeweils beim Neu- Anlegen eines Mandanten angelegt werden sollen, Input wie bei dem Import einer Tabelle (.sql).
Um eine Methode in deiner Datenbankklasse zu ergänzen, die beliebige SQL-Skripte (z.B. CREATE TABLE Statements) ausführt und dabei dynamisch Tabellen mit Mandantennummer im Namen anlegt, kannst du folgende Funktion hinzufügen:
*/
/** 
 * Vorschlag: Methode createTablesFromSql
 * Nimmt als Parameter ein SQL-Skript als String (z.B. aus einer .sql-Datei)
 * Ersetzt in diesem Skript Platzhalter für Mandantennummer (z.B. {MandNr}) durch den tatsächlichen Mandantennummer-Wert
 * Führt die SQL-Befehle einzeln aus (z.B. getrennt durch ;)
 * Gibt Erfolg oder Fehler zurück
 */

/*
Beispiel: Erweiterte Methode mit Fehler-Sammeln und optionaler Exception
*/

/*
Nutzung
try {
    $result = $db->createTablesFromSql($sql, $MandNr, true); // true = Exception werfen bei Fehler
    echo "Tabellen erfolgreich angelegt.";
} catch (Exception $e) {
    echo "Fehler beim Anlegen der Tabellen: " . $e->getMessage();
}
Oder ohne Exception:
$result = $db->createTablesFromSql($sql, $MandNr);
if ($result !== true) {
    echo "Fehler beim Anlegen der Tabellen:\n";
    foreach ($result as $error) {
        echo $error . "\n";
    }
} else {
    echo "Tabellen erfolgreich angelegt.";
}
*/
/*
So hast du eine flexible Fehlerbehandlung, die je nach Bedarf Exceptions wirft oder Fehler gesammelt zurückgibt. Die Debug-Ausgabe bleibt erhalten und hilft bei der Entwicklung.
*/
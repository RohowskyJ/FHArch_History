<?php
namespace Fharch\Core\Modules\Mitglieder\API;

use \PDO;

// Fehleranzeige und Logging aktivieren (nur für Debug, im Produktivbetrieb aus)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/MIB_Member_Repo_php-error.log.txt');

// Shutdown-Funktion direkt am Anfang registrieren
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null) {
        $message = "Shutdown error detected:\n" . print_r($error, true);
        error_log($message);
        file_put_contents(__DIR__ . '/MIB_MemRepository_fatal_error.log', $message, FILE_APPEND);
    }
});

/**
 * Auslesen der Bezahl- Daten aller Mitglieder
 * 
 * @author josef
 *
 */
    class MIB_MemberRepository {
        private \PDO $pdo;
        protected static string $logFile = 'MIB_MemberRepository_debug.log.txt';
        
        public function __construct(\PDO $pdo) {
            $this->pdo = $pdo;
        }
        
        /**
         * Holt Mitglieder-Daten basierend auf dem Listentyp und optionalen Suchparametern
         * Vereinfachte Version mit Debug-Logging
         *
         * @param string $listType
         * @param string|null $search
         * @return array
         */
        public function getMembers(string $listType, ?string $search = null): array {
            $rows = [];
            $params = [];
            $where = [];
            
            // Debug: Log Start der Methode
            $this->log("getMembers called with listType='$listType', search='" . ($search ?? '') . "'");
            
            try {
                // Vereinfachte WHERE-Bedingungen, keine Jahresvergleiche
                switch ($listType) {
                    case "Alle":
                        $where[] = "(mi_austrdat IS NULL ) AND (mi_sterbdat IS NULL )";
                        $where[] = "mi_mtyp <> 'OE'";
                        break;
                    case "offen":
                        // Beispielhafte einfache Bedingung, anpassen nach Bedarf
                        $where[] = "(mi_austrdat IS NULL) AND (mi_sterbdat IS NULL )";
                        $where[] = "mi_mtyp <> 'OE'";
                        break;
                    case "bezahlt":
                        $where[] = "(mi_austrdat IS NULL ) AND (mi_sterbdat IS NULL )";
                        $where[] = "mi_mtyp <> 'OE'";
                        break;
                    case "EM":
                        $where[] = "(mi_austrdat IS NULL ) AND (mi_sterbdat IS NULL )";
                        $where[] = "(mi_mtyp = 'OE' OR mi_mtyp = 'EM')";
                        break;
                    default:
                        // Keine spezielle Bedingung
                        break;
                }
                
                if ($search !== null && trim($search) !== '') {
                    $where[] = "mi_name LIKE :search";
                    $params[':search'] = '%' . $search . '%';
                }
                
                $sql = "SELECT * FROM fv_mitglieder";
                if (count($where) > 0) {
                    $sql .= " WHERE " . implode(' AND ', $where);
                }
                $sql .= " ORDER BY mi_name, mi_vname ASC";
                
                $this->log("SQL: $sql");
                $this->log("Params: " . json_encode($params));
                
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($params);
                
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                $this->log("Rows fetched: " . count($rows));
                
                // Daten anpassen
                foreach ($rows as &$row) {
                    $this->modifyRow($row, $listType);
                }
                
            } catch (\PDOException $e) {
                $this->log("PDOException: " . $e->getMessage());
            } catch (\Throwable $e) {
                $this->log("Throwable: " . $e->getMessage());
            }
            
            return $rows;
        }
        
        /**
         * Beispielhafte Modifikation der Daten vor Ausgabe
         */
        protected function modifyRow(array &$row, string $tabTyp): bool {
            // Beispiel: Name zusammenfassen
            $vorname = trim($row['mi_vname'] ?? '');
            $nachname = trim($row['mi_name'] ?? '');
            $titel = trim(($row['mi_titel'] ?? '') . ' ' . ($row['mi_n_titel'] ?? ''));
            $titel = trim($titel);
            
            $name = $nachname;
            if ($vorname !== '') {
                $name .= ', ' . $vorname;
            }
            if ($titel !== '') {
                $name .= ' (' . $titel . ')';
            }
            $row['mi_name'] = $name;
            
            // Adresse zusammenfassen
            $adresse = trim($row['mi_plz'] ?? '') . ' ' . trim($row['mi_ort'] ?? '');
            if (!empty($row['mi_anschr'])) {
                $adresse .= ', ' . trim($row['mi_anschr']);
            }
            $row['mi_anschr'] = $adresse;
            
            return true;
        }
        
        /**
         * Einfaches Logging in Datei
         */
        protected function log(string $message): void {
            $timestamp = date('Y-m-d H:i:s');
            $entry = "[$timestamp] $message" . PHP_EOL;
            file_put_contents(__DIR__ . '/' . self::$logFile, $entry, FILE_APPEND);
        }
    }
    
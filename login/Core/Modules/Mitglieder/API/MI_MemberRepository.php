<?php
namespace Fharch\Core\Modules\Mitglieder\API;

use \PDO;

// Shutdown-Funktion direkt am Anfang registrieren
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null) {
        $message = "Shutdown error detected:\n" . print_r($error, true);
        error_log($message);
        // Optional: auch in eine separate Datei schreiben
        file_put_contents(__DIR__ . '/fatal_error.log', $message, FILE_APPEND);
    }
});
    
/**
 * Liest Daten aus der Mitgliederdatei aus, Auswahl entsprechend der Listentype (alle, nur aktive, Adressliste, ..)
 * @author josef
 *
 */
class MI_MemberRepository {
    
    protected static string $logFile = 'MI_MemberRepository_debug.log.txt';
    
    // Hier die Eigenschaft deklarieren:
    protected \PDO $pdo;
    
    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Holt Mitglieder-Daten basierend auf dem Listentyp und optionalen Suchparametern
     * @param string $listType z.B. 'Alle', 'Mitgl', 'BezL', ...
     * @param string|null $search optionaler Suchstring
     * @return array
     */
    public function getMembers(string $listType, ?string $search = null): array {
        $sql = "SELECT * FROM fv_mitglieder ";
        $where = [];
        $params = [];
        
        switch ($listType) {
            case 'Adrlist':
            case 'BezL':
            case 'Mitgl':
                $where[] = "mi_austrdat IS NULL AND mi_sterbdat IS NULL";
                $orderBy = "ORDER BY mi_name";
                break;
            case 'nMitgl':
                $where[] = "mi_austrdat IS NOT NULL OR mi_sterbdat IS NOT NULL";
                $orderBy = "ORDER BY mi_name";
                break;
            case 'Alle':
            default:
                # $where[] = "mi_name != ''";
                $orderBy = "ORDER BY mi_id";
        }
        
        if ($search !== null && trim($search) !== '') {
            $where[] = "mi_name LIKE :search";
            $params[':search'] = '%' . $search . '%';
        }
        
        if (count($where) > 0) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        $sql .= " $orderBy";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        $rows = $stmt->fetchAll();
        
        // Daten vor der Rückgabe anpassen
        foreach ($rows as &$row) {
            $this->modifyRow($row, $listType);
        }
        
        return $rows;
    }
    
    /**
     * Änderung von Feldinhalten für die Anzeige
     * 
     * @param array $row
     * @param string $tabTyp
     * @return boolean
     */
    protected function modifyRow(array &$row, $tabTyp)
    {
        // $json = json_encode($row);
        // $this->log("modifyRow wurde aufgerufen, row $json");
        
        $mi_id = $row['mi_id'] ?? 0;
        $row['action'] = "<a href='MitglEdit.php?ID={$mi_id}'>Edit</a>";
        
        if (!empty($row['mi_gebtag']) && $row['mi_gebtag'] != '0000-00-00') {
            $geburtsjahr = (int)substr($row['mi_gebtag'], 0, 4);
            $aktuellesJahr = (int)date('Y');
            $alter = $aktuellesJahr - $geburtsjahr;
            $color = ($alter % 5 === 0) ? 'red' : 'black';
            $row['mi_gebtag'] .= " - <span style='color: {$color};'>{$alter}</span>";
            # $row['mi_gebtag'] = $row['mi_gebtag'] . " - " . $alter;
        }
        
        // Beispiel: Vorname, Nachname und Titel in einer Spalte "Name" zusammenfassen
        $vorname = trim($row['mi_vname'] ?? '');
        $nachname = trim($row['mi_name'] ?? '');
        $titel = trim($row['mi_titel'] ?? '') . ' ' . trim($row['mi_n_titel'] ?? '');
        $titel = trim($titel);
        
        
        switch ($tabTyp) {
            case "Alle" :
                $zusammenfassungName = $nachname;
                if ($vorname !== '') {
                    $zusammenfassungName .= ', ' . $vorname;
                }
                if ($titel !== '') {
                    $zusammenfassungName .= ' (' . $titel . ')';
                }
                $row['mi_name'] = $zusammenfassungName;
                
                // Beispiel: Adresse aus mehreren Feldern zusammenfassen
                $adresse = trim($row['mi_plz'] ?? '') . ' ' . trim($row['mi_ort'] ?? '');
                if (!empty($row['mi_anschr'])) {
                    $adresse .= ', ' . trim($row['mi_anschr']);
                }
                $row['mi_anschr'] = $adresse;
                
                break;
            case "Mitgl" :
                $zusammenfassungName = $nachname;
                if ($vorname !== '') {
                    $zusammenfassungName .= ', ' . $vorname;
                }
                if ($titel !== '') {
                    $zusammenfassungName .= ' (' . $titel . ')';
                }
                $row['mi_name'] = $zusammenfassungName;
                
                // Beispiel: Adresse aus mehreren Feldern zusammenfassen
                $adresse = trim($row['mi_plz'] ?? '') . ' ' . trim($row['mi_ort'] ?? '');
                if (!empty($row['mi_anschr'])) {
                    $adresse .= ', ' . trim($row['mi_anschr']);
                }
                $row['mi_anschr'] = $adresse;
                
                break;
            case "nMitgl" :
                $zusammenfassungName = $nachname;
                if ($vorname !== '') {
                    $zusammenfassungName .= ', ' . $vorname;
                }
                if ($titel !== '') {
                    $zusammenfassungName .= ' (' . $titel . ')';
                }
                $row['mi_name'] = $zusammenfassungName;
                
                // Beispiel: Adresse aus mehreren Feldern zusammenfassen
                $adresse = trim($row['mi_plz'] ?? '') . ' ' . trim($row['mi_ort'] ?? '');
                if (!empty($row['mi_anschr'])) {
                    $adresse .= ', ' . trim($row['mi_anschr']);
                }
                $row['mi_anschr'] = $adresse;
                
                break;
            case "Adrlist" :
                $zusammenfassungName = $nachname;
                if ($vorname !== '') {
                    $zusammenfassungName .= ', ' . $vorname;
                }
                if ($titel !== '') {
                    $zusammenfassungName .= ' (' . $titel . ')';
                }
                $row['mi_name'] = $zusammenfassungName;
                
                // Beispiel: Adresse aus mehreren Feldern zusammenfassen
                $adresse = trim($row['mi_plz'] ?? '') . ' ' . trim($row['mi_ort'] ?? '');
                if (!empty($row['mi_anschr'])) {
                    $adresse .= ', ' . trim($row['mi_anschr']);
                }
                $row['mi_anschr'] = $adresse;
                               
                break;
            default:
        }
  
     
        // Aktion-Spalte mit Edit-Link füllen
       
        // Optional: andere Felder formatieren oder farblich hervorheben
        // Beispiel: Alter berechnen und farblich markieren
        
        
        // Falls Sie die Originalfelder nicht mehr benötigen, können Sie diese entfernen
        // unset($row['mi_vname'], $row['mi_name'], $row['mi_titel'], $row['mi_n_titel'], $row['mi_plz'], $row['mi_ort'], $row['mi_anschr']);
        
        return true;
    }
    
    /** Funktion zum schreiben von Log- Eintägen der Klasse */
    protected static function log(string $message): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $entry = "[$timestamp] $message" . PHP_EOL;
        file_put_contents($this->logFile, $entry, FILE_APPEND);
    }
}
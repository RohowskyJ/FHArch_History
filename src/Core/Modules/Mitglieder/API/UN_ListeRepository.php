<?php
namespace Fharch\Core\Modules\Mitglieder\API;

use \PDO;

/**
 * Liest Daten aus der Mitgliederdatei aus, Auswahl entsprechend der Listentype (alle, nur aktive, Adressliste, ..)
 * @author josef
 *
 */
class UN_ListeRepository {
    private \PDO $pdo;
    protected static string $logFile = 'MI_MemberRepository_debug.log.txt';
    
    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Holt Mitglieder-Daten basierend auf dem Listentyp und optionalen Suchparametern
     * @param string $listType z.B. 'Alle', 'Mitgl', 'BezL', ...
     * @param string|null $search optionaler Suchstring
     * @return array
     */
    public function getUnterst(string $listType, ?string $search = null): array {
        $sql = "SELECT * FROM fv_unterst ";
        $where = [];
        $params = [];
        $orderBy = '';
        switch ($listType) {
            
            case 'Alle':
            case 'Aktive':
            case 'InAktive':
            case 'WeihP':
                #$where[] = '';
                $orderBy = " ORDER BY fu_name ";
                break;
            case 'AdrListE':
            case 'AdrListV':
                #$where[] = '';
                $orderBy = " ORDER BY fu_name";
                break;
            default:
                # $where[] = "mi_name != ''"; 
           #     $orderBy = " ORDER BY fu_id";
        }
  /*
        if ($search !== null && trim($search) !== '') {
            $where[] = "fu_name LIKE :search";
            $params[':search'] = '%' . $search . '%';
        }
        */
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
        
        // 
        
        $fu_id = $row['fu_id'] ?? 0;
        $row['action'] = "<a href='UnterstEdit.php?ID={$fu_id}'>Edit</a>";
        
        
        // Beispiel: Vorname, Nachname und Titel in einer Spalte "Name" zusammenfassen
        $vorname = trim($row['fu_vname'] ?? '');
        $nachname = trim($row['fu_name'] ?? '');
        $titel = trim($row['fu_tit_vor'] ?? '') . ' ' . trim($row['fu_tit_nach'] ?? '');
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
                $row['fu_name'] = $zusammenfassungName;
                
                // Beispiel: Adresse aus mehreren Feldern zusammenfassen
                $adresse = trim($row['fu_plz'] ?? '') . ' ' . trim($row['fu_ort'] ?? '');
                if (!empty($row['fu_adresse'])) {
                    $adresse .= ', ' . trim($row['fu_adresse']);
                }
                $row['fu_adresse'] = $adresse;
                
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
            case "AdrListE" :
            case "AdrListV" :
                $zusammenfassungName = $nachname;
                if ($vorname !== '') {
                    $zusammenfassungName .= ', ' . $vorname;
                }
                if ($titel !== '') {
                    $zusammenfassungName .= ' (' . $titel . ')';
                }
                $row['fu_name'] = $zusammenfassungName;
                
                // Beispiel: Adresse aus mehreren Feldern zusammenfassen
                $adresse = trim($row['f_plz'] ?? '') . ' ' . trim($row['fu_ort'] ?? '');
                if (!empty($row['fu_adresse'])) {
                    $adresse .= ', ' . trim($row['fu_adresse']);
                }
                $row['fu_adresse'] = $adresse;
                               
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
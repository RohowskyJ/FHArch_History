<?php
namespace Fharch\Core\Modules\Oeffentlichkeitsarbeit\API;

use \PDO;
use Fharch\Core\Services\TableColumnMetadata;

const GrafFiles = array("gif","ico","jpeg","jpg","png","tiff"); 

/**
 * Liest Daten aus der Mitgliederdatei aus, Auswahl entsprechend der Listentype (alle, nur aktive, Adressliste, ..)
 * @author josef
 *
 */
class PR_ListRepository {
    private PDO $pdo;
    protected static string $logFile = 'PR_ListRepository_debug.log.txt';
    
    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Holt Mitglieder-Daten basierend auf dem Listentyp und optionalen Suchparametern
     * @param string $listType z.B. 'Alle', 'Mitgl', 'BezL', ...
     * @param string|null $search optionaler Suchstring
     * @return array
     */
    public function getPresse(string $listType, ?string $search = null): array {
        $sql = "SELECT * FROM oe_presse ";
        $where = [];
        $params = [];
        
        switch ($listType) {
            case 'Alle':
            default:
                # $where[] = "mi_name != ''";
                $orderBy = "ORDER BY pr_id";
        }
        
        if ($search !== null && trim($search) !== '') {
            $where[] = "pr_name LIKE :search";
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
    
    protected function modifyRow(array &$row, $tabTyp)
    {
        // $json = json_encode($row);
        // $this->log("modifyRow wurde aufgerufen, row $json");
        $pr_id = $row['pr_id']; // ?? 0;
        
        #if ($tabTyp != "Extern") {
            $row['action'] = "<a href='PresseEdit.php?ID={$pr_id}'>Edit</a>";
        #}
       //    $showCols = ["pr_id", "pr_datum", "pr_name", "pr_ausg", "pr_teaser", "pr_text", "pr_bild_1", "pr_bild_2"];
        switch  ($row['pr_medium']) 
        {
            CASE ('TV'):
                break;
            CASE ('URL'):
                if ($row['pr_text'] == "") {
                    $row['pr_text'] = $row['pr_web_text'];
                }
                $row['pr_name'] .= "<br>" . $row['pr_web_seite'];
                break;
            CASE ('PR'):
            default:
               
        }
       
        $d_path = "../../../../login/AOrd_Verz/Presse/";
        if ($row['pr_bild_1'] != "") {
            $bild1 = $row['pr_bild_1'];
            $ext = strtolower(pathinfo($bild1, PATHINFO_EXTENSION));
            
            $graffile = false;
            foreach (GrafFiles as $key => $val ) {
                if ($val == $ext ) {
                    $graffile = true;
                    break;
                }
            }
            
            if ($graffile) {
                $row['pr_bild_1'] = "<a href='$d_path$bild1' target='_blank'><img src='$d_path$bild1' alt='$bild1' height='200' ></a>";
            } else {
                $row['pr_bild_1'] = "<a href='$d_path$bild1' target='_blank'>$bild1</a>";
            }
        }
        
        if ($row['pr_bild_2'] != "") {
            $bild2 = $row['pr_bild_2'];
            $ext = strtolower(pathinfo($bild2, PATHINFO_EXTENSION));
            $graffile = false;
            foreach (GrafFiles as $key => $val ) {
                if ($val == $ext ) {
                    $graffile = true;
                    break;
                }
            }
            
            if ($graffile) {
                $row['pr_bild_2'] = "<a href='$d_path$bild2' target='_blank'><img src='$d_path$bild2' alt='$bild2' height='200' ></a>";
            } else {
                $row['pr_bild_2'] = "<a href='$d_path$bild2' target='_blank'>$bild2</a>";
            }
        }
        unset($row['pr_bild_2']);
        unset($row['pr_bild_3']);
        unset($row['pr_bild_4']);
        unset($row['pr_bild_5']);
        unset($row['pr_bild_6']);
        unset($row['pr_url_chkd']);
        unset($row['pr_url_obsolete']);
        unset($row['pr_changed-id']);
        unset($row['pr_changed_at']);
 
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
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
class TE_ListRepository {
    private \PDO $pdo;
    protected static string $logFile = 'TE_ListRepository_debug.log.txt';
    
    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Holt Mitglieder-Daten basierend auf dem Listentyp und optionalen Suchparametern
     * @param string $listType z.B. 'Alle', 'Mitgl', 'BezL', ...
     * @param string|null $search optionaler Suchstring
     * @return array
     */
    public function getTermine(string $listType, ?string $search = null): array {
        $sql = "SELECT * FROM oe_va_termine ";
        $where = [];
        $params = [];
        
        switch ($listType) {
            case 'Alle':
            default:
                # $where[] = "mi_name != ''";
                $orderBy = "ORDER BY va_id";
        }
        /*
        if ($search !== null && trim($search) !== '') {
            $where[] = "mi_name LIKE :search";
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
    
    protected function modifyRow(array &$row, $tabTyp)
    {
        /** Ausschluss- Kriterien :  Enddatum > Aktuelles Datum */
     /*
        $today = date('Y-m-d');
        # echo __LINE__ . " today $today <br>";
        
        if ($row['va_datum'] <= $today) {
            unset($row);
            return true;
        }
  */      
        
        /** ab hier sollen die Daten ausgegeben werden */
        // $json = json_encode( );
        // $this->log("modifyRow wurde aufgerufen, row $json");
        $va_id = $row['va_id']; // ?? 0;
        
        #if ($tabTyp != "Extern") {
            $row['action'] = "<a href='TerminEdit.php?ID={$va_id}'>Edit</a>";
        #}
        $vaJahr = '0000';
        if ($row['va_datum'] != '') {
            $vaJahr = substr($row['va_datum'], 0, 4) ."/";
        }
        
        /** $vaJahr muss zum hochladen von js ausgelesen werden ! */
        $d_path = "../../../../../login/AOrd_Verz/Termine/$vaJahr";
        if ($row['va_bild_1'] != "") {
            $bild1 = $row['va_bild_1'];
            $ext = strtolower(pathinfo($bild1, PATHINFO_EXTENSION)); 
            
            $graffile = false;
            foreach (GrafFiles as $key => $val ) {
                if ($val == $ext ) {
                    $graffile = true;
                    break;
                }
            }
            
            if ($graffile) {
                $row['va_bild_1'] = "<a href='$d_path$bild1' target='_blank'><img src='$d_path$bild1' alt='$bild1' height='200' ></a>"; 
            } else {
                $row['va_bild_1'] = "<a href='$d_path$bild1' target='_blank'>$bild1</a>";
            }
        }
        
        if ($row['va_bild_2'] != "") {
            $bild2 = $row['va_bild_2'];
            $ext = strtolower(pathinfo($bild2, PATHINFO_EXTENSION));
            $graffile = false;
            foreach (GrafFiles as $key => $val ) {
                if ($val == $ext ) {
                    $graffile = true;
                    break;
                }
            }
            
            if ($graffile) {
                $row['va_bild_2'] = "<a href='$d_path$bild2' target='_blank'><img src='$d_path$bild2' alt='$bild2' height='200' ></a>";
            } else {
                $row['va_bild_2'] = "<a href='$d_path$bild2' target='_blank'>$bild2</a>";
            }
        }
        unset($row['va_admin_email']);
        unset($row['va_abschluss_id']);
        unset($row['va_akt_pl']);
        unset($row['va_anm_erf']);
        unset($row['va_anm_text']);
        unset($row['va_anmeld_end']);
        unset($row['va_anz_anmeld']);
        unset($row['va_bdld']);
        unset($row['va_staat']);
        unset($row['va_kateg']);
        unset($row['va_link_einladung']);
        unset($row['va_ort']);
        unset($row['va_plaetze']);
        unset($row['va_url_chkd']);
        unset($row['va_va_url_obsolete']);
        unset($row['va_raum']);
        unset($row['va_umfang']);
        unset($row['va_warte']);
        unset($row['va_warte_pl']);
        unset($row['va_plz']);
        unset($row['va_bild_3']);
        unset($row['va_bild_4']);
        unset($row['va_freigabe_id']);
        unset($row['va_freigabe_at']);
        unset($row['va_abschluss_id']);
        unset($row['va_abschluss_at']);
        unset($row['va_storno_id']);
        unset($row['va_storno_at']);
        unset($row['va_created_id']);
        unset($row['va_created_at']);
        unset($row['va_changed_id']);
        unset($row['va_changed_at']);

        return true;
    }
    
    /** Funktion zum schreiben von Log- Eintägen der Klasse */
    protected static function log(string $message): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $entry = "[$timestamp] $message" . PHP_EOL;
        file_put_contents(self::$logFile, $entry, FILE_APPEND);
    }
}
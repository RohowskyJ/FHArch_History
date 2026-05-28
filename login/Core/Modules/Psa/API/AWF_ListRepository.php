<?php
namespace Fharch\Core\Modules\Psa\API;

use PDO;
use Fharch\Core\Services\TableColumnMetadata;

/** Definitionen für Grafik- Datei- Formate */
const GrafFiles = array("gif","ico","jpeg","jpg","png","tiff");

/** Definitionen für Ärmelabzeichen */
const Aermelabz_text  = array('TE' => 'Spezielle Beschreibung notwendig',
    'BR' => 'Ärmelabzeichen für die Braune Uniform',
    'BB' => 'Ärmelabzeichen für die Braune oder Blaue Uniform',
    'BG' => 'Ärmelabzeichen für die Braune oder Grüne Uniform',
    'BL' => 'Ärmelabzeichen für die Blaue Uniform',
    'HE' => 'Ärmelabzeichen für das Hemd',
    'PO' => 'Abzeichen für das Poloshirt, Fleecejacke, ...',
    'GR' => 'Ärmelabzeichen für die Grüne Uniorm',
    'FR' => 'Zugehörigkeitsabzeichen auf Freizeitkleidung (ev. Verein)',
    'XX' => 'Noch nicht näher bestimmte Verwendung'
);

/**
 * Liest Daten aus der Mitgliederdatei aus, Auswahl entsprechend der Listentype (alle, nur aktive, Adressliste, ..)
 * @author josef
 *
 */
class AWF_ListRepository {
    private PDO $pdo;
    protected static string $logFile = '../../../../login/AOrd_Verz/Logging/AWF_ListRepository_debug.log.txt';
    /*
    // Mapping Kürzel => Name für Staaten
    private array $mappingStaaten = [];
    // Mapping Kürzel => Name für Bundesländer (falls benötigt)
    private array $mappingBdld = [];
    */
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Holt Orts-Daten basierend auf dem Listentyp und optionalen Suchparametern
     * @param string $listType z.B. 'Alle', 'Mitgl', 'BezL', ...
     * @param string $proj
     * @param string|null $search optionaler Suchstring
     * @return array
     */
    public function getAermAbz(string $listType, string $fo_fw_id, ?string $search = null) : array {
        $sql = "SELECT * FROM psw_ff_wappen ";
        $where = [];
        $params = [];
        
        switch ($listType) {
            
            case 'Alle':
            default:
                $where = ["fo_fw_id = '$fo_fw_id'"];
                // Beispielbedingung, falls benötigt
                // $where[] = "mi_name != ''";
                // $orderBy = "ORDER BY fw_id";
        }
        $orderBy = " ORDER BY fo_ff_w_sort";
        
        if ($search !== null && trim($search) !== '') {
            $where[] = "fo_fw_id LIKE :search";
            $params[':search'] = '%' . $search . '%';
        }
       
        if (count($where) > 0) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        $sql .= " $orderBy";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
   
        // Daten vor der Rückgabe anpassen
        foreach ($rows as &$row) {
            $this->modifyRow($row, $listType);
        }
        unset($row); // Referenz aufheben
        
        return $rows;
    }
    
    /**
     * Modifiziert eine einzelne Zeile vor der Ausgabe
     * @param array $row
     * @param string $tabTyp
     * @param string $proj
     * @return bool
     */
    protected function modifyRow(array &$row, string $tabTyp): bool
    {
        $fo_id = $row['fo_id'] ?? 0;
        
        if ($tabTyp != "Alle.") {
            $row['action'] = "<a href='AermAbzEdit.php?ID={$fw_id}'>Edit</a>";
        }
       
        $ff_atyp = $row['fo_ff_a_typ_a'];
        #self::log($ff_atyp);
        #self::log($row['fo_ff_abz_typ']);
        
        if ($row['fo_ff_abz_typ'] == "") {
            $row['fo_ff_abz_typ'] = Aermelabz_text[$row['fo_ff_a_typ_a']];
        }
        
        
        $d_path = "../../../../login/AOrd_Verz/PSA/AERM/Aermel_Abz/";
        if ($row['fo_ff_abzeich'] != "") {
            $bild1 = $row['fo_ff_wappen'];
            $ext = strtolower(pathinfo($bild1, PATHINFO_EXTENSION));
            
            $graffile = false;
            foreach (GrafFiles as $key => $val ) {
                #self::log("$val");
                if ($val == $ext ) {
                    $graffile = true;
                    break;
                }
            }
            
            if ($graffile) {
                #self::log("Bild");
                $row['fo_ff_wappen'] = "<a href='$d_path$bild1' target='_blank'><img src='$d_path$bild1' alt='$bild1' height='200' ></a>";
            } else {
                $row['fo_ff_wappen'] = "<a href='$d_path$bild1' target='_blank'>$bild1</a>";
            }
        }
     
        // Unnötige Felder entfernen
        unset($row['fo_ff_w_sort'], $row['fo_ff_a_typ_a'], $row['fo_changed_id'], $row['fo_changed_at']);
        
        return true;
    }
    
    /**
     * Funktion zum Schreiben von Log-Einträgen der Klasse
     * @param string $message
     * @return void
     */
    protected static function log(string $message): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $entry = "[$timestamp] $message" . PHP_EOL;
        file_put_contents(self::$logFile, $entry, FILE_APPEND);
    }
}
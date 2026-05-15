<?php
namespace Fharch\Core\Modules\Mitglieder\API;

use \PDO;

/**
 * Liest Daten aus der Mitgliederdatei aus, Auswahl entsprechend der Listentype (alle, nur aktive, Adressliste, ..)
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
     * @param string $listType z.B. 'Alle', 'Mitgl', 'BezL', ...
     * @param string|null $search optionaler Suchstring
     * @return array
     */
    public function getMembers(string $listType, ?string $search = null): array {
        $sql = "SELECT * FROM fv_mitglieder ";
        $where = [];
        $params = [];
        
        switch ($listType) {
            case "Alle":
                $where[] = " mi_mtyp<>'OE' && ((mi_austrdat IS NULL OR mi_austrdat = '') AND (mi_sterbdat IS NULL OR mi_sterbdat = ''))    ";
                break;
            case "offen":
                $where[] = " mi_mtyp<>'OE' && (mi_m_beitr_bez_bis<'$ljahr' ) && ((mi_austrdat IS NULL OR mi_austrdat = '') AND (mi_sterbdat IS NULL OR mi_sterbdat = ''))  ";
                break;
            case "EM":
                $where[] = " mi_mtyp='OE' || mi_mtyp='EM' && ((mi_austrdat IS NULL OR mi_austrdat = '') AND (mi_sterbdat IS NULL OR mi_sterbdat = ''))     ";
                break;
            case "sticht":
                $where[] = "mi_mtyp<>'OE'  && ((mi_austrdat IS NULL OR mi_austrdat = '') AND (mi_sterbdat IS NULL OR mi_sterbdat = ''))  ";
                break;
            case "bezahlt":
                $where[] = " mi_mtyp<>'OE' && (mi_m_beitr_bez_bis>= '$ljahr' ) && ((mi_austrdat IS NULL OR mi_austrdat = '') AND (mi_sterbdat IS NULL OR mi_sterbdat = ''))  ";
                break;
            case "EM":
                $where[] = " mi_mtyp='OE' || mi_mtyp='EM' && ((mi_austrdat IS NULL OR mi_austrdat = '') AND (mi_sterbdat IS NULL OR mi_sterbdat = ''))     ";
                break;
            default:
                # $where[] = "mi_name != ''";
                $orderBy = "ORDER BY mi_name, mi_vname";
        }
     
        if ($search !== null && trim($search) !== '') {
            $where[] = "mi_name LIKE :search";
            $params[':search'] = '%' . $search . '%';
        }
        
        if (count($where) > 0) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        $orderBy = " ORDER BY mi_name, mi_vname ASC ";
        
        $sql .= " $orderBy";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        $rows = $stmt->fetchAll();
        $actJahr = date('Y');
        $lJahr = date('Y') - 1 ;
        // Daten vor der Rückgabe anpassen
        foreach ($rows as &$row) {
            $row['M_1'] = $row['A_1'] = $row['M_0'] = $row['A_0'] = $row['MA_0'] = $row['M_p'] = $row['A_p'] = $row['MA_p'] = "";
            // ensure Korrektur column exists for every row (formatter will display link if needsCorrection)
            $row['Korrektur'] = '';
            $row['mi_m_beitr_bez_bis'] = $row['mi_m_abo_bez_bis'] = $row['mi_m_beitr_bez'] = $row['mi_m_abo_bez'] = '';
            // Bereite SQL vor, um alle relevanten Bezahldaten für das Mitglied abzufragen
            $sqlBez = "SELECT mb_bez_mb_bis, mb_bez_abo_bis, mb_bez_abo_datum, mb_bez_mb_datum
            FROM fv_mi_bez
            WHERE mi_id = :mi_id AND (mb_bez_mb_bis >= :jahr1 OR mb_bez_abo_bis >= :jahr2)";
            
            $stmtBez = $this->pdo->prepare($sqlBez);
            $stmtBez->execute([
                ':mi_id' => $row['mi_id'],
                ':jahr1' => $actJahr,
                ':jahr2' => $actJahr
            ]);
            $miBez_arr = $stmtBez->fetchAll(PDO::FETCH_ASSOC);
            
            // Werte aus fv_mi_bez in $row eintragen
            foreach ($miBez_arr as $rbez) {
                
                $json = json_encode($rbez);
                // $this->log(__LINE__ . " bez_Daten, row $json");
                
                if ($rbez['mb_bez_mb_bis'] == $lJahr) {
                    $row['M_1'] = $rbez['mb_bez_mb_bis'];
                }
                if ($rbez['mb_bez_abo_bis'] == $lJahr) {
                    $row['A_1'] = $rbez['mb_bez_abo_bis'];
                }
                if ($rbez['mb_bez_mb_bis'] == $actJahr) {
                    $row['M_0'] = $rbez['mb_bez_mb_bis'];
                    $row['mi_m_beitr_bez_bis'] =  $rbez['mb_bez_mb_bis'];
                    $row['mi_m_beitr_bez'] = $rbez['mb_bez_mb_datum'];
                }
                if ($rbez['mb_bez_abo_bis'] == $actJahr) {
                    $row['A_0'] = $rbez['mb_bez_abo_bis'];
                    $row['mi_m_abo_bez_bis'] =  $rbez['mb_bez_abo_bis'];
                    $row['mi_m_abo_bez'] = $rbez['mb_bez_abo_datum'];
                }
                if ($rbez['mb_bez_mb_bis'] > $actJahr) {
                    $row['M_p'] = $rbez['mb_bez_mb_bis'];
                    $row['mi_m_beitr_bez_bis'] =  $rbez['mb_bez_mb_bis'];
                    $row['mi_m_beitr_bez'] = $rbez['mb_bez_mb_datum'];
                }
                if ($rbez['mb_bez_abo_bis'] > $actJahr) {
                    $row['A_p'] = $rbez['mb_bez_abo_bis'];
                    $row['mi_m_abo_bez_bis'] =  $rbez['mb_bez_abo_bis'];
                    $row['mi_m_abo_bez'] = $rbez['mb_bez_abo_datum'];
                }
            }
              
            $this->modifyRow($row, $listType);
        }
        
        return $rows;
    }
    
    /**
     * Modifizierung der Spalten- Inhalte
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
       
        // Beispiel: Vorname, Nachname und Titel in einer Spalte "Name" zusammenfassen
        $vorname = trim($row['mi_vname'] ?? '');
        $nachname = trim($row['mi_name'] ?? '');
        $titel = trim($row['mi_titel'] ?? '') . ' ' . trim($row['mi_n_titel'] ?? '');
        $titel = trim($titel);
        
        // Alle,Offen, stichtag, bezahlt,EM
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
        
        $curjahr = date("Y");
        $ljahr = date("Y") - 1;
        # $ljahr = $ljahr . "-12-31";
    
        $mi_id = $row['mi_id'];
        
        $Bezahlt = "<b style='color:green;font-size:130%;'>&checkmark;</b>";
        $NA = "<span style='color:red;'>&cross;</span>";
        
        if ($row['mi_name'] == "" and $row['mi_org_name'] != "") {
            $row['mi_name'] = $row['mi_org_name'];
        }
        
        $mi_beitr_jahr = '0000';
        if ($row['mi_eintrdat'] != '' && !is_null($row['mi_eintrdat']) ) {
            $mi_beitr_jahr = substr($row['mi_eintrdat'], 0, 4);
        }
 
        $curjahr_m1 = $curjahr - 1;
        $curjahr_1 = $curjahr + 1;
        
        if ($row['M_1'] != '') {
            if ($row['M_1'] == $curjahr_m1) {
                $row['M_1'] = 'paid';
            }
        } else {
            $row['M_1'] = 'notpaid';
        }
        if ($row['A_1'] != '') {
            if ($row['A_1'] == $curjahr_m1) {
                $row['A_1'] = 'paid';
            }
        } else {
            $row['A_1'] = 'notpaid';
        }
        
        if ($row['M_0'] >= $curjahr) {
            $row['M_0'] = 'paid'; //$Bezahlt;
        } else {
            $row['M_0'] = 'unpaid'; // "$a&b=BM' title='MitglBeitr. für heuer bezahlen'>" . "&euro;</a>";
            $row['MA_0'] = 'unpaid'; //"$a&b=BMA' title='Mitgliedsbeitrag + Abo heuer bezahlen'>&euro;</a>";
        }

        if ($row['A_0'] >= $curjahr) {
            $row['A_0'] = 'paid';
            $row['MA_0'] = "";
        } else {
            $row['A_0'] = 'unpaid';
            $row['MA_0'] = "";
        }
        if ($row['M_0'] == 'unpaid' && $row['A_0'] == 'unpaid') {
            $row['MA_0'] = 'unpaid';
        }
      
        if ($row['M_p'] == '') {
            $row['M_p'] = 'unpaid';
            $row['A_p'] = 'unpaid';
            $row['MA_p'] = 'unpaid';
        } else {
            if ($row['M_p'] > $curjahr ) {
                $row['M_p'] = 'paid'; //$Bezahlt;
                $row['MA_p'] = "";
            } else {
                $row['M_p'] = 'unpaid'; // "$a&b=BM' title='MitglBeitr. für heuer bezahlen'>" . "&euro;</a>";
                $row['MA_p'] = 'unpaid'; //"$a&b=BMA' title='Mitgliedsbeitrag + Abo heuer bezahlen'>&euro;</a>";
            }
            
            if ($row['A_p'] > $curjahr) {
                $row['A_p'] = 'paid';
                $row['MA_p'] = "";
            } else {
                
                $row['MA_p'] = "";
            }
            
        }
        
        // previously there was a needsCorrection flag used by frontend;
        // we no longer set it here because the client determines display based
        // on the justUpdated marker sent by the API.
        // keep field for compatibility but default to false
        $row['needsCorrection'] = false;
        
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
        file_put_contents(self::$logFile, $entry, FILE_APPEND);
    }
}
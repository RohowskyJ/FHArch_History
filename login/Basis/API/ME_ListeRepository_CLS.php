<?php
/**
 * Liest Daten aus der Mitgliederdatei aus, Auswahl entsprechend der Listentype (alle, nur aktive, Adressliste, ..)
 * @author josef
 *
 */
class ME_ListeRepository {
    private PDO $pdo;
    protected static string $logFile = 'ME_ListeRepository_debug.log.txt';
    
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Holt Mitglieder-Daten basierend auf dem Listentyp und optionalen Suchparametern
     * @param string $listType z.B. 'Alle', 'Aktiv', 'InAktiv', ...
     * @param string|null $search optionaler Suchstring
     * @return array
     */
    public function getMandErl(string $listType, ?string $search = null): array {
        // SQL mit JOIN auf fv_ben_dat (Alias d) und fv_benutzer (Alias b)
        $sql = "
            SELECT
                e.fu_id,
                e.be_id,
                e.fu_aktiv,
                e.fu_erlauben,
                e.ei_id,
                m.ei_id AS dat_ei_id,
                m.ei_organi,
                m.ei_org_typ,
                m.ei_org_name,
                m.ei_name,
                m.ei_vname
            FROM fv_mand_erl e
            LEFT JOIN fv_mandant m ON m.ei_id = e.ei_id
        ";
        
        $where = [];
        $params = [];
        $orderBy = "";
        /*
        // Switch für Listentyp-Bedingungen (angepasst an fv_benutzer / fv_ben_dat)
        switch ($listType) {
            case 'Aktiv':
                // Beispiel: Benutzer mit be_act = 'a' (aktiv)
                $where[] = "(b.be_act = 'a'  OR b.be_act = '')";
                $orderBy = "ORDER BY d.fd_name";
                break;
                
            case 'InAktiv':
                // Beispiel: Benutzer mit be_act = 'i' (inaktiv) oder leer
                $where[] = "(b.be_act = 'i')";
                $orderBy = "ORDER BY d.fd_name";
                break;
                
            case 'Alle':
            default:
                // Alle Benutzer, keine zusätzliche WHERE-Bedingung
                $orderBy = "ORDER BY b.be_id";
                break;
        }
        */
        // Suchfilter auf Nachname (fd_name) in fv_ben_dat
        if ($search !== null && trim($search) !== '') {
            if (is_numeric($search)) {
                $where[] = "e.be_id = :search";
                $params[':search'] = $search;
            } else {
                // Falls doch mal ein String gesucht wird, z.B. Name o.ä.
                $where[] = "e.be_id LIKE :search";
                $params[':search'] = '%' . $search . '%';
            }
        }
        
        // WHERE-Klausel zusammenbauen
        if (count($where) > 0) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        
        $sql .= " $orderBy";
        
        // Statement vorbereiten und ausführen
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Daten vor der Rückgabe anpassen (sofern Methode vorhanden)
        foreach ($rows as &$row) {
            $this->modifyRow($row, $listType);
        }
        
        return $rows;
    }
    
    protected function modifyRow(array &$row, $tabTyp)
    {
        // $json = json_encode($row);
        // $this->log("modifyRow wurde aufgerufen, row $json");
        
        $fu_id = $row['fu_id'] ?? 0;
        $row['action'] = "<a href='VS_MandBerEdit.php?ID={$fu_id}'>Edit</a>";
        
        if ($row['ei_organi'] == 'P') {
            $row['ei_id'] . " " . $row['ei_vname'] . " " . $row['ei_name'];
        } else {
            $row['ei_id'] .= " " . $row['ei_org_name'];
        }
        
 /*
        if (row['ei_org_typ'] == 'V') {
            $row['ei_id'] = $row['ei_org_name'];
            
        } elseif (row['ei_org_typ'] == 'Privat') {
            // Beispiel: Vorname, Nachname und Titel in einer Spalte "Name" zusammenfassen
            $vorname = trim($row['ei_vname'] ?? '');
            $nachname = trim($row['ei_name'] ?? '');
            $titel = trim($row['ei_titel'] ?? '') . ' ' . trim($row['ei_n_titel'] ?? '');
            $titel = trim($titel);
            
            $zusammenfassungName = $nachname;
            if ($vorname !== '') {
                $zusammenfassungName .= ', ' . $vorname;
            }
            if ($titel !== '') {
                $zusammenfassungName .= ' (' . $titel . ')';
            }
            $row['ei_id'] = $zusammenfassungName;
            /*
            $plz = trim($row['fd_plz'] ?? '');
            $ort = trim($row['fd_ort'] ?? '') ;
            $row['fd_ort'] = $plz . " " . $ort;
            * / 
        }
        
   */     
     
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
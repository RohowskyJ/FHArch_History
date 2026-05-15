<?php

namespace FSArch\Login\Basis;

use PDO;

/**
 * Liest Daten aus der Mitgliederdatei aus, Auswahl entsprechend der Listentype (alle, nur aktive, Adressliste, ..)
 * @author josef
 *
 */
class AM_ListeRepository {
    private PDO $pdo;
    protected static string $logFile = 'AM_ListenRepository_debug.log.txt';
    
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Holt Mitglieder-Daten basierend auf dem Listentyp und optionalen Suchparametern
     * @param string $listType z.B. 'Alle', 'Aktiv', 'InAktiv', ...
     * @param string|null $search optionaler Suchstring
     * @return array
     */
    public function getAdmEMail(string $listType, ?string $search = null): array {
        // SQL mit JOIN auf fv_ben_dat (Alias d) und fv_benutzer (Alias b)
        $sql = "
            SELECT
                a.em_id,
                a.be_ids,
                a.em_active,           
                a.em_mail_grp,
                b.fd_name,
                b.fd_email
            FROM fv_adm_mail a
            LEFT JOIN fv_ben_dat b ON b.be_id = a.be_ids
        ";
        
        $where = [];
        $params = [];
        $orderBy = "ORDER BY b.fd_id";
   
        // Suchfilter auf Nachname (fd_name) in fv_ben_dat
        if ($search !== null && trim($search) !== '') {
            if (is_numeric($search)) {
                $where[] = "b.be_id = :search";
                $params[':search'] = $search;
            } else {
                // Falls doch mal ein String gesucht wird, z.B. Name o.ä.
                $where[] = "b.be_id LIKE :search";
                $params[':search'] = '%' . $search . '%';
            }
        }
        
        // WHERE-Klausel zusammenbauen
        if (count($where) > 0) {
            $sql .= " WHERE " . implode(' AND ', $where);
        } else {
            self::log("Keine WHERE-Bedingung gesetzt, es werden alle Datensätze abgefragt.");
        }
        
        $sql .= " $orderBy";
        
        // Debug-Ausgabe vor Ausführung
        $sqlDebug = $sql;
        foreach ($params as $key => $value) {
            $escapedValue = $this->pdo->quote($value);
            $sqlDebug = str_replace($key, $escapedValue, $sqlDebug);
        }
        #self::log("Ausführbares SQL: " . $sqlDebug);
           
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
        // self::log("modifyRow wurde aufgerufen, row $json");
        
        $em_id = $row['em_id'] ?? 0;
        $be_ids = $row['be_ids'] ?? 0;
        $row['action'] = "<a href='VS_AdmEmailEdit.php?ID={$em_id}&beId={$be_ids}'>Edit</a>";
        // $row['fd_name'] .= $row['fd_email'];
 
        if ($row['em_active'] == 'i') {
            $row['em_active'] = 'Inaktiv';
        } else {
            $row['em_active'] = 'Aktiv';
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
        file_put_contents(self::$logFile, $entry, FILE_APPEND);
    }
}
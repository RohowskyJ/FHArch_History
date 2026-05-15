<?php

namespace Fharch\Core\Auth\API;

use PDO;

/**
 * Liest Daten aus der Mitgliederdatei aus, Auswahl entsprechend der Listentype (alle, nur aktive, Adressliste, ..)
 * @author josef
 *
 */
class RO_ListeRepository {
    private PDO $pdo;
    protected static string $logFile = 'RO_ListenRepository_debug.log.txt';
    
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Holt Mitglieder-Daten basierend auf dem Listentyp und optionalen Suchparametern
     * @param string $listType z.B. 'Alle', 'Aktiv', 'InAktiv', ...
     * @param string|null $search optionaler Suchstring
     * @return array
     */
    public function getUsersRoles(string $listType, ?string $search = null): array {
        // SQL mit JOIN auf fv_ben_dat (Alias d) und fv_benutzer (Alias b)
        $sql = "
            SELECT
                r.fr_id,
                r.be_id,
                r.fr_aktiv,
                r.fl_id,
                b.fl_id AS bes_fl_id,
                b.fl_beschreibung,
                b.fl_modules
            FROM fv_rolle r
            LEFT JOIN fv_rollen_beschr b ON b.fl_id = r.fl_id
        ";
        
        $where = [];
        $params = [];
        $orderBy = "ORDER BY b.fl_id";
   
        // Suchfilter auf Nachname (fd_name) in fv_ben_dat
        if ($search !== null && trim($search) !== '') {
            if (is_numeric($search)) {
                $where[] = "r.be_id = :search";
                $params[':search'] = $search;
            } else {
                // Falls doch mal ein String gesucht wird, z.B. Name o.ä.
                $where[] = "r.be_id LIKE :search";
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
        
        $fr_id = $row['fr_id'] ?? 0;
        $be_id = $row['be_id'] ?? 0;
        $row['action'] = "<a href='RollenEdit.php?ID={$fr_id}&beId={$be_id}'>Edit</a>";
        
 
        if ($row['fr_aktiv'] == 'i') {
            $row['fr_aktiv'] = "<span style='color:red;'>Inaktiv</span>";
        } else {
            $row['fr_aktiv'] = "<span style='color:green;'>Aktiv</span>";
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
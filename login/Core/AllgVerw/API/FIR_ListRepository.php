<?php

namespace Fharch\Core\AllgVerw\API;

use PDO;

/**
 * Liest Daten aus der Mitgliederdatei aus, Auswahl entsprechend der Listentype (alle, nur aktive, Adressliste, ..)
 * @author josef
 *
 */
class FIR_ListRepository {
    private PDO $pdo;
    protected static string $logFile = 'FirmenRepository_debug.log.txt';
    
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Holt Mitglieder-Daten basierend auf dem Listentyp und optionalen Suchparametern
     * @param string $listType z.B. 'Alle', 'Aktiv', 'InAktiv', ...
     * @param string|null $search optionaler Suchstring
     * @return array
     */
    public function getUsers(string $listType, ?string $search = null): array {
        // SQL mit JOIN auf fv_ben_dat (Alias d) und fv_benutzer (Alias b)
        $sql = "
            SELECT
                *
            FROM fv_firmen
           
        ";
        
        $where = [];
        $params = [];
        $orderBy = "";
        
        // Switch für Listentyp-Bedingungen (angepasst an fv_benutzer / fv_ben_dat)
        switch ($listType) {
     
            case 'Alle':
            default:
                // Alle Benutzer, keine zusätzliche WHERE-Bedingung
                $orderBy = "ORDER BY fi_id";
                break;
        }
        
        // Suchfilter auf Nachname (fd_name) in fv_ben_dat
        if ($search !== null && trim($search) !== '') {
            $where[] = "d.fd_name LIKE :search";
            $params[':search'] = '%' . $search . '%';
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
        
        $fi_id = $row['fi_id'] ?? 0;
        $row['action'] = "<a href='FirmenEdit.php?ID={$fi_id}'>Edit</a>";
        
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
        file_put_contents(self::$LogFile, $entry, FILE_APPEND);
    }
}
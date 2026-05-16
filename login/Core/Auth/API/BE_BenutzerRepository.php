<?php

namespace Fharch\Core\Auth\API;

use PDO;

/**
 * Liest Daten aus der Mitgliederdatei aus, Auswahl entsprechend der Listentype (alle, nur aktive, Adressliste, ..)
 * @author josef
 *
 */
class BE_BenutzerRepository {
    private PDO $pdo;
    protected static string $logFile = 'BenutzerRepository_debug.log.txt';
    
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
                b.be_id,
                b.be_uid,
                b.be_act,
                d.fd_id,
                d.be_id AS dat_be_id,
                d.be_mi_id,
                d.fd_anrede,
                d.fd_tit_vor,
                d.fd_vname,
                d.fd_name,
                d.fd_tit_nach,
                d.fd_adresse,
                d.fd_plz,
                d.fd_ort,
                d.fd_tel,
                d.fd_email
            FROM fv_benutzer b
            LEFT JOIN fv_ben_dat d ON b.be_id = d.be_id
        ";
        
        $where = [];
        $params = [];
        $orderBy = "";
        
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
        
        $fd_id = $row['fd_id'] ?? 0;
        $row['action'] = "<a href='BenEdit.php?ID={$fd_id}'>Edit</a>";
        
 
        // Beispiel: Vorname, Nachname und Titel in einer Spalte "Name" zusammenfassen
        $vorname = trim($row['fd_vname'] ?? '');
        $nachname = trim($row['fd_name'] ?? '');
        $titel = trim($row['fd_tit_vor'] ?? '') . ' ' . trim($row['fd_tit_nach'] ?? '');
        $titel = trim($titel);
        
        $zusammenfassungName = $nachname;
        if ($vorname !== '') {
            $zusammenfassungName .= ', ' . $vorname;
        }
        if ($titel !== '') {
            $zusammenfassungName .= ' (' . $titel . ')';
        }
        $row['fd_name'] = $zusammenfassungName;
        
        $plz = trim($row['fd_plz'] ?? '');
        $ort = trim($row['fd_ort'] ?? '') ;
        $row['fd_ort'] = $plz . " " . $ort;
     
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
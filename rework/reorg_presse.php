<?php
// Verbindung mit PDO herstellen
function link_DB_PDO($db_proj = "") {
    $dbhost = 'localhost';
    $dbuser = 'root';
    $dbpass = 'b1teller';
    $database = 'fharch_new';
    
    $dsn = "mysql:host=$dbhost;dbname=$database;charset=utf8mb4";
    try {
        $dbh = new PDO($dsn, $dbuser, $dbpass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
        ]);
        return $dbh;
    } catch (PDOException $e) {
        die("Verbindung fehlgeschlagen: " . $e->getMessage());
    }
}

// Funktion zum Normalisieren von Datumswerten
function normalizeDate($dateStr) {
    if ($dateStr === "" || $dateStr === NULL) {
        return null;
    }
    $date = DateTime::createFromFormat('Y-m-d', $dateStr);
    return ($date && $date->format('Y-m-d') === $dateStr) ? $date->format('Y-m-d') : null;
}

// Verbindung aufbauen
$dbh = link_DB_PDO();

// Daten aus der alten Tabelle lesen
$sql = "SELECT * FROM pr_esse ORDER BY pr_id ASC";
$stmtSelect = $dbh->query($sql);
$pr_arr = $stmtSelect->fetchAll();

// Prepared Statement mit Named Placeholders
$sqlInsert = "INSERT INTO oe_presse (
    pr_datum, pr_name, pr_ausg, pr_medium, pr_seite, pr_teaser,
    pr_text, pr_web_site, pr_url_chkd, pr_url_obsolete, pr_web_text, 
    pr_bild_1, pr_bild_2, pr_bild_3, pr_bild_4, pr_bild_5, pr_bild_6, 
    pr_inet, 
    pr_changed_id, pr_changed_at
) VALUES (
    :pr_datum, :pr_name, :pr_ausg, :pr_medium, :pr_seite, :pr_teaser,
    :pr_text, :pr_web_site, :pr_url_chkd, :pr_url_obsolete, :pr_web_text, 
    :pr_bild_1, :pr_bild_2, :pr_bild_3, :pr_bild_4, :pr_bild_5,  :pr_bild_6, 
    :pr_inet,
    :pr_changed_id, :pr_changed_at
)";
$stmtInsert = $dbh->prepare($sqlInsert);

// Feste Werte für changed_id und changed_at
$changed_id = 1;
$changed_at = date('Y-m-d H:i:s');

foreach ($pr_arr as $row) {
    // Datumswerte normalisieren, NULL falls ungültig
    #$pr_edit_dat = normalizeDate($row->pr_edit_dat);
    #$pr_frei_dat = normalizeDate($row->pr_frei_dat);
    
    // Parameter-Array für execute
    $params = [
        ':pr_datum' => $row->pr_datum,
        ':pr_name' => $row->pr_name,
        ':pr_ausg' => $row->pr_ausg,
        ':pr_medium' => $row->pr_medium,
        ':pr_seite' => $row->pr_seite,
        ':pr_teaser' => $row->pr_teaser,
        ':pr_text' => $row->pr_text,
        ':pr_web_site' => $row->pr_web_site,
        ':pr_url_chkd' => $row->pr_url_chkd,
        ':pr_url_obsolete' => $row->pr_url_obsolete,
        ':pr_web_text' => $row->pr_web_text,
        ':pr_text' => $row->pr_text,
        ':pr_bild_1' => $row->pr_bild_1,
        ':pr_bild_2' => $row->pr_bild_2,
        ':pr_bild_3' => $row->pr_bild_3,
        ':pr_bild_4' => $row->pr_bild_4,
        ':pr_bild_5' => $row->pr_bild_5,
        ':pr_bild_6' => $row->pr_bild_6,
        ':pr_inet' => $row->pr_inet,
       # ':pr_frei_id' => ($row->pr_frei_id === '' || $row->pr_frei_id === null) ? null : (int)$row->pr_frei_id,
        ':pr_changed_id' => intval($row->pr_uidaend),
        ':pr_changed_at' => $row->pr_aenddat,
    ];
    
    try {
        $stmtInsert->execute($params);
    } catch (PDOException $e) {
        echo "Fehler beim Einfügen von pr_id {$row->pr_id}: " . $e->getMessage() . "\n";
    }
}

echo __FILE__ . " " . __LINE__ . " Reorg Tabelle pr_esse nach oe_presse abgeschlossen<br>";
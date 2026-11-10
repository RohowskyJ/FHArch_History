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
$sql = "SELECT * FROM bu_echer ORDER BY bu_id ASC";
$stmtSelect = $dbh->query($sql);
$bs_arr = $stmtSelect->fetchAll();

// Prepared Statement mit Named Placeholders
$sqlInsert = "INSERT INTO oe_buecher (
    bu_titel, bu_utitel, bu_author, bu_verlag, bu_isbn, bu_preis,
    bu_seiten, bu_bilder_anz, bu_bilder_art, bu_format, bu_teaser, bu_text,
    bu_bild_1, bu_text_1, bu_bild_2, bu_text_2, bu_bild_3, bu_text_3, bu_bild_4, bu_text_4, bu_bild_5, bu_text_5, bu_bild_6, bu_text_6,
    bu_bew_ges, bu_bew_bild, bu_bew_txt, bu_editor, bu_id, bu_edit_dat, bu_frei_stat, bu_frei_id, bu_frei_dat,
    bu_changed_id, bu_changed_at
) VALUES (
    :bu_titel, :bu_utitel, :bu_author, :bu_verlag, :bu_isbn, :bu_preis,
    :bu_seiten, :bu_bilder_anz, :bu_bilder_art, :bu_format, :bu_teaser, :bu_text,
    :bu_bild_1, :bu_text_1, :bu_bild_2, :bu_text_2, :bu_bild_3, :bu_text_3, :bu_bild_4, :bu_text_4, :bu_bild_5, :bu_text_5, :bu_bild_6, :bu_text_6,
    :bu_bew_ges, :bu_bew_bild, :bu_bew_txt, :bu_editor, :bu_id, :bu_edit_dat, :bu_frei_stat, :bu_frei_id, :bu_frei_dat,
    :bu_changed_id, :bu_changed_at
)";
$stmtInsert = $dbh->prepare($sqlInsert);

// Feste Werte für changed_id und changed_at
$changed_id = 1;
$changed_at = date('Y-m-d H:i:s');

foreach ($bs_arr as $row) {
    // Datumswerte normalisieren, NULL falls ungültig
    $bu_edit_dat = normalizeDate($row->bu_edit_dat);
    $bu_frei_dat = normalizeDate($row->bu_frei_dat);
    
    // Parameter-Array für execute
    $params = [
        ':bu_titel' => $row->bu_titel,
        ':bu_utitel' => $row->bu_utitel,
        ':bu_author' => $row->bu_author,
        ':bu_verlag' => $row->bu_verlag,
        ':bu_isbn' => $row->bu_isbn,
        ':bu_preis' => $row->bu_preis,
        ':bu_seiten' => $row->bu_seiten,
        ':bu_bilder_anz' => $row->bu_bilder_anz,
        ':bu_bilder_art' => $row->bu_bilder_art,
        ':bu_format' => $row->bu_format,
        ':bu_teaser' => $row->bu_teaser,
        ':bu_text' => $row->bu_text,
        ':bu_bild_1' => $row->bu_bild_1,
        ':bu_text_1' => $row->bu_text_1,
        ':bu_bild_2' => $row->bu_bild_2,
        ':bu_text_2' => $row->bu_text_2,
        ':bu_bild_3' => $row->bu_bild_3,
        ':bu_text_3' => $row->bu_text_3,
        ':bu_bild_4' => $row->bu_bild_4,
        ':bu_text_4' => $row->bu_text_4,
        ':bu_bild_5' => $row->bu_bild_5,
        ':bu_text_5' => $row->bu_text_5,
        ':bu_bild_6' => $row->bu_bild_6,
        ':bu_text_6' => $row->bu_text_6,
        ':bu_bew_ges' => $row->bu_bew_ges,
        ':bu_bew_bild' => $row->bu_bew_bild,
        ':bu_bew_txt' => $row->bu_bew_txt,
        ':bu_editor' => $row->bu_editor,
        ':bu_id' => $row->bu_id,
        ':bu_edit_dat' => $bu_edit_dat,
        ':bu_frei_stat' => $row->bu_frei_stat,
        ':bu_frei_id' => ($row->bu_frei_id === '' || $row->bu_frei_id === null) ? null : (int)$row->bu_frei_id,
        ':bu_frei_dat' => $bu_frei_dat,
        ':bu_changed_id' => $changed_id,
        ':bu_changed_at' => $changed_at,
    ];
    
    try {
        $stmtInsert->execute($params);
    } catch (PDOException $e) {
        echo "Fehler beim Einfügen von bu_id {$row->bu_id}: " . $e->getMessage() . "\n";
    }
}

echo __FILE__ . " " . __LINE__ . " Reorg Tabelle bu_uecher nach oe_buecher abgeschlossen<br>";
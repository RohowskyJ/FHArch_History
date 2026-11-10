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
$sql = "SELECT * FROM va_daten ORDER BY va_id ASC";
$stmtSelect = $dbh->query($sql);
$va_arr = $stmtSelect->fetchAll();

// Prepared Statement mit Named Placeholders
$sqlInsert = "INSERT INTO oe_va_termine (
    va_datum, va_begzt, va_dauer, va_end_dat, va_endzt, va_titel,  va_beschr, va_kateg,
    va_anm_erf,va_inst,va_adresse, va_plz, 
    va_ort, va_staat, va_bdld, va_beitrag_m,va_beitrag_g,va_admin_email,va_kontakt,va_link_einladung,va_umfang,
    va_bild_1,va_bild_2, va_bild_3,va_bild_4, va_internet, va_url_chkd,va_url_obsolete, va_anm_text, va_raum,  va_plaetze, va_warte,
    va_akt_pl, va_wl_pl, va_anz_anmeld, va_anmeld_end, va_freigabe_id, va_freigabe_at, va_abschluss_id, va_abschluss_at, va_storno_id, va_storno_at, 
    va_changed_id, va_changed_at, va_created_id, va_created_at
) VALUES (
    :va_datum, :va_begzt, :va_dauer, :va_end_dat, :va_endzt, :va_titel, :va_beschr,:va_kateg, 
    :va_anm_erf, :va_inst, :va_adresse, :va_plz, 
    :va_ort, :va_staat, :va_bdld, :va_beitrag_m, :va_beitrag_g, :va_admin_email, :va_kontakt, :va_link_einladung, :va_umfang,
    :va_bild_1, :va_bild_2,  :va_bild_3, :va_bild_4, :va_internet, :va_url_chkd,  :va_url_obsolete,:va_anm_text,  :va_raum, :va_plaetze, :va_warte,
    :va_akt_pl, :va_wl_pl, :va_anz_anmeld, :va_anmeld_end, :va_freigabe_id, :va_freigabe_at, :va_abschluss_id, :va_abschluss_at, :va_storno_id, :va_storno_at, 
    :va_changed_id, :va_changed_at, :va_created_id, :va_created_at
)";
$stmtInsert = $dbh->prepare($sqlInsert);


// Feste Werte für changed_id und changed_at
$changed_id = 1;
$changed_at = date('Y-m-d H:i:s');

foreach ($va_arr as $row) {
    // Datumswerte normalisieren, NULL falls ungültig
    #$mu_edit_dat = normalizeDate($row->mu_edit_dat);
    #$mu_frei_dat = normalizeDate($row->mu_frei_dat);
    
    $teile = [];
    
    // Tel hinzufügen, wenn nicht leer
    if (!empty($row->va_beitrag_g)) {
        $teile[] = $row->va_beitrag_g;
    }
    
    // Handy hinzufügen, wenn nicht leer
    if (!empty($row->mu_kustos_handy)) {
        $teile[] = $row->mu_kustos_handy;
    }
    
    // Fax hinzufügen, wenn nicht leer, mit vorangestelltem "Fax: "
    if (!empty($row->mu_kustos_fax)) {
        $teile[] = "Fax: " . $row->mu_kustos_fax;
    }
    
    // Alle Teile mit einem Trennzeichen zusammenfügen, z.B. Komma und Leerzeichen
    $va_beitrag_g_gesamt = implode(", ", $teile);
    
    /*
    // Dann in das Parameter-Array einfügen
    $params = [
        // ... andere Parameter ...
        ':va_beitrag_g' => $va_beitrag_g_gesamt,
        // ... weitere Parameter ...
    ];
    */
    // Parameter-Array für execute
    $params = [
        ':va_datum' => $row->va_datum,
        ':va_begzt' => $row->va_begzt,
        ':va_dauer' => $row->va_dauer,
        ':va_end_dat' => $row->va_end_dat,
        ':va_endzt' => $row->va_endzt,
        ':va_titel' => $row->va_titel,
        ':va_beschr' => $row->va_beschr,
        ':va_kateg' => $row->va_kateg,
        ':va_anm_erf' => $row->va_anm_erf,
        ':va_inst' => $row->va_inst,
        ':va_adresse' => $row->va_adresse,
        ':va_plz' => $row->va_plz,
        ':va_ort' => $row->va_ort,
        ':va_staat' => $row->va_staat,
        ':va_bdld' => $row->va_bdld,
        ':va_beitrag_m' => $row->va_beitrag_m,
        ':va_beitrag_g' => $row->va_beitrag_g,
        ':va_admin_email' => $row->va_admin_email,
        ':va_kontakt' => $row->va_kontakt,
        ':va_link_einladung' => $row->va_link_einladung,
        ':va_umfang' => $row->va_umfang,
        ':va_bild_1' => $row->va_bild_1,
        ':va_bild_2' => $row->va_bild_2,
        ':va_bild_3' => $row->va_bild_3,
        ':va_bild_4' => $row->va_bild_4,
        ':va_internet' => $row->va_internet,
        ':va_url_chkd' => $row->va_url_chkd,
        ':va_url_obsolete' => $row->va_url_obsolete,
        ':va_anm_text' => $row->va_anm_text,
        ':va_raum' => $row->va_raum,
        ':va_plaetze' => $row->va_plaetze,
        ':va_warte' => $row->va_warte,
        ':va_akt_pl' => $row->va_akt_pl,
        ':va_wl_pl'  => $row->va_wl_pl,
        ':va_anz_anmeld' => $row->va_anz_anmeld,
        ':va_anmeld_end' => $row->va_anmeld_end,
        ':va_freigabe_id' => intval($row->va_frei_uid),
        ':va_freigabe_at' => $row->va_freigabe,
        ':va_abschluss_id' => intval($row->va_ab_uid),
        ':va_abschluss_at' => $row->va_abschluss,
        ':va_storno_id' => intval($row->va_Storn_uid),
        ':va_storno_at' => $row->va_storno,
        ':va_changed_id' => intval($row->va_aenduid),
        ':va_changed_at' => $row->va_aenderung,
        ':va_created_id' => intval($row->va_ang_uid),
        ':va_created_at' => $row->va_angelegt
    ];
    
    try {
        $stmtInsert->execute($params);
    } catch (PDOException $e) {
        echo "Fehler beim Einfügen von va_id {$row->va_id}: " . $e->getMessage() . "\n";
    }
}

echo __FILE__ . " " . __LINE__ . " Reorg Tabelle va_daten nach oe_va_termine abgeschlossen<br>";
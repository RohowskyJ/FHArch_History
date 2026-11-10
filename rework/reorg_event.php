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
$sql = "SELECT * FROM va_event ORDER BY va_id ASC";
$stmtSelect = $dbh->query($sql);
$va_arr = $stmtSelect->fetchAll();

// Prepared Statement mit Named Placeholders
$sqlInsert = "INSERT INTO oe_va_event (
    va_tp_id, va_kennz,  va_staat, va_bdld, va_plz, va_name, va_beschreibung, va_anmeld, va_org_name,  va_org_adresse,
    va_org_plz,va_org_tel, va_org_email, 
    va_org_inet, va_org_name_1,va_org_adresse_1, va_org_plz_1,va_org_tel_1,va_org_email_1,
    va_org_inet_1,va_anmeld_2, va_anmeld_3,va_bild, va_meld_anf, va_meld_end,va_zvrnr, va_anwtext, va_bank,  va_BIC,  va_IBAN, va_schlaf1,
    va_schlaf2, 
    va_changed_id, va_changed_at
) VALUES (
    :va_tp_id, :va_kennz, :va_staat, :va_bdld, :va_plz, :va_name, :va_beschreibung, :va_anmeld, :va_org_name, :va_org_adresse, 
    :va_org_plz, :va_org_tel, :va_org_email, 
    :va_org_inet, :va_org_name_1, :va_org_adresse_1, :va_org_plz_1, :va_org_tel_1, :va_org_email_1,
    :va_org_inet_1, :va_anmeld_2,  :va_anmeld_3, :va_bild, :va_meld_anf, :va_meld_end,  :va_zvrnr,:va_anwtext,  :va_bank, :va_BIC,:va_IBAN, :va_schlaf1,
    :va_schlaf2,
    :va_changed_id, :va_changed_at
)";
$stmtInsert = $dbh->prepare($sqlInsert);


// Feste Werte für changed_id und changed_at
$changed_id = 1;
$changed_at = date('Y-m-d H:i:s');

foreach ($va_arr as $row) {
    // Datumswerte normalisieren, NULL falls ungültig
    #$mu_edit_dat = normalizeDate($row->mu_edit_dat);
    #$mu_frei_dat = normalizeDate($row->mu_frei_dat);
    /*
    $teile = [];
    
    // Tel hinzufügen, wenn nicht leer
    if (!empty($row->va_org_adresse_1)) {
        $teile[] = $row->va_org_adresse_1;
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
    $va_org_adresse_1_gesamt = implode(", ", $teile);
    */
    /*
    // Dann in das Parameter-Array einfügen
    $params = [
        // ... andere Parameter ...
        ':va_org_adresse_1' => $va_org_adresse_1_gesamt,
        // ... weitere Parameter ...
    ];
    */
    // Parameter-Array für execute
    $params = [
        ':va_tp_id' => $row->va_tp_id,
        ':va_kennz' => $row->va_kennz,
        ':va_staat' => $row->va_staat,
        ':va_bdld' => $row->va_bdld,
        ':va_plz' => $row->va_plz,
        ':va_name' => $row->va_name,
        ':va_beschreibung' => $row->va_beschreibung,
        ':va_anmeld' => $row->va_anmeld,
        ':va_org_name' => $row->va_org_name,
        ':va_org_adresse' => $row->va_org_adresse,
        ':va_org_plz' => $row->va_org_plz,
        ':va_org_tel' => $row->va_org_tel,
        ':va_org_email' => $row->va_org_email,
        ':va_org_inet' => $row->va_org_inet,
        ':va_org_name_1' => $row->va_org_name_1,
        ':va_org_adresse_1' => $row->va_org_adresse_1,
        ':va_org_plz_1' => $row->va_org_plz_1,
        ':va_org_tel_1' => $row->va_org_tel_1,
        ':va_org_email_1' => $row->va_org_email_1,
        ':va_org_inet_1' => $row->va_org_inet_1,
        ':va_anmeld_2' => $row->va_anmeld_2,
        ':va_anmeld_3' => $row->va_anmeld_3,
        ':va_bild' => $row->va_bild,
        ':va_meld_anf' => $row->va_meld_anf,
        ':va_meld_end' => $row->va_meld_end,
        ':va_zvrnr' => $row->va_zvrnr,
        ':va_anwtext' => $row->va_anwtext,
        ':va_bank' => $row->va_bank,
        ':va_BIC' => $row->va_BIC,
        ':va_IBAN' => $row->va_kontonr,
        ':va_schlaf1' => $row->va_schlaf1,
        ':va_schlaf2' => $row->va_schlaf2,
        ':va_changed_id' => intval($row->va_uidaend),
        ':va_changed_at' => $row->va_aenddat,
    ];
    
    try {
        $stmtInsert->execute($params);
    } catch (PDOException $e) {
        echo "Fehler beim Einfügen von va_id {$row->va_id}: " . $e->getMessage() . "\n";
    }
}

echo __FILE__ . " " . __LINE__ . " Reorg Tabelle va_event nach oe_va_event abgeschlossen<br>";
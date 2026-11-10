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
$sql = "SELECT * FROM mu_basis ORDER BY mu_id ASC";
$stmtSelect = $dbh->query($sql);
$mu_arr = $stmtSelect->fetchAll();

// Prepared Statement mit Named Placeholders
$sqlInsert = "INSERT INTO oe_museen (
    mu_staat, mu_bdland, mu_bez, mu_name, mu_bezeichng, mu_adresse_a,  mu_plz_a, mu_ort_a,
    mu_adresse_p,mu_plz_p,mu_ort_p, mu_eigner, 
    mu_kustos_titel, mu_kustos_vname, mu_kustos_name, mu_kustos_dgr,mu_kustos_tel,mu_kustos_intern,mu_url_chkd,mu_url_obsolete,mu_kustos_email,mu_sammelbeg,
    mu_bild_1, mu_bildbeschr_1, mu_bild_2, mu_bildbeschr_2,mu_mustyp, mu_museigtyp, mu_sammlgschw, mu_besobj_1,  mu_besobj_2, mu_besobj_3,
    mu_anz_obj, mu_archiv, mu_protbuch, mu_abzeich, mu_ausruest, mu_kleinger, mu_grossger, mu_toilett, mu_garderobe, mu_cafe, 
    mu_sonst_anb, mu_rollstuhl, mu_beheinr, mu_oeffnung, mu_saison, mu_oez_mo,  mu_oez_di,  mu_oez_mi,  mu_oez_do,  mu_oez_fr,  mu_oez_sa,  mu_oez_so,  mu_oez_fei, 
    mu_f1_titel, mu_f1_vname, mu_f1_name, mu_f1_dgr, mu_f1_tel, mu_f1_email,
    mu_f2_titel, mu_f2_vname, mu_f2_name, mu_f2_dgr, mu_f2_tel, mu_f2_email,
    mu_changed_id, mu_changed_at
) VALUES (
    :mu_staat, :mu_bdland, :mu_bez, :mu_name, :mu_bezeichng, :mu_adresse_a, :mu_plz_a,:mu_ort_a, 
    :mu_adresse_p, :mu_plz_p, :mu_ort_p, :mu_eigner, 
    :mu_kustos_titel, :mu_kustos_vname, :mu_kustos_name, :mu_kustos_dgr, :mu_kustos_tel, :mu_kustos_intern, :mu_url_chckd, :mur_url_obsolete, :mu_kustos_email, :mu_sammlbeg,
    :mu_bild_1, :mu_bibeschr_1, :mu_bild_2, :mu_bibeschr_2, :mu_mustyp, :mu_museigtyp, :mu_sammlgschw,  :mu_besobj_1, :mu_besobj_2, :mu_besobj_3,
    :mu_anz_obj, :mu_archiv, :mu_protbuch, :mu_abzeich, :mu_ausruest, :mu_kleinger, :mu_grossger, :mu_toilett, :mu_garderobe, :mu_cafe, 
    :mu_sonst_anb, :mu_rollstuhl, :mu_beheinr, :mu_oeffnung, :mu_saison, :mu_oez_mo,  :mu_oez_di,  :mu_oez_mi,  :mu_oez_do,  :mu_oez_fr,  :mu_oez_sa, :mu_oez_so, :mu_oez_fei, 
    :mu_f1_titel, :mu_f1_vname, :mu_f1_name, :mu_f1_dgr, :mu_f1_tel, :mu_f1_email,
    :mu_f2_titel, :mu_f2_vname, :mu_f2_name, :mu_f2_dgr, :mu_f2_tel, :mu_f2_email,
    :mu_changed_id, :mu_changed_at
)";
$stmtInsert = $dbh->prepare($sqlInsert);


// Feste Werte für changed_id und changed_at
$changed_id = 1;
$changed_at = date('Y-m-d H:i:s');

foreach ($mu_arr as $row) {
    // Datumswerte normalisieren, NULL falls ungültig
    #$mu_edit_dat = normalizeDate($row->mu_edit_dat);
    #$mu_frei_dat = normalizeDate($row->mu_frei_dat);
    
    $teile = [];
    
    // Tel hinzufügen, wenn nicht leer
    if (!empty($row->mu_kustos_tel)) {
        $teile[] = $row->mu_kustos_tel;
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
    $mu_kustos_tel_gesamt = implode(", ", $teile);
    
    /*
    // Dann in das Parameter-Array einfügen
    $params = [
        // ... andere Parameter ...
        ':mu_kustos_tel' => $mu_kustos_tel_gesamt,
        // ... weitere Parameter ...
    ];
    */
    // Parameter-Array für execute
    $params = [
        ':mu_staat' => $row->mu_staat,
        ':mu_bdland' => $row->mu_bdland,
        ':mu_bez' => $row->mu_bez,
        ':mu_name' => $row->mu_name,
        ':mu_bezeichng' => $row->mu_bezeichng,
        ':mu_adresse_a' => $row->mu_adresse_a,
        ':mu_plz_a' => $row->mu_plz_a,
        ':mu_ort_a' => $row->mu_ort_a,
        ':mu_adresse_p' => $row->mu_adresse_p,
        ':mu_plz_p' => $row->mu_plz_p,
        ':mu_ort_p' => $row->mu_ort_p,
        ':mu_eigner' => $row->mu_eigner,
        ':mu_kustos_titel' => $row->mu_kustos_titel,
        ':mu_kustos_vname' => $row->mu_kustos_vname,
        ':mu_kustos_name' => $row->mu_kustos_name,
        ':mu_kustos_dgr' => $row->mu_kustos_dgr,
        ':mu_kustos_tel' => $mu_kustos_tel_gesamt,
        ':mu_kustos_intern' => $row->mu_kustos_intern,
        ':mu_url_chkd' => $row->mu_url_chckd,
        ':mu_url_obsolete' => $row->mur_url_obsolete,
        ':mu_kustos_email' => $row->mu_kustos_email,
        ':mu_sammelbeg' => $row->mu_sammlbeg,
        ':mu_bild_1' => $row->mu_bild_1,
        ':mu_bildbeschr_1' => $row->mu_bibeschr_1,
        ':mu_bild_2' => $row->mu_bild_2,
        ':mu_bildbeschr_2' => $row->mu_bibeschr_2,
        ':mu_mustyp' => $row->mu_mustyp,
        ':mu_museigtyp' => $row->mu_museigtyp,
        ':mu_sammlgschw' => $row->mu_sammlgschw,
        ':mu_besobj_1' => $row->mu_besobj_1,
        ':mu_besobj_2' => $row->mu_besobj_2,
        ':mu_besobj_3' => $row->mu_besobj_3,
        ':mu_anz_obj' => $row->mu_anz_obj,
        ':mu_archiv'  => $row->mu_archiv,
        ':mu_protbuch' => $row->mu_protbuch,
        ':mu_abzeich' => $row->mu_abzeich,
        ':mu_ausruest' => $row->mu_ausruest,
        ':mu_kleinger' => $row->mu_kleinger,
        ':mu_grossger' => $row->mu_grossger,
        ':mu_toilett' => $row->mu_toilett,
        ':mu_garderobe' => $row->mu_garderobe,
        ':mu_cafe' => $row->mu_cafe,
        ':mu_sonst_anb' => $row->mu_sonst_anb,
        ':mu_rollstuhl' => $row->mu_rollstuhl,
        ':mu_beheinr' => $row->mu_beheinr,
        ':mu_oeffnung' => $row->mu_oeffnung,
        ':mu_saison' => $row->mu_saison,
        ':mu_oez_mo' => $row->mu_oez_mo,
        ':mu_oez_di' => $row->mu_oez_di,
        ':mu_oez_mi' => $row->mu_oez_mi,
        ':mu_oez_do' => $row->mu_oez_do,
        ':mu_oez_fr' => $row->mu_oez_fr,
        ':mu_oez_sa' => $row->mu_oez_sa,
        ':mu_oez_so' => $row->mu_oez_so,
        ':mu_oez_fei' => $row->mu_oez_fei,
        ':mu_f1_titel' => $row->mu_f1_titel,
        ':mu_f1_vname' => $row->mu_f1_vname,
        ':mu_f1_name' => $row->mu_f1_name,
        ':mu_f1_dgr' => $row->mu_f1_dgr,
        ':mu_f1_tel' => $row->mu_f1_tel,
        ':mu_f1_email' => $row->mu_f1_email,
        ':mu_f2_titel' => $row->mu_f2_titel,
        ':mu_f2_vname' => $row->mu_f2_vname,
        ':mu_f2_name' => $row->mu_f2_name,
        ':mu_f2_dgr' => $row->mu_f2_dgr,
        ':mu_f2_tel' => $row->mu_f2_tel,
        ':mu_f2_email' => $row->mu_f2_email,
        
       # ':mu_frei_id' => ($row->mu_frei_id === '' || $row->mu_frei_id === null) ? null : (int)$row->mu_frei_id,
        ':mu_changed_id' => intval($row->mu_uidaend),
        ':mu_changed_at' => $row->mu_aenddat,
    ];
    
    try {
        $stmtInsert->execute($params);
    } catch (PDOException $e) {
        echo "Fehler beim Einfügen von mu_id {$row->mu_id}: " . $e->getMessage() . "\n";
    }
}

echo __FILE__ . " " . __LINE__ . " Reorg Tabelle mu_basis nach oe_museen abgeschlossen<br>";
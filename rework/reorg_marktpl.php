<?php
/** reorganisation von ffhist_arch auf fharch_neu
 * einlesen alt und ausgabe neu mit Korrekturen
 * 
 * aenduid von varchar auf int bs_changed_id

 * 
 * 
 * 
 * Tabelle neu anlegen, dann übertragen
 */
$debug = false;
$path2ROOT = "../../";
# require "../login/Basis/common//BS_FuncsLib.php";

$db = link_DB('xY');

/** einlesen bs_biete_suche in arr */

$sql = "SELECT * FROM bs_biete_suche ORDER BY bs_id ASC ";

$ret = SQL_QUERY($db, $sql);

$bs_arr = [];

WHILE ( $row = mysqli_fetch_object($ret) ) {
    $row->bs_aenduid =  intval($row->bs_aenduid);
    $bs_arr[$row->bs_id] = $row;
}
var_dump($bs_arr);

/** Array abarbeiten */

$stmt = $db->prepare("INSERT INTO oe_marktplatz (
    bs_loekennz, bs_startdatum, bs_enddatum, bs_kurztext, bs_typ, bs_text,
    bs_email_1, bs_email_2, bs_bild_1, bs_bild_2, bs_bild_3, bs_bild_4,  bs_changed_id, bs_changed_at
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"); //   , ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

foreach ($bs_arr as $row) {
    
    $row->bs_startdatum = normalizeDate($row->bs_startdatum);
    $row->bs_enddatum = normalizeDate($row->bs_enddatum);

    $changed_id = intval($row->bs_aenduid);
    
    $stmt->bind_param(
        "ssssssssssssss", // ssssssssssssssisssss
        $row->bs_loekennz, $row->bs_startdatum, $row->bs_enddatum, $row->bs_kurztext, $row->bs_typ, $row->bs_text,
        $row->bs_email_1, $row->bs_email_2, $row->bs_bild_1, $row->bs_bild_2, $row->bs_bild_3, $row->bs_bild_4, $row->bs_aenduid, $row->bs_aenddate
        );
    
    $stmt->execute();
}

function normalizeDate($dateStr) {
    if ($dateStr === "" || $dateStr === NULL) {
        return NULL;
    }
    $date = DateTime::createFromFormat('Y-m-d', $dateStr);
    return ($date && $date->format('Y-m-d') === $dateStr) ? $date->format('Y-m-d') : NULL;
}


function link_DB($db_proj = "")
{
    global $debug, $module, $LinkDB_database, $path2ROOT;
    
    $dbhost = 'localhost'; //$ini_arr[$server_name]['l_dbh'];
    $dbuser = 'root'; //$ini_arr[$server_name]['l_dbu'];
    $dbpass = 'b1teller';
    $database = 'fharch_new';
    
    # echo __LINE__ . " linkdb_n dbhost $dbhost dbnam $database user $dbuser <br>";  # pass $dbpass
    
    $dblink = mysqli_connect($dbhost, $dbuser, $dbpass) or die('Verbindung zu MySQL gescheitert!' . mysqli_connect_error());
    
    mysqli_select_db($dblink, $database) or die("Datenbankzugriff zu $database gescheitert!");
    # if ($debug) { echo "<pre class=debug> mysqli_select_db:"; print_r($dblink); echo '</pre>'; }
    mysqli_set_charset($dblink, 'utf8mb4');
    $LinkDB_database = $database; # wird in Funktion Tabellen_Spalten_v2.php verwendet
    return $dblink;

} # ende linkDB

function SQL_QUERY($db, $sql)
// --------------------------------------------------------------------------------
{
    global $debug, $module;
    
    if ($return = mysqli_query($db, $sql)) {
        # echo "<pre class=debug>L 725 SQL_Query sql $sql <br>result: " ; print_r($return); echo '</pre>';
        return $return;
    } else {
        echo "<br><b style='color:red;background:white;'>Fehler in mysqli_query: <i>" . mysqli_error($db) . "</i></b> <b><pre style='background:white;'>$sql</pre></b><br>";
        
        if ($debug or $return === false) {
            echo '<pre class=debug>sql result: ';
            print_r($return);
            echo '</pre>';
        }
        exit();
    }
    return $return;
} // Ende von function SQL_QUERY

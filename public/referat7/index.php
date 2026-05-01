<?php

/**
 * Basis der Seite, einlesen Configuration, Externe Seite
 *
 * @author josef Rohowsky - neu 2014
 *        letzte Änderung 2024, Änderung der Struktur, neue Schutzmechanismen, neu einterne Einteilung
 */

session_start();

const Module_Name = 'VF_NÖ';
$module = Module_Name;

/**
 * Angleichung an den Root-Path
 *
 * @var string $path2ROOT
 */
$path2ROOT = "../";


$debug = True;
$debug = False; // Debug output Ein/Aus Schalter

if (!is_file($path2ROOT."login/common/config_d.ini") ||  !is_file($path2ROOT."login/common/config_m.ini") || !is_file($path2ROOT."login/common/config_s.ini")  ) {
#   header("Location: ". $path2ROOT."login/common/install/install.php");
}
/**
 * Zur Benutzung der neuen, gemeinsamen Bibliotheken
 * die neuen Bibs
 */
require $path2ROOT .  'login/common/Funcs.inc'; // Diverse Unterprogramme
#require $path2ROOT .  'login/common/Int_Conf.inc'; // Einlesen in Sess-Parm
#require $path2ROOT .  'login/common/Int_Conf_Const.inc'; // Einlesen in Sess-Parm

initial_debug(); # Wenn $debug=true - Ausgabe von Debug Informationen: $_POST, $_GET, $_FILE

$db = linkDB('VFH');

echo "L 042<br>";
$logo = 'JA';
$header = "<link  href='" . $path2ROOT . "login/common/css/frame_pict.css' rel='stylesheet' type='text/css'>
<link  href='" . $path2ROOT . "login/common/css/w3.css' rel='stylesheet' type='text/css'>
<link  href='" . $path2ROOT . "login/common/css/add.css' rel='stylesheet' type='text/css'>
";
$header .= "<style>nav{float:left;width:320px;margin:10px;border:3px solid grey;}.cont{border:1px solid grey;}@media print{.nav{display:none;}}</style>  \n";

HTML_header('Hauptseite', '', $header, 'Form', '75em'); # Parm: Titel,Subtitel,HeaderLine,Type,width

$ini_arr = parse_ini_file($path2ROOT.'login/common/config_m.ini',True,INI_SCANNER_NORMAL);
$cnt_m = count($ini_arr['Modules']);

# print_r($ini_arr);echo "<br>l 047 ini arr <br>";
if (isset($ini_arr['Modules']) && $cnt_m >10){ # && $cnt_m >10
?>

<img src="imgs/2013_01_top_72.png" alt="imgs/2013_01_top_72.png"
     class="w3-image">
<br />
<div class='w3-third'>
     <fieldset>
          <div class="nav">
               <a href="../login/VF_O_MU_List.php"
                    target='M_Links'>Museen (Feuerwehr und andere Blaulicht
                    Organisationen)</a> <br />
                        <a href="../login/VF_O_AR_List.php"
                    target='A_Links'>Links zu Archiven und Bibliotheken</a> <br />
            <?php
             if ($ini_arr['Modules']['m_6'] == "J") {
                ?>
               <a href="../login/VF_O_TE_List.php"
                    target='Veranstalt'>Veranstaltungskalender</a> <br />
           <?php
           }
           if ($ini_arr['Modules']['m_8'] == "J") {
                ?>
        <a href="../login/VF_O_PR_List.php"
                    target='Presse'>Presse-Information, Spiegel</a> <br />
        <?php
           }
          if ($ini_arr['Modules']['m_2'] == "J") {
                ?>
        <a href="../login/VF_O_BU_List.php"
                    target='Buch'>Buch - Besprechungen</a> <br />
         <?php
           }
           if ($ini_arr['Modules']['m_7'] == "J") {
                ?>
        <a href="../login/VF_O_AN_List.php"
                    target='Marktpl'>Marktplatz</a> <br /> <a href="impress.html">Impressum</a>
               <br />
        <?php
           }
            ?>
        <a href="scripts/VF_EM_Edit.php">Kontakt
                    (E-Mail)</a> <br />

        <?php
        if ($_SERVER['SERVER_ADDR'] == "136.243.155.235") { # || $_SERVER['SERVER_ADDR'] == "127.0.0.1"
            echo "<div style='color:red;size:em2;'>Mitgliedsanmeldung derzeit nicht möglich.</div>";
        } else {
            ?>
            <a href='scripts/VF_M_Anmeld.php' target='MitglAnmeld'>Ich will Mitglied
                    werden</a> <br />
            <?php
        }
        ?>

        <a href="menu_extern_hilfe.php" target='Hilfe'>Hilfe ... </a> <br />
          <a href='../login/index_int.php' >Login zum internen Bereich </a><br />
               <!-- VF_C_Menu_ui_css_v3.php?phase=9 -->
          <a href="DSGVO/Datenschutz_allg_Beschreibg.php" target='DSVGO'>Verarbeitungsbeschreibung
                    nach DSGVO</a> <br />
         <a href="referate.php" target='referate'>Referate</a> <br />
         <a href="Vorstand.php" target='Vorstand'>Vorstandsmitglieder
                    </a> <br />
          </div>
     </fieldset>
</div>
<div class='w3-twothirds'>
     <fieldset>
<?php
if ($_SERVER['SERVER_ADDR'] == "136.243.155.235") { # || $_SERVER['SERVER_ADDR'] == "127.0.0.1" ### Hetzner ISP von Berndorfer
    echo "<div style='color:red;font-size:150%;'>Wegen Übersiedelung der Seite zu einem anderen Service-Provider sind derzeit nur Abfragen erlaubt.
          Mitglieds- Neuanmeldungen sind kurzzeitig nicht möglich. Datenänderungen gehen verloren.<hr></div>";
}

?>
<font size="4em"> <b>Unsere Ziele:</b> <br /> Einladen wollen wir alle,
               die an der Geschichte des Feuerwehrwesens interessiert sind,
               besonders aber jene, die bereits historisch interessante Fahrzeuge,
               Geräte und Ausrüstungsgegenstände besitzen. <br> Das sind:
               Feuerwehren, Feuerwehrmänner und -frauen, Museen und Sammlungen,
               Privatpersonen und private Vereine. <br> Eine der Aufgaben des
               Vereines ist, Feuerwehren und Personen, die bereit sind, an einer
               gezielten Sammeltätigkeit mitzuwirken, bei dieser Tätigkeit zu
               fördern und zu unterstützen.
          </font> <br />
          <p align="center">
               <a href="ziele.php" target="Ziele">Details</a>
          </p>

     </fieldset>
</div>

<div class="w3-content ">
     <hr width="100%">
     <p class="w3-center">
          <font face="Verdana, Arial, Helvetica, sans-serif" size="1">Mit
               freundlicher Unterstützung: (Sponsoren)</font> <br>

          <!-- -->
          <a href="http://www.noemitte.volksbank.at/" target="_blanc"><img
               src="imgs/logo_versand_blauer_hintergrund.jpeg" border="0" width="180"></a>
          &nbsp; &nbsp;
          <!-- -->
          <a href="https://www.joechlinger-gemuese.at/" target="_blanc"><img
               src="imgs/Logo_Joechlinger.jpg" border="0"
               width="180"></a> &nbsp; &nbsp;

     </p>

     <hr width="100%">
     <p class="w3-text-blue">


     <h3 class="w3-center">
          Aktuelles: <a href="referat7/index_act.php" target="Arch">(Archiv = weniger Aktuelles)</a>
     </h3>

     <font size="+1"> <br>"referat7/Wiki_auch_fuer_Feuerwehren.pdf"
          target="Regiow">RegioWiki - Regionale Wiki (Wikipedia) Seiten für
               Österreich</a> <br> <a
          href="https://mitglieder.wikimedia.at/Nachrichten/2014-09-29"
          target="RegioS">Seminar Wikipedia, Regiowiki in der
               Landesfeuerwehrschule Tulln</a> <br> <a
          href="https://regiowiki.at/wiki/Kategorie:Feuerwehr">Feuerwehren in
               Regiowiki</a> <br>
     </font> <font size="+1"> <br> <a href=$path2ROOT."referat7/Pflichtabgabe/Pflichtablieferung_BGBLA_2009_II_271.pdf">Pflichtablieferung
               von Druckwerken - Gesetzestext</a> <br>
     </font> <font size="+1"> <br> <a href="referat7/BedienerDoku_07.pdf">Bedienerhilfe:
               Dokumentation für die Benutzer der Internetseite der Historiker, <b>Version
                    31.05.2023</b><br />
     </a> <a href="referat7/archord.pdf">Archivordnung
               (pdf)<br />
     </a> <a href="referat7/archord.xls">Archivordnung
               (xls)<br />
     </a> <a href="referat7/suchbegr.pdf">vordefinierte
               Suchbegriffe (pdf)<br />
     </a> <br /> <b>CIDOC Information über die Dokumentation in Museen </b><br />
          <a href="http://www.museumsbund.at/leitfaeden-und-standards"
          target="_blanc">CIDOC Leitfäden und Standards<br /></a>

     </font>
</div>

<?php

} else {
    echo "Konfigurations- Fehler. Konfirguration der <b>Module</b> neu aufsetzen. <br>";
}

echo "</div>";
echo "L 0201<br>";
HTML_trailer();
?>


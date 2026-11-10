<?php
/**
 * Menu zur Mitglieder-  Verwaltung
 *
 */

$this->layout('layout', ['title' => $title, 'systemBasePath' => $systemBasePath, 'cssBundles' => $cssBundles]) ?>

<div class="Menu-Header">Auswahl der Verwaltungs- Programme</div>

<?php 
if (!empty($can['mitgl_verw'])) {  // ADM-MI
    ?>
    <div class="Menu-Separator">Mitglieder- Verwaltung</div>
    <div class="Menu-Line">
        Pflege der Mitglieder- Daten
    </div>
    <div class="Menu-Line">
        <a href='MitglList.php' target='Mitgl'>Mitglieder- Verwaltung </a>
    </div>
    
    <div class="Menu-Line">
        Neue Mitglieder Akzeptieren
    </div>
    <div class="Menu-Line">
        <a href='MitglAktivierung.php' target='Mitgl'>Mitglieder- Aktivierung </a>
    </div>
    
    <div class="Menu-Separator">Ehrungen- Verwaltung</div>
    <div class="Menu-Line">
        Pflege der Mitglieder- Ehrungs- Daten
    </div>
    <div class="Menu-Line">
        <a href='MitglEhrgList.php' target='MitglEhrg'>Mitglieder- Ehrungen- Verwaltung </a>
    </div>
    
    <div class="Menu-Separator">Unterstützer- Verwaltung</div>
    <div class="Menu-Line">
        Pflege der Mitglieder- Daten
    </div>
    <div class="Menu-Line">
        <a href='UnterstList.php' target='Unterst'>Unterstützer- Verwaltung </a>
    </div>
    
<?php 
}

if (!empty($can['mitgl_zahlung'])) {  // ADM-MB
?>
    <div class="Menu-Separator">Mitglieder- Zahlungseingangs- Verwaltung</div>
    <div class="Menu-Line">
        Hier werden die Zahlungseingänge (Mitgliedsbeitrag und ABO- Gebühr verwaltet)
    </div>
    <div class="Menu-Line">
        <a href='MitglBezList.php' target='Unterst'>Mitglieds- Beitrags- Verwaltung </a>
    </div>
<?php 
}   

?>
<div class="Menu-Separator" style="margin-top: 48px;">
    <a href="../../logout.php">LOGOFF (HomePage)</a>
</div>

<?php 
/*
echo "<div class='Menu-Separator'>Mitglieder- E-Mail an</div>";

echo "<div class='Menu-Line'>"; // Beginn der Einheit Ausgabe
echo "Mitglieder können E-Mails an andere Mitglieder senden, ohne das Sie die E-Mail Adresse kennen.</a>";
echo "  </div>";  // Ende Feldname

echo "<div class='Menu-Line'>"; // Beginn der Einheit Ausgabe
echo "<a href='MitglEMail.php' target='M-Mail'>Mail an andere Mitglieder senden </a>";
echo "  </div>";  // Ende Feldname

echo "<div class='Menu-Separator'>Mitglieder- Auskuft laut DSVGO</div>";

echo "<div class='Menu-Line'>"; // Beginn der Einheit Ausgabe
echo "<tr><td>Jedes Mitglied kann sich die im System gespeicherten persönliche Daten entsprechend der DSVGO selbst anfordern und bekommt sie sofort per E-Mail zugeschickt.</td></tr>";
echo "  </div>";  // Ende Feldname

echo "<div class='Menu-Line'>"; // Beginn der Einheit Ausgabe
echo "<tr><td><a href='DsvgoMiMail.php' target='M-Datenabfrage' onclick='window.open(this.href, this.target); return false;'>Mitglieder-Daten Auskunft laut DSGVO</a></td></tr>";
echo "  </div>";  // Ende Feldname
*/

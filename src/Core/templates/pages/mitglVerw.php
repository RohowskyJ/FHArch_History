<?php
/**
 * Menu zur Mitglieder-  Verwaltung
 *
 */

$this->layout('layout', ['title' => $title, 'path2ROOT' => $path2ROOT, 'cssBundles' => $cssBundles]) ?>

<div class="Menu-Header">Auswahl der Verwaltungs- Programme</div>

<?php 
if (userHasRole('ADM-MI')) {  // Ist benutzer berechtigt?
    ?>
    <div class="Menu-Separator">Mitglieder- Verwaltung</div>
    <div class="Menu-Line">
        Pflege der Mitglieder- Daten
    </div>
    <div class="Menu-Line">
        <a href='MitglList.php' target='Mitgl'>Mitglieder- Verwaltung </a>
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

if (userHasRole('ADM-MB')) {  // Ist Benutzer berechtigt?
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
################## old values


echo "<div class='Menu-Separator'>Mitglieder- E-Mail an</div>";

echo "<div class='Menu-Line'>"; // Beginn der Einheit Ausgabe
echo "Mitglieder können E-Mails an andere Mitglieder senden, ohne das Sie die E-Mail Adresse kennen.</a>";
echo "  </div>";  // Ende Feldname

echo "<div class='Menu-Line'>"; // Beginn der Einheit Ausgabe
echo "<a href='VF_M_Mail.php' target='M-Mail'>Mail an andere Mitglieder senden </a>";
echo "  </div>";  // Ende Feldname

echo "<div class='Menu-Separator'>Mitglieder- Auskuft laut DSVGO</div>";

echo "<div class='Menu-Line'>"; // Beginn der Einheit Ausgabe
echo "<tr><td>Jedes Mitglied kann sich die im System gespeicherten persönliche Daten entsprechend der DSVGO selbst anfordern und bekommt sie sofort per E-Mail zugeschickt.</td></tr>";
echo "  </div>";  // Ende Feldname

echo "<div class='Menu-Line'>"; // Beginn der Einheit Ausgabe
echo "<tr><td><a href='VF_M_yellow.php' target='M-Datenabfrage'>Mitglieder-Daten Auskunft laut DSGVO</a></td></tr>";
echo "  </div>";  // Ende Feldname

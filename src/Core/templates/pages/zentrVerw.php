<?php 
/** 
 * Menu zur Zentralen (allgemeinen) Verwaltung
 * 
 */

$this->layout('layout', ['title' => $title, 'path2ROOT' => $path2ROOT, 'cssBundles' => $cssBundles]) ?>

<div class="Menu-Header">Auswahl der Verwaltungs- Programme</div>

<div class="Menu-Separator">Mitglieder- Verwaltung</div>

<div class="Menu-Line">
    Verwaltung der Mitglieder, Zahlungseingang und Kontrolle, Mitteilung der gespeicherten Daten nach DSGVO, E-Mail an andere Mitglieder ohne Kenntnis deren Adresse
</div>
<div class="Menu-Line">
    <a href='../Modules/Mitglieder/MitglVerw.php' target='M-Verwaltung'>Mitgliederverwaltung</a>
</div>

<?php 
if (userHasRole('ADM-MA')) {  // Ist benutzer berechtigt?
?>
    <div class="Menu-Separator">Eigentümerverwaltung </div>
    <div class="Menu-Line">
        Da hier auch Daten von Nicht-Mitgliedern aufgenommen werden können, ist eine eigene Verwaltung ohne Mitglieder-Bezug notwendig
    </div>
    <div class="Menu-Line">
       <a href='VF_Z_E_List.php' target='Eigentm'>Eigentümerverwaltung </a>
    </div>
   
    <div class="Menu-Separator">Liste der Empfänger von administrativen E-Mails (Mitglieds- Neuanmeldung, Bezahlung, ... </div>
    <!-- 
    <div class="Menu-Line">
       
    </div>
     -->
    <div class="Menu-Line">
        <a href='../AllgVerw/AdmEmailList.php' target='Mail_List'>Empfänger der automatischen E-Mails</a>
    </div>
    
<?php 
}
 
if (userHasRole('ADM-MI')) {  // Ist benutzer berechtigt?
    ?>
    <div class="Menu-Separator">Benutzer- und Zugriffsverwaltung</div>
    <div class="Menu-Line">
        Pflege der berechtigten Benutzer, Passworte und Berechtigungen.
    </div>
    <div class="Menu-Line">
        <a href='../Auth/BenList.php' target='Benutz'>Benutzer- und Zugriffs- Verwaltung </a>
    </div>
<?php 
}
?>

<div class="Menu-Separator">Firmen (Fzg/Gerät - Hersteller/Aufbauer)</div>
<div class="Menu-Line">
    Liste Fahrzeug- und Geräte- Hersteller und Aufbauer
</div>
<div class="Menu-Line">
    <a href='../AllgVerw/FirmenList.php' target='Config'>Firmen</a>
</div>

<div class="Menu-Separator">Abkürzungen </div>
<div class="Menu-Line">
    Abkürzungen im Fahrzeug- Gerätebereich
</div>
<div class="Menu-Line">
    <a href='../AllgVerw/AbkuerzList.php' target='Config'>Abkürzungen</a>
</div>

<?php if (userHasRole('ADM-ALLE')) {  // Ist benutzer berechtigt? ?> 
    
    <div class="Menu-Separator">Konfiguration der Seite</div>
    <div class="Menu-Line">
        Betreiber der Seite, Vereinsregister, E-Mail-Adresse,
    </div>
    <div class="Menu-Line">
        <a href='../AllgVerw/ConfEdit.php' target='Config'>Konfigurations- Parameter der URL</a>
    </div>
    
    <div class="Menu-Separator">Prozesse, die zu Analysen und Korrekturen dienen, aber unter Umständen vorher geändert/angepasst werden müssen.</div>
    <div class="Menu-Line">
        Pflege verschiedener Daten 
    </div>
    <div class="Menu-Line">
        <a href='VF_Z_Suchb_Gen.php' target='suchbegr'>Suchbegriffe (Findbücher) regenerieren </a><br>
        <a href='VF_Z_Pict_Valid.php' target='Bilder Prüfg'>Bilder- Prüfung (Tabellen - Dirs / vorhanden - nicht vorhanden)</a><br>
        <a href='VF_Z_AR_Renum_AN.php?ei_id=1' target='ArchNr-Renum'>Archiv- Nummern Renum Eig=1 (Verein)</a><br>
        <a href='VF_Z_AR_Renum_AN.php?ei_id=21' target='ArchNr-Renum'>Archiv- Nummern Renum Eig=21 (FF WrNdf)</a><br>
    </div>

    <div class="Menu-Separator">Daten von CSV-Datei in Tabellen einlesen:</div>
    <div class="Menu-Line">
        Dateiformat:<br>
        1. Zeile: Tabellen- Name, z.B.: Test_tab<br>
        2. Zeile: fld_nam1|fld-nam2| ....<br>
        ab der 3. Zeile: Inhalte, z.B.: inh1|inh2| ...
        
    <div class="Menu-Line">
        <a href='VF_Z_DS_2_Table.php' target='Flat-File Imp'>FlatFile Import in eine Tabelle</a>
    </div>
 
    <div class="Menu-Separator">Datenbank- Tabellen Exportieren und Importieren:</div>
    <div class="Menu-Line">
        
    </div>
    <div class="Menu-Line">
        <a href='VF_Z_DB_backup.php' target='DB_BU'>Datenbank Sichern und wieder Herstellen</a>
    </div>

    <div class="Menu-Separator">Sitzungs- Protokolle</div>
    <div class="Menu-Line">Protokolle, ....
    </div>
    <div class="Menu-Line">
        <a href='VF_P_RO_List.php' target='P-Verwaltung'>Liste der Protokolle</a>
    </div>
<?php } ?>

<div class="Menu-Separator">PMitglieder- E-Mail an </div>
<div class="Menu-Line">
    Mitglieder können E-Mails an andere Mitglieder senden, ohne das Sie die E-Mail Adresse kennen
</div>
<div class="Menu-Line">
    <a href='VF_M_Mail.php' target='M-Mail'>Mail an andere Mitglieder senden </a>
</div>

<div class="Menu-Separator">Mitglieder- Auskuft laut DSVGO</div>
<div class="Menu-Line">
    Jedes Mitglied kann sich die im System gespeicherten persönlichne Daten entsprechend der DSVGO selbst anfordern und bekommt sie sofort per E-Mail zugeschickt
</div>
<div class="Menu-Line">
    <a href='VF_M_yellow.php' target='M-Mail'>DSVGO Information abrufen  </a>
</div>

<div class="Menu-Separator">Benutzer- Auskuft laut DSVGO</div>
<div class="Menu-Line">
    Jeder Benutzer kann sich die im System gespeicherten persönlichne Daten entsprechend der DSVGO selbst anfordern und bekommt sie sofort per E-Mail zugeschickt
</div>
<div class="Menu-Line">
    <a href='VF_B_yellow.php' target='M-Mail'>DSVGO Information abrufen </a>
</div>

<div class="Menu-Line">
    <a href="../../../public/logoff.php">LOGOFF (HomePage)</a>
</div>

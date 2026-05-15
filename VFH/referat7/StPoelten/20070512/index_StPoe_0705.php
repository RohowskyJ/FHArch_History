<?php
session_start();
# ------------------------------------------------------------------------------------------------------------------
#
#Inhaltsverzeichnis und Aufruf für Archivierte Berichte


$module    = 'VF_Arch';
$path2ROOT = "../../../../";

$debug = True; $debug = False;  // Debug output Ein/Aus Schalter

require $path2ROOT.'login/common/BA_HTML_Funcs.lib.php' ;  // Diverse Unterprogramme
 require $path2ROOT.'login/common/BA_Funcs.lib.php' ;  // Diverse Unterprogramme

initial_debug(); # Wenn $debug=true - Ausgabe von Debug Informationen: $_POST, $_GET, $_FILES

$logo = 'JA';
$header = "<link  href='".$path2ROOT."login/common/css/frame_pict.css' rel='stylesheet' type='text/css'>";
BA_HTML_header('Verein Feuerwehrhistoriker in NÖ) ',$header,'Form','75em'); # Parm: Titel,Subtitel,HeaderLine,Type,width

?>

<body class="w3-container ">
<div class="w3-content ">  <!-- max-width:45em; margin:5em;--->
<fieldset>
<h1>140 Jahrfeier der FF St. Pölten Stadt mit einem Umzug mit historischem Gerät</h1>

<img src="ARVF-20070512-pict0285_s.jpg" alt="Kastenspritze" align="right">
<p>Am 12. Mai 2007 fand der Umzug mit historischem Gerät statt.
Am Umzug nahmen ca. 80 Gruppen teil, vom handgezogenen Karren oder Schlauchwagen über
diverse Handpumpen, Dampfspritzen, Anfang der Motorisierung bis hin zum modernen Gerät war alles vertreten.
</p>

<p>Quellen: Foto: Wolfgang Svejcar
</p>


</fieldset>
</div>
<?php 
 BA_HTML_trailer();
 ?>

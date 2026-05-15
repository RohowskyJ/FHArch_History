<?php
session_start();
# ------------------------------------------------------------------------------------------------------------------
#
#Inhaltsverzeichnis und Aufruf für Archivierte Berichte


$module    = 'VF_Arch';
$path2ROOT = "../../../../../";

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
<p>Der Feuerwehrtag dws Jahres 2011 fand an 17.Oktober im Marmorsaal des Stift Geras statt.</p>

<p>Die Hauptvorträge waren:
</p>
<ul compact>
 <li>
  <a href="Friedrich_Ludwig_Jahn.pdf" target="Geras">Friedrich Ludwig Jahn - Aus der Sicht der Turner, Fr. Mag. Elke Nebenführ</a>
  </li>
 <li>
  <a href="Gruenderzeit_FW_Gilbert.pdf" target="Geras">Die Gründerzeit der Feuerwehren, Hans Gilbert Müller</a>
  </li>
 <li>
  <a href="Schoenerer_Moll.pdf" target="Geras">Georg Ritter von Schönerer, Friedel Rainer Moll</a>
  </li>
 <li>
  <a href="TF_Geras_gruendg_Mueck.pdf" target="Geras">Turnerfeuerwehr Geras, Anton Mück</a>
  </li>
</ul>
<p>Berichte: </p>
<ul compact>
<li>
<a href="hist_stammtisch.html" target="Geras">Historiker- Stammtisch (Vorabend)</a>
</li>
<li>
<a href="historikertag.html" target="Geras">Historikertag</a>
</li>
<li>
<a href="2011_eimer_katschutz/eimer_katschutz.html" target="Geras">Die Ausstellung</a>
</li>
</ul>

<div class='w3-table' style='margin=auto'>

<table>
<tbody>


</tbody>
     </table>
<p>Quellen: Texte und Fotos: Josef Rohowsky
</p>
     </div>


</fieldset>
</div>
<?php 
 BA_HTML_trailer();
 ?>

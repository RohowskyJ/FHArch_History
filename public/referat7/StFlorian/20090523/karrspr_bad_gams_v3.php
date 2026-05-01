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


<div class='w3-table' style='margin=auto'>

<table>
<tbody>
<tr>
 <th>
Karrenspritze ohne Saugwerk, Bad Gams, Steiermark
  </th>
</tr>

<tr>
 <td>
  <img src="../../../../login/AOrd_Verz/124/09/06/20090523/124-20090523-W-IMG_9192.JPG" alt="Karrenspritze ohne Saugwerk, Löschgruppe" align="right"/>
Zur nachbarlichen Hilfeleistung ist sogar eine Löschmannschaft aus der Steiermark angerückt. Sie steht unter der Leitung des Kameraden Alois GRITSCH und zeigt und einen Löscheinsatz mit einer Knaust-Karrenspritze
ohne Saugwerk, abprotzbar, Baujahr 1908, und eine Metallene Krückenspritze.
 <img src="../../../../login/AOrd_Verz/124/09/06/20090523/124-20090523-W-IMG_9188.JPG" alt="Karrenspritze ohne Saugwerk, Löschgruppe" align="left"/>
 Im folgenden Bild  links der Karren und rechts die abgeprotzte Spritze zu sehen.
 <img src="../../../../login/AOrd_Verz/124/09/06/20090523/124-20090523-W-IMG_9196.JPG" alt="Karrenspritze ohne Saugwerk, Löschgruppe" align="right"/>
  </td>
</tr>


</tbody>
     </table>
<p>Quellen: Texte zur Präsentation: Dr. jur. Alfred Zeilmayr, Fotos: Josef Rohowsky
</p>
     </div>


</fieldset>
</div>
<?php 
 BA_HTML_trailer();
 ?>

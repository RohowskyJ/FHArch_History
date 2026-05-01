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
Dampffeuerspritze
mit Pferdezug
<br/>
(1902 - 1947)
<br/>
FF Vöcklabruck
  </th>
</tr>

<tr>
 <td>
   <img src="../../../../login/AOrd_Verz/124/09/06/20070512/124-20070512-W-IMG_5815.JPG" alt="Dampffeuerspritze" align="right" />
Die Menschenkraft beim Betrieb von Feuerspritzen wurde ab 1828 (England!)
durch die Dampfmaschine langsam abgelöst, in Österreich erst ab 1867
( Firma KNAUST).
Pferdezug, vereinzelt wird aber auch Dampfkraft für den Fahrbetrieb verwendet!
<br/>
Leistungen: von 500 bis 1800 l/min, Gewicht um die 1.500 kg,
<br/>
Sonderkonstruktionen 4.500 - 5.000 l/min, aber mit Eigengewicht 5.500 kg.
<br/>
   <img src="../../../../login/AOrd_Verz/124/09/06/20070512/124-20070512-W-IMG_5817.JPG" alt="Dampffeuerspritze" align="left" />
Aus Einsatzberichten wissen wir, dass die erreichten Fahrgeschwindigkeiten
der Pferde gezogenen Dampffeuerpritzen um die 7,7 km/h betragen haben.
<br/>
Probleme bereitetet aber die Betriebsbereitschaft, da es
mindestens 10 Minuten dauert, bis der notwendige Dampfdruck
erreicht wird. Zwischenbehelf waren zusätzliche Handdruckspritze oder
"Stützfeuer" unter dem Kessel oder Druckgas!
<br/>


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

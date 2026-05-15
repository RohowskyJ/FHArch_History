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
   <img src="../../../../login/AOrd_Verz/124/09/06/20090523/124-20090523-W-IMG_img_9237.JPG" alt="Dampffeuerspritze" align="right" />
Die Menschenkraft beim Betrieb von Feuerspritzen wurde ab 1828 (England!)
durch die Dampfmaschine langsam abgelöst, in Österreich erst ab 1867
( Firma KNAUST).
Pferdezug, vereinzelt wird aber auch Dampfkraft für den Fahrbetrieb verwendet!
<br/>
Leistungen: von 500 bis 1800 l/min, Gewicht um die 1.500 kg,
<br/>
Sonderkonstruktionen 4.500 - 5.000 l/min, aber mit Eigengewicht 5.500 kg.
<br/>
   <img src="../../../../login/AOrd_Verz/124/09/06/20090523/124-20090523-W-IMG_9241.JPG" alt="Dampffeuerspritze" align="left" />
Aus Einsatzberichten wissen wir, dass die erreichten Fahrgeschwindigkeiten
der Pferde gezogenen Dampffeuerpritzen um die 7,7 km/h betragen haben.
<br/>
Probleme bereitetet aber die Betriebsbereitschaft, da es
mindestens 10 Minuten dauert, bis der notwendige Dampfdruck
erreicht wird. Zwischenbehelf waren zusätzliche Handdruckspritze oder
"Stützfeuer" unter dem Kessel oder Druckgas!
<br/>

<table summary="Statistik"   border="1">
<tr>
 <th align=right>
Statistik Ende Dezember 1886:
  </th>
   <td>
6 Dampffeuerspritzen
</td>
</tr>
<tr>
 <th align=right>
Statistik 1. Jänner 1914:
  </th>
   <td>
28 Dampffeuerspritzen
</td>
</tr>
<tr>
 <th align=right>
Statistik 1933:
  </th>
   <td>
noch 20 Dampffeuerspritzen vorhanden!
</td>
</tr>
</table>


"Kriegseinsätze" von Dampffeuerspritzen wegen des Treibstoffmangels!
<br/>
Das heute von den Kameraden der FF VÖCKLABRUCK vorgeführte
Gerät stammt aus der Produktion KERNREUTER, Wien.
<br/>
Leistung
ca. 900 l/min, Beschaffung 1902 (Kosten 6.530 Kronen),
<br/>
Ersteinsatz war am
6. September 1902 in ATTNANG-PUCHHEIM / Außerdienststellung 1947 /
Restaurierung ab 1988.
<br/>
<br/>
Haben alle den Hornisten erkannt?
<br/>
   <img src="../../../../login/AOrd_Verz/124/09/06/20070512/124-20070512-img_5821.JPG" alt="Dampffeuerspritze"  />
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

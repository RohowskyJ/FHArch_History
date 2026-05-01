<?php
session_start();
# ------------------------------------------------------------------------------------------------------------------
#
#Inhaltsverzeichnis und Aufruf für Archivierte Berichte


$module    = 'VF_Arch';
$path2ROOT = "../../../../";

$debug = True; $debug = False;  // Debug output Ein/Aus Schalter

require $path2ROOT.'login/common/BA_HTML_Funcs.lib.php' ;  // Diverse Unterprogramme
require $path2ROOT.'login/common/BA_Funcs.lib.php' ;  // Diverse UnterprogrammeJ

initial_debug(); # Wenn $debug=true - Ausgabe von Debug Informationen: $_POST, $_GET, $_FILES

$VF_logo = 'JA';
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
Löschfahrzeug ÖAF
mit Anhängespritze
<br/>
(1927 - 1964)
<br/>
Oldtimerverein FF Wels
  </th>
</tr>

<tr>
 <td>
   <img src="../../../../login/AOrd_Verz/124/09/06/20090523/124-20090523-W-IMG_9256.JPG" alt="Löschfahrzeug ÖAF mit Anhängespritze" align="right" />
Nicht jedes Feuerwehrfahrzeug war mit einer eingebauten Feuerlöschpumpe
ausgestattet. Vielfach wurden für schon vorhandene Anhängespritzen
Zugfahrzeuge beschafft, die dann als MTF und Geräteträger fungierten.
<br/>
So auch der hier vorgeführte Austro-Fiat, Type AFN 36, Feuerwehraufbau
Firma Zellinger, Linz, Baujahr 1927, Viertakt-Vierzylinder-Vergasermotor
mit 36 PS, Bauartgeschwindigkeit ~ 50 km/h, Gesamtgewicht 4.200 kg,
ursprünglich mit Anhä;ngespritze Knaust (Baujahr 1923 / 1250 l/min bei 8 bar).
<br/>
   <img src="../../../../login/AOrd_Verz/124/09/06/20090523/124-20090523-W-IMG_9259.JPG" alt="Löschfahrzeug ÖAF mit Anhägespritze" align="left" />
Einsatz, auch Überland, daher "Landtrain" genannt, in Verwendung
<br/>
1927 - 1958 bei der FF Wels,
<br/>
1958 - 1964 bei der Freiwilligen Betriebsfeuerwehr Fritsch, Wels,
<br/>
dann als Oldtimer.
<br/>
In Betrieb genommen wird die zweirädrige Motorspritze, Fabrikat Rosenbauer,
Viertakt-Vierzylinder-Vergasermotor Fiat mit 22 PS, FP Rosenbauer F 70,
Leistung 700 l/min bei 7 bar

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

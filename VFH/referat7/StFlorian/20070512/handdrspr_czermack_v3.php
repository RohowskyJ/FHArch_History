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
Handdruckspritze der
Firma CZERMACK,
mit Saugwerk, im Handzug
<br/>
(19. Jh. - Mitte 20. Jh.)
<br/>
FF Marchtrenk

  </th>
</tr>

<tr>
 <td>
   <img src="../../../../login/AOrd_Verz/124/09/06/20070512/124-20070512-W-IMG_5798.JPG" alt="Handdruckspritze Czermack" align="right" />
Erst die Erfindung des Saugschlauches, die erste Abbildung stammt aus 1724,
erlaubt die "direkte" Wasserentnahme aus einer Wasserstelle.
Leistungen der Handdruckspritzen: 120 bis 300 l/min, Spritzweiten horizontal
von 15 bis ca. 30 m, Bedienungspersonal 6 bis 14 Personen.
<br/>
Die Bedienung der Handdruckspritzen war sehr personalintensiv,
zur Ablösung muss vielfach neben dem eigenen Feuerwehrpersonal
auch die Bevölkerung und das Militär herangezogen werden!
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
   <img src="../../../../login/AOrd_Verz/124/09/06/20070512/124-20070512-W-IMG_5801.JPG" alt="Handdruckspritze Czermack" align="left" />
Zur Versorgung der in der Regel unmittelbar vor Ort aufgestellten
Handdruckspritzen werden später auch eigene Wasserzubringer (Hydrophore)
eingesetzt. Diese Feuerlöschpumpen, die  aber auch zur direkten
Brandbekämpfung eingesetzt werden können, bringen mit
Pumpmannschaften von 8 - 14 Personen eine Förderleistung bis
über 300  l/min.
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

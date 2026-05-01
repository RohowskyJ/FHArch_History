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
Anhängespritze LINZ III
mit Traktor Lanz-Bulldog
<br/>
(1925 - unbekannt)
<br/>
Oldtimerverein FF Wels
  </th>
</tr>

<tr>
 <td>
   <img src="../../../../login/AOrd_Verz/124/09/06/20070512/124-20070512-W-IMG_5831.JPG" alt="Landfahrmotorspritze" align="right" />
Ab dem  29. Oktober 1910  erste Benzinmotorspritze Österreichs, das "Dreigerät",
Fabrikat Rosenbauer.  Bei der k.k. Staatsbahnfeuerwehr LINZ  werden
die Dampffeuerspritzen und Handdruckspritzen durch Motorspritzen, bei denen
sich der Benzinmotor durchgesetzt hat, abgelöst!
Auf Vierrad- oder Zweiradfahrgestellen, da wegen ihres Gewichtes
noch nicht tragbar! TS erst ab 1923/1925!

Die hier gezeigte Landfahrmotorspritze mit zweirädrigem, sechssitzigem
Vorderwagen ist für Pferdebespannung aber auch für Kfz-Zug geeignet.
Früher bei der Betriebsfeuerwehr Haunoldmühle in Verwendung, Ankauf
September 2002 durch den Oldtimerverein der FF Wels, Restaurierung in den
Jahren 2003 / 2004.
<br/>
   <img src="../../../../login/AOrd_Verz/124/09/06/20070512/124-20070512-W-IMG_5827.JPG" alt="Landfahrmotorspritze" align="left" />
Erzeuger war die  Feuerlöschgeräte- und Spritzenfabrik Konrad Rosenbauer
in Linz, Baujahr 1925, Vergasermotor Viertakt-Vierzylinder STEUDEL,
Kamenz/ Sachsen, Motorleistung 16 KW (22 PS) bei 2200 U/min;
Feuerlöschpumpe Rosenbauer, Type F 90, Leistung 700 l/min bei 7 bar.
<br/>
Unser Zugfahrzeug heute ist ein Oldtimer-Traktor, Fabrikat Lanz-Bulldog,
Baujahr 1940, 55 PS, beigestellt von Herrn Siegfried ACHLEITNER,
Ried im Traunkreis, der auch selbst fährt!

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

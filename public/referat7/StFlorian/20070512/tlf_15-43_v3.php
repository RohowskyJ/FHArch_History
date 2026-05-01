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
TLF 15/43
<br/>
(1944 - 1975)
<br/>
Im Bestand des OÖ.
Feuerwehrmuseums,
betreut durch <br/>Oldtimerverein der FF Wels
  </th>
</tr>

<tr>
 <td>
   <img src="../../../../login/AOrd_Verz/124/09/06/20070512/124-20070512-W-IMG_5850.JPG" alt="" align="right" />
Tanklöschfahrzeuge haben vor dem Zweiten Weltkrieg nicht zu den
Standardfahrzeugen der Feuerwehren gehört: Aufgrund der Erfahrungen aus den
Bombenangriffen auf die deutschen Städte mit den vielen Großbränden und
sogar Feuerstürmen - vielfach zerstörte Wasserleitungen - musste dringend
ein neuer Fahrzeugtyp, das Tanklöschfahrzeug, geschaffen werden. das OÖ
Feuerwehrmuseum kann ihnen die beiden Typen zeigen, in der Halle
die Tankspritze TS 25 (ab 1943 TLF 25) auf Fahrgestell Henschel und
in der Vorführung die TS 15 (ab 1943 TLF 15):
<br/>
Fahrgestell Opel-Blitz 3,6-6700 mit Straßen- oder Allradantrieb, Aufbau
KHD-Magirus, Ulm,  Viertakt-Sechszylinder-Vergasermotor mit (gedrosselten
68 PS, Bauartgeschwindigkeit 80 km/h, Gesamtgewicht 6.700 kg,
Baujahr 1944, Löschwassertank 2.500 l, FP 515, Leistung 1.500 l/min bei
8 bar, mit Schnellangriffseinrichtung, Besatzung 2 Personen.
<br/>
Eingesetzt:
<br/>
1944 - 1945 bei 2. Luftschutzabteilung mot. 17, einer Luftwaffeneinheit, dann
<br/>
1945 - 1975  bei der FF der Stadt Wels,
<br/>
ab 1975  bei der FF St. Pankraz
<br/>
Dieses TLF, heute in den Originalzustand zurückgebaut, war ab 1950 auch
Träger der ersten HD-Pumpe der Fa. Rosenbauer, ebenfalls im Museum zu sehen!
<br/>
Erzeugt wurden 1943-1945: 850 TLF 15 und 794 TLF 25 = 1.644 Einheiten

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

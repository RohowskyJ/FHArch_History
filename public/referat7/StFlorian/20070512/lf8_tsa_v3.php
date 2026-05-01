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
LF 8
mit TSA
<br/>
(1943 - 1982)
<br/>
Oldtimerverein FF Wels
  </th>
</tr>

<tr>
 <td>
   <img src="../../../../login/AOrd_Verz/124/09/06/20070512/124-20070512-W-IMG_5840.JPG" alt="LF( mit TSA" align="right" />
Während die Feuerschutzpolizeien, wie die früheren Berufsfeuerwehren
in der Zeit 1938 - 1945 bezeichnet wurden, überwiegend mit dem Fahrzeugtypen
Schweres Löschgruppenfahrzeug (SLG) und Großes
Löschgruppenfahrzeug (GLG) -
ab 1943 LF 15 bzw. LF 25 benannt - ausgerüstet wurden, war das
Leichte Löschgruppenfahrzeug (LLG) - ab 1943 LF 8 benannt - das
Standardfahrzeug für die FF in den OÖ. Städten und Märkten. Alle diese
Fahrzeuge haben jetzt einen geschlossenen Aufbau (Wende: ab den 30er
Jahren wegen heftiger Kältejahre!).
<br/>
Wegen der zu geringen Nutzlast musste die FP - jetzt eine
Einheits-Tragkraftspritze TS 8 - Leistung 800 l/min bei 8 bar - mit den Saugschläuchen,
einem Teil der Druckschläuche, Armaturen etc. in einem TSA
(Gesamtgewicht 995 kg) mitgeführt werden. Mannschaft (Löschgruppe 1:8) und Rest der
Ausrüstung im LF.
<br/>
Fahrgestell DB L 1500 S, Ausführung F, Viertakt-Sechszylinder-Vergasermotor
mit 60 PS, Bauartgeschwindigkeit 84 km/h, Gesamtgewicht 3.900 kg,
Baujahr 1942, eingesetzt 1943 - 1982 bei der FF Kufstein,
Tirol, Ankauf durch Oldtimerverein 1989.
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

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
Handdruckspritze
mit Saugwerk,
mit Pferdezug
<br/>
(19. Jh. - Mitte 20. Jh.)

FF Höft

  </th>
</tr>

<tr>
 <td>
   <img src="../../../../login/AOrd_Verz/124/09/06/20070512/124-20070512-W-IMG_5804.JPG" alt="Handdruckspritze mit Saugwerk" align="right" />
Der Transport der Feuerlöschgeräte geschah früher grundsätzlich
händisch, für vierrädrige Mannschaftswagen, Fahrspritzen
und Wasserwagen war jedoch Vorspann mit Pferden notwendig: Beistellung auf dem
flachen Land durch
die Bauern, in den Märkten und Städten durch die Fuhrleute. "Auslobung"
von Prämien für zuerst Eintreffende zur Hebung der Motivation:
der Erste erhält einen Gulden!
<br/>
"Eigene" Pferde gibt es nur bei den großen Berufsfeuerwehren oder sehr großen
FF mit ständiger Wache wie FF LINZ.
<br/>
<br/>
   <img src="../../../../login/AOrd_Verz/124/09/06/20070512/124-20070512-W-IMG_5807.JPG" alt="Handdruckspritze mit Saugwerk" align="left" />
Die heute benötigten vier Pferde hat uns Herr Adolf PRAMENDORFER
aus Hofkirchen an der Trattnach, Bezirk Grieskirchen, beigestellt.
Herr PRAMENDORFER hat früher die Furthmühle in Ruhringsdorf
betrieben, heute sind er und seine Tochter Monika leidenschaftliche Kutschenfahrer.
<br/>
Aus Einsatzberichten ist bekannt, dass die erreichten Fahrgeschwindigkeiten
der Pferde gezogenen Fahrspritzen um die 12 km/h betragen haben. Weite
Überlandeinsätze der Feuerwehren mussten daher mittels Eisenbahntransport
erfolgen!
<br/>

<table summary="Statistik"   border="1">
<tr>
 <th align=right>
Statistik Ende Dezember 1886:
  </th>
   <td>
240 Spritzen mit Saugwerk, 144 Spritzen ohne
Saugwerk, 196 kleinere Tragspritzen.
</td>
</tr>
<tr>
 <th align=right>
Statistik 1. Jänner 1914:
  </th>
 <td>
1.165 Spritzen mit Saugwerk, 130 Spritzen ohne
 Saugwerk, 709 kleinere Tragspritzen.
  </td>
</tr>
<tr>
<th align=right>
Statistik 1954:
 </th>
 <td>
Noch immer 673 Handdruckspritzen vorhanden.
  </td>
</tr>
</table>
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

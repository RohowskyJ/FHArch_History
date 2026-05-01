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
<h1>Florianitag der
<a href="http://www.ff-perchtoldsdorf.at/" target=_new>FF Perchtoldsdorf</a>
, 3. und 4. Mai 2008</h1>

<p>Am Samstag, den 3. und Sonntag, den 4. Mai fand in Perchtoldsdorf der Florianitag 2008 statt. In Zusammenarbeit mit der
Berufsfeuerwehr Wien fand am Samstag eine Fahrzeugausstellung "Alt und Neu" statt. Eine Übung beendete die Vorführungen.
Am Sonntag wurde bei einer Feldmesse vor dem Feuerwehrhaus das neue Tunnell&ouml;schfahrzeug (TLFA-T 4000/200) geweiht.
</p>

Einige Bilder vom Samstag, 3.5.2008:

<div class='w3-table' style='margin=auto'>

<table>
<tbody>
 <tr>
  <th colspan="2">Alt und Neu, friedlich vereint. Im Vordergrung die "Fahrspritze der FF Hetzendorf"</th>
   <th>
<img src="../../../../login/AOrd_Verz/124/09/06/20080503/124-20080503-W-IMG_7305.JPG" alt="Fahrspritze Hetzendorf" width="350px">
</th>
 </tr>  

 <tr>
  <th>
<img src="../../../../login/AOrd_Verz/124/09/06/20080503/124-20080503-W-IMG_7328.JPG" alt="Handdruck- Dapfspritze, BJ. 1900" width="350px">
</th>
   <th colspan="2">Die kombinierte Handdruck- Dampfspritze der FF Perchtoldsdorf, Baujahr 1900, Kernreuther Wien.<br>
    Im Handbetrieb, bis genug Dampfdruck im Kessel war, dann wurde auf Dampfantrieb umgestellt.

</th>
 </tr>

 <tr>
  <th colspan="2">Betrieb mit Publikumsbeteiligung ...
      </th>
   <th>
<img src="../../../../login/AOrd_Verz/124/09/06/20080503/124-20080503-W-IMG_7343.JPG" alt="Publikumsbeteiligung" width="350px">
</th>
 </tr>

 <tr>
  <th>
<img src="../../../../login/AOrd_Verz/124/09/06/20080503/124-20080503-W-IMG_7372.JPG" alt="Dampfbetrieb" width="350px">
</th>
   <th colspan="2">Diese Dampfspritzen waren bis in den Zweiten Weltkrieg in Betrieb,
    da die gummibereiften Fahrzeuge bei Brandbomben (Phosphorbomben) nicht eingesetzt werden konnten.
</th>
 </tr>

 <tr>
  <th colspan="2">Die erste "Auto-Gasspritze", Baujahr 1902, ein Elektroauto<br>
     mit eigener Kraft unterwegs!
</th>
   <th>
<img src="../../../../login/AOrd_Verz/124/09/06/20080503/124-20080503-W-IMG_7384.JPG" alt="Gasspritze auf Elektroauto" width="350px">
</th>
 </tr>

 <tr>    
  <th>
<img src="../../../../login/AOrd_Verz/124/09/06/20080503/124-20080503-W-IMG_7387.JPG" alt="Steyr 380" width="350px">
</th>
   <th colspan="2">Tankl&ouml;schfahrzeug auf Steyr 380, Baujahr 1958, im Dienst bis 1969<br><br>
     im Bild mit dem Hochdruckrohr in Betrieb.
</th>
 </tr>

 <tr>
  <th colspan="2">&Uuml;bungsannahme: Verkehrsunfall PKW gegen Autobus, mehrere Verletze,
      Rettung der Verletzten im Bus mit Hilfe der Rettungsb&uuml;hne f&uuml;r LKW.
     <br/><br/>
      Beim PKW ist im Motorraum ein Feuer ausgebrochen, das innere des Busses ist verqualmt,
      Der Bus steht zu knapp neben der Wand, die Personen im Bus k&ouml;nnen nur
      &uuml;ber die Seitenscheiben gerettet werden.
    </th>
   <th>
<img src="../../../../login/AOrd_Verz/124/09/06/20080503/124-20080503-W-IMG_7414.JPG" alt="PKW gegen Bus" width="350px">
</th>
 </tr>


 <tr>
  <th>
<img src="../../../../login/AOrd_Verz/124/09/06/20080503/124-20080503-W-IMG_7426.JPG" alt="Neues Fahrzeug im Einsatz" width="350px">
</th>
   <th colspan="2">Das neue Tunnell&ouml;schfahrzeug (TLFA-T 4000/200) <br><br>
     im Einsatz.
</th>
 </tr>


</tbody>
     </table>
<p>Quellen:  Fotos: Josef Rohowsky
</p>
     </div>


</fieldset>
</div>
<?php 
 BA_HTML_trailer();
 ?>

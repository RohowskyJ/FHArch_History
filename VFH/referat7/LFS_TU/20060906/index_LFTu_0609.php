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
HTML_header('Verein Feuerwehrhistoriker in NÖ) ',$header,'adm','100em'); # Parm: Titel,Subtitel,HeaderLine,Type,width

?>

<body class="w3-container ">
<div class="w3-content ">  <!-- max-width:45em; margin:5em;--->
<fieldset>
<h1>Schauraum im neuen Landesfeuerwehrkommando NÖ/Tulln</h1>

<p>In Zusammenarbeit mit dem  Landesfeuerwehrkommando NÖ
und dem Verein der Feuerwehrhistoriker in NÖ
wurde der Schauraum im neuen LFKDO in Tulln gestaltet und eingerichtet.
</p>


<div class='w3-table' style='margin=auto'>

<table>
<tbody>

 <tr>
  <th colspan="2">Figurinen, Helme und Seitenwaffen</th>
   <th>
<img src="../../../../login/AOrd_Verz/124/09/06/20060906/124-20060906-W-IMG_4587.JPG" alt="" width="200px">
</th>
 </tr>

 <tr>
  <th>
<img src="../../../../login/AOrd_Verz/124/09/06/20060906/124-20060906-W-IMG_4574.JPG" alt="" width="200px">
</th>
   <th colspan="2">Landfahrspritze Czermack, Teplitz,<br>
     1905, FF Klosterneuburg Weidling

</th>
 </tr>

 <tr>
  <th colspan="2">Dampf-Handdruckspritze, Franz Kernreuter,<br> 1913,
      FF Langenzersdorf.<br/>
      Im Schrifttum sieht man auch Kernreuther. In der Firmenliteratur und
      auf den Typenschildern wird der Name als "Kernreuter" geschrieben.

</th>
   <th>
<img src="../../../../login/AOrd_Verz/124/09/06/20060906/124-20060906-W-IMG_4573.JPG" alt="" width="200px">
</th>
 </tr>

 <tr>
  <th>
<img src="../../../../login/AOrd_Verz/124/09/06/20060906/124-20060906-W-IMG_4575.JPG" alt="" width="200px">
</th>
   <th colspan="2">Löschfahrzeug Austro Fiat AFL 1935, <br>
    Tragkraftspritze Rosenbauer HR 60
</th>
 </tr>

 <tr>
  <th colspan="2">Abprotz(gebirgs)spritze, Rosenbauer, Linz, FF Hollenstein/Ybbs<br>
  im Hintergrund Armaturen und eine "Rauchhaube" nach BD Müller, BF Wien
</th>
   <th>
<img src="../../../../login/AOrd_Verz/124/09/06/20060906/124-20060906-W-IMG_4605.JPG" alt="" width="200px">
</th>
 </tr>

 <tr>
  <th>
<img src="../../../../login/AOrd_Verz/124/09/06/20060906/124-20060906-W-IMG_4623.JPG" alt="" width="200px">
</th>
   <th colspan="2">Orden und Ehrenzeichen<br><br>
     Bundes-Auszeichnungen
</th>
 </tr>

 <tr>
  <th colspan="2">Einmann-Handspritze</th>
   <th>
<img src="../../../../login/AOrd_Verz/124/09/06/20060906/124-20060906-W-IMG_4611.JPG" alt="" width="200px">
</th>
 </tr>

</tbody>
     </table>
<p>Quellen: Fotos: Josef Rohowsky
</p>
     </div>


</fieldset>
</div>
<?php 
 BA_HTML_trailer();
 ?>

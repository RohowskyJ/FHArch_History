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
     
<h1>1. Museumsfest des OÖ Feuerwehrmuseums<br> in St. Florian/Linz</h1>

<p>Am 12. Mai 2007 fand das erste Museumsfest des
Oberösterreichischen Feuerwerhmuseums in St. Florian/Linz
bei hervorragendem Wetter statt. Um 12:00 Uhr begann die Feier mit
der Eröffnung durch den Herrn Landesbranddirektor Johann Huber.
Anwesend waren zahlreiche Gäste aus Österreich und
dem Bayrischen Raum (Region Passau).
</p>


<table summary="Einstiegsseite" border="1"  >

<tbody>

<tr>
 <th colspan="2">Musikalische Begleitung</th>
</tr>

<tr>
 <td colspan="2">
   <img src="../../../../login/AOrd_Verz/124/09/06/20070512/124-20070512-W-IMG_5762.JPG" alt="Feuerwehrmusikkapelle Windhaag" align="right"  width="250px" />
   Für die musikalische Umrahmung sorgte die
   Feuerwehr-Musikkapelle WINDHAAG bei Freistadt unter ihrem Kapellmeister
   Leopold PAMMER.
  </td>
</tr>

<tr>
 <th colspan="2">Ehrungen und Festansprachen</th>
</tr>

<tr>
 <td colspan="2">
    <img src="../../../login/AOrd_Verz/124/09/06/20070512/124-20070512-W-IMG_5777.JPG" alt="Ehrungen" align="left"  width="250px" />
    <img src="20070512_img_5789_1_96.jpg" alt="Ehrungen" align="right" width="70px" />
    Anschliessend an die Ehrungen und Festansprachen fand eine Vorführung
    von historischen Löscheinsätzen stand. Die Moderation erfolgte durch
    Herrn Dr. Alfred Zeilmayr, als sein Assistent war HBI Berger aktiv.
  </td>
</tr>

<tr>
 <th colspan="2">Historische Vorführung</th>
</tr>

<tr>
 <th>1.</th>

 <td>
    <img src="20070512_img_5789_2_96.jpg" alt="Nachtwächter" align="right"  width="70px" >
Aufsteigender Rauch,
daraufhin erfolgt "Feuerruf" des
<a href="nachtwaechter_v3.php" target=_new>Nacht- bzw. Feuerwächters</a> und
Sturmgeläute durch die "Feuerglocke"
<br/>
Nachtwächter-Darstellung durch Kamerad Bernecker
  </td>
</tr>

<tr>
 <th>2.</th>

 <td>
    <img src="../../../../login/AOrd_Verz/124/09/06/20070512/124-20070512-W-IMG_5792.JPG" alt="Kastenspritze ohne Saugwerk" align="right"   width="250px" />
<a href="kastspr_o_s_v3.php" target=_new>Kastenspritze ohne Saugwerk</a>,
Füllung mittels Löscheimer
<br>
Oldtimerverein FF Wels
und FF Lambach

  </td>
</tr>

<tr>
 <th>3.</th>

 <td>
    <img src="../../../../login/AOrd_Verz/124/09/06/20070512/124-20070512-W-IMG_5798.JPG" alt="Handdruckspritze Czermack" align="right"  width="250px" />
<a href="handdrspr_czermack_v3.php" target=_new>Handdruckspritze der
Firma CZERMACK,
mit Saugwerk, im Handzug</a>
<br/>
(19. Jh. - Mitte 20. Jh.)
<br>
FF Marchtrenk
  </td>
</tr>

<tr>
 <th>4.</th>

 <td>
    <img src="../../../../login/AOrd_Verz/124/09/06/20070512/124-20070512-W-IMG_5804.JPG" alt="Handdruckspritze mit Saugwerk" align="right"  width="250px" />
<a href="handdrspr_m_s_v3.php" target=_new>Handdruckspritze
mit Saugwerk,
mit Pferdezug</a>
<br/>
(19. Jh. - Mitte 20. Jh.)
<br>
FF Höft
  </td>
</tr>

<tr>
 <th>5.</th>

 <td>
    <img src="../../../../login/AOrd_Verz/124/09/06/20070512/124-20070512-W-IMG_5815.JPG" alt="Dampffeuerspritze" align="right"  width="250px" />
<a href="dampffeuerspr_v3.php" target=_new>Dampffeuerspritze
mit Pferdezug</a>
(1902 - 1947)
<br>
FF Vöcklabruck
  </td>
</tr>

<tr>
 <th>6.</th>

 <td>
    <img src="../../../../login/AOrd_Verz/124/09/06/20070512/124-20070512-W-IMG_5831.JPG" alt="" align="right"  width="250px" />
<a href="anhspr_v3.php" target=_new>Anhängespritze LINZ III
mit Traktor Lanz-Bulldog</a>
(1925 - unbekannt)
<br>
Oldtimerverein FF Wels
  </td>
</tr>

<tr>
 <th>7.</th>

 <td>
    <img src="../../../../login/AOrd_Verz/124/09/06/20070512/124-20070512-W-IMG_5836.JPG" alt="" align="right"  width="250px" />
<a href="fiat_turin_v3.php" target=_new>Löschfahrzeug Fiat Turin
mit Vorbaupumpe</a>
(1922 - heute als Oldtimer)
<br>
FF Schwertberg
  </td>
</tr>

<tr>
 <th>8.</th>

 <td>
    <img src="../../../../login/AOrd_Verz/124/09/06/20070512/124-20070512-W-IMG_5838.JPG" alt="Löschfahrzeug ÖAF" align="right"  width="250px" />
<a href="oeaf_anhspr_v3.php" target=_new>Löschfahrzeug ÖAF
mit Anhängespritze</a>
(1927 - 1964)
<br>
Oldtimerverein FF Wels
  </td>
</tr>

<tr>
 <th>9.</th>

 <td>
    <img src="../../../../login/AOrd_Verz/124/09/06/20070512/124-20070512-W-IMG_5840.JPG" alt="" align="right"  width="250px" />
<a href="lf8_tsa_v3.php" target=_new>LF 8
mit TSA</a>
(1943 - 1982)
<br>
Oldtimerverein FF Wels
  </td>
</tr>

<tr>
 <th>9a.</th>


 <td>
    <img src="../../../../login/AOrd_Verz/124/09/06/20070512/124-20070512-W-IMG_5850.JPG" alt="" align="right"  width="250px" />
<a href="tlf_15-43_v3.php
" target=_new>TLF 15/43</a>
(1944 - 1975)
<br>
Im Bestand des OÖ
Feuerwehrmuseums,
betreut durch Oldtimerverein der FF Wels
  </td>
</tr>

<tr>
 <th>10.</th>

 <td>
    <img src="../../../../login/AOrd_Verz/124/09/06/20070512/124-20070512-W-IMG_5859.JPG" alt="" align="right"  width="250px" />
<a href="blitz_wien_v3.php" target=_new>TLF Opel-Blitz,
Modell Wien</a>
<br>
Oldtimerverein FF Wels

  </td>
</tr>

<tr>
 <th>11.</th>

 <td>
    <img src="../../../../login/AOrd_Verz/124/09/06/20070512/124-20070512-W-IMG_5861.JPG" alt="" align="right"  width="250px" />
<a href="steyr_380_v3.php" target=_new>TLF 2000 auf Steyr 380 Z</a>
(1957 - 1997)
<br>
Ingo Weickenkas und Martin Pammer, Braunau am Inn
  </td>
</tr>

<tr>
 <th>12.</th>

 <td>
    <img src="../../../../login/AOrd_Verz/124/09/06/20070512/124-20070512-W-IMG_5862.JPG" alt="" align="right"  width="250px" />
<a href="mb_1113_v3.php" target=_new>TLF 2000 auf MB 1113</a>
(1968 - 2006)
<br>
Michael Thomasberger, Eidenberg
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

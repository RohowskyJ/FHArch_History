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

$VF_logo = 'JA';
$header = "<link  href='".$path2ROOT."login/common/css/frame_pict.css' rel='stylesheet' type='text/css'>";
BA_HTML_header('Verein Feuerwehrhistoriker in NÖ) ',$header,'Form','75em'); # Parm: Titel,Subtitel,HeaderLine,Type,width

?>

<body class="w3-container ">
<div class="w3-content ">  <!-- max-width:45em; margin:5em;--->

<h1>2. Museumsfest des OÖ Feuerwehrmuseums<br> in St. Florian/Linz</h1>
<p>    
<a href="../../../../login/AOrd_Verz/124/09/06/20090523/124-20090523-W-IMG_9133.JPG" target=_new><img src="../../../../login/AOrd_Verz/124/09/06/20090523/124-20090523-W-IMG_9133.JPG" alt="Gäste" align="right"  width="250px"/></a>
Am 23. Mai 2009 fand das zweite Museumsfest des
Oberösterreichischen Feuerwerhmuseums in St. Florian/Linz zum 25 jährigen Bestehen
bei hervorragendem Wetter statt. Um 14:00 Uhr begann die Feier mit
der Eröffnung durch den Herrn Landesbranddirektor Johann Huber.
Anwesend waren zahlreiche Gäste aus Österreich und
dem Bayrischen Raum, Sachsen, Slowakei und Polen.
</p>

<div class='w3-table' style='margin=auto'>

<table>
<tbody>
<tr>
 <th colspan="2">Musikalische Begleitung</th>
</tr>

<tr>
 <td colspan="2">
   <a href="../../../../login/AOrd_Verz/124/09/06/20090523/124-20090523-W-IMG_9120.JPG" target=_new><img src="../../../../login/AOrd_Verz/124/09/06/20090523/124-20090523-W-IMG_9120.JPG" alt="Feuerwehrmusikkapelle Windhaag" align="right" width="250px"/></a>
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
    <a href="../../../../login/AOrd_Verz/124/09/06/20090523/124-20090523-W-IMG_9155.JPG" target=_new><img src="../../../../login/AOrd_Verz/124/09/06/20090523/124-20090523-W-IMG_9155.JPG" alt="Ansprache" align="left"  width="250px"/></a>
    <a href="../../../../login/AOrd_Verz/124/09/06/20090523/124-20090523-W-IMG_9162.JPG" target=_new><img src="../../../../login/AOrd_Verz/124/09/06/20090523/124-20090523-W-IMG_9162.JPG" alt="Ehrengeschenke" align="right"  width="250px"/></a>
    Anschliessend an die Ehrungen und Festansprachen fand eine Vorführung
    von historischen Löscheinsätzen stand.
    <br/><br/>
    <br/><br/>
    <a href="../../../../login/AOrd_Verz/124/09/06/20090523/124-20090523-W-IMG_9172.JPG" target=_new><img src="../../../../login/AOrd_Verz/124/09/06/20090523/124-20090523-W-IMG_9172.JPG" alt="Ehrengeschenke" align="right"  width="250px"/></a>
    Die Moderation erfolgte durch
    Herrn Dr. Alfred Zeilmayr, als sein Assistent war HBI Berger aktiv.
  </td>
</tr>

<tr>
 <th colspan="2">Historische Vorführung</th>
</tr>

<tr>
 <th>1.</th>

 <td>
    <a href="../../../../login/AOrd_Verz/124/09/06/20090523/124-20090523-W-IMG_9177.JPG" target=_new><img src="../../../../login/AOrd_Verz/124/09/06/20090523/124-20090523-W-IMG_9177.JPG" alt="roem. Loeschtrupp" align="right"  width="250px"/></a>
Diese <a href="roem_loeschtrupp_v3.php" target=_new> römische Löschtruppe</a>
"live" wird dargestellt von der Sektion XV (quindecim)
Appollinaris des Kulturvereins Furthmühle unter dem
Sektionsleiter bzw. "Centurio" Walter FLOTZINGER.
  </td>
</tr>

<tr>
 <th>2.</th>

 <td>
    <img src="../../../../login/AOrd_Verz/124/09/06/20090523/124-20090523-W-IMG_9181.JPG" alt="Nachtwächter" align="right"  width="70px"/>
Aufsteigender Rauch,
daraufhin erfolgt "Feuerruf" des
<a href="nachtwaechter_v3.php" target=_new>Nacht- bzw. Feuerwächters</a> und
Sturmgeläute durch die "Feuerglocke"
<br/>
Nachtwächter-Darstellung durch Kamerad Bernecker
  </td>
</tr>

<tr>
 <th>3.</th>

 <td>
    <img src="../../../../login/AOrd_Verz/124/09/06/20090523/124-20090523-W-IMG_9182.JPG" alt="Kastenspritze ohne Saugwerk" align="right"   width="250px"/>
<a href="kastspr_o_s_v3.php" target=_new>Kastenspritze ohne Saugwerk</a>,
F&uuml;llung mittels L&ouml;scheimer
<br>
Oldtimerverein FF Wels
und FF Lambach

  </td>
</tr>

<tr>
 <th>4.</th>

 <td>
    <img src="../../../../login/AOrd_Verz/124/09/06/20090523/124-20090523-W-IMG_9192.JPG" alt="Karrenspritze ohne Saugwerk" align="right"  width="250px"/>
<a href="karrspr_bad_gams_v3.php" target=_new>Karrenspritze der
FF Bad Gams, ohne Saugwerk
</a>
<br/>
(Baujahr 1908)
<br>
FF Bad Gams
  </td>
</tr>

<tr>
 <th>5.</th>

 <td>
    <img src="../../../../login/AOrd_Verz/124/09/06/20090523/124-20090523-W-IMG_9237.JPG" alt="Dampffeuerspritze" align="right"  width="250px"/>
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
    <img src="../../../../login/AOrd_Verz/124/09/06/20090523/124-20090523-W-IMG_9248.JPG" alt="" align="right"  width="250px"/>
<a href="anhspr_v3.php" target=_new>Anhängespritze LINZ III
mit Traktor Steyr 26</a>
(1925 - unbekannt)
<br>
Oldtimerverein FF Wels und FF Schwand-Freudenthal
  </td>
</tr>

<tr>
 <th>7.</th>

 <td>
    <img src="../../../../login/AOrd_Verz/124/09/06/20090523/124-20090523-W-IMG_9259.JPG" alt="Löschfahrzeug ÖAF" align="left"  width="250px"/>
    <img src="../../../../login/AOrd_Verz/124/09/06/20090523/124-20090523-W-IMG_9256.JPG" alt="Löschfahrzeug ÖAF" align="right"  width="250px"/>
<a href="oeaf_anhspr_v3.php" target=_new>Löschfahrzeug ÖAF AFN 36
mit Anhängespritze</a>
(1927 - 1964)
<br>
Oldtimerverein FF Wels
  </td>
</tr>

<tr>
 <th>8.</th>


 <td>
    <img src="../../../../login/AOrd_Verz/124/09/06/20090523/124-20090523-W-IMG_9262.JPG" alt="" align="right"  width="250px"/>
<a href="tlf_15-43_v3.php" target=_new>TLF 15/43</a>
(1944 - 1975)
<br>
Im Bestand des OÖ.
Feuerwehrmuseums,
betreut durch Oldtimerverein der FF Wels
  </td>
</tr>


</tbody>
     </table>
<p>Quellen: Texte zur Präsentation: Dr. jur. Alfred Zeilmayr, Fotos: Josef Rohowsky
</p>
     </div>


</div>
<?php 
 BA_HTML_trailer();
 ?>

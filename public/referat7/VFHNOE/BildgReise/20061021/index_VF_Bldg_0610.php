<?php
session_start();
# ------------------------------------------------------------------------------------------------------------------
#
#Inhaltsverzeichnis und Aufruf für Archivierte Berichte


$module    = 'VF_Arch';
$path2ROOT = "../../../../../";

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
<h1>Studienreise 2006 der Feuerwehrhistoriker in NÖ</h1>
<P>Der Verein &bdquo;Feuerwehrhistoriker in N&Ouml;&ldquo; hat
am 21. Oktober 2006
seine Studienreise nach Oberösterreich durchgeführt.
</P>

<p>Die Studienreise war für die Dauer  eines Tages geplant und
führte uns nach Oberösterreich, in die Bezirke Ried im Innkreis,
Vöcklabruck und Gmunden.
<br>
Die Fahrzeuge wurden in dankendswerter Weise von folgenden Feuerwehren zur
Verfügung gestellt:
<br>
FF Amstetten, FF Wr.Neudorf und FF St. Pölten.
</p>

<div class='w3-table' style='margin=auto'>

<table>
<tbody>
<tr>
 <td>     
<img src="../../../../../login/AOrd_Verz/124/09/06/20061021/124-20061021-W-IMG_5219.JPG" alt="Feuerwehrmuseum Tumeltsham" align=right border="1" width="250px">
Als erste Station war das
Feuerwehrmuseum Tumeltsham
am Plan.
Das Museum befand sich im Dachgeschoß des
Feuerwehrhauses, ist aber breits längere Zeit in einem eigenen Gebäuder, nicht weit von der Feuerwehr.
<br>
<br>
Wie (fast) alle Museen hat es bereits Platznot, und man ist schon
länger auf der Suche, daher hat man schon
ein anderes Gebäude in der Nähe des Feuerwehrhauses gefunden,
das bereits hergerichtet wird.
<br>
<br>
Die Sammlung umfasst Gegenstände aus fast allen Bereichen des Feuerwehrgeschehens:
von der Bekleidung angefangen (Uniformen, Helme, Kappen), Ehrungen und Auszeichnungen,
Pumpen, Armaturen und Schäuche, verschiedenste andere Werkzeuge, Ehrengeschenke.
und Bilder.
</td>
</tr>


<tr>
 <td>
<img src="../../../../../login/AOrd_Verz/124/09/06/20061021/124-20061021-W-IMG_5233.JPG" alt="Spritzenhaus Mettmach" align="left" width="250px">
<br>      
<br>
Als nächstes besuchten wir das Feuerwehrmuseum in Mettmach. Es ist ein kleines,
altes Gerätehaus (Grundriß 7 x 3,5 m), das einen
pferdegezogenen Spritzenwagen mit allem Zubehör
eine Handpumpe für vier Mann
(die auch bei der Waldviertler
Firetrophy 2006 mit der Nummer 35 dabei waren!),
eine Motorspritze, Uniformen, Helme und einige Kleingeräte.
  </td>
</tr>

<tr>
 <td>
  <br>
Zwischendurch haben wir uns in Ried im Gasthaus Kellerbräu gestärkt.
  <br>
  <br>
  <br>
  </td>
</tr>

<tr>
 <td>
<img src="../../../../../login/AOrd_Verz/124/09/06/20061021/124-20061021-W-IMG_5243.JPG" alt="Kohle und Dampf" align="right" width="250px">

Als Hauptpunkt im Nachmittagsprogramm war die OÖ Landesausstellung 2006
"Kohle und Dampf" in Ampfelwang am Programm. Die Ausstellung ist in der
ehemaligen Sortieranlage des Untertag-Braunkohleabbaues und die Eisenbahnausstellung
im Rundschuppen untergebracht. Für die Zeit nach der Landesausstellung
ist eine Nutzung als Museum für Bergbau und Eisenbahngeschichte  geplant.
  <br>
Diese Ausstellung ist besonders interessant, da sie für alle Altersgruppen
konzipiert war (eigener Kinderpfad, der sich mit dem "normalen"
Ausstellungsweg immer wieder überschneidet), Zeitzeugenberichte
(Dienstag und Donnerstags, pensionierte Bergleute und Bahnbedienstete) bringen
den Besuchern das Thema für ein Museum etwas ungewohnt, aber sehr anschaulich
näher (z.B. die  Führerstandbesichtigung mit einem Dampflokfahrer
als Führer).
  </td>
</tr>

<tr>
 <td>
<img src="../../../../../login/AOrd_Verz/124/09/06/20061021/124-20061021-W-IMG_5275.JPG" alt="Papiermacher und Feuerwehrmuseum Steyrermühl" align="left" width="250px">

Als letzte Station des Tages besuchten wir das
Papiermachermuseum Laakirchen-Steyrermühl, in den Räumlichkeiten
(ein nicht mehr zur Produktion genutztes Fabriksgebä;ude)
der ehemaligen Zellstofffabrikation. In den Plänen für das
Papiermachermuseum war bereits ein Teil für ein
Feuerwehrmuseum eingeplant.
  <br>
In diesem Museum sind alle sechs Feuerwehren des Laakirchener Pflichtbereiches
vertreten. Die ausgestellten Exponate sind Leihgaben, und als solche im
Eigentum der Besitzer.
  <br>
Einen weiteren interessanten Aspekt bietet das Papiermachermuseum als solches:
für uns als Archivare ist ja das Papier, auf dem unter anderem unsere Protokolle
als Informationsträger stehen, besonders interessant (Eigenschaften, Aufbewahrung, ...).
  </td>
</tr>


</tbody>
     </table>
<p>
Es war ein interessanter Tag, aber wie immer bei einem solchen Pensum,
man kann nicht ins Detail gehen, für genauere Betrachtungen muss man
auf jeden Fall noch mindestens einmal (und da wahrscheinlich jeweils den ganzen Tag)
in die entsprechenden Museen pilgern.
</p>
     </div>


</fieldset>
</div>
<?php 
 BA_HTML_trailer();
 ?>

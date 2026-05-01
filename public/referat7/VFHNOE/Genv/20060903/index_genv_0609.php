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

$VF_logo = 'JA';
$header = "<link  href='".$path2ROOT."login/common/css/frame_pict.css' rel='stylesheet' type='text/css'>";
BA_HTML_header('Verein Feuerwehrhistoriker in NÖ) ',$header,'Form','75em'); # Parm: Titel,Subtitel,HeaderLine,Type,width

?>

<body class="w3-container ">
<div class="w3-content ">  <!-- max-width:45em; margin:5em;--->
<fieldset>

  
<h1>Generalversammlung der Feuerwehrhistoriker in N&Ouml;</h1>
<P>Der Verein &bdquo;Feuerwehrhistoriker in NÖ&ldquo; hat
am 2. September 2006
in den Räumlichkeiten der FF Gars
am Kamp seine Generalversammlung abgehalten.
</P>
<P>Die Begrüßung erfolgte durch den Kdt. der FF
Gars am Kamp</P>
<h2>Tagesordnungspunkte:</h2>
<dl>
<dt><strong>
Eröffnung durch den Obmann, Bericht des Obmannes
 </strong>
 </dt>
  <dd>
   </dd>
<dt><strong>
Feststellung der Beschlussfähigkeit
  </strong>
 </dt>
  <dd>Beschlußfähigkeit ist gegeben.
   </dd>
<dt><strong>
Totengedenken
  </strong>
 </dt>
  <dd>
Rudi Ceyka, &ndash; verstorben 1. April
   </dd>
<dt><strong>
Bericht des Obmannes über die
Vereinstätigkeit im 1. Jahr
  </strong>
 </dt>
  <dd>
Im ersten Jahre
des Bestehen des Vereines wurde die innere Struktur errichtet. Die
einzelnen Referate wurden eingerichtet und haben ihre Arbeit
aufgenommen.
   </dd>
<dt><strong>
Bericht des Kassiers über die
Finanzgebarung
  </strong>
 </dt>
  <dd>
Der Kassier berichtet über die Finanzen des Vereines und über die Sponsoren.
   </dd>
<dt><strong>
Bericht der Rechnungsprüfer und
Beschlussfassung über den Antrag auf Entlastung des Kassiers
</strong>
 </dt>
  <dd>
Die Rechnungsprüfer berichteten über eine korrekte Kassaführung.
Der Kassier und sein Stellvertreter wurden einstimmig entlastet.
   </dd>
<dt><strong>
Beschlussfassung über den
Voranschlag für das Jahr 2006/2007
</strong>
 </dt>
  <dd>
 Der Voranschlag für das Jahr 2006/2007 wurde einstimmig beschlossen.
   </dd>
<dt><strong>
Beschlussfassung über die
Entlastung der übrigen Mitglieder des Vorstandes
</strong>
 </dt>
  <dd>
Die übrigen
Mitglieder des Vorstandes wurden einstimmig entlastet.
   </dd>
<dt><strong>
Wahl zweier Rechnungsprüfer
</strong>
 </dt>
  <dd>
Die Rechnungsprüfer wurden bestellt (ein "alter" und ein "neuer").
   </dd>
<dt><strong>
Verleihung einer Ehrenmitgliedschaft
</strong>
 </dt>
  <dd>
    <table summary="Ehrenmitgliedschaft" >
     <tr>
       <th>
<IMG SRC="../../../../../login/AOrd_Verz/124/09/06/20060902/124-20060902-W-IMG_4309.JPG" NAME="Ehrenmitglied Patricia H. Fischer" BORDER=0>
        </th>
       <th>
<IMG SRC="../../../../../login/AOrd_Verz/124/09/06/20060902/124-20060902-W-IMG_4294.JPG" NAME="Urkunde"  height="360">
        </th>
      </tr>
     <tr>
       <td colspan="2">
Frau
Patricia H. Fischer wird auf Grund ihres großen Engagements für
die historischen Fahrzeuge und die Unterstützung, Hilfe und
Förderung der Ziele des Vereines als Ehrenmitglied in den Verein
aufgenommen.
        </td>
      </tr>
     </table>
   </dd>
<dt><strong>
Zielsetzung für das nächste
Jahr durch den Obmann
</strong>
 </dt>
  <dd>
    <ul>
     <li>
Vertiefung der
Arbeit in den Referaten.
      </li>
     <li>
Erfassung der
vorhandenen Objekte.
      </li>
     <li>
Zusammenarbeit
mit dem NÖ-Landesfeuerwehrverband.
      </li>
     <li>
Erstellung eines
Archivarlehrganges.
      </li>
     <li>
Betreuung der
Sammlung des Landesfeuerwehrverbandes.
      </li>
     <li>
Vorbereiten einer
österreichweiten Vernetzung der Arbeit auf dem Gebiete der
Feuerwehrgeschichte.
      </li>
    </ul>
   </dd>
<dt><strong>
Allfälliges
</strong>
 </dt>
  <dd>
   </dd>
</dl>

</fieldset>
</div>
<?php 
 BA_HTML_trailer();
 ?>

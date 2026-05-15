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
Römischer Löschtrupp
  </th>
</tr>

<tr>
 <td>
   <img src="../../../../login/AOrd_Verz/124/09/06/20090523/124-20090523-W-IMG_9177.JPG" alt="roem. Loeschtrupp" align="right" />

 Dieser Aufmarsch einer römischen Löschtruppe - eine Bezeichnung "Feuerwehr", wie sie auch im deutschen Sprachgebrauch erst ab 1847
(KARLSRUHE9 eingeführt wurde, gibt es im lateinischen Wortschatz nicht - ist bei unserem Museumsfest eine Premiere!

Das Verdienst, das Löschwesen für die größeren Siedlungen in seinem Weltreich wirkungsvoll "organisiert" zu haben, darf mit Recht ROM in Anspruch nehmen. Am Anfang, etwa ab 300 v. Chr. standen allerdings mehrere nicht sehr taugliche Versuche.

Erst 6 n. Chr. veranlasst schlussendlich eine Brandkatastrophe in ROM den Kaiser AUGUSTUS, anstelle einer von ihm geschaffenen "Sklavenfeuerwehr" sieben Wachkohorten ("cohortes vigilum") von je 1000-1200 Freigelassenen aufzustellen, denen neben ihren polizeilichen Aufgaben auch der Löschdienst obliegt. Ihre Ausstattung besteht neben der üblichen Ausrüstung aus
Löscheimern (hamae), Brechäxten (dolabrae), Leitern (scalae), Löschdecken
(centones), Feuerpatschen (scopae) und ähnlichem mehr. Auch die
Verwendung von frühen Feuerspritzen (siphones) - erfunden im 3. Jahrhundert
v. Chr. durch den  Griechen KTESIBIOS - ist nachgewiesen.

Neben den "Vigiles", der "öffentlichen", berufsmäßig organisierten  "Feuerwehr haben die Römer in den größeren Siedlungen ihrer Provinzen aber über ein zusätzliches organisiertes Löschwesen verfügt und zwar in Form der
"collegiati", Vereine von Handwerkern, die unseren Freiwilligen Feuerwehren ähnlich waren, wobei ihre Erfahrung im Umgang mit Geräten, die sich auch zur Brandbekämpfung eigneten, ausschlaggebend war.

Unter diesen Feuerwehrvereinen sind es vor allem die "fabri" (d.s. Hand-werker, die mit "harten" Materialien arbeiten, wie Schmiede, Zimmerleute, Maurer usw.), die Äxte, Sägen und Zangen zur Brandbekämpfung verwendet haben, oder die "centonarii", die Flickenteppichhändler, die mit Wasser oder Essig getränkten Feuerpatschen die Flammen zu bekämpfen suchten. Sie waren straff organisiert und dem Militär vergleichbar gegliedert. Gemeinsam ist allen diesen Vereinen, dass sie als "Freiwillige Feuerwehr" nur neben ihrer beruflichen Haupttätigkeit eingesetzt wurden.

Das "alte Rom" kannte also ein duales System, die Einheiten einer (militärisch organisierten) Berufsfeuerwehr und daneben eine "zivile Feuerlöschtruppe". Die Trennung dürfte aber nicht sehr streng gewesen sein, da nach der Quellenlage "collegiati" beispielsweise auch in Rom zum Einsatz gekommen sind. Gesichert ist dieses römische Feuerlöschwesen auch durch Funde in
Österreich (CETIUM, VINDOBONA, CARNUNTUM und FLAVIA SOLVA)



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

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
<div style='text-align: center'>
<h1 >Archivarlehrgang des N&Ouml;LFKdo</h1>
</div>
<FONT FACE="Arial, sans-serif"><FONT SIZE=3>
<p>
Der Archivarlehrgang des N&Ouml; Landesfeuerwehr- Kommandos wurde am
5.4.2008 das erste mal als Modullehrgang ausgerichtet: Die
Ausarbeitenden und Vortragenden kamen aus den Reihen des Vereines
&bdquo;Feuerwehrhistoriker in N&Ouml;&ldquo;, vom N&Ouml; und O&Ouml;
Landesfeuerwehr- Kommado.
</P>
<div style='text-align: center'>
<IMG SRC="Vortragende_c.jpg" NAME="Grafik1"  >
</div>
<FONT FACE="Arial, sans-serif"><FONT SIZE=3>
<p>
Von links nach rechts: Anton M&uuml;ck (V), Siegfried Hollauf (N&Ouml;LFKDO),
Hans Setznagel (V), Hans Gilbert M&uuml;ller (O&Ouml;LFKDO), Ing.
Herbert Schanda (V), Franz Wiesenhofer (V) und Karl Zehetner (V =
Verein &bdquo;Feuerwehrhistoriker in N&Ouml;)</P>
<P>Den
Vormittag verbrachten wir bei den Themen &bdquo;Auszeichnungen und
Leistungsabzeichen&ldquo;, &bdquo;Rechtsformen der
Feuerwehren&ldquo;,Technikgeschichte des Feuerwehrwesens&ldquo;, Die
Entstehung des Tankwagens&ldquo; und &bdquo;Ausbildung und
Feuerwehrschulen&ldquo;.</P>
<p>Den
Nachmittag verbrachten wir bei einer Unterrichtseinheit
&bdquo;Museumskunde&ldquo; und anschliessendem Stationsbetrieb bei
den Themen &bdquo;Beleuchtungsk&ouml;rper&ldquo;, &bdquo;Behandlung
historischer Objekte&ldquo; und &bdquo;Dokumentierung des aktuellen
Feuerwehrgeschehens&ldquo;:</P>
<div style='text-align: center'>
<table summary="Bilder">
<tr>
 <th>
<IMG SRC="Setznagel_c.jpg" NAME="Grafik2" ALIGN=LEFT WIDTH=146 HEIGHT=217 BORDER=0>
 </th><th>
<IMG SRC="Zehetner_c.jpg" NAME="Grafik3" ALIGN=LEFT WIDTH=335 HEIGHT=209 BORDER=0>
</th>
</tr>
</table>
</div>

<P>Durch
das gro&szlig;e Interesse bedingt (der Lehrgang war auf 70 Teilnehmer
limitiert), ist f&uuml;r den Herbst eine Wiederholung des 1. Teiles
(Grundlagen der Feuerwehrgeschichte) und die beiden Module geplant.
Die LG werden voraussichtlich an einem Freitag und dem Samstag
stattfinden.
</P>
<P>Als
Voraussetzung muss der Interessierte als Feuerwehrarchivar im FDISK
eingetragen sein, die Anmeldung erfolgt (nach Bekanntgabe der
Lehrgangstermines) ebenfalls mit FDISK.

</P>

</FONT>

</fieldset>
</div>
<?php 
 BA_HTML_trailer();
 ?>

<?php
session_start();
# ------------------------------------------------------------------------------------------------------------------
#
#Inhaltsverzeichnis und Aufruf für Archivierte Berichte


$module    = 'VF_Arch';
$path2ROOT = "../../../../";

$debug = True; $debug = False;  // Debug output Ein/Aus Schalter

 require $path2ROOT.'login/common/BA_Funcs.lib.php' ;  // Diverse Unterprogramme

initial_debug(); # Wenn $debug=true - Ausgabe von Debug Informationen: $_POST, $_GET, $_FILES

$logo = 'JA';
$header = "<link  href='".$path2ROOT."login/common/css/frame_pict.css' rel='stylesheet' type='text/css'>";
HTML_header('Verein Feuerwehrhistoriker in NÖ) ','Achivierte Berichte',$header,'Form','75em'); # Parm: Titel,Subtitel,HeaderLine,Type,width

?>

<body class="w3-container ">
<div class="w3-content ">  <!-- max-width:45em; margin:5em;--->
<fieldset>
<P CLASS="western" ALIGN=CENTER><FONT COLOR="#ff0000"><FONT SIZE=5 STYLE="font-size: 20pt"><B>Auf
den Spuren der Feuerwehrger&auml;tehersteller</B></FONT></FONT></P>
<P CLASS="sdfootnote-western"><BR>
</P>
<P CLASS="western" ALIGN=LEFT>Manchmal ist es nur ein kleines
Blechschildchen auf einer alten Spritze, das neugierig macht. Au&szlig;er
dem Namen wei&szlig; man wenig oder gar nichts &uuml;ber die
Erzeugerfirma. Deshalb befassten sich die Teilnehmer der
Internationalen Arbeitsgemeinschaft f&uuml;r Feuerwehr- und
Brandschutzgeschichte in ihrer 14. Tagung in Kurort Jonsdorf,
Oberlausitz, Sachsen, mit dem Thema &bdquo;Firmengeschichten der
Feuerwehrger&auml;tehersteller&ldquo;.</P>
<P CLASS="western" ALIGN=LEFT>Das Ergebnis ist in einem 675seitigen
Tagungsband bzw. auf einer CD-ROM dokumentiert. Sie enthalten die
Geschichte von &uuml;ber 60 ehemaligen sowie bis heute noch
produzierenden Firmen dieser Branche in Deutschland, Kroatien,
Niederlande, &Ouml;sterreich, Schweiz, Tschechien und Polen und sind
somit ein hervorragendes Nachschlagewerk &uuml;ber die Historie der
Feuerl&ouml;schger&auml;te- und -fahrzeugproduzenten dieser L&auml;nder.
Die Textbeitr&auml;ge sind zum Teil mit historisch wertvollen Fotos
und Produktreklamen der Firmen untermauert.</P>
<P CLASS="western" ALIGN=LEFT><BR>
</P>
<P CLASS="western" ALIGN=LEFT>&Ouml;sterreich ist darin vertreten
mit:</P>
<P CLASS="western" ALIGN=LEFT>Herbert G. Brandstetter &ndash; &bdquo;Der
Feuerl&ouml;schger&auml;tehersteller Gugg in Braunau am Inn&ldquo;,</P>
<P CLASS="western" ALIGN=LEFT>Erwin Chalupar &ndash;
&bdquo;Industrieelektronik P&ouml;lz IEP ein verl&auml;&szlig;licher
Partner der Feuerwehr&ldquo;,</P>
<P CLASS="western" ALIGN=LEFT>Gerhard Eichberger &ndash; &bdquo;Firma
Josef Seiwald Karosseriebau Ges.m.b.H., Oberalm, Salzburg&ldquo;,</P>
<P CLASS="western" ALIGN=LEFT>Roman Felsner &ndash;
&bdquo;Firmengeschichten der Feuerwehrger&auml;tehersteller und
Lieferanten in K&auml;rnten&ldquo;,</P>
<P CLASS="western" ALIGN=LEFT>Josef H&ouml;tzl &ndash; &bdquo;H&ouml;tzl
&ndash; Feuerwehrfahrzeuge&ldquo;,</P>
<P CLASS="western" ALIGN=LEFT>Heinrich Krenn &ndash; &bdquo;Firma
Knaust, Wien 1822-1938&ldquo;,</P>
<P CLASS="western" ALIGN=LEFT>Hans Gilbert M&uuml;ller &ndash; &bdquo;Ein
Unternehmen mit Vergangenheit und Zukunft&ldquo; (Rosenbauer),</P>
<P CLASS="western" ALIGN=LEFT>Peter Poloma &ndash; &bdquo;Firmengeschichte
von Austro-Fiat&ldquo;,</P>
<P CLASS="western" ALIGN=LEFT>Johann Sallaberger &ndash; &bdquo;Haberkorn
produziert seit &uuml;ber 100 Jahren Feuerwehrschl&auml;uche&ldquo;,</P>
<P CLASS="western" ALIGN=LEFT>Werner Satra &ndash; &bdquo;Firmengeschichte
der Fa. Langer&ldquo; (Wr. Neudorf),</P>
<P CLASS="western" ALIGN=LEFT>Eugen Schertler &ndash;
&bdquo;Firmengeschichte der F. Haberkorn&ldquo; (Bregenz),</P>
<P CLASS="western" ALIGN=LEFT>Adolf Schinnerl &ndash;
&bdquo;Glockengie&szlig;erei Oberascher in Salzburg &ndash;
Erfinderin des Gasstrahlers&ldquo;,</P>
<P CLASS="western" ALIGN=LEFT>Peter Schmid &ndash; &bdquo;Treffen der
Generationen &ndash; Eine Chronik des &Ouml;sterreichischen
Feuerwehrfahrzeugherstellers MARTE&ldquo; (Weiler),</P>
<P CLASS="western" ALIGN=LEFT>Peter Schmid und Martin A. Keckeis &ndash;
&bdquo;Firmengeschichte der Rechner`s Ges.m.b.H. (Ludesch),</P>
<P CLASS="western" ALIGN=LEFT>Karl Heinz Wagner &ndash;
&bdquo;Ger&auml;tehersteller in Tirol&ldquo; (Grassmayr, Th&ouml;ni,
Empl),</P>
<P CLASS="western" ALIGN=LEFT>Dr. Alfred Zeilmayr &ndash; &bdquo;Die
Dynastie Rosenbauer im Ober&ouml;sterreichischen Feuerwehrwesen&ldquo;,</P>
<P CLASS="western" ALIGN=LEFT>Hans Gilbert M&uuml;ller &ndash;
&bdquo;Reginald Czermack&ldquo; (Teplitz in B&ouml;hmen und Wien).</P>
<P CLASS="western" ALIGN=LEFT><BR>
</P>
<P CLASS="western" ALIGN=LEFT>Der Tagung vorangegangen ist am 27.
September 2006 die 9. Sitzung der CTIF-Geschichtekommission.
Beschlossen wurde das Prozedere der Zertifizierung von
Feuerwehrmuseen und beraten die Regulativs zur Bewertung von
historischen Feuerwehrkraftfahrzeugen, welche der n&auml;chsten
Delegiertenversammlung des CTIF zur Beschlussfassung vorgelegt wird.
Als erstem Feuerwehrmuseum wurde dem &bdquo;Feuerwehrbewegungszentrum&ldquo;
in Pribyslav, Tschechien, die Zertifizierungsurkunde mit einer
G&uuml;ltigkeitsdauer von zehn Jahren &uuml;berreicht. In der
Kommission vertreten waren Delegierte der Nationalen CTIF-Komitees
von D&auml;nemark, Deutschland, Gro&szlig;britannien, Kroatien,
Niederlande, &Ouml;sterreich, Polen, Schweden, Schweiz, Slowenien,
Tschechien und Ungarn, ein Gast des Landesfeuerwehrverbandes von
S&uuml;dtirol und heuer erstmals auch Vertreter von Belarus
(Wei&szlig;russland) und Griechenland.</P>
<P CLASS="western" ALIGN=LEFT><BR>
</P>
<P CLASS="western" ALIGN=LEFT>W&auml;hrend in der Kommission pro
Nation jeweils nur ein Delegierter vertreten ist, kann in der
Internationalen Arbeitsgemeinschaft, die unter der Patronanz der
CTIF-Kommission steht und als offenes Forum gef&uuml;hrt wird, jeder
an der Feuerwehrgeschichte Interessierte unabh&auml;ngig von der
Delegierung durch einen Verband mitarbeiten. Ausgezeichnet waren die
von Oberbrandmeister Hans-Joachim Augustin, Kurort Jonsdorf,
gemeinsam mit dem Kommissionsvorsitzenden BR Adolf Schinnerl
organisierten Veranstaltungen mit 86 Teilnehmerinnen und Teilnehmern
durch die Anwesenheit des CTIF-Pr&auml;sidenten Walter Egger. Er
bedankte sich f&uuml;r die grossartige Arbeit bei den
Kommissionsmitgliedern und lobte insbesondere den Idealismus der
Forscher und Forscherinnen der Internationalen Arbeitsgemeinschaft,
die ja gr&ouml;sstenteils auf eigene Kosten teilnehmen und dar&uuml;ber
hinaus den wertvollen Tagungsband selbst finanzieren.</P>
<P CLASS="western" ALIGN=LEFT><BR>
</P>
<P CLASS="western" ALIGN=LEFT>Auf der Heimfahrt machten Teilnehmer
aus &Ouml;sterreich, Kroatien, Slowenien und Tschechien einen
Zwischenstopp im b&ouml;hmischen Teplitz und besuchten das Grab von
Reginald Czermack auf dem aufgelassenen evangelischen Friedhof. Sie
erwiesen damit dem Pionier der Feuerwehrger&auml;teherstellung und
ersten Vorsitzenden des &Ouml;sterreichischen
Feuerwehr-Reichsverbandes (1889-1903) die besondere Ehre.</P>
<P CLASS="western" ALIGN=LEFT>Das Grab hat nach langer Suche der
Mentor der &ouml;sterreichischen Feuerwehrgeschichte, Dr. Hans
Schneider (+1997) im Jahr 1990 entdeckt. Der aufgelassene Friedhof,
dessen Grabsteine mehrheitlich verblasste deutsche Inschriften
tragen, ist in einem desolaten Zustand. Die deutsch sprechenden
Einwohner wurden 1945 vertrieben und so hat zu den Verstorbenen
niemand mehr einen Bezug. Eine Ausnahme ist die Grabst&auml;tte der
Familie Czermack-Wartek. Im Jahr 2003 konnte BR Adolf Schinnerl als
Leiter des &Ouml;BFV-Sachgebietes 1.5 mit Hilfe des ehemaligen
Pr&auml;sidenten des f&ouml;deralen Feuerwehrverbandes der CSSR, Dr.
Miroslav Repisky, und Finanzierung durch den &Ouml;BFV wenigstens die
Grabinschrift wieder in lesbaren Zustand versetzten lassen. Sie sind
nunmehr bem&uuml;ht, das Andenken Czermacks auch in den tschechischen
Feuerwehren zu wecken, da er auch deren erster Vorsitzender war.</P>
<P CLASS="western" ALIGN=LEFT><BR>
</P>
<P CLASS="western" ALIGN=LEFT STYLE="margin-left: 10cm">OBR Johann
Sallaberger</P>
<P CLASS="western" ALIGN=LEFT STYLE="margin-left: 10cm">Sachgebietsleiter
1.5</P>
<P CLASS="western" ALIGN=LEFT><BR>
</P>

<img src="RK-20060927-GruppeKaine043.jpg" alt="Tagungsteilnehmer in G&ouml;rlitz" width="500" align="left">
<br><br><br><br><br>
<P CLASS="western"><I>Die Tagungsteilnehmer auf dem Stadtplatz in
G&ouml;rlitz mit Oberb&uuml;rgermeister Joachim Paulick (Bildmitte
mit CTIF-Jubil&auml;umsbuch) beim Besuch der historischen Stadt, die
auch durch die seit 1864 hier bestehende Feuerl&ouml;schger&auml;teerzeugung
bekannt ist.                                                                    (Foto Keine)</I></P>
<P CLASS="western" ALIGN=LEFT><BR>

</fieldset>
</div>
<?php 
 HTML_trailer();
 ?>

<?php
session_start();
# ------------------------------------------------------------------------------------------------------------------
#
#Inhaltsverzeichnis und Aufruf für Archivierte Berichte


$module    = 'VF_Arch';
$path2ROOT = $path2VF = "../../../../";

$debug = True; $debug = False;  // Debug output Ein/Aus Schalter

 require $path2VF.'login/common/BA_Funcs.lib.php' ;  // Diverse Unterprogramme

initial_debug(); # Wenn $debug=true - Ausgabe von Debug Informationen: $_POST, $_GET, $_FILES

$logo = 'JA';
$header = "<link  href='".$path2VF."login/common/css/frame_pict.css' rel='stylesheet' type='text/css'>";
HTML_header('Verein Feuerwehrhistoriker in NÖ) ','Achivierte Berichte',$header,'Form','75em'); # Parm: Titel,Subtitel,HeaderLine,Type,width

?>



<P LANG="de-DE" CLASS="western" ALIGN=RIGHT STYLE="margin-bottom: 0cm">
<FONT FACE="Arial, sans-serif"><B>Erlauftaler Feuerwehrmuseum</B></FONT></P>
<P LANG="de-DE" CLASS="western" STYLE="margin-bottom: 0cm"><BR>
</P>
<P LANG="de-DE" CLASS="western" STYLE="margin-bottom: 0cm"><BR>
</P>
<P LANG="de-DE" CLASS="western" STYLE="margin-bottom: 0cm"><FONT FACE="Arial, sans-serif"><B>Sonderausstellung
&bdquo;&Ouml;sterreichische Uniformen&ldquo;</B></FONT></P>
<P LANG="de-DE" CLASS="western" STYLE="margin-bottom: 0cm"><BR>
</P>
<P LANG="de-DE" CLASS="western" STYLE="margin-bottom: 0cm"><FONT FACE="Arial, sans-serif">Purgstall
an der Erlauf &ndash; Das vierzigste Bestandsjubil&auml;um des
Feuerwehrmuseums Purgstall wird mit der Sonderausstellung
&bdquo;&Ouml;sterreichische Uniformen&ldquo; gro&szlig; gefeiert.
Er&ouml;ffnet wurde die Ausstellung von Landesrat Dr. Stephan
Pernkopf am Freitag, den 23. April 2010 in Anwesenheit von Veteranen
aus Randegg und vielen Feuerwehrmitgliedern aus Wien und
Nieder&ouml;sterreich, an der Spitze Kaiser Franz Joseph, verk&ouml;rpert
durch Uniform-Sammler Peter Poloma aus Laxenburg. Die
Uniform-Leihgaben stammen von Ehrenoberbrandinspektor Karl Zehetner,
der selbst ein privates Feuerwehrmuseum in Frohsdorf betreibt.
Zehetner pr&auml;sentiert Uniformen der Post, Bundesbahn,
Gendarmerie, Polizei, Zollwache, Justiz, Wiener Stra&szlig;enbahn,
Rotes Kreuz und der Feuerwehr, sodass man die Entwicklung dieser im
Wandel der Zeit erleben kann. Weiters zu sehen sind auch Uniformen
des Milit&auml;rs (1. und 2. Weltkrieg), des St&auml;ndestaates, des
Bundesheeres sowie von Nieder&ouml;sterreichischen B&uuml;rgergarden.
</FONT>
</P>
<P LANG="de-DE" CLASS="western" STYLE="margin-bottom: 0cm"><FONT FACE="Arial, sans-serif">Die
Museumsf&uuml;hrer und Oldtimerfahrer unter der F&uuml;hrung von
Museumsleiter Franz Wiesenhofer trugen dazu bei, dass den G&auml;sten
wieder ein unvergesslicher Abend geboten wurde. F&uuml;r die
musikalische Umrahmung sorgte eine Bl&auml;sergruppe der Werkskapelle
Busatis und die Moderation der Er&ouml;ffnungsfeier f&uuml;hrte
Robert H&uuml;lmbauer durch.</FONT></P>
<P LANG="de-DE" CLASS="western" STYLE="margin-bottom: 0cm"><BR>
</P>
<P LANG="de-DE" CLASS="western" STYLE="margin-bottom: 0cm"><BR>
</P>
<P LANG="de-DE" CLASS="western" STYLE="margin-bottom: 0cm"><FONT FACE="Arial, sans-serif"><B>Die
&Ouml;ffnungszeiten der Sonderausstellung sind vom:</B></FONT></P>
<P LANG="de-DE" CLASS="western" STYLE="margin-bottom: 0cm"><FONT FACE="Arial, sans-serif">1.
Mai bis 8. August 2010 </FONT>
</P>
<P LANG="de-DE" CLASS="western" STYLE="margin-bottom: 0cm"><FONT FACE="Arial, sans-serif">Samstag,
Sonntag und Feiertag</FONT></P>
<P LANG="de-DE" CLASS="western" STYLE="margin-bottom: 0cm"><FONT FACE="Arial, sans-serif">von
13 bis 17 Uhr</FONT></P>
<P LANG="de-DE" CLASS="western" STYLE="margin-bottom: 0cm"><BR>
</P>



<P LANG="de-DE" CLASS="western" STYLE="margin-bottom: 0cm"><FONT FACE="Arial, sans-serif"><B>Information-Homepage:
<a href="http://museum.ff-purgstall.at" target=_blank>museum.ff-purgstall.at</a></B></FONT></P>
<P LANG="de-DE" CLASS="western" STYLE="margin-bottom: 0cm"><FONT FACE="Arial, sans-serif"><B>Telefon:
0664 / 58 429 58</B></FONT></P>
<P LANG="de-DE" CLASS="western" STYLE="margin-bottom: 0cm"><BR>
</P>
<P LANG="de-DE" CLASS="western" STYLE="margin-bottom: 0cm"><BR>
</P>
<img src="NOen_Nr_17-2010_titelseite.jpg" alt="Bericht Er&ouml;ffnung, N&Ouml;N"/>
<img src="Uniform_2_IMG_4650.jpg" alt="Foto von der Er&ouml;ffnung"/>
<img src="Uniform_3_IMG_4682.jpg" alt="Foto von der Er&ouml;ffnung"/>
<img src="Uniform_4_IMG_4694.jpg" alt="Foto von der Er&ouml;ffnung"/>
<img src="Uniform_5_IMG_4700.jpg" alt="Foto von der Er&ouml;ffnung"/>


<?php 
 HTML_trailer();
 ?>

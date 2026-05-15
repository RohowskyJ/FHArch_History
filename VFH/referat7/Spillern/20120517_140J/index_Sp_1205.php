<?php
session_start();
# ------------------------------------------------------------------------------------------------------------------
#
#Inhaltsverzeichnis und Aufruf für Archivierte Berichte


$module    = 'VF_Arch';
$path2ROOT = "../../../../";

$debug = True; $debug = False;  // Debug output Ein/Aus Schalter

require $path2ROOT.'login/common/BA_HTML_Funcs.lib.php' ; 
require $path2ROOT.'login/common/BA_Funcs.lib.php' ;  // Diverse Unterprogramme

initial_debug(); # Wenn $debug=true - Ausgabe von Debug Informationen: $_POST, $_GET, $_FILES

$logo = 'JA';
$header = "<link  href='".$path2ROOT."login/common/css/frame_pict.css' rel='stylesheet' type='text/css'>";
BA_HTML_header('Verein Feuerwehrhistoriker in NÖ) ',$header,'Form','75em'); # Parm: Titel,Subtitel,HeaderLine,Type,width

?>

<body class="w3-container ">
<div class="w3-content ">  <!-- max-width:45em; margin:5em;--->
<fieldset>
<legend>140 Jahre FF Spillern am 17.05.2012 <font size="-1">(Verein Feuerwehrhistoriker in NÖ)</font></legend><br>
<p> <strong>Fotos: Brigitta Laager (Feuerwehrhistoriker in NÖ), Josef Rohowsky (FF Wr. Neudorf, Feuerwehrhistoriker in NÖ)</strong></p>
<p>
<font size="+1">
Den Organisatoren dieses Umzuges kann man nur für die Planung und Durchführung dieses Umzuges
gratulieren und danken: anscheinend reibungsloser Ablauf und bereits ein fertig ausgedruckter "Teilnehmerkatalog" zur Veranstaltung.<br/>
</font>
 &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <a href="Spillern_Katalog_tit_m.png"><img src="Spillern_Katalog_tit_m.png" alt="Titel" width="250px" /></a> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <a href="Spillern_Katalog_innen_m.png"><img src="Spillern_Katalog_innen_m.png" alt="Innen"  width="250px"/></a>
</p>

<div class='w3-table' style='margin=auto'>

<table>
<tbody>                                                  

<tr><td>&nbsp;</td><td colspan="6">Hohe Persönlichkeiten im vorausfahrenden Fahrzeug.<br/>Für die Fahrzeugbeschreibung siehe Nummer 25 in "Fahrzeuge bis 1949".</td><td colspan="4" align="center"><a href="../../../../login/AOrd_Verz/124/09/06/20120517/124-20120517-W-IMG_0266.JPG"><img src="../../../../login/AOrd_Verz/124/09/06/20120517/124-20120517-W-IMG_0266.JPG" alt="0266" width="250px"/></a></td></tr>

<tr><td></td><td colspan="6"><a href="grp_fuss_v3.php" target="Teiln">Fusstruppen</a></td><td colspan="4" align="center"><a href="../../../../login/AOrd_Verz/124/09/06/20120517/124-20120517-W-IMG_0287.JPG"><img src="../../../../login/AOrd_Verz/124/09/06/20120517/124-20120517-W-IMG_0287.JPG" alt="0287" width="250px"/></a></td></tr>
<tr><td></td><td colspan="6"><a href="fzg_bis_1949_v3.php" target="Teiln">Fahrzeuge bis 1949</a></td><td colspan="4" align="center"><a href="../../../../login/AOrd_Verz/124/09/06/20120517/124-20120517-W-IMG_0290.JPG"><img src="../../../../login/AOrd_Verz/124/09/06/20120517/124-20120517-W-IMG_0290.JPG" alt="0290" width="250px"/></a></td></tr>
<tr><td></td><td colspan="6"><a href="fzg_ab_1950_v3.php" target="Teiln">Fahrzeuge ab 1950</a></td><td colspan="4" align="center"><a href="../../../../login/AOrd_Verz/631/09/06/20120517/631-20120517-W-DSCN6249.JPG"><img src="../../../../login/AOrd_Verz/631/09/06/20120517/631-20120517-W-DSCN6249.JPG" alt="6249" width="250px"></a></td></tr>
<tr><td></td><td colspan="6"><a href="fzg_ab_1980_v3.php" target="Teiln">Aktuelle Fahrzeuge (ab 1980)</a></td><td colspan="4" align="center"><a href="../../../../login/AOrd_Verz/631/09/06/20120517/631-20120517-W-DSCN6316.JPG"><img src="../../../../login/AOrd_Verz/631/09/06/20120517/631-20120517-W-DSCN6316.JPG" alt="6316" width="250px"/></a></td></tr>


<tr><td></td><td colspan="6">Ehrentribüne</td><td colspan="4" align="center"><a href="../../../../login/AOrd_Verz/124/09/06/20120517/124-20120517-W-IMG_0464.JPG"><img src="../../../../login/AOrd_Verz/124/09/06/20120517/124-20120517-W-IMG_0464.JPG" alt="0464" width="250px"/></a></td></tr>
<tr><td></td><td colspan="6">Kommentator Jörg Würzelberger</td><td colspan="4" align="center"><a href="../../../../login/AOrd_Verz/124/09/06/20120517/124-20120517-W-IMG_0467.JPG"><img src="../../../../login/AOrd_Verz/124/09/06/20120517/124-20120517-W-IMG_0467.JPG" alt="0467" width="250px"/></a></td></tr>
<tr><td></td><td colspan="6">Verteilung der Erinnerungsstücke an die Teilnehmer des Umzuges</td><td colspan="4" align="center"><a href="../../../../login/AOrd_Verz/124/09/06/20120517/124-20120517-W-IMG_0468.JPG"><img src="../../../../login/AOrd_Verz/124/09/06/20120517/124-20120517-W-IMG_0468.JPG" alt="0468" width="250px"/></a></td></tr>
<tr><td></td><td colspan="6">Ehrentafel für Kommandant und Feuerwehrärztin </td><td colspan="4" align="center"><a href="../../../../login/AOrd_Verz/124/09/06/20120517/124-20120517-W-IMG_0470.JPG"><img src="../../../../login/AOrd_Verz/124/09/06/20120517/124-20120517-W-IMG_0470.JPG" alt="0470" width="250px"/></a><a href="../../../../login/AOrd_Verz/631/09/06/20120517/631-20120517-W-DSCN6422.JPG"><img src="../../../../login/AOrd_Verz/631/09/06/20120517/631-20120517-W-DSCN6422.JPG" alt="6422" width="250px"/></a></td></tr>


</tbody>
     </table>
<p>Quellen:  Fotos: Brigitta Laager, Josef Rohowsky
</p>
     </div>


</fieldset>
</div>
<?php 
 BA_HTML_trailer();
 ?>

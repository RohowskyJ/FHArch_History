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
BA_HTML_header('Verein Feuerwehrhistoriker in NÖ) ',$header,'Form','90em'); # Parm: Titel,Subtitel,HeaderLine,Type,width

?>

<body class="w3-container ">
<!-- div class="w3-content ">  --> <!-- max-width:45em; margin:5em;--->
<fieldset>
<legend>140 Jahre Landesfeuerwehrverband NÖ am 19.09.2009: <font size="-1">(Verein Feuerwehrhistoriker in NÖ)</font></legend><br>
<p> <strong>Fotos: Erich Koller (FF Gmünd), Josef Rohowsky (FF Wr. Neudorf)</strong></p>

<div class='w3-table' style='margin=auto'>

<table>
<tbody>

<tr><td colspan="11" align="center"><strong>Teilnehmende Organisationen</strong></td></tr>
<tr><td></td><td colspan="6">Bergrettung</td><td colspan="4" align="center"><a href="dscn0351_m.jpg"><img src="dscn0351_m.jpg" alt="" width="250px"/></a></td></tr>
<tr><td></td><td colspan="6">Bundesheer, Spürfahrzeug auf "DINGO" Fahrgestell</td><td colspan="4" align="center"><a href="dscn0423_m.jpg"><img src="dscn0423_m.jpg" alt="" width="250px"/></a></td></tr>
<tr><td></td><td colspan="6">Polizei</td><td colspan="4" align="center"><a href="dscn0356_m.jpg"><img src="dscn0356_m.jpg" alt="" width="250px"/></a></td></tr>
<tr><td></td><td colspan="6">Rettung</td><td colspan="4" align="center"><a href="dscn0357_m.jpg"><img src="dscn0357_m.jpg" alt="" width="250px"/></a></td></tr>
<tr><td></td><td colspan="6">ÖBB, Lok Taurus im Feuerwehrdesign</td><td colspan="4" align="center"><a href="dscn0392_m.jpg"><img src="dscn0392_m.jpg" alt="" width="250px"/></a> &nbsp; <a href="dscn0398_m.jpg"><img src="dscn0398_m.jpg" alt="" width="250px"/></a></td></tr>
<tr><td colspan="11" align="center"><strong>Musikalische Unterstützung</strong></td></tr>
<tr><td></td><td colspan="6">Militärkapelle</td><td colspan="4" align="center"><a href="dscn0474_m.jpg"><img src="dscn0474_m.jpg" alt="" width="250px"/></a></td></tr>
<tr><td></td><td colspan="6">Polizeikapelle</td><td colspan="4" align="center"><a href="dscn0521_m.jpg"><img src="dscn0521_m.jpg" alt="" width="250px"/></a></td></tr>
<tr><td></td><td colspan="6">Trachtenkapelle</td><td colspan="4" align="center"><a href="dscn0487_m.jpg"><img src="dscn0487_m.jpg" alt="" width="250px"/></a></td></tr>
<tr><td colspan="11" align="center"><strong>Beim Festakt</strong></td></tr>
<tr><td></td><td colspan="6">Einmarsch der Fahnenträger</td><td colspan="4" align="center"><a href="dscn0510_m.jpg"><img src="dscn0510_m.jpg" alt="" width="250px"/></a></td></tr>
<tr><td></td><td colspan="6">Zum Festakt angetreten</td><td colspan="4" align="center"><a href="dscn0532_m.jpg"><img src="dscn0532_m.jpg" alt="" width="250px"/></a></td></tr>
<tr><td colspan="11" align="center"><strong>Aktuelles bei der Feuerwehr</strong></td></tr>
<tr><td></td><td colspan="6">Straße - Schiene - Tunnel ein Mehrwegefahrzeug</td><td colspan="4" align="center"><a href="dscn0395_m.jpg"><img src="dscn0395_m.jpg" alt="" width="250px"/></a></td></tr>
<tr><td></td><td colspan="6">Teleskopmastbühne</td><td colspan="4" align="center"><a href="dscn0429_m.jpg"><img src="dscn0429_m.jpg" alt="" width="250px"/></a></td></tr>
<tr><td></td><td colspan="6">Katatrophenhilfsdienst (KHD), Leistungsfähige Pumpe</td><td colspan="4" align="center"><a href="dscn0417_m.jpg"><img src="dscn0417_m.jpg" alt="" width="250px"/></a></td></tr>
<tr><td></td><td colspan="6">Flugdienst, hier ein Polizeihubschrauber als Wassertransporter</td><td colspan="4" align="center"><a href="dscn0419_m.jpg"><img src="dscn0419_m.jpg" alt="" width="250px"/></a> &nbsp; <a href="dscn0648_m.jpg"><img src="dscn0648_m.jpg" alt="" width="250px"/></a></td></tr>
<tr><td></td><td colspan="6">Feuerwehrjugend</td><td colspan="4" align="center"><a href="dscn0461_m.jpg"><img src="dscn0461_m.jpg" alt="" width="250px"/></a></td></tr>
<tr><td colspan="11" align="center"><strong>Oldtimer Parade bei 140 Jahre Landesfeuerwehrverband NÖ am 19.09.2009</strong></td></tr>
<tr><th colspan="11">Am Hauptplatz, Oldtimer</th></tr>                                                        
<tr><td></td><td colspan="4">Kastenspritze</td><td></td><td colspan="3">Privates Feuerwehrmuseum Frohsdorf</td><td colspan="3" align="center"><a href="../../../../login/AOrd_Verz/124/09/06/20090919/124-20090919-W-IMG_9848.JPG"><img src="../../../../login/AOrd_Verz/124/09/06/20090919/124-20090919-W-IMG_9848.JPG" alt="" width="250px"/></a> &nbsp; <a href="dscn0452_m.jpg"><img src="dscn0452_m.jpg" alt="" width="250px"/></a> &nbsp; <a href="dscn0466_m.jpg"><img src="dscn0466_m.jpg" alt="" width="250px"/></a></td></tr>
<tr><td></td><td colspan="4">Kastenspritze</td><td></td><td colspan="3">Feuerwehrmuseum Münchendorf</td><td colspan="3" align="center"><a href="dscn0457_m.jpg"><img src="dscn0457_m.jpg" alt="" width="250px"/></a> &nbsp; <a href="dscn0473_m.jpg"><img src="dscn0473_m.jpg" alt="" width="250px"/></a></td></tr>
<tr><th colspan="11">Beim Umzug dabei:</th></tr>
<tr><th>StartNr.</th><th>Marke/Type</th><th>Bauj.</th><th>Takt.Verw.</th><th>Aufbau</th><th>PS/kW</th><th>Hubr.</th><th>EigGew.</th><th>Organisation</th><th>Name</th><th>Kommentar</tr>
<tr><td>00</td><td>Musikkapelle</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td><a href="../../../../login/AOrd_Verz/124/09/06/20090919/124-20090919-W-IMG_9902.JPG"><img src="../../../../login/AOrd_Verz/124/09/06/20090919/124-20090919-W-IMG_9902.JPG" alt="" width="250px"/></a></td><td></td></tr>
<tr><td>00</td><td>Reitergruppe</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td><a href="../../../../login/AOrd_Verz/124/09/06/20090919/124-20090919-W-IMG_9903.JPG"><img src="../../../../login/AOrd_Verz/124/09/06/20090919/124-20090919-W-IMG_9903.JPG" alt="" width="250px"/></a></td><td></td></tr>
<tr><td>01</td><td>Handwerker mit Löschausrüstung</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td><a href="../../../../login/AOrd_Verz/124/09/06/20090919/124-20090919-W-IMG_9908.JPG"><img src="../../../../login/AOrd_Verz/124/09/06/20090919/124-20090919-W-IMG_9908.JPG" alt="" width="250px"/></a></td><td></td></tr>
<tr><td>02</td><td>Handspritze mit fahrbarem Wasserbehäter</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td><a href="../../../../login/AOrd_Verz/124/09/06/20090919/124-20090919-W-IMG_9909.JPG"><img src="../../../../login/AOrd_Verz/124/09/06/20090919/124-20090919-W-IMG_9909.JPG" alt="" width="250px"/></a></td><td></td></tr>
<tr><td>03</td><td>Kastenspritze</td><td>1847</td><td></td><td></td><td></td><td></td><td></td><td>seit 1970 bei FF Leobersdorf</td><td><a href="../../../../login/AOrd_Verz/124/09/06/20090919/124-20090919-W-IMG_9911.JPG"><img src="../../../../login/AOrd_Verz/124/09/06/20090919/124-20090919-W-IMG_9911.JPG" alt="" width="250px"/></a></td><td></td></tr>
<tr><td>04</td><td>Holzleiter Magirus</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td><a href="../../../../login/AOrd_Verz/124/09/06/20090919/124-20090919-W-IMG_9913.JPG"><img src="../../../../login/AOrd_Verz/124/09/06/20090919/124-20090919-W-IMG_9913.JPG" alt="" width="250px"/></a></td><td></td></tr>
<tr><td>05</td><td>Kastenspritze</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td><a href="../../../../login/AOrd_Verz/124/09/06/20090919/124-20090919-W-IMG_9916.JPG"><img src="../../../../login/AOrd_Verz/124/09/06/20090919/124-20090919-W-IMG_9916.JPG" alt="" width="250px"/></a></td><td></td></tr>
<tr><td>06</td><td>Handdruckspritze</td><td>1902</td><td></td><td></td><td></td><td></td><td></td><td>FF Sooss</td><td><a href="../../../../login/AOrd_Verz/124/09/06/20090919/124-20090919-W-IMG_9917.JPG"><img src="../../../../login/AOrd_Verz/124/09/06/20090919/124-20090919-W-IMG_9917.JPG" alt="" width="250px"/></a></td><td></td></tr>
<tr><td>07</td><td>Dampfspritze</td><td>19xx</td><td></td><td></td><td></td><td></td><td></td><td>FF Gainfarn</td><td><a href="../../../../login/AOrd_Verz/124/09/06/20090919/124-20090919-W-IMG_9920.JPG"><img src="../../../../login/AOrd_Verz/124/09/06/20090919/124-20090919-W-IMG_9920.JPG" alt="" width="250px"/></a></td><td></td></tr>
<tr><td>08</td><td>Handdruckspritze</td><td>1870</td><td></td><td></td><td></td><td></td><td></td><td>FF Gainfarn</td><td><a href="../../../../login/AOrd_Verz/124/09/06/20090919/124-20090919-W-IMG_9923.JPG"><img src="../../../../login/AOrd_Verz/124/09/06/20090919/124-20090919-W-IMG_9923.JPG" alt="" width="250px"/></a></td><td></td></tr>
<tr><td>09</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td>Stadtmuseum Traiskirchen, Abt. Feuerwehr</td><td><a href="../../../../login/AOrd_Verz/124/09/06/20090919/124-20090919-W-IMG_9924.JPG"><img src="../../../../login/AOrd_Verz/124/09/06/20090919/124-20090919-W-IMG_9924.JPG" alt="" width="250px"/></a></td><td></td></tr>
<tr><td>10</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td>FF Traisen</td><td><a href="../../../../login/AOrd_Verz/124/09/06/20090919/124-20090919-W-IMG_9927.JPG"><img src="../../../../login/AOrd_Verz/124/09/06/20090919/124-20090919-W-IMG_9926.JPG" alt="" width="250px"/></a></td><td></td></tr>
<!--
<tr><td>11</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td><a href="../../../../login/AOrd_Verz/124/09/06/20090919/124-20090919-W-IMG_9600.JPG"><img src="../../../../login/AOrd_Verz/124/09/06/20090919/124-20090919-W-IMG_9600.JPG" alt="" width="250px"/></a></td><td></td></tr>
-->
<tr><td>12</td><td>Austro Daimler 920</td><td>1912</td><td></td><td></td><td>24</td><td>FF Mödling</td><td></td><td>Feuerwehrauto mit Löschausrüstung</td><td><a href="../../../../login/AOrd_Verz/124/09/06/20090919/124-20090919-W-IMG_9929.JPG"><img src="../../../../login/AOrd_Verz/124/09/06/20090919/124-20090919-W-IMG_9929.JPG" alt="" width="250px"/></a></td><td></td></tr>
<tr><td>13</td><td>Motorspritze Gr. III</td><td>1920</td><td></td><td></td><td></td><td></td><td></td><td>FF Gföhl</td><td><a href="../../../../login/AOrd_Verz/124/09/06/20090919/124-20090919-W-IMG_9930.JPG"><img src="../../../../login/AOrd_Verz/124/09/06/20090919/124-20090919-W-IMG_9930.JPG" alt="" width="250px"/></a></td><td></td></tr>
<tr><td>14</td><td>Hanomag Diesel mit Drehleiter<td>DL: 1909</td><td></td><td></td><td></td><td></td><td></td><td>Privatmuseum Alois Weiß, Pottendorf</td><td><a href="../../../../login/AOrd_Verz/124/09/06/20090919/124-20090919-W-IMG_9932.JPG"><img src="../../../../login/AOrd_Verz/124/09/06/20090919/124-20090919-W-IMG_9932.JPG" alt="" width="250px"/></a></td><td></td></tr>
<tr><td>15</td><td>Rettungswagen, Holz</td><td>1908</td><td></td><td></td><td>2</td><td>0</td><td></td><td>ÖRK KHD Mödling</td><td><a href="../../../../login/AOrd_Verz/124/09/06/20090919/124-20090919-W-IMG_9936.JPG"><img src="../../../../login/AOrd_Verz/124/09/06/20090919/124-20090919-W-IMG_9936.JPG" alt="" width="250px"/></a></td><td>seit 1896 nachgewiesen, bis Anfang 2. Weltkrieg, Rettungsdienst von Feuerwehren durchgef&uuml;hrt, vereinzelt noch heute (FF Admont, Stmk)</td></tr>
<tr><td>16</td><td>Gräf und Stift</td><td></td><td></td><td></td><td></td><td></td><td></td><td>FF Bad Vöslau</td><td><a href="../../../../login/AOrd_Verz/124/09/06/20090919/124-20090919-W-IMG_9939.JPG"><img src="../../../../login/AOrd_Verz/124/09/06/20090919/124-20090919-W-IMG_9939.JPG" alt="" width="250px"/></a></td><td></td></tr>
<tr><td>17</td><td>ÖAF AFN 39LN</td><td></td><td></td><td></td><td></td><td></td><td></td><td>FF Brunn am Gebirge</td><td><a href="../../../../login/AOrd_Verz/124/09/06/20090919/124-20090919-W-IMG_9941.JPG"><img src="../../../../login/AOrd_Verz/124/09/06/20090919/124-20090919-W-IMG_9941.JPG" alt="" width="250px"/></a></td><td></td></tr>
<!--
<tr><td>18</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td><a href="../../../../login/AOrd_Verz/124/09/06/20090919/124-20090919-W-IMG_9600.JPG"><img src="../../../../login/AOrd_Verz/124/09/06/20090919/124-20090919-W-IMG_9600.JPG" alt="" width="250px"/></a></td><td></td></tr>
-->
<tr><td>19</td><td>Mercedes L 1500 S</td><td>1941</td><td>LLG</td><td>Rosenbauer</td><td>60/</td><td>2594</td><td>2500</td><td>Feuerwehrmuseum Gars</td><td><a href="../../../../login/AOrd_Verz/124/09/06/20090919/124-20090919-W-IMG_9942.JPG"><img src="../../../../login/AOrd_Verz/124/09/06/20090919/124-20090919-W-IMG_9942.JPG" alt="Mercedes L1500S" width="250px"/></a></td><td></td></tr>
<tr><td>20</td><td>Mercedes L 1500 S</td><td>1947</td><td>LF 8</td><td></td><td></td><td></td><td></td><td></td><td><a href="../../../../login/AOrd_Verz/124/09/06/20090919/124-20090919-W-IMG_9978.JPG"><img src="../../../../login/AOrd_Verz/124/09/06/20090919/124-20090919-W-IMG_9978.JPG" alt="" width="250px"/></a></td><td></td></tr>
<tr><td>21</td><td>Opel Blitz</td><td>1939</td><td>TLF</td><td></td><td>75</td><td></td><td></td><td>Erlauftaler Feuerwehrmuseum</td><td><a href="../../../../login/AOrd_Verz/124/09/06/20090919/124-20090919-W-IMG_9943.JPG"><img src="../../../../login/AOrd_Verz/124/09/06/20090919/124-20090919-W-IMG_9943.JPG" alt="" width="250px"/></a></td><td></td></tr>
<tr><td>22</td><td>Dodge Truck 3/4t 4x4 WC52</td><td>1943</td><td>VF</td><td></td><td>115</td><td>3875</td><td></td><td>FF Thal</td><td><a href="../../../../login/AOrd_Verz/124/09/06/20090919/124-20090919-W-IMG_9944.JPG"><img src="../../../../login/AOrd_Verz/124/09/06/20090919/124-20090919-W-IMG_9944.JPG" alt="" width="250px"/></a></td><td></td></tr>
<tr><td>23</td><td>Nufield Traktor mit Feuerwehranhänger</td><td></td><td></td><td></td><td></td><td></td><td></td><td>FF Statzendorf</td><td><a href="../../../../login/AOrd_Verz/124/09/06/20090919/124-20090919-W-IMG_9945.JPG"><img src="../../../../login/AOrd_Verz/124/09/06/20090919/124-20090919-W-IMG_9945.JPG" alt="" width="250px"/></a></td><td></td></tr>
<tr><td>24</td><td>Opel Blitz</td><td></td><td>LF</td><td>Langer, Wr. Neudorf</td><td></td><td></td><td></td><td>FF Fischamend</td><td><a href="../../../../login/AOrd_Verz/124/09/06/20090919/124-20090919-W-IMG_9946.JPG"><img src="../../../../login/AOrd_Verz/124/09/06/20090919/124-20090919-W-IMG_9946.JPG" alt="" width="250px"/></a></td><td></td></tr>
<tr><td>25</td><td>Steyr</td><td></td><td>TLF</td><td></td><td></td><td></td><td></td><td>FF Perchtoldsdorf</td><td><a href="../../../../login/AOrd_Verz/124/09/06/20090919/124-20090919-W-IMG_9947.JPG"><img src="../../../../login/AOrd_Verz/124/09/06/20090919/124-20090919-W-IMG_9947.JPG" alt="" width="250px"/></a></td><td></td></tr>
<tr><td>26</td><td>Steyr 586</td><td></td><td>TLF</td><td></td><td></td><td></td><td></td><td>FF Gföhl</td><td><a href="../../../../login/AOrd_Verz/124/09/06/20090919/124-20090919-W-IMG_9949.JPG"><img src="../../../../login/AOrd_Verz/124/09/06/20090919/124-20090919-W-IMG_9949.JPG" alt="" width="250px"/></a></td><td></td></tr>
<tr><td>27</td><td>VW Bus</td><td></td><td>MTF</td><td></td><td></td><td></td><td></td><td>FF Baden Stadt</td><td><a href="../../../../login/AOrd_Verz/124/09/06/20090919/124-20090919-W-IMG_9950.JPG"><img src="../../../../login/AOrd_Verz/124/09/06/20090919/124-20090919-W-IMG_9950.JPG" alt="" width="250px"/></a></td><td></td></tr>
<tr><td>28</td><td>Opel Blitz</td><td></td><td>TLF 1000</td><td></td><td></td><td></td><td></td><td>FF Münchendorf</td><td><a href="../../../../login/AOrd_Verz/124/09/06/20090919/124-20090919-W-IMG_9951.JPG"><img src="../../../../login/AOrd_Verz/124/09/06/20090919/124-20090919-W-IMG_9951.JPG" alt="" width="250px"/></a></td><td></td></tr>

</tbody>
     </table>

     </div>



</fieldset>
</div>
<?php 
 BA_HTML_trailer();
 ?>

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
<h1>2. Waldviertler FIRE-Trophy 2006</h1>
<h2>Rundfahrt historischer Einsatzfahrzeuge</h2>
<p>Veranstalter: <strong>Verein Feuerwehrmuseum Gars</strong> </p>

<p>Die Waldviertler FIRE-Trophy fand am 2. und 3. September 2006 statt..JPG
<p />
<p>
Am 2. September fand im Rahmen der Trophy in Gars am Kamp ein
<a href="../../../VFHNOE/Genv/20060902/index_genv_0609.php" target=_new>
Historikersymposium unter dem Titel "Historische Einsatzfahrzeuge"</a>
statt. Es wurden sehr interessante
Vorträ;ge gehalten, wobei der Zeitrahmen leider viel zu kurz war
(Dr. Alfred Zeilmayr, Hans Gilbert Müller, wHR Mag. Horst Sekyra, Ing. Josef Hötzl,
Obstlt. Rupert Schoißwohl, unter Moderation von Fr. Patricia Fischer, die Ihre
Vorträge leider dem Zeitdruck opfern musste).
</p>

<p>Nach den Vorträgen, die trotz starken Kürzungen über den
vorgesehenen Zeitrahmen hinaus dauerten, wurde mit den bereits zur Trophy
eingetroffenen Fahrzeugen die Amethyst-Welt in Maissau besucht.
</p>

<a href="../../../../../login/AOrd_Verz/124/09/06/20060903/124-20060903-W-IMG_4421.JPG" target="B1" ><img src="../../../../../login/AOrd_Verz/124/09/06/20060903/124-20060903-W-IMG_4421.JPG" alt="Ehrengaeste Start" align="right" width="30%"></a>
<p>Die Rundfahrt begann am 3. September 2006 um 9:30 am Hauptplatz in Eggenburg, wo
auf der Ehrentribüne  einige wichtige regionale und überregionale
Persönlichkeiten Platz genommen hatten.
</p>

<p>Die Fahrt führte von Eggenburg über Burgschleinitz, Maissau,
Reinprechtspölla, Maria Dreieichen, Stockern, nach Sigmundsherberg,
wo beim Eisenbahnmuseum die Mittagspause abgehalten wurde.
Am Nachmittag ging die Fahrt durch Kattau, Stoitzendorf, Straning,
Limberg (Sonderprüfung), Oberdürnbach, Burgschleinitz, Reinprechtspölla,
Nonndorf, zum Ziel in Gars. Die Streckenlänge betrug 67 km.
</p>

<h2>Das Starterfeld:</h2>


<div class='w3-table' style='margin=auto'>

<table>
<tbody>

<tr>
<th >1
  </th>
    <td>
       
       <table summary="Daten" border="1">
       <tr>
        <th width="20%">angemeldet (Fahrer)</th>
         <td>Klaghofer Manfred, Wien</td>
       </tr>

       <tr>
        <th>Fahrzeug (Marke + Type)</th>
         <td>Austro Fiat AFN</td>
       </tr>

       <tr>
        <th>Funktion bei der Feuerwehr</th>
         <td>Gasspritze</td>
       </tr>

       <tr>
        <th>Baujahr</th>
         <td>1927</td>
       </tr>

       <tr>
        <th>Besonderheit</th>
         <td>vormals BF Wien</td>
       </tr>

       </table>
</td>

<td>
<img src="../../../../../login/AOrd_Verz/124/09/06/20060903/124-20060903-W-IMG_4364.JPG" alt="Fahrzeug 1" align="left" width="90%">
      </td>

</tr>

<tr>
<th >2
  </th>
    <td>nicht am Start
      </td>
</tr>
<tr>
<th >3
  </th>
    <td>

       <table summary="Daten" border="1">
       <tr>
        <th width="20%">angemeldet (Fahrer)</th>
         <td>Feuerwehrmuseum Gars</td>
       </tr>

       <tr>
        <th>Fahrzeug (Marke + Type)</th>
         <td>Mercedes L 1500 S</td>
       </tr>

       <tr>
        <th>Funktion bei der Feuerwehr</th>
         <td>LLG</td>
       </tr>

       <tr>
        <th>Baujahr</th>
         <td>1941</td>
       </tr>

       <tr>
        <th>Besonderheit</th>
         <td>Polizeigr&uuml;n</td>
       </tr>

       </table>
       </td>
       <td>
<img src="../../../../../login/AOrd_Verz/124/09/06/20060903/124-20060903-W-IMG_4366.JPG" alt="Fahrzeug 3" align="right"  width="90%">
      </td>
</tr>
<tr>
<th >4
  </th>
    <td>

       <table summary="Daten" border="1">
       <tr>
        <th width="20%">angemeldet (Fahrer)</th>
         <td>Feuerwehrmuseum Gars</td>
       </tr>

       <tr>
        <th>Fahrzeug (Marke + Type)</th>
         <td>Opel Blitz 3.6-36</td>
       </tr>

       <tr>
        <th>Funktion bei der Feuerwehr</th>
         <td>TWG 1700</td>
       </tr>

       <tr>
        <th>Baujahr</th>
         <td>1942</td>
       </tr>

       <tr>
        <th>Besonderheit</th>
         <td>vorm FF Poysdorf</td>
       </tr>

       </table>
       </td>
       <td>
       <img src="../../../../../login/AOrd_Verz/124/09/06/20060903/124-20060903-W-IMG_4367.JPG" alt="Fahrzeug 4" align="left" width="90%">
      </td>
</tr>
<tr>
<th >5
  </th>
    <td>

       <table summary="Daten" border="1">
       <tr>
        <th width="20%">angemeldet (Fahrer)</th>
         <td>Feuerwehrmuseum Gars</td>
       </tr>

       <tr>
        <th>Fahrzeug (Marke + Type)</th>
         <td>Opel Blitz 3.6-6700 A</td>
       </tr>

       <tr>
        <th>Funktion bei der Feuerwehr</th>
         <td>TLF 1500</td>
       </tr>

       <tr>
        <th>Baujahr</th>
         <td>1943</td>
       </tr>

       <tr>
        <th>Besonderheit</th>
         <td>vormals BtF Tilly/K&auml;rnten</td>
       </tr>

       </table>
              </td>
       
      <td>
      <img src="../../../../../login/AOrd_Verz/124/09/06/20060903/124-20060903-W-IMG_4369.JPG" alt="Fahrzeug 5" align="right" width="90%">
      </td>
</tr>
<tr>
<th >6
  </th>
    <td>
       
       <table summary="Daten" border="1">
       <tr>
        <th width="20%">angemeldet (Fahrer)</th>
         <td>Wanek Friedrich</td>
       </tr>

       <tr>
        <th>Fahrzeug (Marke + Type)</th>
         <td>Steyr Puch 703 APF Haflinger</td>
       </tr>

       <tr>
        <th>Funktion bei der Feuerwehr</th>
         <td>BLF</td>
       </tr>

       <tr>
        <th>Baujahr</th>
         <td>1964</td>
       </tr>

       <tr>
        <th>Besonderheit</th>
         <td>vormals FF Tradigist</td>
       </tr>

       </table>
              </td>
       
      <td>
      <img src="../../../../../login/AOrd_Verz/124/09/06/20060903/124-20060903-W-IMG_4370.JPG" alt="Fahrzeug 6" align="left" width="90%">
      </td>
</tr>
<tr>
<th >7
  </th>
    <td>
       
       <table summary="Daten" border="1">
       <tr>
        <th width="20%">angemeldet (Fahrer)</th>
         <td>Hengl Max Ing. (Johann Rauscher)</td>
       </tr>

       <tr>
        <th>Fahrzeug (Marke + Type)</th>
         <td>Steyr 586g</td>
       </tr>

       <tr>
        <th>Funktion bei der Feuerwehr</th>
         <td>TLF 1800</td>
       </tr>

       <tr>
        <th>Baujahr</th>
         <td>1961</td>
       </tr>

       <tr>
        <th>Besonderheit</th>
         <td>vormals FF Stiefern</td>
       </tr>

       </table>
              </td>
       
      <td>
      <img src="../../../../../login/AOrd_Verz/124/09/06/20060903/124-20060903-W-IMG_4371.JPG" alt="Fahrzeug 7 " align="right" width="90%">
      </td>
</tr>
<tr>
<th >8
  </th>
    <td>
       
       <table summary="Daten" border="1">
       <tr>
        <th width="20%">angemeldet (Fahrer)</th>
         <td>FF Purgstall (Franz Wiesenhofer)</td>
       </tr>

       <tr>
        <th>Fahrzeug (Marke + Type)</th>
         <td>Opel Blitz 1.75</td>
       </tr>

       <tr>
        <th>Funktion bei der Feuerwehr</th>
         <td>LLF</td>
       </tr>

       <tr>
        <th>Baujahr</th>
         <td>1959</td>
       </tr>

       <tr>
        <th>Besonderheit</th>
         <td>&nbsp;</td>
       </tr>

       </table>
              </td>
       
      <td>
      <img src="../../../../../login/AOrd_Verz/124/09/06/20060903/124-20060903-W-IMG_4372.JPG" alt="Fahrzeug 8 " align="left" width="90%">
      </td>
</tr>
<tr>
<th >9
  </th>
    <td>
       
       <table summary="Daten" border="1">
       <tr>
        <th width="20%">angemeldet (Fahrer)</th>
         <td>Hengl Max Ing. (Franz Arthaber)</td>
       </tr>

       <tr>
        <th>Fahrzeug (Marke + Type)</th>
         <td>Chevrolet Suburban</td>
       </tr>

       <tr>
        <th>Funktion bei der Feuerwehr</th>
         <td>LF</td>
       </tr>

       <tr>
        <th>Baujahr</th>
         <td>1978</td>
       </tr>

       <tr>
        <th>Besonderheit</th>
         <td>vormals FF Zitternberg</td>
       </tr>

       </table>
              </td>
       
      <td>
      <img src="../../../../../login/AOrd_Verz/124/09/06/20060903/124-20060903-W-IMG_4373.JPG" alt="Fahrzeug 9 " align="right" width="90%">
      </td>
</tr>
<tr>
<th >10
  </th>
    <td>
       
       <table summary="Daten" border="1">
       <tr>
        <th width="20%">angemeldet (Fahrer)</th>
         <td>Umgeher Alfred, St. P&ouml;lten</td>
       </tr>

       <tr>
        <th>Fahrzeug (Marke + Type)</th>
         <td>VW 11 Cabrio, Aufbau Austro Tatra</td>
       </tr>

       <tr>
        <th>Funktion bei der Feuerwehr</th>
         <td>&nbsp;</td>
       </tr>

       <tr>
        <th>Baujahr</th>
         <td>1953</td>
       </tr>

       <tr>
        <th>Besonderheit</th>
         <td><strong>vormals Gendarmerie</strong></td>
       </tr>

       </table>
              </td>
       
      <td>
       <img src="../../../../../login/AOrd_Verz/124/09/06/20060903/124-20060903-W-IMG_4374.JPG" alt="Fahrzeug 10 " align="left" width="90%">
      </td>
</tr>
<tr>
<th >11
  </th>
    <td>
       
       <table summary="Daten" border="1">
       <tr>
        <th width="20%">angemeldet (Fahrer)</th>
         <td>FF Poysdorf (Josef Pfeiffer)</td>
       </tr>

       <tr>
        <th>Fahrzeug (Marke + Type)</th>
         <td>VW 23</td>
       </tr>

       <tr>
        <th>Funktion bei der Feuerwehr</th>
         <td>KDO</td>
       </tr>

       <tr>
        <th>Baujahr</th>
         <td>1963</td>
       </tr>

       <tr>
        <th>Besonderheit</th>
         <td>&nbsp;</td>
       </tr>

       </table>
              </td>
       
      <td>
       <img src="../../../../../login/AOrd_Verz/124/09/06/20060903/124-20060903-W-IMG_4377.JPG" alt="Fahrzeug 11 " align="right" width="90%">
      </td>
</tr>
<tr>
<th >12
  </th>
    <td>
        
       <table summary="Daten" border="1">
       <tr>
        <th width="20%">angemeldet (Fahrer)</th>
         <td>BF Wien</td>
       </tr>

       <tr>
        <th>Fahrzeug (Marke + Type)</th>
         <td>Morris Commercial C8</td>
       </tr>

       <tr>
        <th>Funktion bei der Feuerwehr</th>
         <td>R&uuml;st</td>
       </tr>

       <tr>
        <th>Baujahr</th>
         <td>1941</td>
       </tr>

       <tr>
        <th>Besonderheit</th>
         <td>ehemaliger Artillerieschlepper, dann FF Gablitz</td>
       </tr>

       </table>
              </td>
       
      <td>
       <img src="../../../../../login/AOrd_Verz/124/09/06/20060903/124-20060903-W-IMG_4478.JPG" alt="Fahrzeug 12 " align="left" width="90%">
      </td>
</tr>
<tr>
<th >13
  </th>
    <td>
       
       <table summary="Daten" border="1">
       <tr>
        <th width="20%">angemeldet (Fahrer)</th>
         <td>Feuerwehr- Oldtimerverein Wels</td>
       </tr>

       <tr>
        <th>Fahrzeug (Marke + Type)</th>
         <td>Steyr 586</td>
       </tr>

       <tr>
        <th>Funktion bei der Feuerwehr</th>
         <td>TLF 2000</td>
       </tr>

       <tr>
        <th>Baujahr</th>
         <td>1966</td>
       </tr>

       <tr>
        <th>Besonderheit</th>
         <td>vormals FF Braunegg</td>
       </tr>

       </table>
              </td>
       
      <td>
       <img src="../../../../../login/AOrd_Verz/124/09/06/20060903/124-20060903-W-IMG_4378.JPG" alt="Fahrzeug 13 " align="right" width="90%">
      </td>
</tr>
<tr>
<th >14
  </th>
    <td>
       
       <table summary="Daten" border="1">
       <tr>
        <th width="20%">angemeldet (Fahrer)</th>
         <td>Feuerwehr- Oldtimerverein Wels</td>
       </tr>

       <tr>
        <th>Fahrzeug (Marke + Type)</th>
         <td>Austro Fiat AFN 36</td>
       </tr>

       <tr>
        <th>Funktion bei der Feuerwehr</th>
         <td>LF + Anhängerspritze</td>
       </tr>

       <tr>
        <th>Baujahr</th>
         <td>1927</td>
       </tr>

       <tr>
        <th>Besonderheit</th>
         <td>&nbsp;</td>
       </tr>

       </table>
              </td>
       
      <td>
      <img src="../../../../../login/AOrd_Verz/124/09/06/20060903/124-20060903-W-IMG_4380.JPG" alt="Fahrzeug 14 " align="left" width="90%">
      </td>
</tr>
<tr>
<th >15
  </th>
    <td>
       
       <table summary="Daten" border="1">
       <tr>
        <th width="20%">angemeldet (Fahrer)</th>
         <td>Feuerwehr- Oldtimerverein Wels</td>
       </tr>

       <tr>
        <th>Fahrzeug (Marke + Type)</th>
         <td>Opel Blitz 3.6-36</td>
       </tr>

       <tr>
        <th>Funktion bei der Feuerwehr</th>
         <td>FLKS 15</td>
       </tr>

       <tr>
        <th>Baujahr</th>
         <td>1940</td>
       </tr>

       <tr>
        <th>Besonderheit</th>
         <td>P.+A.Magirus</td>
       </tr>

       </table>
              </td>
       
      <td>
      <img src="../../../../../login/AOrd_Verz/124/09/06/20060903/124-20060903-W-IMG_4382.JPG" alt="Fahrzeug  " align="right" width="90%">
      </td>
</tr>
<tr>
<th >16
  </th>
    <td>
       
       <table summary="Daten" border="1">
       <tr>
        <th width="20%">angemeldet (Fahrer)</th>
         <td>Feuerwehr- Oldtimerverein Wels</td>
       </tr>

       <tr>
        <th>Fahrzeug (Marke + Type)</th>
         <td>Opel Blitz 3.6-6700 A</td>
       </tr>

       <tr>
        <th>Funktion bei der Feuerwehr</th>
         <td>TWG 1500</td>
       </tr>

       <tr>
        <th>Baujahr</th>
         <td>1943/1955</td>
       </tr>

       <tr>
        <th>Besonderheit</th>
         <td>Modell Wien</td>
       </tr>

       </table>
              </td>
       
      <td>
      <img src="../../../../../login/AOrd_Verz/124/09/06/20060903/124-20060903-W-IMG_4383.JPG" alt="Fahrzeug 16  " align="left" width="90%">
      </td>
</tr>
<tr>
<th >17
  </th>
    <td>
       
       <table summary="Daten" border="1">
       <tr>
        <th width="20%">angemeldet (Fahrer)</th>
         <td>Feuerwehr- Oldtimerverein Wels</td>
       </tr>

       <tr>
        <th>Fahrzeug (Marke + Type)</th>
         <td>Mercedes L4500 S</td>
       </tr>

       <tr>
        <th>Funktion bei der Feuerwehr</th>
         <td>DL 22 Magirus</td>
       </tr>

       <tr>
        <th>Baujahr</th>
         <td>1944</td>
       </tr>

       <tr>
        <th>Besonderheit</th>
         <td>vormals RLM SDL 22</td>
       </tr>

       </table>
              </td>
       
      <td>
      <img src="../../../../../login/AOrd_Verz/124/09/06/20060903/124-20060903-W-IMG_4384.JPG" alt="Fahrzeug 17 " align="right" width="90%"> 
      </td>
</tr>
<tr>
<th >18
  </th>
    <td>
       
       <table summary="Daten" border="1">
       <tr>
        <th width="20%">angemeldet (Fahrer)</th>
         <td>FF Lohnsburg am Kobernau&szlig;erwald (Johann Walchetseder)</td>
       </tr>

       <tr>
        <th>Fahrzeug (Marke + Type)</th>
         <td>Dodge WC 55</td>
       </tr>

       <tr>
        <th>Funktion bei der Feuerwehr</th>
         <td>LF</td>
       </tr>

       <tr>
        <th>Baujahr</th>
         <td>1939/corr. 1943</td>
       </tr>

       <tr>
        <th>Besonderheit</th>
         <td>als BLF noch im Einsatz</td>
       </tr>

       </table>
              </td>
       
      <td>
      <img src="../../../../../login/AOrd_Verz/124/09/06/20060903/124-20060903-W-IMG_4385.JPG" alt="Fahrzeug 18 " align="left" width="90%">
      </td>
</tr>
<tr>
<th >19
  </th>
    <td>
       
       <table summary="Daten" border="1">
       <tr>
        <th width="20%">angemeldet (Fahrer)</th>
         <td>FF Klosterneuburg Stadt (Leopold Katzmayer </td>
       </tr>

       <tr>
        <th>Fahrzeug (Marke + Type)</th>
         <td>Mercedes LAF 322/46</td>
       </tr>

       <tr>
        <th>Funktion bei der Feuerwehr</th>
         <td>TLF 2000</td>
       </tr>

       <tr>
        <th>Baujahr</th>
         <td>1963</td>
       </tr>

       <tr>
        <th>Besonderheit</th>
         <td>&nbsp;</td>
       </tr>

       </table>
              </td>
       
      <td>
      <img src="../../../../../login/AOrd_Verz/124/09/06/20060903/124-20060903-W-IMG_4386.JPG" alt="Fahrzeug 19 " align="right" width="90%">
      </td>
</tr>
<tr>
<th >20
  </th>
    <td>
       
       <table summary="Daten" border="1">
       <tr>
        <th width="20%">angemeldet (Fahrer)</th>
         <td>Feuerwache Eberweis</td>
       </tr>

       <tr>
        <th>Fahrzeug (Marke + Type)</th>
         <td>Opel Blitz 1.75</td>
       </tr>

       <tr>
        <th>Funktion bei der Feuerwehr</th>
         <td>LF</td>
       </tr>

       <tr>
        <th>Baujahr</th>
         <td>1958</td>
       </tr>

       <tr>
        <th>Besonderheit</th>
         <td></td>
       </tr>

       </table>
              </td>
       
      <td>
      <img src="../../../../../login/AOrd_Verz/124/09/06/20060903/124-20060903-dscn0593.JPG" alt="Fahrzeug 20 " align="right" width="90%">
      </td>
</tr>
<tr>
<th >21
  </th>
    <td>
       
       <table summary="Daten" border="1">
       <tr>
        <th width="20%">angemeldet (Fahrer)</th>
         <td>Kerndl Franz, Klosterneuburg</td>
       </tr>

       <tr>
        <th>Fahrzeug (Marke + Type)</th>
         <td>Opel Blitz 1.9</td>
       </tr>

       <tr>
        <th>Funktion bei der Feuerwehr</th>
         <td>LLF</td>
       </tr>

       <tr>
        <th>Baujahr</th>
         <td>1964</td>
       </tr>

       <tr>
        <th>Besonderheit</th>
         <td>vormals FF Weidling</td>
       </tr>

       </table>
              </td>
       
      <td>
      <img src="../../../../../login/AOrd_Verz/124/09/06/20060903/124-20060903-W-IMG_4387.JPG" alt="Fahrzeug 21 " align="right" width="90%">
      </td>
</tr>
<tr>
<th >22
  </th>
    <td>
       
       <table summary="Daten" border="1">
       <tr>
        <th width="20%">angemeldet (Fahrer)</th>
         <td>Kerndl Franz (Rudolf T&uuml;rk), Klosterneuburg</td>
       </tr>

       <tr>
        <th>Fahrzeug (Marke + Type)</th>
         <td>Opel Blitz 1.9</td>
       </tr>

       <tr>
        <th>Funktion bei der Feuerwehr</th>
         <td>TLF 1000</td>
       </tr>

       <tr>
        <th>Baujahr</th>
         <td>1965</td>
       </tr>

       <tr>
        <th>Besonderheit</th>
         <td>vormals FF Weidling</td>
       </tr>

       </table>
              </td>
       
      <td>
      <img src="../../../../../login/AOrd_Verz/124/09/06/20060903/124-20060903-W-IMG_4388.JPG" alt="Fahrzeug 22 " align="left" width="90%">
      </td>
</tr>
<tr>
<th >23
  </th>
    <td>
       
       <table summary="Daten" border="1">
       <tr>
        <th width="20%">angemeldet (Fahrer)</th>
         <td>FF M&uuml;nchendorf (Robert Kaiser)</td>
       </tr>

       <tr>
        <th>Fahrzeug (Marke + Type)</th>
         <td>Opel Blitz 2.1</td>
       </tr>

       <tr>
        <th>Funktion bei der Feuerwehr</th>
         <td>TLF 1000</td>
       </tr>

       <tr>
        <th>Baujahr</th>
         <td>1974</td>
       </tr>

       <tr>
        <th>Besonderheit</th>
         <td>&nbsp;</td>
       </tr>

       </table>
              </td>
       
      <td>
      <img src="../../../../../login/AOrd_Verz/124/09/06/20060903/124-20060903-W-IMG_4389.JPG" alt="Fahrzeug  " align="right" width="90%">

      </td>
</tr>
<tr>
<th >24
  </th>
    <td>
       
       <table summary="Daten" border="1">
       <tr>
        <th width="20%">angemeldet (Fahrer)</th>
         <td>Rehrl Franz, Berndorf</td>
       </tr>

       <tr>
        <th>Fahrzeug (Marke + Type)</th>
         <td>Citroen Torpedo 92</td>
       </tr>

       <tr>
        <th>Funktion bei der Feuerwehr</th>
         <td>&nbsp;</td>
       </tr>

       <tr>
        <th>Baujahr</th>
         <td>1926</td>
       </tr>

       <tr>
        <th>Besonderheit</th>
         <td>kein Einsatzfahrzeug</td>
       </tr>

       </table>
              </td>
       
      <td>
       <img src="../../../../../login/AOrd_Verz/124/09/06/20060903/124-20060903-W-IMG_4446.JPG" alt="Fahrzeug 24 " align="left" width="90%">
      </td>
</tr>
<tr>
<th >25
  </th>
    <td>
       
       <table summary="Daten" border="1">
       <tr>
        <th width="20%">angemeldet (Fahrer)</th>
         <td>FF Ried (Franz Braid)</td>
       </tr>

       <tr>
        <th>Fahrzeug (Marke + Type)</th>
         <td>Austro Fiat AFN</td>
       </tr>

       <tr>
        <th>Funktion bei der Feuerwehr</th>
         <td>LF</td>
       </tr>

       <tr>
        <th>Baujahr</th>
         <td>1931</td>
       </tr>

       <tr>
        <th>Besonderheit</th>
         <td>&nbsp;</td>
       </tr>

       </table>
              </td>
       
      <td>
      <img src="../../../../../login/AOrd_Verz/124/09/06/20060903/124-20060903-W-IMG_4390.JPG" alt="Fahrzeug 25 " align="right" width="90%">
      </td>
</tr>
<tr>
<th >26
  </th>
    <td>
       
       <table summary="Daten" border="1">
       <tr>
        <th width="20%">angemeldet (Fahrer)</th>
         <td>FF Berndorf (Walter Mieser)</td>
       </tr>

       <tr>
        <th>Fahrzeug (Marke + Type)</th>
         <td>Steyr 1500 A</td>
       </tr>

       <tr>
        <th>Funktion bei der Feuerwehr</th>
         <td>LF 8/S</td>
       </tr>

       <tr>
        <th>Baujahr</th>
         <td>1944/1956</td>
       </tr>

       <tr>
        <th>Besonderheit</th>
         <td>&nbsp;</td>
       </tr>

       </table>
              </td>
       
      <td>
      <img src="../../../../../login/AOrd_Verz/124/09/06/20060903/124-20060903-W-IMG_4392.JPG" alt="Fahrzeug 26 " align="left" width="90%">
      </td>
</tr>
<tr>
<th >27
  </th>
    <td>
       
       <table summary="Daten" border="1">
       <tr>
        <th width="20%">angemeldet (Fahrer)</th>
         <td>FF Friedersbach (Ewald Engelmaier)</td>
       </tr>

       <tr>
        <th>Fahrzeug (Marke + Type)</th>
         <td>Steyr 380</td>
       </tr>

       <tr>
        <th>Funktion bei der Feuerwehr</th>
         <td>TLF 1500</td>
       </tr>

       <tr>
        <th>Baujahr</th>
         <td>1958</td>
       </tr>

       <tr>
        <th>Besonderheit</th>
         <td>erstes TLF im Bezirk Zwettl</td>
       </tr>

       </table>
              </td>
       
      <td>
      <img src="../../../../../login/AOrd_Verz/124/09/06/20060903/124-20060903-W-IMG_4393.JPG" alt="Fahrzeug 27 " align="right" width="90%">
      </td>
</tr>
<tr>
<th >28
  </th>
    <td>
       
       <table summary="Daten" border="1">
       <tr>
        <th width="20%">angemeldet (Fahrer)</th>
         <td>Str&auml;ssler Karl, Wien</td>
       </tr>

       <tr>
        <th>Fahrzeug (Marke + Type)</th>
         <td>Steyr 480</td>
       </tr>

       <tr>
        <th>Funktion bei der Feuerwehr</th>
         <td>TLF 2000</td>
       </tr>

       <tr>
        <th>Baujahr</th>
         <td>1963</td>
       </tr>

       <tr>
        <th>Besonderheit</th>
         <td>vormals FF Eibesthal</td>
       </tr>

       </table>
              </td>
       
      <td>
       <img src="../../../../../login/AOrd_Verz/124/09/06/20060903/124-20060903-W-IMG_4394.JPG" alt="Fahrzeug 28 " align="left" width="90%">
      </td>
</tr>
<tr>
<th >29
  </th>
    <td>
       
       <table summary="Daten" border="1">
       <tr>
        <th width="20%">angemeldet (Fahrer)</th>
         <td>FF Irnfritz (Anton Isak)</td>
       </tr>

       <tr>
        <th>Fahrzeug (Marke + Type)</th>
         <td>Steyr 586g</td>
       </tr>

       <tr>
        <th>Funktion bei der Feuerwehr</th>
         <td>TLF 1800</td>
       </tr>

       <tr>
        <th>Baujahr</th>
         <td>1965</td>
       </tr>

       <tr>
        <th>Besonderheit</th>
         <td>vormals BF Wien</td>
       </tr>

       </table>
              </td>
       
      <td>
        <img src="../../../../../login/AOrd_Verz/124/09/06/20060903/124-20060903-W-IMG_4395.JPG" alt="Fahrzeug 29 " align="right" width="90%">
      </td>
</tr>
<tr>
<th >30
  </th>
    <td>
       
       <table summary="Daten" border="1">
       <tr>
        <th width="20%">angemeldet (Fahrer)</th>
         <td>FF Gmünd (Erich Koller)</td>
       </tr>

       <tr>
        <th>Fahrzeug (Marke + Type)</th>
         <td>Steyr 680 M3</td>
       </tr>

       <tr>
        <th>Funktion bei der Feuerwehr</th>
         <td>TLF 3300</td>
       </tr>

       <tr>
        <th>Baujahr</th>
         <td>1969</td>
       </tr>

       <tr>
        <th>Besonderheit</th>
         <td>vormals ÖBH, Munitionslager Hieflau</td>
       </tr>

       </table>
              </td>
       
      <td>
      <img src="../../../../../login/AOrd_Verz/124/09/06/20060903/124-20060903-W-IMG_4396.JPG" alt="Fahrzeug 30 " align="left" width="90%">
      </td>
</tr>
<tr>
<th >31
  </th>
    <td>
       
       <table summary="Daten" border="1">
       <tr>
        <th width="20%">angemeldet (Fahrer)</th>
         <td>Landstetter Johann, Artstetten</td>
       </tr>

       <tr>
        <th>Fahrzeug (Marke + Type)</th>
         <td>Steyr Puch 700 C</td>
       </tr>

       <tr>
        <th>Funktion bei der Feuerwehr</th>
         <td>&nbsp;</td>
       </tr>

       <tr>
        <th>Baujahr</th>
         <td>1962</td>
       </tr>

       <tr>
        <th>Besonderheit</th>
         <td><strong>vormals Gendarmerie</strong></td>
       </tr>

       </table>
              </td>
       
      <td>
      <img src="../../../../../login/AOrd_Verz/124/09/06/20060903/124-20060903-W-IMG_4397.JPG" alt="Fahrzeug 31 " align="right" width="90%">
      </td>
</tr>
<tr>
<th >32
  </th>
    <td>
       
       <table summary="Daten" border="1">
       <tr>
        <th width="20%">angemeldet (Fahrer)</th>
         <td>FF Sigmundsherberg</td>
       </tr>

       <tr>
        <th>Fahrzeug (Marke + Type)</th>
         <td>Puch 250 SG</td>
       </tr>

       <tr>
        <th>Funktion bei der Feuerwehr</th>
         <td>Krad</td>
       </tr>

       <tr>
        <th>Baujahr</th>
         <td>1956</td>
       </tr>

       <tr>
        <th>Besonderheit</th>
         <td>Feuerwehrstreife</td>
       </tr>

       </table>
              </td>
       
      <td>
      <img src="../../../../../login/AOrd_Verz/124/09/06/20060903/124-20060903-W-IMG_4399.JPG" alt="Fahrzeug 32 " align="left" width="90%">
      </td>
</tr>
<tr>
<th >33
  </th>
    <td> nicht am Start
      </td>
      <td>
       
      </td>
</tr>
<tr>
<th >34
  </th>
    <td>
       
       <table summary="Daten" border="1">
       <tr>
        <th width="20%">angemeldet (Fahrer)</th>
         <td>FF Schwertberg (Karl Hochreiter)</td>
       </tr>

       <tr>
        <th>Fahrzeug (Marke + Type)</th>
         <td>Fiat 1 C</td>
       </tr>

       <tr>
        <th>Funktion bei der Feuerwehr</th>
         <td>LF</td>
       </tr>

       <tr>
        <th>Baujahr</th>
         <td>1922</td>
       </tr>

       <tr>
        <th>Besonderheit</th>
         <td>&auml;testes Fahrzeug, vom Publikum aller Stationen als "Sch&ouml;nstes Fahrzeug" gew&auml;hlt, Wanderpokal "Amethyst"</td>
       </tr>

       </table>
              </td>
       
      <td>
      <img src="../../../../../login/AOrd_Verz/124/09/06/20060903/124-20060903-W-IMG_4402.JPG" alt="Fahrzeug 34 " align="right" width="90%">
      </td>
</tr>
<tr>
<th >35
  </th>
    <td>
       
       <table summary="Daten" border="1">
       <tr>
        <th width="20%">angemeldet (Fahrer)</th>
         <td>FF Gro&szlig;weiffendorf (Walter Paulusberger)</td>
       </tr>

       <tr>
        <th>Fahrzeug (Marke + Type)</th>
         <td>Gespann mit Gugg Handpumpe, neues KLF</td>
       </tr>

       <tr>
        <th>Funktion bei der Feuerwehr</th>
         <td>&nbsp;</td>
       </tr>

       <tr>
        <th>Baujahr</th>
         <td>1886 und 1893</td>
       </tr>

       <tr>
        <th>Besonderheit</th>
         <td>zuletzt L&ouml;schzug II Gro&szlig; Reith, original nachgeschnittene Uniformen 1890</td>
       </tr>

       </table>
              </td>
       
      <td>
       <img src="../../../../../login/AOrd_Verz/124/09/06/20060903/124-20060903-W-IMG_4512.JPG" alt="Fahrzeug 35 " align="left" width="90%">
      </td>
</tr>
<tr>
<th >36
  </th>
    <td>
       
       <table summary="Daten" border="1">
       <tr>
        <th width="20%">angemeldet (Fahrer)</th>
         <td>Feuerwehr- Oldtimerverein Hard (Karl Hartmann)</td>
       </tr>

       <tr>
        <th>Fahrzeug (Marke + Type)</th>
         <td>Mercedes L 1500 S</td>
       </tr>

       <tr>
        <th>Funktion bei der Feuerwehr</th>
         <td>LF 8</td>
       </tr>

       <tr>
        <th>Baujahr</th>
         <td>1941</td>
       </tr>

       <tr>
        <th>Besonderheit</th>
         <td>kam auf eigener Achse - 12 Std. Wegzeit</td>
       </tr>

       </table>
              </td>
       
      <td>
      <img src="../../../../../login/AOrd_Verz/124/09/06/20060903/124-20060903-W-IMG_4404.JPG" alt="Fahrzeug 36 " align="right" width="90%">
      </td>
</tr>
<tr>
<th >37
  </th>
    <td>
       
       <table summary="Daten" border="1">
       <tr>
        <th width="20%">angemeldet (Fahrer)</th>
         <td>FF Hötzelsdorf (Josef Heimberger)</td>
       </tr>

       <tr>
        <th>Fahrzeug (Marke + Type)</th>
         <td>Opel Blitz 3.6-6700 A</td>
       </tr>

       <tr>
        <th>Funktion bei der Feuerwehr</th>
         <td>TWG 1800</td>
       </tr>

       <tr>
        <th>Baujahr</th>
         <td>1943</td>
       </tr>

       <tr>
        <th>Besonderheit</th>
         <td>Aufbau Dlouhy, vormals BF Wien</td>
       </tr>

       </table>
              </td>
       
      <td>
      <img src="../../../../../login/AOrd_Verz/124/09/06/20060903/124-20060903-W-IMG_4405.JPG" alt="Fahrzeug 37 " align="left" width="90%">
      </td>
</tr>
<tr>
<th >38
  </th>
    <td>
  
       <table summary="Daten" border="1">
       <tr>
        <th width="20%">angemeldet (Fahrer)</th>
         <td>FF Rekawinkel (Franz Kettele)</td>
       </tr>

       <tr>
        <th>Fahrzeug (Marke + Type)</th>
         <td>Dodge WC 54</td>
       </tr>

       <tr>
        <th>Funktion bei der Feuerwehr</th>
         <td>RF</td>
       </tr>

       <tr>
        <th>Baujahr</th>
         <td>1942</td>
       </tr>

       <tr>
        <th>Besonderheit</th>
         <td>&nbsp;</td>
       </tr>

       </table>
              </td>
       
      <td>
      <img src="../../../../../login/AOrd_Verz/124/09/06/20060903/124-20060903-W-IMG_4406.JPG" alt="Fahrzeug 38 " align="right" width="90%">
      </td>
</tr>
<tr>
<th >39
  </th>
    <td>
       
       <table summary="Daten" border="1">
       <tr>
        <th width="20%">angemeldet (Fahrer)</th>
         <td>FF Krems (Karl Plutsch)</td>
       </tr>

       <tr>
        <th>Fahrzeug (Marke + Type)</th>
         <td>Opel Blitz 3.6-6700 A</td>
       </tr>

       <tr>
        <th>Funktion bei der Feuerwehr</th>
         <td>TWG 1800</td>
       </tr>

       <tr>
        <th>Baujahr</th>
         <td>1944/1951</td>
       </tr>

       <tr>
        <th>Besonderheit</th>
         <td>&nbsp;</td>
       </tr>

       </table>
              </td>
       
      <td>
       <img src="../../../../../login/AOrd_Verz/124/09/06/20060903/124-20060903-W-IMG_4407.JPG" alt="Fahrzeug 39 " align="left" width="90%">
      </td>
</tr>
<tr>
<th >40
  </th>
    <td>nicht am Start
      </td>
</tr>
<tr>
<th >41
  </th>
    <td>
       
       <table summary="Daten" border="1">
       <tr>
        <th width="20%">angemeldet (Fahrer)</th>
         <td>FF Obersdorf (V Christian G&ouml;ssinger)</td>
       </tr>

       <tr>
        <th>Fahrzeug (Marke + Type)</th>
         <td>Steyr 586g</td>
       </tr>

       <tr>
        <th>Funktion bei der Feuerwehr</th>
         <td>RF</td>
       </tr>

       <tr>
        <th>Baujahr</th>
         <td>1963</td>
       </tr>

       <tr>
        <th>Besonderheit</th>
         <td>vormals FF Felixdorf, vormals BF Wien</td>
       </tr>

       </table>
              </td>
       
      <td>
      <img src="../../../../../login/AOrd_Verz/124/09/06/20060903/124-20060903-W-IMG_4408.JPG" alt="Fahrzeug 41 " align="right" width="90%">
      </td>
</tr>
<tr>
<th >42
  </th>
    <td>
       
       <table summary="Daten" border="1">
       <tr>
        <th width="20%">angemeldet (Fahrer)</th>
         <td>FF Rekawinkel (Franz Kettele)</td>
       </tr>

       <tr>
        <th>Fahrzeug (Marke + Type)</th>
         <td>Opel Blitz 1.9</td>
       </tr>

       <tr>
        <th>Funktion bei der Feuerwehr</th>
         <td>TLF 1000</td>
       </tr>

       <tr>
        <th>Baujahr</th>
         <td>1964</td>
       </tr>

       <tr>
        <th>Besonderheit</th>
         <td>&nbsp;</td>
       </tr>

       </table>
              </td>
       
      <td>
       <img src="../../../../../login/AOrd_Verz/124/09/06/20060903/124-20060903-W-IMG_4409.JPG" alt="Fahrzeug 42 " align="left" width="90%">
      </td>
</tr>
<tr>
<th >43
  </th>
    <td>
       
       <table summary="Daten" border="1">
       <tr>
        <th width="20%">angemeldet (Fahrer)</th>
         <td>FF Kritzendorf (Ludwig Mayr)</td>
       </tr>

       <tr>
        <th>Fahrzeug (Marke + Type)</th>
         <td>Opel Blitz 2.1</td>
       </tr>

       <tr>
        <th>Funktion bei der Feuerwehr</th>
         <td>TLF 1000</td>
       </tr>

       <tr>
        <th>Baujahr</th>
         <td>1971</td>
       </tr>

       <tr>
        <th>Besonderheit</th>
         <td>&nbsp;</td>
       </tr>

       </table>
              </td>
       
      <td>
<img src="../../../../../login/AOrd_Verz/124/09/06/20060903/124-20060903-W-IMG_4410.JPG" alt="Fahrzeug 43 " align="right" width="90%">
      </td>
</tr>
<tr>
<th >44
  </th>
    <td>
       
       <table summary="Daten" border="1">
       <tr>
        <th width="20%">angemeldet (Fahrer)</th>
         <td>FF Spillern (Bernhardd Aschacher)</td>
       </tr>

       <tr>
        <th>Fahrzeug (Marke + Type)</th>
         <td>Mercedes L 408 D</td>
       </tr>

       <tr>
        <th>Funktion bei der Feuerwehr</th>
         <td>LF</td>
       </tr>

       <tr>
        <th>Baujahr</th>
         <td>1968</td>
       </tr>

       <tr>
        <th>Besonderheit</th>
         <td>&nbsp;</td>
       </tr>

       </table>
              </td>
       
      <td>
<img src="../../../../../login/AOrd_Verz/124/09/06/20060903/124-20060903-W-IMG_4411.JPG" alt="Fahrzeug 44 " align="left" width="90%">
      </td>
</tr>
<tr>
<th >45
  </th>
    <td>
       
       <table summary="Daten" border="1">
       <tr>
        <th width="20%">angemeldet (Fahrer)</th>
         <td>FF Klausen-Leopoldsdorf</td>
       </tr>

       <tr>
        <th>Fahrzeug (Marke + Type)</th>
         <td>Land Rover</td>
       </tr>

       <tr>
        <th>Funktion bei der Feuerwehr</th>
         <td>BLF</td>
       </tr>

       <tr>
        <th>Baujahr</th>
         <td>1964</td>
       </tr>

       <tr>
        <th>Besonderheit</th>
         <td>&nbsp;</td>
       </tr>

       </table>
              </td>
       
      <td>
      <img src="../../../../../login/AOrd_Verz/124/09/06/20060903/124-20060903-W-IMG_4412.JPG" alt="Fahrzeug 45 " align="right" width="90%">
      </td>
</tr>
<tr>
<th >46
  </th>
    <td>
       
       <table summary="Daten" border="1">
       <tr>
        <th width="20%">angemeldet (Fahrer)</th>
         <td>Pertschek Fred</td>
       </tr>

       <tr>
        <th>Fahrzeug (Marke + Type)</th>
         <td>Fiat 1100 T</td>
       </tr>

       <tr>
        <th>Funktion bei der Feuerwehr</th>
         <td>KLF</td>
       </tr>

       <tr>
        <th>Baujahr</th>
         <td>1968</td>
       </tr>

       <tr>
        <th>Besonderheit</th>
         <td>&nbsp;</td>
       </tr>

       </table>
              </td>
       
      <td>
      <img src="../../../../../login/AOrd_Verz/124/09/06/20060903/124-20060903-W-IMG_4413.JPG" alt="Fahrzeug 46 " align="left" width="90%">
      </td>
</tr>
<tr>
<th >47
  </th>
    <td>
       
       <table summary="Daten" border="1">
       <tr>
        <th width="20%">angemeldet (Fahrer)</th>
         <td>Widorn Wolfdieter, Wien</td>
       </tr>

       <tr>
        <th>Fahrzeug (Marke + Type)</th>
         <td>Steyr 680 M3</td>
       </tr>

       <tr>
        <th>Funktion bei der Feuerwehr</th>
         <td>TLF 3300</td>
       </tr>

       <tr>
        <th>Baujahr</th>
         <td>1969</td>
       </tr>

       <tr>
        <th>Besonderheit</th>
         <td>vormals FF Gainfarn, vormals &Ouml;BH</td>
       </tr>

       </table>
              </td>
            <td>
       <img src="../../../../../login/AOrd_Verz/124/09/06/20060903/124-20060903-W-IMG_4416.JPG" alt="Fahrzeug 47 " align="right" width="90%">
      </td>
</tr>
<tr>
<th >48
  </th>
    <td>
       
       <table summary="Daten" border="1">
       <tr>
        <th width="20%">angemeldet (Fahrer)</th>
         <td>FF Gro&szlig; Siegharts (Andreas Paschinger)</td>
       </tr>

       <tr>
        <th>Fahrzeug (Marke + Type)</th>
         <td>Tatra 138 AV</td>
       </tr>

       <tr>
        <th>Funktion bei der Feuerwehr</th>
         <td>KF 12</td>
       </tr>

       <tr>
        <th>Baujahr</th>
         <td>1968</td>
       </tr>

       <tr>
        <th>Besonderheit</th>
         <td>vormals FF Hollabrunn</td>
       </tr>

       </table>
              </td>
      <td>
       <img src="../../../../../login/AOrd_Verz/124/09/06/20060903/124-20060903-W-IMG_4418.JPG" alt="Fahrzeug 48 " align="left" width="90%">
      </td>
</tr>
<tr>
<th >49
  </th>
    <td>
       
       <table summary="Daten" border="1">
       <tr>
        <th width="20%">angemeldet (Fahrer)</th>
         <td>Feuerwehrmuseum Gars</td>
       </tr>

       <tr>
        <th>Fahrzeug (Marke + Type)</th>
         <td>Mercedes LAF 1113</td>
       </tr>

       <tr>
        <th>Funktion bei der Feuerwehr</th>
         <td>TLF 2400</td>
       </tr>

       <tr>
        <th>Baujahr</th>
         <td>1968</td>
       </tr>

       <tr>
        <th>Besonderheit</th>
         <td>&nbsp;</td>
       </tr>

       </table> 
              </td>
      <td>
      <img src="../../../../../login/AOrd_Verz/124/09/06/20060903/124-20060903-W-IMG_4400.JPG" alt="Fahrzeug 49 " align="right" width="90%">
      </td>
</tr>

</tbody>
     </table>
     
     <br><br>
<img src="../../../../../login/AOrd_Verz/124/09/06/20060903/124-20060903-W-IMG_4541.JPG" alt="Ehrengaeste Ziel" align="left" width="90%"><br>
<p>Die Siegerparade in Gars vor dem Feuerwehrhaus wurde von Peter Krumhaar
(Verein der Feuerwehrhistoriker in NÖ, Referatsleiter, Referat 2 "Mit Motorkraft
bewegte Fahrzeuge") kommentiert. <br>
Als Ehrengäste waren der Hr. Landesfeuerwehrkommandant
LBD KR Josef Buchta, Mitglieder des Bezirkskommandos Horn, Hr. Bgm. Falk und andere
Persönlichkeiten anwesend. Mit der Siegerehrung waren Fr. Patricia Fischer, (Redaktion historische Fahrzeuge)
Hr. Bgm. Falk, Hr. LBD Josef Buchta und die Mitglieder des BFKDO betraut.
     </p>
     </div>
</fieldset>
</div>
<?php 
BA_HTML_trailer();
 ?>

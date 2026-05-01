<?php
session_start();

$module    = 'VF_Beschr';
$path2ROOT = "../";

$debug = True; $debug = False;  // Debug output Ein/Aus Schalter


/**
 * Angleichung an den Root-Path
 *
 * @var string $path2ROOT
 */
require $path2ROOT.'login/common/BA_HTML_Funcs.lib.php' ;  // Diverse Unterprogramme
require $path2ROOT.'login/common/BA_Funcs.lib.php' ;  // Diverse Unterprogramme
$flow_list = False;

initial_debug(); # Wenn $debug=true - Ausgabe von Debug Informationen: $_POST, $_GET, $_FILES

$logo = 'JA';

$header = "";
BA_HTML_header('IMPRESSUM',$header,'Form','75em');
echo "<div  class='w3-container'>";
?>


<p>
<dl>
<dt><strong>Herausgeber und f&uuml;r den Inhalt verantwortlich:</strong></dt>
<dd>Verein "Feuerwehrhistoriker in Nieder&ouml;sterreich"
    <br>
    Vereinsregister Nr.: ZVR-Zahl 598019887
    <br/><br/>
  </dd>
<dt><b>Obmann:</b></dt>
<dd>
    Obmann Lukas Brodtrager<br/>
    Lagerhausgasse 15<br/>
    A-2763 Pernitz
    <br/><br/>
  </dd>

<dt><b>Finanzen und Sponsoring:</b></dt>
<dd>
    Wolfgang Riegler
    <br/><br/>
  </dd>
<!-- 
<dt><b>Recht:</b></dt>
<dd>
     Mag. Horst Rainer Sekyra
    <br/><br/>
  </dd>
 -->
<dt><strong>Schriftführer und Webmaster und EDV- Belange :</strong></dt>
  <dd>
     Ing. Josef Rohowsky<br/>
     Archiv FF Wr. Neudorf<br/>
     Ricoweg 34 <br/>
     A-2351 Wiener Neudorf<br/>
<br>
</dd>
<dt><b>Bildnachweis:</b></dt>
<dd>
<p align=justify>
Bildarchiv des Vereines "Feuerwehrhistoriker in Niederösterreich, Fotos von Mitgliedern des Vereines"<br>
Alle neueren Bilder haben den Urheber am Bild links unten eingetragen

</p>
</dd>

<!--
für die php version
   # echo "<div style='float: right;'>  <label>Vereinsregister Nr.: ZR-Zahl " . $_SESSION['Config']['c_Vereinsreg'] . "</label></div>";
<dt><a href="rechtl.htm">Rechtliche Information bez&uuml;glich Links</a></dt>
<dd></dd>
-->

</dl>

<center>
<br><br>
<a href="index.php">
    Zur Startseite</a>
</center>


</body>
</html>


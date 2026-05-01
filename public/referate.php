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
BA_HTML_header('Referate',$header,'Form','75em');
echo "<div  class='w3-container'>";
?>


<h1>Feuerwehrhistoriker in Niederösterreich</h1>
<p><font size="+2">
Um die Feuerwehrgeschichte gezielt zu bearbeiten, wurden Referate eingerichtet.
</font>
</p>

<p>
<font size="+1">
<strong>
Referat 1: Administratives <br/>
</strong>
</font>
<strong>
Leiter: Wolfgang Riegler
<br>
</strong>

</p>

<p>
<font size="+1">
<strong>

Referat 2:</a> Fahrzeuge und Geräte <br/>
</strong>
</font>
<strong>
Leiter: Willibald Schermann
<br/>
</strong>

In diesem Referat werden alle Fahrzeuge und Geräte erfasst.
</p>

<p>
<font size="+1">
<strong>
Referat 3: öffentlichkeitsarbeit, Publikationen, Museen, Archive, Inventar, ...
<br/>
</strong>
</font>
<strong>
Leiter: Franz Blüml
<br/>
</strong>

</p>

<p>
<font size="+1">
<strong>
Referat 4: Persönliche Ausrüstungen:<br/>
</strong>
</font>
<strong>
Leiter: Karl Zehetner,
<br/>
</strong>


Uniformen, Auszeichnungen, ...<br/>
</p>

<center>
<br><br>
<a href="index.php">
    Zur Startseite</a>
</center>

</body>

</html>


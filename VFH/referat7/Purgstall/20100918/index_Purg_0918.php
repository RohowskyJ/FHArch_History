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

$logo = 'NEIN';
$header = "<link  href='".$path2ROOT."login/common/css/frame_pict.css' rel='stylesheet' type='text/css'>";
HTML_header('Verein Feuerwehrhistoriker in NÖ) ','Achivierte Berichte',$header,'Form','75em'); # Parm: Titel,Subtitel,HeaderLine,Type,width

?>

<body class="w3-container ">
<div class="w3-content ">  <!-- max-width:45em; margin:5em;--->
<fieldset>
<h1>18.9.2010 Feier 140 Jahre Freiwillige Feuerwehr Purgstall</h1>
<P>Die Freiwillige Feuerwehr Purgstall hat am 18.9.2010 ihr 140 jähriges Bestandsjubiläum gefeiert.
<br/>
In dieser Feierstunde wurden viele Personen geehrt, unter anderem wurde eine neu gestiftete Gemeindemedaille für
langjährigen Dienst (30, 50, 60 Jahre oder länger) in der Feuerwehr zum ersten Mal an Kameraden verliehen.
</P>


<div class='w3-table' style='margin=auto'>

<table>
<tbody>
    <tr>
       <th>
<a href="img_1261_p.jpg" target="_new"><IMG SRC="img_1261_p.jpg" alt="Langjährige Mitglieder" width="150px"/></a>
        </th>
       <th>
<a href="img_1278_p_r.jpg" target="_new"><IMG SRC="img_1278_p_r.jpg" alt="Medaille an der Brust von Kamerad Franz Wiesenhofer"  width="100px"/></a>
        </th>
       <th>
<a href="1978_Urkunde_Marktgem_Purgstall.jpg" target="_new"> <IMG SRC="1978_Urkunde_Marktgem_Purgstall.jpg" alt="Urkunde zur Medaille" width="100"/></a>
        </th>
      </tr>

</tbody>
     </table>

     </div>
<P>Von Seiten des Vereines der Feuerwehrhistoriker in NÖ wurde Kamerad Franz Wiesenhofer auf Grund seiner Verdienste für
die Feuerwehrgeschichte in NÖ mit einer Plakette "Dank und Anerkennung" ausgesprochen.
</P>
<div class='w3-table' style='margin=auto'>

<table>
<tbody>
   <tr>
       <th>
<a href="img_1276_p.jpg" target="_new"><IMG SRC="img_1276_p.jpg" alt="Ehrung Wiesenhofer und Gattin" width="150px"/></a>
        </th>
       <th>
<a href="1979_Plakette_FHNOe.jpg" target="_new"><IMG SRC="1979_Plakette_FHNOe.jpg" alt="Plakette" width="100"/></a>
        </th>
       <th>
<a href="1977_Urkunde_FHNOe.jpg" target="_new"> <IMG SRC="1977_Urkunde_FHNOe.jpg" alt="Urkunde zur Plakette" width="100"/></a>
        </th>
      </tr>

</tbody>
     </table>

     </div>


</fieldset>
</div>
<?php 
HTML_trailer();
 ?>

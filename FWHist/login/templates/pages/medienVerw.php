<?php
/** 
 * Medien, Menu
 * 
 */

$this->layout('layout', ['title' => $title, 'path2ROOT' => $path2ROOT, 'cssBundles' => $cssBundles , 'has' => $has ] ) ;

if (!empty($debug)) {
    var_dump($has);
}
?>

<div class="Menu-Header">Medien- Verwaltung</div>

<div class="Menu-Separator">Medien: Audio-, Foto- und Video- Materialien</div>
<div class="Menu-Line">
   <p> <b>Hier werden die von Mitgliedern erstellten Fotos <b>einzeln</b> ins Netz gestellt, 
und können heruntergeladen und dürfen für Zwecke des Vereines mit Namensnennung des Fotografen (Urheber) verwendet werden. </b></p>
<p>Ab 2024.06 müssen die hochzuladenden Foto-Dateinamen das richtige Format haben/bekommen (Urh-Datum-original-Namen), sonst werden sie nicht hochgeladen. Auf die richtige Größe (maximal 20 x 20 cm oder 800 x 800 Pixel) werden sie automatisch umgewandelt.</p>
</div>
<div class="Menu-Line">
   <a href='MedienVerzList.php?ID=Abf' target='Medien'>Medien- Verwaltung </a>
</div>

<div class="Menu-Separator">Massen- Upload von Medien-Daten</div>
<div class="Menu-Line">
   Massen upload mit Rework (Größe, Wasserzeichen, Verzeichnis- Record wird erstellt, Fotos werden am Speicherort abgelegt und vor der Anzeige in die Tabellen eingefügt)
</div>
<div class="Menu-Line">
Hier können die von Mitgliedern erstellten Fotos auf den Server je  Veranstaltung massenweise geladen und die erste Tabelleneintragung für die Fotos wird erstellt.
          Die bearbeiteten Fotos (Größenanpassung, Copyright Info) werden in dem dazu bestimmten Verzeichnis abgelegt.
          Die Daten über die Veranstaltung werden erfasst.
</div>
<div class="Menu-Line">
   <a href='MassUpload.php' target='mss-Upl'>Massen- Hochladen</a>
</div>
   
<div class="Menu-Separator">Veranstaltungs- Berichte erstellen</div>
<div class="Menu-Line">
    <b>Berichte über Vereins- oder historisch interessante Ereignisse</b>
    </div>
<div class="Menu-Line">
     Hier werden die bereits mit Hilfe der Medien- Bibliothek abgelegten Fotos zur Birichts- Erstellung genutzt.
</div>
<div class="Menu-Line">
    <a href='BerichtsList.php' target='Berichte'>Liste der bestehenden Berichte</a>
</div>
  

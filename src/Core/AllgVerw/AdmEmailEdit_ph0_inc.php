<?php

use Fharch\Core\Services\FormRendererFlex;

/**
 * Automatische Benachrichtigung für ADMINS bei Änderungen, Wartung, Formular
 *
 * @author Josef Rohowsky - neu 2023
 *
 */
if ($debug) {
    echo "<pre class=debug>AdmEmailEdit_ph0_inc.php ist gestarted</pre>";
}

$editProtect = false;  // mit true: keine Eingabe möglich für die ganze Seite
$readonly = "";

if (!userBerechtigtOK($Zugr) ) {
    # $editProtect = true;
    # $readonly = false;
}

$forms = new FormRendererFlex($meta, $phase,  $neu, [], $editProtect, $module );

echo "<div class='white'>";

echo "<input type='hidden' name='em_id' value='" . $neu['em_id'] . "' />";
echo "<input type='hidden' name='be_ids' value='" . $neu['be_ids'] . "' />";
# var_dump($neu);
# =========================================================================================================
echo $forms->renderHeader('Administrator E-Mails');
# =========================================================================================================

echo $forms->renderTextLikeFieldFlex('em_id','0','','','readonly');
echo $forms->renderTextLikeFieldFlex('be_ids','0','','','readonly');

# =========================================================================================================
echo $forms->renderTrenner('Benutzer'); 
# =========================================================================================================

echo $forms->renderTextLikeFieldFlex('fd_name', 35);
AutoCompForm_Benutzer();

echo $forms->renderSelectFieldFlex('em_mail_grp', VF_Mail_Grup);

$a_arr = array(
    "a" => 'Aktiv',
    "i" => 'Inaktiv'
);
echo $forms->renderRadioFieldFlex('em_active', $a_arr);
# =========================================================================================================
echo $forms->renderTrenner('Letzte Änderung');
# =========================================================================================================

echo $forms->renderTextLikeFieldFlex('em_changed_id','0','','','readonly');
echo $forms->renderTextLikeFieldFlex('em_changed_at','0','','','readonly');

# =========================================================================================================

if (userBerechtigtOK($Zugr) ) {
    echo "<p>Nach Eingabe aller Daten oder Änderungen  drücken Sie ";
    echo "<button type='submit' name='phase' value='1' class='green'>Daten abspeichern</button></p>";
}

echo "<p><a href='AdmEmailList.php'>Zurück zur Liste</a></p>";

echo "</div>";
# =========================================================================================================

echo "<script type='text/javascript' src='" . $path2ROOT . "login/common/javascript/FS_AutoComp_Benutzer.js' ></script>";

if ($debug) {
    echo "<pre class=debug>AdmEmailEdit_ph0_inc.php beendet</pre>";
}
?>
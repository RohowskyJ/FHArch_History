<?php

$_SESSION[$module]['Inc_Arr'][] = "RollenList_inc.php"; 

echo "<h4>Liste der auszuübenden Rollen</h4>";

$NeuRec = "<a href='RollenEdit.php?ID=0&benu=".$neu['fd_name']." ".$neu['fd_vname']."&beId=".$neu['be_id']."'>Neue Rolle anlegen</a>";
# ===========================================================================================
# Definition der Auswahlmöglichkeiten (mittels radio Buttons)
# ===========================================================================================
echo "<input type='hidden' id='srch_Id' value='".$neu['be_id']."'>";
$list_ID = 'RO';
$lTitel = ["Alle" => "Alle Rollen"];

require $path2ROOT . 'src/Core/Services/ListFuncsLib.php';

?>
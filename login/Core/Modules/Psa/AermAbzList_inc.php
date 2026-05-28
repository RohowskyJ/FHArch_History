<?php

# ===========================================================================================
# Definition der Auswahlmöglichkeiten (mittels radio Buttons)
# ===========================================================================================
echo "<input type='hidden' id='srch_Id' value='".$fw_id."'>";

$list_ID = 'AWA';

$lTitel = ["Alle." => "Alle Abzeichen "];
$proj = $fw_id;
$NeuRec = "";
if (isset($_SESSION['BS_Prim']['BE'])) {
    $NeuRec = " &nbsp; &nbsp; &nbsp; <a href='OrtsEdit.php?ID=0' > Neuen Datensatz anlegen </a>";
    $lTitel = ["Alle" => "Alle Abzeichen "];
}

require $path2ROOT . 'login/Core/Services/ListFuncsLib.php';

HTML_trailer();

?>
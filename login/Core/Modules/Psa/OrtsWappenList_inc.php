<?php

# ===========================================================================================
# Definition der Auswahlmöglichkeiten (mittels radio Buttons)
# ===========================================================================================
echo "<input type='hidden' id='srch_Id' value='".$fo_id."'>";

$list_ID = 'AWG';

$lTitel = ["Alle." => "Alle Abzeichen "];
$proj = $fo_id;
$NeuRec = "";
if (isset($_SESSION['BS_Prim']['BE'])) {
    $NeuRec = " &nbsp; &nbsp; &nbsp; <a href='OrtsWappenEdit.php?ID=0' > Neuen Datensatz anlegen </a>";
    $lTitel = ["Alle" => "Alle Abzeichen "];
}

require $path2ROOT . 'login/Core/Services/ListFuncsLib.php';

HTML_trailer();

?>
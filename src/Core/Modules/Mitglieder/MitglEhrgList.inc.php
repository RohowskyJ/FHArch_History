<?php

/**
 * Liste der vom Verein verliehenen Ehrungen
 * 
 * @author Josef Rohowsky - neu 2023
 * 
 * 
 */

# ===========================================================================================
# Definition der Auswahlmöglichkeiten (mittels radio Buttons)
# ===========================================================================================
$_SESSION[$module]['mi_id'] = $neu['mi_id'];
$table_eh = "fv_mi_ehrung";
$select_eh = " WHERE mi_id = '" . $neu['mi_id'] . "' ";
$sort_eh = " ORDER BY 'me_eh_datum' ASC ";

echo "<input type='hidden' id ='srch_Id' value='".$neu['mi_id']."'>";
$list_ID = "MIE";
$lTitel = array(
    "Alle" => 'Alle Datensätze'
);
$NeuRec = "<a href='MitglEhrgEdit.php?ID=0' >Neuen Datensatz eingeben</a>";

require $path2ROOT . 'src/Core/Services/ListFuncsLib.php';

?>

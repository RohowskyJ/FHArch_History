<!Doctytpe html>
<?php

/**
 * Automatische Benachrichtigung für ADMINS bei Änderungen
 * 
 * @author Josef Rohowsky - neu 2023 reo 2026
 * 
 */
session_start(); # die SESSION aktivieren

$module  = 'ADM-Email';
$sub_mod = 'List';
$Zugr = "ADM-ALL";

/**
 * Angleichung an den Root-Path
 *
 * @var string $path2ROOT
 */
$path2ROOT = "../../../";

$debug = False; // Debug output Ein/Aus Schalter
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/EMail_List_php-error.log.txt');
# var_dump($_SERVER);

require $path2ROOT . 'login/Basis/common/BS_FuncsLib.php';
require $path2ROOT . 'login/Basis/common/FS_CommFuncsLib.php';
require $path2ROOT . 'login/Basis/common/FS_ConstLib.php';

$debug = False; // Debug output Ein/Aus Schalter

require $path2ROOT . 'login/common/VF_Comm_Funcs.lib.php';

$ber = userBerechtigtOK($Zugr);

use FhArch\Core\Database\DB_GenericLog;
require $path2ROOT . "Src/Core/Database/DB_GenericLog.php";

$title = "E-Mail- Empfänger für automatische E-Mails ";

$ListHead = "Admin- E_Mail Verwaltung - Administrator ";
$title = "Admin- E_Mail- Daten";

$TABUcss = true;
HTML_header($title, '', 'Admin', '90em'); # Parm: Titel,Subtitel,HeaderLine,Type,width

initial_debug('GET','POST');

// XR_Database mit bestehender PDO-Instanz initialisieren
$DBD = new DB_GenericLog();

$pdo = $DBD->getPDO();

# ===========================================================================================
# Definition der Auswahlmöglichkeiten (mittels radio Buttons)
# ===========================================================================================
$T_list_texte = array(
    "Alle" => "Alle E-Mail- Ziele "
);
$NeuRec = "<a href='AdmEmailEdit.php?ID=0' >Neues E-Mail Ziel eingeben</a>";

# ===========================================================================================
# Definition der Auswahlmöglichkeiten (mittels radio Buttons)
# ===========================================================================================
echo "<input type='hidden' id='srch_Id' value=''>";
$list_ID = 'EMA';
$lTitel = ["Alle" => "Alle E-Mail- Ziele "];

require $path2ROOT . 'src/Core/Services/ListFuncsLib.php';

HTML_trailer();

?>
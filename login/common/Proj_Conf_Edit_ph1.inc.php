<?php
var_dump($_POST);
/**
 * Includes-Liste
 * enthält alle jeweils includierten Scritpt Files
 */
$_SESSION[$module]['Inc_Arr'][] = "Proj_Conf_Edit_ph1.inc.php";

if ($debug) {
    echo "<pre class=debug>Proj_Conf_Edit_ph1.inc.php ist gestarted</pre>";
}
console_log('proj start ph1');
if ($neu['bild_datei_1'] != "") {
    $neu['c_bild_1'] = $neu['bild_datei_1'];
}
if ($neu['bild_datei_2'] != "") {
    $neu['c_bild_2'] = $neu['bild_datei_2'];
}

$updas_s = "\n[Config]\n";
$updas_m = "\n[Modules]\n"; # assignements for UPDATE xxxxx SET `variable` = 'Wert'

foreach ($neu as $name => $value) { # fÃ¼r alle Felder aus der tabelle 

    if (substr($name,0,2) != "c_") {
        continue;
    } #
 
    if ($name == "c_Institution") {
        $updas_s .= "inst = '$value'\n ";
    }
    if ($name == "c_Vereinsreg") {
        $updas_s .= "vreg = '$value'\n ";
    }
    if ($name == "c_Verantwortl") {
        $updas_s .= "vant = '$value'\n ";
    }
    if ($name == "c_email") {
        $updas_s .= "vema = '$value'\n ";
    }
    if ($name == "c_Ver_Tel") {
        $updas_s .= "vtel = '$value'\n ";
    }
    if ($name == "c_mode") {
        $updas_s .= "mode = '$value'\n ";
    }
    if ($name == "c_Wartung") {
        $updas_s .= "wart = '$value'\n ";
    }

    if ($name == "c_Wart_Grund") {
        $updas_s .= "warg = '$value'\n ";
    }
    if ($name == "c_Eignr") {
        $updas_s .= "eignr = '$value'\n ";
    }
    if ($name == "c_bild_1") {
        $updas_s .= "sign = '$value'\n ";
    }
    if ($name == "c_bild_2") {
        $updas_s .= "fpage = '$value'\n ";
    }
    if ($name == "c_Homepage") {
        $updas_s .= "homp = '$value'\n ";
    }
    if ($name == "c_Eigner") {
        $updas_s .= "eignr = '$value'\n ";
    }
    if ($name == "c_ptyp") {
        $updas_s .= "ptyp = '$value'\n ";
    }
    if ($name == "c_store") {
        $updas_s .= "store = '$value'\n ";
    }
    if ($name == "c_def_pw") {
        $updas_s .= "def_pw = '$value'\n ";
    }
    if ($name == "c_Perr") {
        $updas_s .= "cPerr = '$value'\n ";
    }
    if ($name == "c_Debug") {
        $updas_s .= "cDeb = '$value'\n ";
    }

    if ($name == "c_Module_1") {
        $updas_m .= "m_1 = '$value'\n ";
    }
    if ($name == "c_Module_2") {
        $updas_m .= "m_2 = '$value'\n ";
    }

    if ($name == "c_Module_3") {
        $updas_m .= "m_3 = '$value'\n ";
    }
    if ($name == "c_Module_4") {
        $updas_m .= "m_4 = '$value'\n ";
    }
    if ($name == "c_Module_5") {
        $updas_m .= "m_5 = '$value'\n ";
    }
    if ($name == "c_Module_6") {
        $updas_m .= "m_6 = '$value'\n ";
    }
    if ($name == "c_Module_7") {
        $updas_m .= "m_7 = '$value'\n ";
    }
    if ($name == "c_Module_8") {
        $updas_m .= "m_8 = '$value'\n ";
    }
    if ($name == "c_Module_9") {
        $updas_m .= "m_9 = '$value'\n ";
    }
    if ($name == "c_Module_10") {
        $updas_m .= "m_10 = '$value'\n ";
    }
    if ($name == "c_Module_11") {
        $updas_m .= "m_11 = '$value'\n ";
    }
    if ($name == "c_Module_12") {
        $updas_m .= "m_12 = '$value'\n ";
    }
    if ($name == "c_Module_13") {
        $updas_m .= "m_13 = '$value'\n ";
    }
    if ($name == "c_Module_14") {
        $updas_m .= "m_14 = '$value'\n ";
    }
    if ($name == "c_Module_15") {
        $updas_m .= "m_15 = '$value'\n ";
    }

} # Ende der Schleife

$dsn = $path2ROOT."login/common/config_s.ini";

$datei = fopen($dsn, 'w');
fputs($datei, $updas_s);
fclose($datei);

$dsn = $path2ROOT."login/common/config_m.ini";

$datei = fopen($dsn, 'w');
fputs($datei, $updas_m);
fclose($datei);

$updas = "";
foreach ($neu as $fld => $value ) {
    
    if (substr($fld,0,2) != "c_") {
        continue;
    } #
    if ($fld == 'cPerr_A' || $fld == 'cDeb_A' || $fld == 'SelDisA' ){
        continue;
    }
    
    $updas .= ",$fld = '$value'";
}

$sql = "UPDATE fh_proj_config SET  c_aenduid='".$_SESSION['VF_Prim']['p_uid']."'$updas WHERE `c_flnr`='1' ";

if ($debug) {
    echo "<div class='toggle-SqlDisp'>";
    echo "<pre class=debug style='background-color:lightblue;font-weight:bold;'>Proj_Conf_Edit_ph1.inc.php $sql </pre>";
    echo "</div>";
}

$result = SQL_QUERY($db, $sql);

if (isset($_SESSION[$module]['inst'])) {
    header("Location: ".$_SESSION[$module]['inst']);
} else {
    header("Location: ".$path2ROOT."/VFH/index.php");
}

if ($debug) {
    echo "<pre class=debug>Proj_Conf_Edit_ph1.inc.php beendet</pre>";
}

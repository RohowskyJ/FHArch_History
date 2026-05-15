<?php
/**
 * Includes-Liste
 * enthält alle jeweils includierten Scritpt Files
 */
$_SESSION[$module]['Inc_Arr'][] = "ConfEdit_ph1_inc.php";

if ($debug) {
    echo "<pre class=debug>ConfEdit_ph1_inc.php ist gestarted</pre>";
}
# console_log('proj start ph1');
if ($neu['bild_datei_1'] != "") {
    $neu['c_bild_1'] = $neu['bild_datei_1'];
}
if ($neu['bild_datei_2'] != "") {
    $neu['c_bild_2'] = $neu['bild_datei_2'];
}

$updas_s = "\n[Config]\n";

foreach ($neu as $name => $value) { # für alle Felder aus der tabelle 

    if (substr($name,0,2) != "c_") {
        
        unset($neu[$name]);
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
    if ($name == "c_bpath") {
        $updas_s .= "bpath = '$value'\n ";
    }
    if ($name == "c_miBeitr") {
        $updas_s .= "miBeitr = '$value'\n ";
    }
   
} # Ende der Schleife

$dsn = $path2ROOT."login/Basis/common/$cfg";
#var_dump($dsn);
$datei = fopen($dsn, 'w');
fputs($datei, $updas_s);
fclose($datei);


$result = $conf->updateConfig(1,$neu);

if (isset($_SESSION[$module]['inst'])) {
    header("Location: ".$_SESSION[$module]['inst']);
} else {
    header("Location: ".$path2ROOT."/public/index.php");
}

if ($debug) {
    echo "<pre class=debug>ConfEdit_ph1_inc.php beendet</pre>";
}

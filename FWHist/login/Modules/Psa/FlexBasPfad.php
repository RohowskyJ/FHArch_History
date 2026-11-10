<?php
session_start();

$DocRoot = $_SERVER['DOCUMENT_ROOT'];
$bPath = "";
if ($_SERVER['HTTP_HOST'] == 'localhost') {
    $bP_arr = explode("/",dirname($_SERVER['SCRIPT_NAME']) );
    
    $bPath = $bP_arr[1] . "/";
}

echo __FILE__ . " " . __LINE__ . " DocRoot $DocRoot bPath $bPath <br>";

$targpath = $DocRoot . "/" . $bPath . "login/Core/Services/EnvSessionManager.php";

$currpath = __DIR__;

echo __FILE__ . " " . __LINE__ . " Currpath $currpath  . Target $targpath <br>";

require $DocRoot . "/" . $bPath . "VFH/targPfad.php";

$relPath = getRelativePath($currpath, $targpath);

echo __FILE__. " " . __LINE__ . " Relativer Pfad $relPath<br>";


require __DIR__ . "/" . $relPath . $bPath . 'login/Core/Services/FuncsLib.php';

HTML_header("oba jetza"
    );

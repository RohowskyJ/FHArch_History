<?php
/**
 * Zentrale Verwaltung
 * 
 * @author Josef Rohowsky - neu 2020
 */

declare(strict_types=1);

session_start();

$module = 'ADM';
$sub_module = 'ZentralVerw';

$_SESSION['BS_Prim']['Mod'] = ['module' => $module, 'smod' => $sub_module, 'caller' => $module];

if (!isset($_SESSION['BS_Prim']['BE'])) {
    $bPath = $_SESSION['BS_Prim']['Env']['basePath'];
    header('location: $bPath/fharch-oop/VFH/');
}

$path2ROOT = "../../../";

require $path2ROOT . 'login/Core/Services/FuncsLib.php';
require $path2ROOT . 'login/Core/Services/CommFuncsLib.php';
require $path2ROOT . 'login/Core/Services/ConstLib.php';

require_once __DIR__ . '/../../../vendor/autoload.php';

use League\Plates\Engine;

$debug = false;

$templates = new Engine(__DIR__ . '/../templates'); // /src/core/templates

$data = [
    'title' => 'Zentrale- Verwaltungs- Seite',
    'debug' => $debug,
    'path2ROOT' => $path2ROOT,
    'cssBundles' => ['base', 'menu'],
];

echo $templates->render('pages/zentrVerw', $data);
exit;

?>

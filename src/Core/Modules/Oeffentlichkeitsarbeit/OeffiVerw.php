<?php
/** 
 * Menu Öffentlichkeitsarbeit
 * 
 */
declare(strict_types=1);

session_start();

$module = 'OEF';
$sub_module = 'Menu';

$Zugr = "ADM-MI";

$_SESSION['BS_Prim']['Mod'] = ['module' => $module, 'smod' => $sub_module, 'caller' => $module];

$path2ROOT = "../../../../";

require $path2ROOT . 'login/Basis/common/BS_FuncsLib.php';
require $path2ROOT . 'login/Basis/common/FS_CommFuncsLib.php';
require $path2ROOT . 'login/Basis/common/FS_ConstLib.php';
/*
 $ber = userBerechtigtOK($Zugr);
 if (!ber) {
 header("Location $path2ROOT/public/");
 }
 */
require_once __DIR__ . '/../../../../vendor/autoload.php';

use League\Plates\Engine;
var_dump($_SESSION);
$debug = false;
# $rootPfad = "/FHArch-oop"

use Fharch\Core\envSessionManager;
$envManager = new EnvSessionManager();
$basePath = $envManager->getEnv('basePath');
echo __LINE__ . " basepath $basePath <bR>";
var_dump($_SESSION);

$templates = new Engine(__DIR__ . '/../../templates'); // /src/core/templates

$data = [
    'title' => 'Öffentlichkeits- Arbeits- Seite',
    'debug' => $debug,
    'path2ROOT' => $path2ROOT,
    'configOk' => $configOk ?? false,
    'SI' => $SI ?? null,
    'cssBundles' => ['base', 'menu'],
    /**
     * Prüfen,  ob Module installiert, wenn JA, dann Anzeige
     */
    'has' => [
        'Archiv' => is_file($path2ROOT . 'src/Core/Modules/Archivalien/ArchivVerw.php'),
        'Doku' => is_file($path2ROOT . 'src/Core/Modules/Oeffentlichkeitsarbeit/DokuVerw.php'),
        'Foto' => is_file($path2ROOT . 'src/Core/Modules/Fotografie/MediaVerw.php'),
        'Inventar' => is_file($path2ROOT . 'src/Core/Modules/Inventar/InventarVerw.php'),
    ],
];

echo $templates->render('pages/oeffiVerw', $data);
exit;
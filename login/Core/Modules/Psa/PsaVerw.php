<?php
/** 
 * Pesrsönliche Schutzausrüstung
 * 
 */
declare(strict_types=1);

session_start();

$module = 'OEF';
$sub_module = 'Menu';

$Zugr = "ADM-PSA";

$_SESSION['BS_Prim']['Mod'] = ['module' => $module, 'smod' => $sub_module, 'caller' => $module];

if (!isset($_SESSION['BS_Prim']['BE'])) {
    $bPath = $_SESSION['BS_Prim']['Env']['basePath'];
    header('location: $bPath/fharch-oop/VFH/');
}

$path2ROOT = "../../../../";

require $path2ROOT . 'login/Core/Services/FuncsLib.php';
require $path2ROOT . 'login/Core/Services/CommFuncsLib.php';
require $path2ROOT . 'login/Core/Services/ConstLib.php';
/*
 $ber = userBerechtigtOK($Zugr);
 if (!ber) {
 header("Location $path2ROOT/public/");
 }
 */
require_once __DIR__ . '/../../../../vendor/autoload.php';

use League\Plates\Engine;
# var_dump($_SESSION);
$debug = false;
# $rootPfad = "/FHArch-oop"

use Fharch\Core\envSessionManager;
$envManager = new EnvSessionManager();
$basePath = $envManager->getEnv('basePath');
echo __LINE__ . " basepath $basePath <bR>";
# var_dump($_SESSION);

$templates = new Engine(__DIR__ . '/../../templates'); // /src/core/templates

$data = [
    'title' => 'Persönliche Schutzausrüstungs- Arbeits- Seite',
    'debug' => $debug,
    'path2ROOT' => $path2ROOT,
    'configOk' => $configOk ?? false,
    'SI' => $SI ?? null,
    'cssBundles' => ['base', 'menu'],
    /**
     * Prüfen,  ob Module installiert, wenn JA, dann Anzeige
     */
    'has' => [
        'Ehrung' => is_file($path2ROOT . 'login/Core/Modules/Psa/OrtsList.php'),
        'AermAb' => is_file($path2ROOT . 'login/Core/Modules/Psa/OrtsList.php'),
        'OldEhrg' => is_file($path2ROOT . 'login/VF_PS_OV_O_List.php'),
        'OldAermAb' => is_file($path2ROOT . 'login/VF_PS_OV_O_List.php'),
    ],
];

echo $templates->render('pages/psaVerw', $data);
exit;
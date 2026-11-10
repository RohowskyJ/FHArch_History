<?php

declare(strict_types=1);

/**
 * Mitgliederverwaltung, E-mail an Mitglieder, Mail erfassen , senden
 * 
 * @author Josef Rohowsky - neu 2020
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Prüfen, ob Session gültig ist
if (empty($_SESSION['BS_Prim'])) {
    // Session ungültig → Weiterleitung auf externe URL
    if ($_SERVER['HTTP_HOST'] == 'localhost') {
        header('Location: /localhost/Feuerwehr_Archiv/FWHist/');
        exit;
    }
    if (empty($_SESSION['BS_Prim']['BE']['be_id'])) {
        header('Location: ' . ($_SESSION['BS_Prim']['RoBase'] ?? dirname(__DIR__, 3) . DIRECTORY_SEPARATOR) . '/');
        exit;
    }
    header('Location: https://www.feuerwehrhistoriker.at/FWHist/');
    exit;
}

# $_SESSION['BS_Prim'] ??= [];
$systemBasePath = $_SESSION['BS_Prim']['RoBase'] ?? dirname(__DIR__, 3) . DIRECTORY_SEPARATOR;

require_once $systemBasePath . 'vendor/autoload.php';

use FWHist\Core\EnvSessionManager;
use FWHist\Auth\AccessGuard;
new EnvSessionManager();

use FWHist\Database\DB_GenericLog;
use FWHist\Services\TableColumnMetadata;
use FWHist\Services\FormRendererFlex;
#use FWHist\Modules\Mitglieder\API\MI_MitgliederModule;
use FWHist\Services\HTMLPage;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


$module = 'MVW';
$sub_mod = 'all';

/*
// Shutdown-Funktion direkt am Anfang registrieren
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null) {
        $message = "Shutdown error detected:\n" . print_r($error, true);
        error_log($message);
        // Optional: auch in eine separate Datei schreiben
        file_put_contents(__DIR__ . '/MI_Edit_fatal_error.log', $message, FILE_APPEND);
    }
});
    
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/MI_Edit_php-error.log.txt');
*/
require $systemBasePath . 'login/Services/FuncsLib.php';
require $systemBasePath . 'login/Services/CommFuncsLib.php';
require $systemBasePath . 'login/Services/ConstLib.php';
require $systemBasePath . 'login/Services/PHP_MailFuncsLib.php';

$guard = AccessGuard::fromSession(new DB_GenericLog());
if (!$guard->isLoggedIn() || AccessGuard::isPublicView()) {
    http_response_code(403);
    exit('Für diesen Programmteil besteht keine Berechtigung.');
}

$envManager = new EnvSessionManager();
$basePath = $envManager->getEnv('basePath')  ;
$configFileS = $envManager->getEnv('configFileS');

$header = "";

$page = new HTMLPage('E-Mail an andere Mitglieder',
    $systemBasePath,
    $basePath,
    ['headExtras' => $header,
        'type' => 'Form',
        'width' => '60em',
    ]
    );

$mailCSS = $basePath . "/FWHist/css/email_form.css";
$page->addCSS($mailCSS);
$page->loadConfig($configFileS);
$page->renderHeader();

initial_debug('POST','GET'); # Wenn $debug=true - Ausgabe von Debug Informationen: $_POST, $_GET, $_FILES

// ============================================================================================================
// Eingabenerfassung und defauls
// ============================================================================================================
# var_dump($_POST);
$DBD = new DB_GenericLog();
#var_dump($DBD);
$pdo = $DBD->getPDO();
#var_dump($pdo);

// ============================================================================================================
// Eingabenerfassung und defauls Teil 1 - alle POST Werte werden später in array $neu gestelltt
// ============================================================================================================
if (isset($_POST['phase'])) {
    $phase = $_POST['phase'];
} else {
    $phase = 0;
}

if (isset($_GET['mi_email'])) {
    $mi_email = $_GET['mi_email'];
} 
if (isset($_POST['mi_email'])) {
    $mi_email = $_POST['mi_email'];
}
if (!isset($mi_email) || $mi_email == "") {
    echo "kein Mitglied ausgewählt. bitte nochmal auswählen.<br>"; 
    echo "<a href='MitglEmail.php'>zur Auswahl</a>";
}

/** Daten erheben */

$sql = "SELECT mi_anrede, mi_titel, mi_name, mi_vname, mi_n_titel, mi_email
        FROM mi_mitglieder
        WHERE mi_email = :email";

$params = ['email' => $mi_email];

$empfaenger = $DBD->fetchOne($sql, $params);

$abs = $_SESSION['BS_Prim']['BE']['uid'];
$sql = "SELECT mi_anrede, mi_titel, mi_name, mi_vname, mi_n_titel, mi_email
        FROM mi_mitglieder
        WHERE mi_email = :email";

$params = ['email' => $abs];

$absender = $DBD->fetchOne($sql, $params);

/** Daten für die E_Mail zum absenden */
if ($phase == 1) {
    
    $errors = [];
    $old = [];
    
    // Nachricht aus POST
    $nachrichtRaw = $_POST['nachricht'] ?? '';
    $nachricht = trim($nachrichtRaw);
    $old['nachricht'] = $nachricht;
    
    // Validierung
    if (empty($nachricht)) {
        $errors['nachricht'] = 'Die Nachricht darf nicht leer sein.';
    } elseif (mb_strlen($nachricht) > 500) {
        $errors['nachricht'] = 'Die Nachricht darf maximal 500 Zeichen lang sein.';
    }
    
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        $_SESSION['old'] = $old;
        header('Location: kontaktformular.php');
        exit;
    }
    
    // HTML-Sonderzeichen entschärfen
    $nachricht = htmlspecialchars($nachricht, ENT_QUOTES, 'UTF-8');
    
    $empfaengerAnrede = erstelleAnrede($empfaenger);
    $absenderAnrede = trim($absender['mi_anrede'] . ' ' . $absender['mi_titel'] . ' ' . $absender['mi_name'] . ' ' . $absender['mi_vname']);
    
    // Betreff dynamisch aus Absendername
    $betreff = 'Neue Nachricht von ' . $absender['mi_name'];
    
    // E-Mail-Text (Plain Text)
    $emailText = "
        $empfaengerAnrede,<br><br>
        $nachricht <br><br>
        Mit freundlichen Grüßen 
        $absenderAnrede
        ";
    
    $empf = $empfaenger['mi_email'] ;
    $send = $absender['mi_email'];
   
    sendEmail($empf, $betreff, $emailText, $send);
    sendEmail($send, "Historiker Kontaktmailer: Kopie Ihrer Nachricht an $empfaengerAnrede", $emailText);
    
    # ================================================================================================================
    # Schreiben des logs
    # ================================================================================================================
    
    $log_rec = "       Absender: " . $_SESSION['send']['mi_anrede'] . " \n" . "Absender E-Mail: " . $_SESSION['send']['mi_email'] . " \n" . " Empfänger Name: " . $_SESSION['empf']['mi_anrede'] . "  \n" . "        Mail To: " . $_SESSION['empf']['mi_email'] . " \n" . "           Text: $text <<<<< Ende Text  -----\n";
    
    $logDateiname = writelog( "kontaktmail", $log_rec);
    ?>

<!-- =========================================================================================================== -->
<!--                                  Begin der angzuzeigenden Form                                              -->
<!-- =========================================================================================================== -->
<div class=white>
<?php
echo $send_anr;
echo "<p>Eine E-Mail mit Ihrer Nachricht wurde an <b>$empf_anr </b> gesendet.
          Sie erhalten eine Kopie dieser E-Mail an Ihre E-Mail-Adresse <q>$send</q> gesendet.
        </p>";
echo "<p>Ihre Nachricht lautete: <q><cite style='color:darkblue;'><b>$emailText</b></cite></q></p>";
echo VF_Spam_Text . VF_Gruesse;
?>
</div>
<?php    
}

/** Eingabe für die Nachricht */
if ($phase == 0) {


    // Nachricht vom Formular (max 500 Zeichen)
    $nachricht = substr(trim($_POST['nachricht'] ?? ''), 0, 500);
    
    // Anrede Empfänger zusammensetzen
    $empfaengerAnrede = trim($empfaenger['mi_anrede'] . ' ' . $empfaenger['mi_titel'] . ' ' . $empfaenger['mi_name'] . ' ' . $empfaenger['mi_vname']);
    
    // Anrede Absender zusammensetzen
    $absenderAnrede = trim($absender['mi_anrede'] . ' ' . $absender['mi_titel'] . ' ' . $absender['mi_name'] . ' ' . $absender['mi_vname']);
    
    echo "<input type='hidden' name='phase' value='1'>";
    echo "<input type='hidden' name='mi_email' value='$mi_email'>";
    ?>
    <form action="MitglMEdit.php" method="post" novalidate>
    <fieldset>
    <legend>Nachricht</legend>
    
    <label for="empfaenger">Empfänger:</label>
    <input type="text" id="empfaenger" name="empfaenger"
        value="<?=htmlspecialchars($empfaengerAnrede)?>" readonly>
        
        <label for="nachricht">Nachricht (max. 500 Zeichen):</label>
        <textarea id="nachricht" name="nachricht" maxlength="500" required
        class="<?= isset($errors['nachricht']) ? 'error' : '' ?>"
            ><?=htmlspecialchars($old['nachricht'] ?? '')?></textarea>
            <?php if (isset($errors['nachricht'])): ?>
                <div class="error-message"><?=htmlspecialchars($errors['nachricht'])?></div>
            <?php endif; ?>

            <label for="absender">Absender:</label>
            <input type="text" id="absender" name="absender" 
                   value="<?=htmlspecialchars($absenderAnrede)?>" readonly>

            <button type="submit">Nachricht senden</button>
        </fieldset>

<?php 
}

$page->renderFooter();

/** Hilfsfunktion: korrekte deutsche Anrede */
function erstelleAnrede(array $person): string {
    $anrede = ($person['mi_anrede'] === 'Hr.') ? 'Sehr geehrter Herr' : 'Sehr geehrte Frau';
    $titel = !empty($person['mi_titel']) ? ' ' . $person['mi_titel'] : '';
    return $anrede . $titel . ' ' . $person['mi_name'];
}

?>
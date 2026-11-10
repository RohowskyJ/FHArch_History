<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

# $systemBasePath = $_SESSION['BS_Prim']['Env']['systemBasePath'];
require_once __DIR__ . '/vendor/autoload.php';

use FWHist\Core\EnvSessionManager;
use FWHist\Database\DB_GenericLog;
use FWHist\Auth\Auth;
use FWHist\Services\HTMLPage;

// Shutdown-Funktion direkt am Anfang registrieren
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null) {
        $message = "Shutdown error detected:\n" . print_r($error, true);
        error_log($message);
        // Optional: auch in eine separate Datei schreiben
        file_put_contents(__DIR__ . '/LOGIN_public_fatal_error.log', $message, FILE_APPEND);
    }
});

$envManager = new EnvSessionManager();
$systemBasePath = $envManager->getEnv('systemBasePath');
$basePath = $envManager->getEnv('basePath');
$requestPath = parse_url($_SERVER['SCRIPT_NAME'] ?? '', PHP_URL_PATH);
if (is_string($requestPath) && ($appPosition = stripos($requestPath, '/FWHist')) !== false) {
    $basePath = substr($requestPath, 0, $appPosition);
}
$configFileS = $envManager->getEnv('configFileS');

$srv = $_SERVER['HTTP_HOST'];
$SI = 'fwhist';
if ($srv == 'localhost') {
    $SI = 'l';
}

$pdo = new DB_GenericLog();

$auth = new Auth($pdo);

$error = null;
$message = null;
$action = $_POST['action'] ?? 'login';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = is_string($_POST['user_id'] ?? null) ? trim($_POST['user_id']) : '';
    $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';

    if (!$auth->login($userId, $password)) {
        $error = 'Ungültige Benutzer-ID oder Passwort.';
    } elseif ($action === 'change_password') {
        $newPassword = is_string($_POST['new_password'] ?? null) ? $_POST['new_password'] : '';
        $confirmPassword = is_string($_POST['confirm_password'] ?? null) ? $_POST['confirm_password'] : '';

        if (strlen($newPassword) < 8) {
            $error = 'Das neue Passwort muss mindestens 8 Zeichen lang sein.';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'Die neuen Passwörter stimmen nicht überein.';
        } else {
            $auth->changePassword($password, $newPassword);
            $message = 'Das Passwort wurde geändert. Sie können sich jetzt anmelden.';
            $action = 'login';
        }
    } elseif ($action === 'login') {
        $user = $auth->getUser();
        if ($user === null) {
            throw new RuntimeException('Nach erfolgreicher Anmeldung fehlt der Benutzerdatensatz.');
        }

        session_regenerate_id(true);
        $_SESSION['BS_Prim'] ??= [];
        $_SESSION['BS_Prim']['BE'] = [
            'be_id' => $user['be_id'],
            'roles' => $auth->getRoles(),
            'uid' => $userId,
        ];
        $_SESSION['BS_Prim']['Mod'] = ['module' => 'VFH', 'smod' => 'IntStart', 'caller' => 'VFH'];
        
        # $_SESSION['BS_Prim'] `\FWHist\Auth\Authz::loadFromDb($pdo, (int)$user['be_id']);`
            
        header('Location: login/Controllers/MainMenu.php');
        exit;
    } else {
        $error = 'Ungültige Aktion.';
    }
}
$isChangePassword = $action === 'change_password';

$header = "
 <style>
        body { font-family: Arial, sans-serif; background: #f0f0f0; padding: 2rem; }
        form { background: white; padding: 2rem; border-radius: 8px; max-width: 320px; margin: auto; }
        label { display: block; margin-bottom: 0.5rem; }
        input[type= 'text'], input[type='password'] { width: 100%; padding: 0.5rem; margin-bottom: 1rem; }
        button { padding: 0.5rem 1rem; }
        .error { color: red; margin-bottom: 1rem; }
        .success { color: green; margin-bottom: 1rem; }
        #header .custom-col.s3.m2.l1 img { width: 110px !important; max-width: 100%; height: auto; }
    </style>

";

$page = new HTMLPage("Login zu Feuerwehr- Archiv",
    $systemBasePath, 
    $basePath,
    ['headExtras' => $header,
     'type' => 'Form',
        'width' => '60em',
    ]
    );

$page->loadConfig($configFileS);
$page->renderHeader();

#HTMLClass::renderHeader("Login zu Feuerwehr- Arciv", $header , 'Form', '60em');

#var_dump($pdo);
#var_dump($auth);
?>
<div class="login-panel">
    <h2>Login</h2>

    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></div>
    <?php endif; ?>
    <?php if ($message): ?>
        <div class="success"><?= htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></div>
    <?php endif; ?>

    <label for="user_id">Benutzer-ID</label>
    <input type="text" id="user_id" name="user_id" required autofocus
           value="<?= htmlspecialchars(is_string($_POST['user_id'] ?? null) ? $_POST['user_id'] : '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" />

    <label for="password">Passwort</label>
    <input class="password-field" type="password" id="password" name="password" required />

    <div id="change-password-fields" <?= $isChangePassword ? '' : 'hidden' ?>>
        <label for="new_password">Neues Passwort (mindestens 8 Zeichen)</label>
        <input class="password-field" type="password" id="new_password" name="new_password" minlength="8" autocomplete="new-password"
               <?= $isChangePassword ? 'required' : '' ?> />

        <label for="confirm_password">Neues Passwort wiederholen</label>
        <input class="password-field" type="password" id="confirm_password" name="confirm_password" autocomplete="new-password"
               <?= $isChangePassword ? 'required' : '' ?> />
    </div>

    <label>
        <input type="checkbox" id="toggle-password-visibility" />
        Passwörter anzeigen
    </label>

    <input type="hidden" id="action" name="action" value="<?= $isChangePassword ? 'change_password' : 'login' ?>" />
    <button type="submit" id="login-button" <?= $isChangePassword ? 'hidden' : '' ?>>Anmelden</button>
    <button type="button" id="show-change-password" <?= $isChangePassword ? 'hidden' : '' ?>>Passwort ändern</button>
    <button type="submit" id="change-password-button" <?= $isChangePassword ? '' : 'hidden' ?>>Passwort ändern</button>
</div>

<script>
const changeFields = document.getElementById('change-password-fields');
const actionField = document.getElementById('action');
const newPassword = document.getElementById('new_password');
const confirmPassword = document.getElementById('confirm_password');
const loginButton = document.getElementById('login-button');
const changePasswordButton = document.getElementById('change-password-button');
const showChangePasswordButton = document.getElementById('show-change-password');
const togglePasswordVisibility = document.getElementById('toggle-password-visibility');

togglePasswordVisibility.addEventListener('change', () => {
    document.querySelectorAll('#myform .password-field').forEach((input) => {
        input.type = togglePasswordVisibility.checked ? 'text' : 'password';
    });
});

showChangePasswordButton.addEventListener('click', () => {
    changeFields.hidden = false;
    newPassword.required = true;
    confirmPassword.required = true;
    actionField.value = 'change_password';
    loginButton.hidden = true;
    showChangePasswordButton.hidden = true;
    changePasswordButton.hidden = false;
    newPassword.focus();
});
</script>

<?php 
$page->renderFooter();
?>

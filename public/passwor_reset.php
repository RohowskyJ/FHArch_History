<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/../vendor/autoload.php';

use Fharch\Core\Database\PdoFactory;
use Fharch\Core\Database\Database;
use Fharch\Core\Auth\Auth;

$config = [
    'dsn'  => 'mysql:host=localhost;dbname=fharch;charset=utf8mb4',
    'user' => 'root',
    'pass' => '',
];
$pdo = PdoFactory::create($config);
$db = new Database($pdo);
$auth = new Auth($db);

$token = $_GET['token'] ?? '';
$error = null;
$message = null;
$showForm = true;

if ($token === '') {
    $error = 'Kein Token angegeben.';
    $showForm = false;
} else {
    $beId = $auth->validatePasswordResetToken($token);
    if ($beId === null) {
        $error = 'Ungültiger oder abgelaufener Token.';
        $showForm = false;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $showForm) {
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if ($newPassword === '' || $confirmPassword === '') {
        $error = 'Bitte beide Passwortfelder ausfüllen.';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'Die Passwörter stimmen nicht überein.';
    } elseif (strlen($newPassword) < 8) {
        $error = 'Das Passwort muss mindestens 8 Zeichen lang sein.';
    } else {
        // Passwort aktualisieren
        try {
            // Benutzer laden
            $user = $db->fetchOne('SELECT * FROM fv_benutzer WHERE be_id = :be_id', ['be_id' => $beId]);
            if (!$user) {
                throw new RuntimeException('Benutzer nicht gefunden.');
            }
            
            // Passwort hashen und speichern
            $hash = password_hash($newPassword, PASSWORD_DEFAULT);
            $db->query(
                'UPDATE fv_erlauben SET fe_pw = :pw, fe_changed_id = :changed_id, fe_changed_at = NOW() WHERE be_id = :be_id',
                [
                    'pw' => $hash,
                    'changed_id' => $beId,
                    'be_id' => $beId,
                ]
                );
            
            // Token als benutzt markieren
            $auth->markPasswordResetTokenUsed($token);
            
            $message = 'Das Passwort wurde erfolgreich geändert. Du kannst dich jetzt anmelden.';
            $showForm = false;
        } catch (\Throwable $e) {
            $error = 'Fehler beim Ändern des Passworts: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8" />
    <title>Passwort zurücksetzen</title>
</head>
<body>
    <h1>Passwort zurücksetzen</h1>

    <?php if ($message): ?>
        <p style="color:green;"><?=htmlspecialchars($message)?></p>
        <p><a href="/login.php">Zur Anmeldung</a></p>
    <?php elseif ($error): ?>
        <p style="color:red;"><?=htmlspecialchars($error)?></p>
    <?php endif; ?>

    <?php if ($showForm): ?>
        <form method="post" action="">
            <label for="new_password">Neues Passwort</label><br />
            <input type="password" id="new_password" name="new_password" required autofocus /><br /><br />

            <label for="confirm_password">Passwort bestätigen</label><br />
            <input type="password" id="confirm_password" name="confirm_password" required /><br /><br />

            <button type="submit">Passwort ändern</button>
        </form>
    <?php endif; ?>
</body>
</html>

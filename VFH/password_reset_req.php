<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/../vendor/autoload.php';

use Fharch\Core\Database\PdoFactory;
use Fharch\Core\Database\Database;
use Fharch\Core\Auth\Auth;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailException;

$config = [
    'dsn'  => 'mysql:host=localhost;dbname=fharch;charset=utf8mb4',
    'user' => 'root',
    'pass' => '',
];
$pdo = PdoFactory::create($config);
$db = new Database($pdo);
$auth = new Auth($db);

$message = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = trim($_POST['user_id'] ?? '');
    
    if ($userId === '') {
        $error = 'Bitte Benutzer-ID eingeben.';
    } else {
        // Benutzer suchen
        $user = $db->fetchOne('SELECT be_id, be_uid, be_act, fd_email FROM fv_benutzer b JOIN fv_ben_dat d ON b.be_id = d.be_id WHERE be_uid = :uid AND be_act = "a"', ['uid' => $userId]);
        
        if (!$user) {
            $error = 'Benutzer nicht gefunden oder inaktiv.';
        } elseif (empty($user['fd_email'])) {
            $error = 'Keine E-Mail-Adresse hinterlegt.';
        } else {
            // Token generieren
            $token = bin2hex(random_bytes(32));
            $expires = new DateTime('+1 hour');
            
            // Token speichern
            $auth->createPasswordResetToken((int)$user['be_id'], $token, $expires);
            
            // Reset-Link bauen
            $resetLink = sprintf(
                '%s/password_reset.php?token=%s',
                (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'],
                urlencode($token)
                );
            
            // Mail senden
            $mail = new PHPMailer(true);
            try {
                // Servereinstellungen
                $mail->isSMTP();
                $mail->Host = 'smtp.example.com'; // SMTP-Server anpassen
                $mail->SMTPAuth = true;
                $mail->Username = 'smtp-user@example.com'; // SMTP-Benutzer
                $mail->Password = 'smtp-password';         // SMTP-Passwort
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;
                
                // Empfänger
                $mail->setFrom('no-reply@example.com', 'Deine Anwendung');
                $mail->addAddress($user['fd_email'], $user['fd_vname'] . ' ' . $user['fd_name']);
                
                // Inhalt
                $mail->isHTML(true);
                $mail->Subject = 'Passwort zurücksetzen';
                $mail->Body = '<p>Hallo ' . htmlspecialchars($user['fd_vname']) . ',</p>'
                    . '<p>Du hast ein Passwort-Reset angefordert. Bitte klicke auf den folgenden Link, um dein Passwort zurückzusetzen:</p>'
                        . '<p><a href="' . htmlspecialchars($resetLink) . '">' . htmlspecialchars($resetLink) . '</a></p>'
                            . '<p>Der Link ist 1 Stunde gültig.</p>'
                                . '<p>Wenn du diese Anfrage nicht gestellt hast, ignoriere diese E-Mail.</p>';
                                
                                $mail->send();
                                $message = 'Eine E-Mail mit dem Passwort-Reset-Link wurde versendet.';
            } catch (MailException $e) {
                $error = 'Fehler beim Versand der E-Mail: ' . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8" />
    <title>Passwort zurücksetzen anfordern</title>
</head>
<body>
    <h1>Passwort zurücksetzen anfordern</h1>

    <?php if ($message): ?>
        <p style="color:green;"><?=htmlspecialchars($message)?></p>
    <?php elseif ($error): ?>
        <p style="color:red;"><?=htmlspecialchars($error)?></p>
    <?php endif; ?>

    <form method="post" action="">
        <label for="user_id">Benutzer-ID</label><br />
        <input type="text" id="user_id" name="user_id" required autofocus /><br /><br />
        <button type="submit">Passwort-Reset-Link anfordern</button>
    </form>
</body>
</html>

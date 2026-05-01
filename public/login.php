<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/../vendor/autoload.php';

use Fharch\Core\Database\DB_GenericLog;
use Fharch\Core\Auth\Auth;


$srv = $_SERVER['HTTP_HOST'];
$SI = 'vfh';
if ($srv == 'localhost') {
    $SI = 'l';
}
#$config = require __DIR__ . "/../src/config/ConfigLib_d_".$SI.".php";

$pdo = new DB_GenericLog();

$auth = new Auth($pdo);

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_POST['user_id'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($auth->login($userId, $password)) {
        // Login erfolgreich
        # $_SESSION['roles'] = 
        $roles = $auth->getRoles();
        # $_SESSION['user'] = 
        $user = $auth->getUser();
        $_SESSION['BS_Prim']['BE']['be_id'] = $user['be_id'];
        $_SESSION['BS_Prim']['BE']['roles'] = $roles[0];

        header("Location: ../src/Core/Controllers/MainMenu.php");
        exit;
    } else {
        $error = 'Ungültige Benutzer-ID oder Passwort.';
    }
}

?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8" />
    <title>Login</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f0f0f0; padding: 2rem; }
        form { background: white; padding: 2rem; border-radius: 8px; max-width: 320px; margin: auto; }
        label { display: block; margin-bottom: 0.5rem; }
        input[type="text"], input[type="password"] { width: 100%; padding: 0.5rem; margin-bottom: 1rem; }
        button { padding: 0.5rem 1rem; }
        .error { color: red; margin-bottom: 1rem; }
    </style>
</head>
<body>
<?php 
var_dump($pdo);
var_dump($auth);
?>
<form method="post" action="">
    <h2>Login</h2>

    <?php if ($error): ?>
        <div class="error"><?=htmlspecialchars($error)?></div>
    <?php endif; ?>

    <label for="user_id">Benutzer-ID</label>
    <input type="text" id="user_id" name="user_id" required autofocus />

    <label for="password">Passwort</label>
    <input type="password" id="password" name="password" required />

    <button type="submit">Anmelden</button>
</form>

</body>
</html>


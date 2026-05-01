<?php
declare(strict_types=1);

session_start();

if (!isset($_SESSION['user'])) {
    header('Location: /login.php');
    exit;
}

$user = $_SESSION['user'];

?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8" />
    <title>Dashboard</title>
</head>
<body>
    <h1>Willkommen, <?=htmlspecialchars($user['be_uid'])?></h1>
    <p><a href="/logout.php">Logout</a></p>
</body>
</html>

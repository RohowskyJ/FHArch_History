<?php
session_start();


// Prüfen, ob Session gültig ist
if (empty($_SESSION['BS_Prim'])) {
    // Session ungültig → Weiterleitung auf externe URL
    if ($_SERVER['HTTP_HOST'] == 'localhost') {
        header('Location: /localhost/Feuerwehr_Archiv/FWHist/');
        exit;
    }
    header('Location: https://www.feuerwehrhistoriker.at/FWHist/');
    exit;
}

session_unset();// remove all session variables
session_destroy();// destroy the session
?>
<!DOCTYPE html>
<html>
<body>
All session variables are now removed, and the session is destroyed. 
</body>
</html>
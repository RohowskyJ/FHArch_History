<?php
session_start();
session_unset();// remove all session variables
session_destroy();// destroy the session
?>
<!DOCTYPE html>
<html>
<body>
All session variables are now removed, and the session is destroyed. 
</body>
</html>
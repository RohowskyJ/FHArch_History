<?php

$module = 'phpinfo';
$debug = true;
$debug = false; // Debug output Ein/Aus Schalter
# ----------------------------------------------------------------------------------------------
# php Env Vars anzeigen
#
# wird nur in /login/comon/index verwendet !
#
# 2022-02-05 B.R.Gaicki - V5 (PixRipTab & login )
# -----------------------------------------------------------------------------------------------
require 'BA_Funcs.lib.php'; // Diverse Unterprogramme
require 'BA_HTML_Funcs.lib.php';

initial_debug(); # Wenn $debug=true - Ausgabe von Debug Informationen: $_POST, $_GET, $_FILES
# var_dump(php_ini_loaded_file(), php_ini_scanned_files());

BA_HTML_header('PHPINFO anzeigen', '', '', '');
phpinfo();

BA_HTML_trailer();

<?php
$srv_root = $_SERVER['REQUEST_URI'];
$ar = explode("/",$srv_root);
header("Location: /".$ar[1]."/public/index.php");
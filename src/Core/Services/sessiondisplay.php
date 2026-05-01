<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
<link href='css/w3.css' rel='stylesheet' type='text/css'>
</head>
<body>
	<h3>$_SESSION</h3>
<?php
echo "<table class='w3-table w3-striped w3-hoverable'
       style='border:1px solid black;background-color:white;margin:0px;'>";

if (isset($_SESSION)) {
    foreach ($_SESSION as $name => $value) { # as $val
        # echo "<tr><td>[$name]</td><td>=> "; print_r($value); echo "</td></tr>";
        echo "<tr><td>[$name]</td><td>"; # print_r($value);
        echo "</td></tr>";
        if (is_array($value)) {
            # echo "<tr><td>[$name]</td><td>=> "; echo "</td></tr>";
            foreach ($value as $sname => $svalue) {
                echo "<tr></td><td></td><td></td><td>[$sname]</td><td>=> ";
                print_r($svalue);
                echo "</td></tr>";
            }
        } else {
            echo "<tr><td>[$name]</td><td>=> ";
            print_r($value);
            echo "</td></tr>";
        }
    }
    echo '<table>';
}
?>
</body>
</html>
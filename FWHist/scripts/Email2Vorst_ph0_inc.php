<?php

/**
 * E-Mail an den Vorstand: Formular bzw. Bestätigung (öffentlich, ohne Login)
 *
 * Erwartet aus Email2Vorst.php: $values, $err, $sent, $groups, $pfg, $contact, $basePath, $debug
 *
 * @author Josef Rohowsky - neu 2026
 */

$h = static fn(string $s): string => htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$field = "style='font-size:1.15em;padding:.4em;width:100%;max-width:40em;box-sizing:border-box'";
$labelStyle = "style='display:block;font-weight:bold;font-size:1.1em;margin:1em 0 .3em'";

echo "<h2>Nachricht an den Vorstand der Feuerwehrhistoriker in NÖ</h2>";

if ($sent) {
    echo "<div class='white' role='status' style='padding:1em;font-size:1.2em'>";
    echo "<p><b>Vielen Dank, Ihre Nachricht wurde gesendet.</b></p>";
    echo "<p>Die Antwort kommt an die von Ihnen angegebene E-Mail-Adresse.</p>";
    echo "<p><a href='" . $h($basePath . '/FWHist') . "'>Zurück zur Home Page</a></p>";
    echo "</div>";

    return;
}

echo "<div class='white' style='padding:1em'>";
echo "<p>Die Nachricht geht an die Mitglieder des Vorstands. Ihre Angaben werden nur zur Bearbeitung Ihrer Anfrage verwendet.</p>";

if ($err !== '') {
    echo "<div role='alert' style='margin:1em 0;padding:.8em;border:3px solid #b00;background:#fff0f0;color:#900;font-size:1.15em'><b>" . $h($err) . "</b></div>";
}

echo "<label for='adresse' {$labelStyle}>An</label>";
echo "<select id='adresse' name='adresse' {$field}>";
echo "<option value=''" . ($values['adresse'] === '' ? " selected='selected'" : '') . ">Allgemein (alle Vorstandsmitglieder)</option>";
foreach ($groups as $key => $group) {
    $txt = V_Funktion[$group['funct']];
    echo "<option value='" . $h((string) $key) . "'" . ($values['adresse'] === (string) $key ? " selected='selected'" : '') . ">" . $h($txt) . "</option>"; // statt $txt: $group['funct']
    
}
echo "</select>";

echo "<label for='betreff' {$labelStyle}>Betreff</label>";
echo "<input type='text' id='betreff' name='betreff' maxlength='80' required value='" . $h($values['betreff']) . "' {$field}>";

echo "<label for='msgtext' {$labelStyle}>Meine Nachricht</label>";
echo "<textarea id='msgtext' name='msgtext' rows='10' maxlength='3000' required {$field}>" . $h($values['msgtext']) . "</textarea>";

echo "<label for='absender' {$labelStyle}>Mein Name</label>";
echo "<input type='text' id='absender' name='absender' maxlength='80' required autocomplete='name' value='" . $h($values['absender']) . "' {$field}>";

echo "<label for='email' {$labelStyle}>Meine E-Mail-Adresse (für die Antwort)</label>";
echo "<input type='email' id='email' name='email' maxlength='120' required autocomplete='email' value='" . $h($values['email']) . "' {$field}>";

echo $pfg->renderFields($contact);   // CSRF-Token, Honigtopf, Nummern-Captcha

echo "<p><button type='submit' name='phase' value='1' class='green' style='font-size:1.3em;padding:.6em 1.4em'>Nachricht senden</button></p>";
echo "<p><a href='" . $h($basePath . '/FWHist') . "'>Zurück zur Home Page</a></p>";
echo "</div>";
?>

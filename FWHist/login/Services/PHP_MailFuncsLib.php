<?php

/**
 * Bibliothek für Funktionen zur Nutzug von PHPMailer.
 *
 * @author  B.R.Gaicki  - neu 2018  in eigene Bibliothek 2024_04-28 Josef Rohowsky
 *
 * Funcs - Gemeinsame Konstantendefinitionen und Unterprogramme - Version 5 by B.Richard Gaicki
 *
 * ----------------------------------------------------------------------------------
 * Unterprogramme:
 *  - sendEmail    - zum Versenden von Emails
 */

// Import PHPMailer classes into the global namespace
// These must be at the top of your script, not inside a function
# namespace MyProject;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

if ($_SERVER['SERVER_NAME'] == 'localhost') { # am localhost
    /** @var string $VF_HP URL der Seite für Aufrufe */
    $VF_HP = 'http://localhost/Feuerwehr_Archiv/FWHist';
} else {
    $VF_HP = "https://" . $_SERVER['SERVER_NAME'];
}


/**
 * Unterprogramm zum Versenden von Emails
 *
 * @param string $MailTo
 *            Empänger(Liste)
 * @param string $MailSubject
 *            Subject Text der EMail
 * @param string $text
 *            Inhalt der Email in HTML formatext
 * @param string $reply_to
 *            optionale 'Reply-To' E-Mail-Adresse als ',' Komma separated list
 * @param string $ccList 
 *                      Liste für cc- Adressen    als ',' Komma separated list    
 * @param string $bccList 
 *                      Liste für bcc- Adressen    als ',' Komma separated list                  
 * @param string $attachList
 *                      Liste für Attachments als ',' Komma separated list
 * @return boolean
 *
 * @global boolean $debug Anzeige von Debug- Informationen: if ($debug) { echo "Text" }
 * @global string $systemBasePath String zur root-Angleichung für relative Adressierung
 * @global string $VF_HP URL der Seite für Aufrufe
 */
function sendEmail($MailTo, $MailSubject, $text, $reply_to = "", $ccList = "", $bccList = "", $attachlist = '')
// --------------------------------------------------------------------------------
{
    global $debug, $systemBasePath, $basePath, $VF_HP, $adr_list;
    #var_dump($adr_list);
    AppLogger::info("Adressliste $adr_list");
    require_once $systemBasePath . 'vendor/phpmailer/phpmailer/src/Exception.php';
    require_once $systemBasePath . 'vendor/phpmailer/phpmailer/src/PHPMailer.php';
    require_once $systemBasePath . 'vendor/phpmailer/phpmailer/src/SMTP.php';
 
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $hostName = parse_url('http://' . $host, PHP_URL_HOST) ?: $host;

    $SI = 'l';
    if (stripos($hostName, 'localhost') !== false || stripos($hostName, '127.0.0.1') !== false) {
        $SI == 'fwhist';
    }
    $ini_arr = parse_ini_file($systemBasePath.'login/config/config_s_'.$SI.'.ini', true, INI_SCANNER_NORMAL);
    $vema = $ini_arr['Config']['vema'];
    AppLogger::info("vema $vema");
    $absender = 'noreply@feuerwehrhistoriker.at';

    if ($reply_to == "") {
        $sysgen_text = 'Diese Nachricht wurde <u>automatisch</u> erstellt.' . '<br><b>Senden Sie keine Antwort an die Absenderadresse.</b>';
    } else {
        $sysgen_text = 'Diese Nachricht wurde <u>automatisch</u> erstellt.' . '<br><b>Wir bitten Sie nicht direkt auf diese E-Mail zu antworten.</b>' . "<br>Bei Fragen wenden Sie sich an <a href='mailto:" . $reply_to . "'>" . $reply_to . "</a>.";
    }
    $sysgen_text = '<p style="max-width:40em; text-align:center; align:center; background-color:white; border:4px solid red; border-radius:15px; padding:1em">' . $sysgen_text . '</p>';

    $Mailtext = '<html>' . '<head>' . "<meta charset='UTF-8'>" . '<title>Feuerwehrhistoriker Info Mail</title>' . "\n<style>" . "p  {margin-top:1em; margin-bottom:1em;}" . "h4 {margin-top:0px;margin-bottom:5px;color:darkblue;font-size:120%}" . "fieldset{background-color:white;border-radius:15px;border:1px solid blue;margin:10px 0 10px 0}" . "legend {background-color:white;color:darkblue;font-weight:bold;font-size:110%}" . "div.white { background-color:white;border-style:none;padding:5px 10px 5px}" . "span.blink {color:red;font-weight:bold;font-size:130%;animation:errorblink 1.5s linear infinite;}" . "@keyframes errorblink { 0% {opacity:0;} 50% {opacity:0.7;} 00% { opacity:0;} }" . "</style>" . "\n</head>" . "\n<body>" . '<div style="text-align:left;">' . '<img src="' . $basePath . 'login/common/imgs/Lehner_Dauber_Signet_s_72.jpg" alt="Feuerwehrhistoriker in NÖ" style="border:2px solid blue;">' . "$sysgen_text" . '</div>' . "<br>\n" . $text . "\n</body></html>";

    if ($debug) { # or $_SERVER['SERVER_NAME'] == 'localhost')
        echo "<pre class=debug>Mailto geändert<br>von: $MailTo";
        #$MailTo = $adm_list;
        echo "<br>auf: $MailTo</pre>";
        echo "<br><div class=debug>";
        echo "Mail to: $MailTo";
        echo "<hr>Subject: $MailSubject";
        echo "<hr>$Mailtext<hr>Ende der E-Mail";
        echo "</div></br>";
    }

    $mail = new PHPMailer(true); // Passing `true` enables exceptions
    try {
        // Server settings
        $mail->SMTPDebug = 0; // Enable verbose debug output 0 off
        
        $mail->isSMTP(); // Set mailer to use SMTP
        $mail->Host = 'smtp.easyname.com'; // Specify main and backup SMTP servers
        $mail->SMTPAuth = true; // Enable SMTP authentication
        $mail->Username = '222196mail4'; // SMTP username
        $mail->Password = '.bz3tbg1q1yw'; // SMTP password
        
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // statt 'ssl' 7.0.2
        $mail->Port = 465; // TCP port to connect to
        $mail->setLanguage('de', 'phpmailer.lang-de.php');
        
        $mail->CharSet='UTF-8';

        // Recipients
        switch ($_SERVER['SERVER_NAME']) {
            case "localhost":
                $mail->setFrom($absender, 'VFHNÖ Test'); // This is the email your form sends From
                $mail->addAddress($vema);

                break;
            default:
                $mail->setFrom($absender); // This is the email your form sends From
                foreach (explode(',', $MailTo) as $address) {
                    $mail->addAddress($address); // Add a recipient address (Name is optional)
                }
                
                if ($ccList != '' ) {
                    foreach (explode(',', $ccList) as $address) {
                        $mail->addCC($address); // Add a recipient address (Name is optional)
                    }
                }
                
                if ($bccList != '' ) {
                    foreach (explode(',', $bccList) as $address) {
                        $mail->addBCC($address); // Add a recipient address (Name is optional)
                    }
                }
        }

        if ($reply_to != "") {
            foreach (explode(',', $reply_to) as $address) {
                $mail->addReplyTo($address); // Add a recipient address (Name is optional)
            }
        }

        // Attachments
        // $mail->addAttachment('/var/tmp/file.tar.gz'); // Add attachments
        // $mail->addAttachment('/tmp/image.jpg', 'new.jpg'); // Optional name

        if ($attachList != '' ) {
            foreach (explode(',', $atachList) as $data) {
                $mail->addAttachment($data); // 
            }
        }
        
        // Content
        $mail->isHTML(true); // Set email format to HTML
        $mail->Subject = $MailSubject;
        $mail->Body = $Mailtext; # $text;
        // $mail->AltBody = 'This is tSubject line goes here'.he body in plain text for non-HTML mail clients';

        $mail->send();
        if ($debug) { # or $_SERVER['SERVER_NAME'] == 'localhost')
            echo '<br><b>Mail wurde gesendet</b><br>';
        }
    } catch (Exception $e) {
        echo 'Message could not be sent.';
        echo 'Mailer Error: ' . $mail->ErrorInfo;
    }

    return true;
}

// Ende von function sendEmail

/**
 * Ende der Bibliothek
 * 
 * @author Josef Rohowsky - 20240428 - v7.0.2 20260205
 */

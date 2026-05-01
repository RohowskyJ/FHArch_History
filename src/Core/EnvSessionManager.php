<?php
/** 
 * Hier eine PHP-Klasse, die beim Erstaufruf (z.B. /public/index.php) die Session-Variable mit den gewünschten Werten setzt und bei Folgeaufrufen prüft, ob sie vorhanden ist, und bei Bedarf aktualisiert:
 */

namespace Fharch\Core;

class EnvSessionManager {
    
    private const SESSION_KEY = 'BS_Prim';
    
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->initializeOrUpdateSession();
    }
    
    private function initializeOrUpdateSession(): void {
        if (!isset($_SESSION[self::SESSION_KEY]['Env'])) {
            $this->setEnvSession();
        } else {
            $this->updateEnvSessionIfNeeded();
        }
    }
    
    private function setEnvSession(): void {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $pathParts = explode('/', trim(parse_url($uri, PHP_URL_PATH), '/'));
        
        $basePath = "";
        if ($_SERVER['HTTP_HOST'] === 'localhost') {
            $basePath = !empty($pathParts[0]) ? '/' . $pathParts[0] : '';
        } 
        
        $_SESSION[self::SESSION_KEY]['Env'] = [
            'caller'   => $uri,
            'remAddr'  => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            'srvAddr'  => $_SERVER['SERVER_ADDR'] ?? '127.0.0.1',
            'basePath' => $basePath,
            'updated'  => time()
        ];
    }
    
    private function updateEnvSessionIfNeeded(): void {
        // Aktualisierung nur, wenn sich die URI geändert hat
        if (($_SESSION[self::SESSION_KEY]['Env']['caller'] ?? '') !== ($_SERVER['REQUEST_URI'] ?? '')) {
            $this->setEnvSession();
        }
    }
    
    // Optional: Zugriff auf die gespeicherten Werte
    public function getEnv(string $key = null) {
        $data = $_SESSION[self::SESSION_KEY]['Env'] ?? [];
        if ($key) {
            return $data[$key] ?? null;
        }
        return $data;
    }
}

/*
// Beispiel der Nutzung:
$envManager = new EnvSessionManager();
// Zugriff z.B. auf basePath:
$basePath = $envManager->getEnv('basePath');
Erklärung:

Beim ersten Aufruf wird $_SESSION['BS_Prim']['Env'] mit caller (aktueller Request URI), remAddr (Client-IP), srvAddr (Server-IP) und basePath (erster Teil des URI-Pfads) gesetzt.
Bei Folgeaufrufen prüft die Klasse, ob sich die URI geändert hat, und aktualisiert die Session-Daten bei Bedarf.
Die Klasse startet die Session nur, wenn sie noch nicht gestartet wurde.
Der Zugriff auf die gespeicherten Werte erfolgt über die Methode getEnv().
Diese Struktur ist robust, kapselt die Logik sauber und lässt sich leicht erweitern oder anpassen.

*/


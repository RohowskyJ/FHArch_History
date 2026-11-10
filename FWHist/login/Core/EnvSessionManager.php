<?php

namespace FWHist\Core;

// Shutdown-Funktion direkt am Anfang registrieren
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null) {
        $message = "Shutdown error detected:\n" . print_r($error, true);
        error_log($message);
        // Optional: auch in eine separate Datei schreiben
        file_put_contents(__DIR__ . '../../../login/AOrd_Verz/Logging/Env_Edit_fatal_error.log', $message, FILE_APPEND);
    }
});
    

class EnvSessionManager {
    
    private const SESSION_KEY = 'BS_Prim';
    private string $systemBasePath;
    
    public function __construct(string $systemBasePath = '') {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
        
        $this->systemBasePath = $systemBasePath ?: $this->detectSystemBasePath();
        
        $this->initializeOrUpdateSession();
    }
    
    private function initializeOrUpdateSession(): void {
        $currentUri = $_SERVER['REQUEST_URI'] ?? '';
        $storedEnv = $_SESSION[self::SESSION_KEY]['Env'] ?? null;
        
        if ($storedEnv === null) {
            // Session noch nicht gesetzt: komplette Initialisierung
            $this->setEnvSession();
        } elseif (($storedEnv['caller'] ?? '') !== $currentUri) {
            // Nur URI-bezogene Werte aktualisieren, systemBasePath bleibt unverändert
            $this->updateUriRelatedSession($currentUri);
        }
    }
    
    private function setEnvSession(): void {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        
        $hostName = parse_url('http://' . $host, PHP_URL_HOST) ?: $host;
        
        $pathParts = explode('/', trim(parse_url($uri, PHP_URL_PATH) ?? '', '/'));
        $basePath = '';
        $configFileS = '';
        if (stripos($hostName, 'localhost') !== false || stripos($hostName, '127.0.0.1') !== false) {
            $basePath = !empty($pathParts[0]) ? '/' . $pathParts[0] : '';
            $configFileS = 'login/config/config_s_l.ini';
        } else {
            $configFileS = 'login/config/config_s_fwhist.ini';
        }
        
        $_SESSION[self::SESSION_KEY]['Env'] = [
            'DocRoot'        => $_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__, 2),
            'Host'           => $host,
            'caller'         => $basePath,
            'remAddr'        => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            'srvAddr'        => $_SERVER['SERVER_ADDR'] ?? '127.0.0.1',
            'configFileS'    => $this->systemBasePath . $configFileS,
            'basePath'       => $uri,
            'systemBasePath' => $this->systemBasePath,
            'updated'        => time(),
            'https'          => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        ];
    }
    
    private function updateUriRelatedSession(string $currentUri): void {
        $_SESSION[self::SESSION_KEY]['Env']['caller'] = $currentUri;
        $_SESSION[self::SESSION_KEY]['Env']['updated'] = time();
        
        $_SESSION[self::SESSION_KEY]['Env']['remAddr'] = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $_SESSION[self::SESSION_KEY]['Env']['srvAddr'] = $_SERVER['SERVER_ADDR'] ?? '127.0.0.1';
        $_SESSION[self::SESSION_KEY]['Env']['Host'] = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $_SESSION[self::SESSION_KEY]['Env']['basePath'] = $this->calculateBasePath($currentUri);
        $_SESSION[self::SESSION_KEY]['Env']['https'] = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    }
    
    private function calculateBasePath(string $uri): string {
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $hostName = parse_url('http://' . $host, PHP_URL_HOST) ?: $host;
        $pathParts = explode('/', trim(parse_url($uri, PHP_URL_PATH) ?? '', '/'));
        if (stripos($hostName, 'localhost') !== false || stripos($hostName, '127.0.0.1') !== false) {
            return !empty($pathParts[0]) ? '/' . $pathParts[0] : '';
        }
        return '';
    }
    
    private function detectSystemBasePath(): string {
        $path = dirname(__DIR__);
        return str_replace('\\', '/', $path) . '/';
    }
    
    public function getEnv(?string $key = null) {
        $data = $_SESSION[self::SESSION_KEY]['Env'] ?? [];
        if ($key) {
            return $data[$key] ?? null;
        }
        return $data;
    }
    
    public function clear(): void {
        unset($_SESSION[self::SESSION_KEY]['Env']);
    }
}
/*
Nutzungshinweise
Beim Erzeugen der Klasse kannst du optional den Systempfad übergeben, z.B.:

$RoBase = rtrim(__DIR__, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
$RoBase = str_replace('\\', '/', $RoBase);

$envManager = new EnvSessionManager($RoBase);
Alternativ ermittelt die Klasse den Pfad automatisch (ein Verzeichnis über dem Core-Verzeichnis).

Die Session wird automatisch gestartet, falls noch nicht aktiv.

Die Session-Variable $_SESSION['BS_Prim']['Env'] enthält nun neben Web-Pfaden auch den Systempfad (systemBasePath).

Über getEnv('systemBasePath') kannst du den Systempfad jederzeit abrufen.

So hast du sowohl den Web-Pfad (basePath) als auch den System-Pfad (systemBasePath) bequem in der Session verfügbar.

*/

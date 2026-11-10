<?php

namespace FWHist\Services;

/**
 * Einfache Missbrauchsbremse für öffentliche Formulare (ohne Datenbank).
 *
 * Je Bucket und Schlüssel (z. B. 'anm-ip' + IP-Adresse) wird eine Liste von Zeitpunkten in einer Datei geführt;
 * hit() zählt einen Treffer und liefert false, wenn das Limit im Zeitfenster erreicht ist. Dateinamen sind Hashes,
 * die Dateien enthalten nur Zeitstempel. Fehler beim Dateizugriff werden protokolliert und blockieren nicht
 * (die Bremse darf das Formular nicht lahmlegen, die übrigen Schutzmaßnahmen bleiben aktiv).
 *
 * Die IP-Adresse kommt aus REMOTE_ADDR. Hinter einem Reverse-Proxy steht dort die Adresse des Proxys, dann
 * clientKey() anpassen (X-Forwarded-For nur von vertrauenswürdigen Proxys übernehmen).
 */
final class RateLimiter
{
    public function __construct(private string $dir)
    {
    }

    /** Schlüssel für den Besucher: IPv4 vollständig, IPv6 auf das /64-Netz verkürzt. */
    public static function clientKey(): string
    {
        $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
        if (str_contains($ip, ':')) {
            $packed = @inet_pton($ip);
            if ($packed !== false) {
                return bin2hex(substr($packed, 0, 8)) . '/64';
            }
        }

        return $ip;
    }

    /** Zählt einen Treffer; false = Limit erreicht (der Treffer wird dann nicht gezählt). */
    public function hit(string $bucket, string $key, int $max, int $windowSeconds): bool
    {
        return $this->run($bucket, $key, $max, $windowSeconds, true);
    }

    /** true = Limit erreicht, ohne zu zählen. */
    public function isBlocked(string $bucket, string $key, int $max, int $windowSeconds): bool
    {
        return !$this->run($bucket, $key, $max, $windowSeconds, false);
    }

    /** Zählt immer (auch über dem Limit), z. B. für falsche Eingaben. */
    public function record(string $bucket, string $key, int $windowSeconds): void
    {
        $this->run($bucket, $key, PHP_INT_MAX, $windowSeconds, true);
    }

    private function run(string $bucket, string $key, int $max, int $window, bool $count): bool
    {
        if (!is_dir($this->dir) && !@mkdir($this->dir, 0775, true) && !is_dir($this->dir)) {
            error_log('RateLimiter: Verzeichnis nicht verfügbar: ' . $this->dir);

            return true;
        }

        $file = rtrim($this->dir, '/\\') . DIRECTORY_SEPARATOR . substr(hash('sha256', $bucket . '|' . $key), 0, 40) . '.json';
        $fh = @fopen($file, 'c+');
        if ($fh === false) {
            error_log('RateLimiter: Datei nicht beschreibbar: ' . $file);

            return true;
        }

        $allowed = true;
        try {
            flock($fh, LOCK_EX);
            $raw = stream_get_contents($fh);
            $times = json_decode($raw === false || $raw === '' ? '[]' : $raw, true);
            $now = time();
            $times = array_values(array_filter(is_array($times) ? $times : [], static fn($t) => is_int($t) && $t > $now - $window));

            if ($count && (count($times) < $max || $max === PHP_INT_MAX)) {
                $times[] = $now;
            } elseif (count($times) >= $max) {
                $allowed = false;
            }

            if ($count) {
                ftruncate($fh, 0);
                rewind($fh);
                fwrite($fh, json_encode($times));
            }
        } finally {
            flock($fh, LOCK_UN);
            fclose($fh);
        }

        if (random_int(1, 50) === 1) {
            $this->purge();
        }

        return $allowed;
    }

    /** Löscht Dateien, die seit mehr als zwei Tagen nicht verändert wurden. */
    public function purge(int $olderThanSeconds = 172800): void
    {
        foreach (glob(rtrim($this->dir, '/\\') . DIRECTORY_SEPARATOR . '*.json') ?: [] as $f) {
            if (@filemtime($f) < time() - $olderThanSeconds) {
                @unlink($f);
            }
        }
    }
}

<?php
namespace FWHist\Services;

/**
 * 
 *    Methoden- bibliothek für allgemeine Funktionen (
 * Basis für allgmein benötigte Funktionen.
 * Benötigte PHP- Funktionen: GD, CURL, PDO
 * @author Josef Rohowsky Konvertierung von FuncsLib.php nach OOP Juni 2026
 *
 *    Enthält Methoden für die Auwahl von Namen und Begriffen,
 * 
 *  -HTML_header      - Ausgabe des Seiten- Headers, Laden der Seitenparameter als config_s_*.ini, wobei l für localhost und VFH für den Hist steht
 *  -HTML_trailer     - Ausgabe Seitenende
 *  -initial_debug    - Debug Anzeige mit Auswahl nach Parameter
 *  -table_exists     - Feststelle, welche Tabellen mit Prefix existiert
 *  -console_log      - Schreiben eines eintrags im Browser- concole.log 
 *  convertInternationalDateToSql Konvertiert Internationale Datenformate nach sql- Datums- Format Y-m-d
 *  writelog     - zum Schreiben von Eintragungen in LOG Files
 *  -logandMail   - zum Schreiben von Eintragungen in LOG Files und versenden einer Mail an die Admins
 *  
 *
 *
 */


class FuncsClass
{
    /**
     * Datumskonversion zum Format für DateTime in SQL
     * 
     * @param string $date
     * @return string|NULL
     */
    public static function convertInternationalDateToSql(string $date): ?string
    {
        $dt = \DateTime::createFromFormat('d.m.Y', $date);
        if ($dt === false) {
            return null;
        }
        return $dt->format('Y-m-d');
    }
    
    /**  
     * Schreiben einers Log- Eintrages in eine bestimmte Log- datei
     * 
     * @param string $message
     * @param string $file
     */
    public static function writelog(string $message, string $file = 'app.log'): void
    {
        $logEntry = date('Y-m-d H:i:s') . " - " . $message . PHP_EOL;
        file_put_contents(__DIR__ . '/../../logs/' . $file, $logEntry, FILE_APPEND);
    }
    
    // Weitere allgemeine Funktionen hier...
}
<?php
/**
 * Klasse zur Verwaltung von Tabellen-Spalten-Metadaten aus einer MySQL-Datenbank.
 *
 * Diese Klasse liest Spalteninformationen (Name, Kommentar, Typ, Nullable, Länge, Keys etc.)
 * aus der information_schema.COLUMNS-Tabelle und speichert sie in internen Arrays.
 *
 * Besonderheit:
 * - Bei Abfrage mehrerer Tabellen werden die Metadaten-Arrays kumulativ befüllt,
 *   sodass alle Spalten aller Tabellen in den Arrays enthalten sind.
 * - Die Methode getColumnsForTables liefert ein assoziatives Array mit Tabellenname => Spaltenliste.
 *
 * Nutzung:
 *   $pdo = new PDO(...);
 *   $meta = new BS_TableColumnMetadata($pdo, 'datenbankname', true);
 *   $columnsByTable = $meta->getColumnsForTables(['tabelle1', 'tabelle2']);
 *   $nullableMap = $meta->getNullableMap();
 *   // usw.
 */

namespace Fharch\Core\Services;

use PDO;

class TableColumnMetadata
{
    /** @var \PDO */
    private $connection;
    
    /** @var bool */
    private $debug = false;
    
    /** @var string */
    private $databaseName;
    
    /** @var array<string, string> Spalte => 'Y' wenn nullable, sonst 'N' */
    private $nullable = [];
    
    /** @var array<string, string> Spalte => Kommentar */
    private $comments = [];
    
    /** @var array<string, string> Spalte => CSS-Style */
    private $styles = [];
    
    /** @var array<string, string> Spalte => 'text' | 'num' | '' */
    private $types = [];
    
    /** @var array<string, string> Spalte => Tabellenname */
    private $tableNames = [];
    
    /** @var array<string, int> Spalte => Max-Length (falls varchar) */
    private $maxLengths = [];
    
    /** @var array<string, string> Spalte => Key-Typ (PRI, UNI, MUL, etc.) */
    private $keys = [];
    
    /** DSN des Log-Files */
    private static string $logFile = 'TableColumnMetadata_debug.log.txt';
    
    /**
     * Konstruktor
     *
     * @param \PDO    $connection   PDO-Verbindungsobjekt
     * @param string $databaseName Standard-Datenbankname
     * @param bool   $debug        Optionales Debug-Flag (default false)
     */
    public function __construct(\PDO $connection, string $databaseName, bool $debug = false)
    {
        $this->connection = $connection;
        $this->databaseName = $databaseName;
        $this->debug = $debug;
        
        if ($this->debug) {
            echo "TableColumnMetadata initialisiert.<br>";
            self::log(__LINE__ . " TableColumnMetadata initialisiert.");
        }
    }
    
    /**
     * Liest Spalteninformationen einer einzelnen Tabelle aus und ergänzt die Metadaten-Arrays.
     *
     * Wichtig: Die Metadaten-Arrays werden hier **nicht** zurückgesetzt, sondern erweitert.
     *
     * @param string      $tableName Tabellenname
     * @param string|null $database  Optional anderer Datenbankname
     * @return array                 Array mit Spaltennamen (in Reihenfolge)
     * @throws RuntimeException      Bei Fehlern in der DB-Abfrage
     */
    public function getTableColumns(string $tableName, ?string $database = null): array
    {
        $database = $database ?: $this->databaseName;
        
        if ($this->debug) {
            echo "Lese Spaltendefinitionen für Tabelle '$tableName' in DB '$database'<br>";
        }
        
        $sql = "SELECT COLUMN_NAME, COLUMN_COMMENT, COLUMN_TYPE, IS_NULLABLE, CHARACTER_MAXIMUM_LENGTH, COLUMN_KEY
                FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = :database AND TABLE_NAME = :tableName
                ORDER BY ORDINAL_POSITION";
        
        $stmt = $this->connection->prepare($sql);
        if (!$stmt) {
            $errorInfo = $this->connection->errorInfo();
            throw new RuntimeException("Prepare fehlgeschlagen: " . implode(" ", $errorInfo));
        }
        
        $stmt->bindValue(':database', $database, PDO::PARAM_STR);
        $stmt->bindValue(':tableName', $tableName, PDO::PARAM_STR);
        
        if (!$stmt->execute()) {
            $errorInfo = $stmt->errorInfo();
            throw new RuntimeException("Execute fehlgeschlagen: " . implode(" ", $errorInfo));
        }
        
        $columns = [];
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Spalten mit Kommentar, der mit '!' beginnt, überspringen
            if (isset($row['COLUMN_COMMENT']) && mb_substr($row['COLUMN_COMMENT'], 0, 1) === '!') {
                continue;
            }
            
            $column = $row['COLUMN_NAME'];
            $columns[] = $column;
            
            // Metadaten ergänzen
            $this->comments[$column] = $row['COLUMN_COMMENT'] ?? '';
            $this->maxLengths[$column] = isset($row['CHARACTER_MAXIMUM_LENGTH']) ? (int)$row['CHARACTER_MAXIMUM_LENGTH'] : 0;
            $this->nullable[$column] = ($row['IS_NULLABLE'] === 'YES') ? 'Y' : 'N';
            $this->tableNames[$column] = $tableName;
            $this->keys[$column] = $row['COLUMN_KEY'] ?? '';
            
            $type = strtolower($row['COLUMN_TYPE'] ?? '');
            if (strpos($type, 'int(') !== false) {
                $this->styles[$column] = 'text-align:right;padding-right:3px;';
                $this->types[$column] = 'num';
            } elseif (strpos($type, 'char(') === false && $type !== 'text') {
                $this->styles[$column] = 'text-align:center;';
                $this->types[$column] = '';
            } else {
                $this->types[$column] = 'text';
            }
        }
        
        return $columns;
    }
    
    /**
     * Liest Spalteninformationen für mehrere Tabellen aus.
     *
     * Vor der Abfrage werden die Metadaten-Arrays geleert, um eine frische kumulative Befüllung zu gewährleisten.
     *
     * @param array       $tableNames Array von Tabellennamen
     * @param string|null $database   Optionaler Datenbankname
     * @return array                 Assoziatives Array: [Tabellenname => [Spaltennamen]]
     */
    public function getColumnsForTables(array $tableNames, ?string $database = null): array
    {
        $database = $database ?: $this->databaseName;
        
        // Metadaten-Arrays vor der Gesamtabfrage leeren
        $this->comments = [];
        $this->maxLengths = [];
        $this->nullable = [];
        $this->styles = [];
        $this->types = [];
        $this->keys = [];
        $this->tableNames = [];
        
        $allColumns = [];
        
        foreach ($tableNames as $tableName) {
            $columns = $this->getTableColumns($tableName, $database);
            $allColumns[$tableName] = $columns;
        }
        
        return $allColumns;
    }
    
    /**
     * Getter für nullable Map (Spalte => 'Y' oder 'N')
     *
     * @return array<string, string>
     */
    public function getNullableMap(): array
    {
        return $this->nullable;
    }
    
    /**
     * Getter für Kommentare (Spalte => Kommentar)
     *
     * @return array<string, string>
     */
    public function getCommentsMap(): array
    {
        return $this->comments;
    }
    
    /**
     * Getter für CSS-Styles (Spalte => CSS-String)
     *
     * @return array<string, string>
     */
    public function getStylesMap(): array
    {
        return $this->styles;
    }
    
    /**
     * Getter für Typen (Spalte => 'text' | 'num' | '')
     *
     * @return array<string, string>
     */
    public function getTypesMap(): array
    {
        return $this->types;
    }
    
    /**
     * Getter für Tabellenzuordnung (Spalte => Tabellenname)
     *
     * @return array<string, string>
     */
    public function getTableNamesMap(): array
    {
        return $this->tableNames;
    }
    
    /**
     * Getter für maximale Längen (Spalte => int)
     *
     * @return array<string, int>
     */
    public function getMaxLengthsMap(): array
    {
        return $this->maxLengths;
    }
    
    /**
     * Getter für Keys (Spalte => Key-Typ)
     *
     * @return array<string, string>
     */
    public function getKeysMap(): array
    {
        return $this->keys;
    }
    
    /**
     * Schreibt eine Debug-Nachricht in die Logdatei
     *
     * @param string $message
     * @return void
     */
    protected static function log(string $message): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $entry = "[$timestamp] $message" . PHP_EOL;
        file_put_contents(self::$logFile, $entry, FILE_APPEND);
    }
}

/**
Erläuterungen
Die Klasse erwartet ein PDO-Objekt als DB-Verbindung.
Die Methode getTableColumns liest die Spalteninfos einer einzelnen Tabelle und ergänzt die Metadaten-Arrays.
Die Methode getColumnsForTables leert vor der Abfrage alle Metadaten-Arrays und ruft dann für jede Tabelle getTableColumns auf, um alle Metadaten kumulativ zu sammeln.
Die Getter-Methoden liefern die Metadaten-Arrays zurück.
Debug-Ausgaben und Logging sind optional über das $debug-Flag steuerbar.
SQL-Abfrage nutzt benannte Parameter mit PDO.
Die Spalten werden nach ORDINAL_POSITION sortiert, um die Reihenfolge der Spalten in der Tabelle zu erhalten.
Spalten mit Kommentar, der mit ! beginnt, werden übersprungen (wie im Original).
Wenn du möchtest, kann ich dir auch Beispielcode zeigen, wie du die Klasse initialisierst und nutzt. Möchtest du das?

Beispielcode zur Nutzung der Klasse
<?php
// Beispiel: PDO-Verbindung aufbauen
$host = 'localhost';
$dbname = 'deine_datenbank';
$user = 'dein_benutzer';
$pass = 'dein_passwort';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Fehler als Exceptions
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Fetch als assoziatives Array
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Native prepares nutzen
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("Verbindung fehlgeschlagen: " . $e->getMessage());
}

// Klasse einbinden (wenn in separater Datei)
// require_once 'BS_TableColumnMetadata.php';

// Instanz der Klasse erzeugen, Debug auf true für Ausgaben
$meta = new BS_TableColumnMetadata($pdo, $dbname, true);

// Tabellen, deren Spalten-Metadaten abgefragt werden sollen
$tables = ['table_1', 'table_2'];

// Metadaten für mehrere Tabellen abfragen
$columnsByTable = $meta->getColumnsForTables($tables);

// Ausgabe der Spalten je Tabelle
echo "<h2>Spalten je Tabelle:</h2>";
foreach ($columnsByTable as $table => $columns) {
    echo "<strong>Tabelle: $table</strong><br>";
    echo implode(', ', $columns) . "<br><br>";
}

// Zugriff auf Metadaten-Arrays
$nullableMap = $meta->getNullableMap();
$commentsMap = $meta->getCommentsMap();
$stylesMap = $meta->getStylesMap();
$typesMap = $meta->getTypesMap();
$tableNamesMap = $meta->getTableNamesMap();
$maxLengthsMap = $meta->getMaxLengthsMap();
$keysMap = $meta->getKeysMap();

// Beispiel: Ausgabe einiger Metadaten für alle Spalten
echo "<h2>Metadaten aller Spalten:</h2>";
echo "<table border='1' cellpadding='5' cellspacing='0'>";
echo "<tr><th>Spalte</th><th>Tabelle</th><th>Nullable</th><th>Kommentar</th><th>Typ</th><th>Max Länge</th><th>Key</th><th>Style</th></tr>";

foreach ($tableNamesMap as $column => $tableName) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($column) . "</td>";
    echo "<td>" . htmlspecialchars($tableName) . "</td>";
    echo "<td>" . htmlspecialchars($nullableMap[$column] ?? '') . "</td>";
    echo "<td>" . htmlspecialchars($commentsMap[$column] ?? '') . "</td>";
    echo "<td>" . htmlspecialchars($typesMap[$column] ?? '') . "</td>";
    echo "<td>" . htmlspecialchars($maxLengthsMap[$column] ?? '') . "</td>";
    echo "<td>" . htmlspecialchars($keysMap[$column] ?? '') . "</td>";
    echo "<td style='" . ($stylesMap[$column] ?? '') . "'>Beispiel</td>";
    echo "</tr>";
}

echo "</table>";
Erklärung
Zuerst wird eine PDO-Verbindung zur MySQL-Datenbank aufgebaut.
Dann wird die Klasse BS_TableColumnMetadata mit der PDO-Verbindung, dem Datenbanknamen und optional Debug-Flag instanziert.
Mit getColumnsForTables werden die Spalten aller gewünschten Tabellen abgefragt.
Die Metadaten-Arrays können über die Getter-Methoden abgefragt werden.
Im Beispiel wird eine HTML-Tabelle mit den wichtigsten Metadaten aller Spalten ausgegeben.
*/

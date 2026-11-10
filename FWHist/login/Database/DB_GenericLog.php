<?php

declare(strict_types=1);

/**
 * Paket: Prefix-freier, generischer DB-Layer + ausgelagerte Whitelist + Table-Gateways (Beispiele)
 * --------------------------------------------------------------------------------------------
 * Ziel:
 *  - KEINE Prefix-Logik mehr in FS_Database
 *  - FS_Database kann ALLE Tabellen nutzen (generisch)
 *  - Logging/Debug/Query-Logging/Maskierung bleiben in FS_Database
 *  - Whitelist ausgelagert (einfach wartbar)
 *  - Neue Klassen (Gateways/Repos) rufen nur FS_Database-Methoden auf
 *
 * Strukturvorschlag:
 *  /common/lib/
 *    Basis/FS_Database.php
 *    Config/FS_AllowedTables.php
 *    Tables/FS_TableGateway.php
 *    Tables/BenutzerTable.php         (Beispiel)
 *    Tables/MandantTable.php          (Beispiel)
 *
 * Hinweis:
 *  - Tabellen-/Spaltennamen können nicht gebunden werden -> immer Identifier validieren/quoten.
 *  - Whitelist-Prüfung ist optional pro Gateway; bei externen Tabellennamen unbedingt aktivieren.
 */

namespace FWHist\Database;

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../../login/AOrd_Verz/Logging/DB_GenLog_reposi_php-error.log.txt');

$srv = $_SERVER['HTTP_HOST'] ?? '';
$SI = $srv === 'localhost' ? 'l' : 'fwhist';

require_once __DIR__ . "/../../login/config/ConfigLib_d_$SI.php";

/**
 * DB_GenericLog (prefix-frei)
 * - PDO Wrapper + sichere Query-Helper
 * - Logging (Levels, Context, Request-ID)
 * - Query-Logging (optional, mit Maskierung sensibler Parameter)
 * - Transaction Helpers (BEGIN/COMMIT/ROLLBACK) + Logging
 */
final class DB_GenericLog
{
    private \PDO $pdo;
    private static ?self $instance = null;
    
    // ---------------------------------------------------------------------
    // Logging
    // ---------------------------------------------------------------------
    public const LOG_DEBUG = 100;
    public const LOG_INFO  = 200;
    public const LOG_WARN  = 300;
    public const LOG_ERROR = 400;
    
    private bool $logEnabled = true;
    private int $logLevel = self::LOG_INFO;
    
    private string $logFile;
    
    private bool $logQueries = true;
    private bool $maskSensitive = true;
    
    /** Keys, die standardmäßig maskiert werden (Case-insensitive contains) */
    private array $sensitiveKeyFragments = [
        'pass', 'pwd', 'password',
        'token', 'secret', 'api_key', 'apikey',
        'session', 'cookie',
        'auth', 'bearer',
        'salt',
    ];
    
    private string $requestId;
    private int $maxLogStringLen = 4000;
    
    public function __construct()
    {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        
        $this->pdo = new \PDO($dsn, DB_USER, DB_PASS, $options);
        
        // Default-Logfile: /login/common/logs/fs_database.log.txt (wie in deiner Klasse angedeutet)
        /*
        $baseDir = rtrim(
            (string)($_SERVER['DOCUMENT_ROOT'] ?? '') . DIRECTORY_SEPARATOR . "FWHist"  . DIRECTORY_SEPARATOR . "login" . DIRECTORY_SEPARATOR .  "AOrd_Verz" . DIRECTORY_SEPARATOR . 'Logging',
            DIRECTORY_SEPARATOR
            );
         */
        $baseDir = __DIR__ . '/../../login/AOrd_Verz/Logging/';
        // robust: falls $baseDir leer ist (CLI), ins aktuelle Verzeichnis loggen
        if ($baseDir === '' || !is_dir($baseDir)) {
            $baseDir = __DIR__;
        }
        
        $this->logFile = $baseDir . DIRECTORY_SEPARATOR . 'DB_GenericLog.log.txt';
        $this->requestId = $this->generateRequestId();
        
        $this->log(self::LOG_INFO, 'DB_GenericLog initialized', [
            'dsn' => $this->safeDsnForLog($dsn),
        ]);
    }
    
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    // ---------------------- Public Logger API ----------------------------
    
    public function setLoggingEnabled(bool $enabled): void
    {
        $this->logEnabled = $enabled;
    }
    
    public function setLogLevel(int $level): void
    {
        $this->logLevel = $level;
    }
    
    public function setLogFile(string $path): void
    {
        $this->logFile = $path;
    }
    
    public function setLogQueries(bool $enabled): void
    {
        $this->logQueries = $enabled;
    }
    
    public function setMaskSensitive(bool $enabled): void
    {
        $this->maskSensitive = $enabled;
    }
    
    public function setRequestId(string $requestId): void
    {
        $this->requestId = $requestId;
    }
    
    public function getPDO(): \PDO
    {
        return $this->pdo;
    }
    
    // ---------------------------------------------------------------------
    // Identifier-Sicherheit
    // ---------------------------------------------------------------------
    
    /** Identifier validieren (Spaltenname/Tabellenname) */
    public function validateIdentifier(string $name): void
    {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $name)) {
            $this->log(self::LOG_ERROR, 'Invalid identifier', ['identifier' => $name]);
            throw new \InvalidArgumentException("Ungültiger Identifier: $name");
        }
    }
    
    /** Identifier als `name` quoten */
    public function q(string $identifier): string
    {
        if ($identifier === '*') {
            # error_log("q: identifier is '*', returning without backticks");
            return '*'; // kein Backtick um '*'
        }
        $this->validateIdentifier($identifier);
        return "`{$identifier}`";
    }
    
    /** Spaltenliste validieren und quoten */
    public function quoteColumns(array $columns): array
    {
        $out = [];
        foreach ($columns as $col) {
            $out[] = $this->q((string)$col);
        }
        return $out;
    }
    
    // ---------------------------------------------------------------------
    // Query Helpers (Logging integriert)
    // ---------------------------------------------------------------------
    
    public function query(string $sql, array $params = []): \PDOStatement
    {
        $t0 = microtime(true);
        
        if ($this->logQueries) {
            $this->log(self::LOG_DEBUG, 'SQL prepare', [
                'sql'    => $this->trimForLog($sql),
                'params' => $this->sanitizeParamsForLog($params),
            ]);
        }
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            if ($this->logQueries) {
                $this->log(self::LOG_DEBUG, 'SQL executed', [
                    'duration_ms' => round((microtime(true) - $t0) * 1000, 2),
                    'rowCount'    => $stmt->rowCount(),
                ]);
            }
            
            return $stmt;
        } catch (\PDOException $e) {
            $this->log(self::LOG_ERROR, 'SQL error', [
                'message' => $e->getMessage(),
                'sql'     => $this->trimForLog($sql),
                'params'  => $this->sanitizeParamsForLog($params),
            ]);
            throw $e;
        }
    }
    
    public function fetchOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->query($sql, $params);
        $row = $stmt->fetch();
        return $row !== false ? $row : null;
    }
    
    public function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    public function exec(string $sql, array $params = []): int
    {
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }
    
    // ---------------------------------------------------------------------
    // CRUD Helpers (generisch, ohne Whitelist; Whitelist ist Aufgabe der Gateways)
    // ---------------------------------------------------------------------
    
    public function insert(string $table, array $data, string $prefix = '', bool $autoTimestamp = true): int
    {
        $this->validateIdentifier($table);
        if ($data === []) {
            throw new \InvalidArgumentException('Insert data darf nicht leer sein.');
        }
        if ($autoTimestamp) {
            $this->addTimestamps($data, $prefix);
        }
        
        $cols = array_keys($data);
        foreach ($cols as $c) $this->validateIdentifier((string)$c);
        
        $colsQuoted = array_map(fn($c) => $this->q((string)$c), $cols);
        $phs = array_map(fn($c) => ':' . (string)$c, $cols);
        
        $sql = "INSERT INTO {$this->q($table)} (" . implode(', ', $colsQuoted) . ") VALUES (" . implode(', ', $phs) . ")";
        $this->query($sql, $data);
        
        return (int)$this->pdo->lastInsertId();
    }
    
    public function update(
        string $table,
        array $data,
        array $where,
        string $prefix = '',
        bool $autoTimestamp = true
    ): int
    {
        $this->validateIdentifier($table);
        if ($data === []) {
            throw new \InvalidArgumentException('Update data darf nicht leer sein.');
        }
        if ($where === []) {
            throw new \InvalidArgumentException('Update WHERE darf nicht leer sein (Schutz vor Full-Table-Update).');
        }
        if ($autoTimestamp) {
            $this->addTimestamps($data, $prefix);
        }
        
        $setParts = [];
        $params = [];
        
        foreach ($data as $col => $val) {
            $this->validateIdentifier((string)$col);
            $ph = ':s_' . (string)$col;
            $setParts[] = $this->q((string)$col) . " = {$ph}";
            $params[$ph] = $val;
        }
        
        $whereSql = $this->buildWhereEq($where, $params);
        
        $sql = "UPDATE {$this->q($table)} SET " . implode(', ', $setParts) . $whereSql;
        # $this->log(self::LOG_INFO, "update sql: $sql");
        return $this->exec($sql, $params);
    }
    
    public function delete(string $table, array $where): int
    {
        $this->validateIdentifier($table);
        if ($where === []) {
            throw new \InvalidArgumentException('Delete WHERE darf nicht leer sein (Schutz vor Full-Table-Delete).');
        }
        
        $params = [];
        $whereSql = $this->buildWhereEq($where, $params);
        
        $sql = "DELETE FROM {$this->q($table)}" . $whereSql;
        return $this->exec($sql, $params);
    }
    
    public function select(
        string $table,
        array $columns = ['*'],
        array $where = [],
        array $orderBy = [],
        ?int $limit = null,
        ?int $offset = null
        ): array {
            $this->validateIdentifier($table);
            
            $params = [];
            $colsSql = $this->buildSelectColumns($columns);
            # error_log("buildSelectColumns returned: $colsSql");
            
            $whereSql = $this->buildWhereEq($where, $params);
            $orderSql = $this->buildOrderBy($orderBy);
            $limitSql = $this->buildLimitOffset($limit, $offset, $params);
            
            $sql = "SELECT {$colsSql} FROM {$this->q($table)}{$whereSql}{$orderSql}{$limitSql}";
            /*
            error_log("buildSelectColumns returned: $colsSql");
            // Debug-Ausgabe
            error_log("Aufruf : " . $table);
            error_log("SQL Query: " . $sql);
            error_log("Parameters: " . json_encode($params));
            */
            return $this->fetchAll($sql, $params);
    }
    
    // ---------------------------------------------------------------------
    // Transactions
    // ---------------------------------------------------------------------
    
    public function beginTransaction(): void
    {
        $this->log(self::LOG_INFO, 'TX BEGIN');
        $this->pdo->beginTransaction();
    }
    
    public function commit(): void
    {
        $this->pdo->commit();
        $this->log(self::LOG_INFO, 'TX COMMIT');
    }
    
    public function rollBack(): void
    {
        $this->pdo->rollBack();
        $this->log(self::LOG_WARN, 'TX ROLLBACK');
    }

    public function getLastInsertId(): int
    {
        return (int)$this->pdo->lastInsertId();
    }

    /** Setzt Änderungsfelder nur, wenn sie im Datenarray bereits vorgesehen sind. */
    private function addTimestamps(array &$data, string $prefix = ''): void
    {
        $changedAt = $prefix . 'changed_at';
        $changedId = $prefix . 'changed_id';

        if (array_key_exists($changedAt, $data)) {
            $data[$changedAt] = date('Y-m-d H:i:s');
        }
        if (array_key_exists($changedId, $data)) {
            $data[$changedId] = $_SESSION['BS_Prim']['BE']['be_id']
                ?? $_SESSION['BS_Prim']['be_id']
                ?? 0;
        }
    }

    /** Führt semikolongetrennte CREATE-/ALTER-Statements für ein Modul aus. */
    public function createTablesFromSql(string $sqlScript, string|int $mandantennummer, bool $throwException = false): bool|array
    {
        $mandant = (string)$mandantennummer;
        if (!preg_match('/^[A-Za-z0-9_]+$/', $mandant)) {
            throw new \InvalidArgumentException('Ungültige Mandantennummer.');
        }

        $sqlScript = str_replace('{MandNr}', $mandant, $sqlScript);
        $statements = array_filter(array_map('trim', explode(';', $sqlScript)));
        $errors = [];

        foreach ($statements as $statement) {
            try {
                $this->query($statement);
            } catch (\PDOException $e) {
                $error = 'Error executing statement: ' . $e->getMessage() . ' | SQL: ' . $statement;
                $errors[] = $error;
                $this->log(self::LOG_ERROR, 'Module table setup failed', ['message' => $error]);
                if ($throwException) {
                    throw new \RuntimeException($error, 0, $e);
                }
            }
        }

        return $errors === [] ? true : $errors;
    }

    public function setupModuleTables(
        string $moduleName,
        string $sqlDefinition,
        string|int $mandantennummer,
        bool $throwException = false
    ): bool|array {
        $this->log(self::LOG_INFO, 'Setting up module tables', ['module' => $moduleName]);
        return $this->createTablesFromSql($sqlDefinition, $mandantennummer, $throwException);
    }
    
    // ---------------------------------------------------------------------
    // Builder intern
    // ---------------------------------------------------------------------
    
    private function buildWhereEq(array $where, array &$params): string
    {
        if ($where === []) return '';
        
        $parts = [];
        foreach ($where as $col => $val) {
            $this->validateIdentifier((string)$col);
            $ph = ':w_' . (string)$col;
            $parts[] = $this->q((string)$col) . " = {$ph}";
            $params[$ph] = $val;
        }
        return ' WHERE ' . implode(' AND ', $parts);
    }
    
    private function buildOrderBy(array $orderBy): string
    {
        if ($orderBy === []) return '';
        
        $parts = [];
        foreach ($orderBy as $col => $dir) {
            $this->validateIdentifier((string)$col);
            $dirU = strtoupper((string)$dir);
            if (!in_array($dirU, ['ASC', 'DESC'], true)) {
                $this->log(self::LOG_ERROR, 'Invalid ORDER direction', ['col' => (string)$col, 'dir' => (string)$dir]);
                throw new \InvalidArgumentException("Ungültige ORDER Richtung: $dir");
            }
            $parts[] = $this->q((string)$col) . " {$dirU}";
        }
        return ' ORDER BY ' . implode(', ', $parts);
    }
    
    private function buildLimitOffset(?int $limit, ?int $offset, array &$params): string
    {
        $sql = '';
        if ($limit !== null) {
            if ($limit < 0) throw new \InvalidArgumentException('LIMIT muss >= 0 sein.');
            $sql .= ' LIMIT :_limit';
            $params[':_limit'] = $limit;
        }
        if ($offset !== null) {
            if ($offset < 0) throw new \InvalidArgumentException('OFFSET muss >= 0 sein.');
            if ($limit === null) {
                // MySQL braucht LIMIT wenn OFFSET verwendet wird -> setze sehr großes LIMIT
                $sql .= ' LIMIT 18446744073709551615';
            }
            $sql .= ' OFFSET :_offset';
            $params[':_offset'] = $offset;
        }
        return $sql;
    }
    
    private function buildSelectColumns(array $columns): string
    {
        # if ($columns === ['*'] || $columns === []) return '*';
        if (count($columns) === 1 && $columns[0] === '*') {
            return '*'; // kein Backtick um '*'
        }
        $out = [];
        foreach ($columns as $col) {
            $col = (string)$col;
  
            $out[] = $this->q($col);
        }
        return implode(', ', $out);
    }
    
    // ---------------------------------------------------------------------
    // Logging intern
    // ---------------------------------------------------------------------
    
    private function log(int $level, string $message, array $context = []): void
    {
        if (!$this->logEnabled || $level < $this->logLevel) return;
        
        $levelName = match ($level) {
            self::LOG_DEBUG => 'DEBUG',
            self::LOG_INFO  => 'INFO',
            self::LOG_WARN  => 'WARN',
            self::LOG_ERROR => 'ERROR',
            default => 'LOG',
        };
        
        $time = date('Y-m-d H:i:s');
        $ctx  = $context ? json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '';
        $line = "[{$time}] [{$levelName}] [{$this->requestId}] {$message}" . ($ctx ? " {$ctx}" : "") . "\n";
        
        try {
            error_log($line, 3, $this->logFile);
        } catch (\Throwable $e) {
            // Fallback: wenn Datei nicht beschreibbar ist, wenigstens in PHP error_log
            error_log($line);
        }
    }
    
    private function sanitizeParamsForLog(array $params): array
    {
        if (!$this->maskSensitive) return $params;
        
        $masked = [];
        foreach ($params as $key => $value) {
            $k = is_string($key) ? $key : (string)$key;
            $lowerKey = strtolower($k);
            
            $mask = false;
            foreach ($this->sensitiveKeyFragments as $frag) {
                if (strpos($lowerKey, $frag) !== false) {
                    $mask = true;
                    break;
                }
            }
            
            if ($mask) {
                $masked[$k] = '***MASKED***';
                continue;
            }
            
            // Kürzen großer Strings im Log
            if (is_string($value) && mb_strlen($value) > 300) {
                $masked[$k] = mb_substr($value, 0, 300) . '...';
            } else {
                $masked[$k] = $value;
            }
        }
        return $masked;
    }
    
    private function trimForLog(string $str): string
    {
        if (mb_strlen($str) > $this->maxLogStringLen) {
            return mb_substr($str, 0, $this->maxLogStringLen) . '...';
        }
        return $str;
    }
    
    private function generateRequestId(): string
    {
        try {
            return bin2hex(random_bytes(8));
        } catch (\Throwable $e) {
            return dechex((int)(microtime(true) * 1000000)) . '-' . dechex(random_int(0, 0xffff));
        }
    }
    
    private function safeDsnForLog(string $dsn): string
    {
        // Entfernt potenziell sensible Teile aus DSN (meist sind da keine Passwörter drin)
        return preg_replace('/(password|passwd)=([^;]+)/i', '$1=***', $dsn) ?? $dsn;
    }
}



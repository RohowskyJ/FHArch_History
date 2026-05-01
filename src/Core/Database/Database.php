<?php
declare(strict_types=1);

namespace Fharch\Core\Database;

use PDO;
use PDOException;
use PDOStatement;
use RuntimeException;
use InvalidArgumentException;

/**
 * Optionaler Logger-Hook, damit Database nicht direkt von /core/Logging abhängt.
 * Ihr könnt hier eure Logger-Implementierung adaptieren (PSR-3 oder eigenes).
 */
/*
interface QueryLoggerInterface
{
    /**
     * @param array<string, mixed> $context
     * /
    public function log(string $sql, array $params = [], array $context = []): void;
}
*/
/**
 * Generische Database-Klasse für modulübergreifende Nutzung.
 * Enthält:
 * - query(), fetchOne(), fetchAll(), fetchValue()
 * - insert(), update(), delete() (CRUD-Helpers)
 * - Transaktionen via transactional()
 *
 */
final class Database
{
    private PDO $pdo;
    private ?QueryLoggerInterface $logger;
    
    public function __construct(PDO $pdo, ?QueryLoggerInterface $logger = null)
    {
        $this->pdo = $pdo;
        $this->logger = $logger;
    }
    
    public function pdo(): PDO
    {
        return $this->pdo;
    }
    
    /**
     * @param array<string, mixed> $params
     */
    public function query(string $sql, array $params = []): PDOStatement
    {
        $this->logger?->log($sql, $params, ['type' => 'query']);
        
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $value) {
            // Named params: ':id' oder 'id' akzeptieren
            $param = is_string($key) && $key !== '' && $key[0] !== ':' ? ':' . $key : $key;
            
            // PDO kann Typen meist selbst, aber NULL explizit:
            if ($value === null) {
                $stmt->bindValue($param, null, PDO::PARAM_NULL);
            } else {
                $stmt->bindValue($param, $value);
            }
        }
        $stmt->execute();
        
        return $stmt;
    }
    
    /**
     * @return array<string, mixed>|null
     * @param array<string, mixed> $params
     */
    public function fetchOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->query($sql, $params);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }
    
    /**
     * @return array<int, array<string, mixed>>
     * @param array<string, mixed> $params
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }
    
    /**
     * @param array<string, mixed> $params
     */
    public function fetchValue(string $sql, array $params = []): mixed
    {
        $stmt = $this->query($sql, $params);
        $val = $stmt->fetchColumn();
        return $val === false ? null : $val;
    }
    
    /**
     * INSERT helper.
     *
     * @param array<string, mixed> $data
     * @return string lastInsertId (DB-abhängig; bei UUID ggf. nicht nutzen)
     */
    public function insert(string $table, array $data): string
    {
        $table = self::assertIdentifier($table);
        
        if ($data === []) {
            throw new InvalidArgumentException('Insert data cannot be empty.');
        }
        
        $columns = array_keys($data);
        foreach ($columns as $col) {
            self::assertIdentifier((string)$col);
        }
        
        $colList = implode(', ', array_map(fn($c) => "`$c`", $columns));
        $paramList = implode(', ', array_map(fn($c) => ':' . $c, $columns));
        
        $sql = "INSERT INTO `$table` ($colList) VALUES ($paramList)";
        $this->query($sql, $data);
        
        return $this->pdo->lastInsertId();
    }
    
    /**
     * UPDATE helper.
     *
     * @param array<string, mixed> $data
     * @param array<string, mixed> $where Named params für WHERE
     * @return int affected rows
     */
    public function update(string $table, array $data, string $whereSql, array $where = []): int
    {
        $table = self::assertIdentifier($table);
        
        if ($data === []) {
            throw new InvalidArgumentException('Update data cannot be empty.');
        }
        if (trim($whereSql) === '') {
            throw new InvalidArgumentException('Update requires a WHERE clause.');
        }
        
        $setParts = [];
        foreach ($data as $col => $_) {
            self::assertIdentifier((string)$col);
            $setParts[] = "`$col` = :set_$col";
        }
        
        $params = [];
        foreach ($data as $col => $val) {
            $params["set_$col"] = $val;
        }
        // where params dürfen nicht mit set_ kollidieren
        foreach ($where as $k => $v) {
            $params[$k] = $v;
        }
        
        $sql = "UPDATE `$table` SET " . implode(', ', $setParts) . " WHERE $whereSql";
        $stmt = $this->query($sql, $params);
        
        return $stmt->rowCount();
    }
    
    /**
     * DELETE helper.
     *
     * @param array<string, mixed> $params
     * @return int affected rows
     */
    public function delete(string $table, string $whereSql, array $params = []): int
    {
        $table = self::assertIdentifier($table);
        
        if (trim($whereSql) === '') {
            throw new InvalidArgumentException('Delete requires a WHERE clause.');
        }
        
        $sql = "DELETE FROM `$table` WHERE $whereSql";
        $stmt = $this->query($sql, $params);
        
        return $stmt->rowCount();
    }
    
    /**
     * Transaktion-Wrapper.
     * Rollback bei Exception, ansonsten Commit.
     *
     * @template T
     * @param callable(self):T $fn
     * @return string
     */
    public function transactional(callable $fn): mixed
    {
        $this->logger?->log('BEGIN', [], ['type' => 'tx']);
        $this->pdo->beginTransaction();
        
        try {
            $result = $fn($this);
            $this->pdo->commit();
            $this->logger?->log('COMMIT', [], ['type' => 'tx']);
            return $result;
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
                $this->logger?->log('ROLLBACK', [], ['type' => 'tx']);
            }
            throw $e;
        }
    }
    
    /**
     * Sicherheits-Helper: validiert Tabellen-/Spaltennamen, um SQL-Injection über Identifier zu verhindern.
     * Für Werte IMMER prepared statements nutzen (machen wir oben).
     */
    private static function assertIdentifier(string $identifier): string
    {
        // Erlaubt: a-z A-Z 0-9 _  (kein Punkt, kein Backtick, kein Leerzeichen)
        if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $identifier)) {
            throw new InvalidArgumentException("Invalid SQL identifier: {$identifier}");
        }
        return $identifier;
    }
}


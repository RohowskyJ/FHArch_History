<?php
/** 
 * Klasse für Autocomplete Handling
 */
declare(strict_types=1);

namespace Fharch\Core\Services\API;

##require_once '../config/FS_ConfigLib.php';

use Fharch\Core\Database\DB_GenericLog;
use Fharch\Core\Services\Logger;

abstract class AutocompleteAPI {
    protected string $table;
    protected array $searchFields;
    protected string $idField;
    protected array $displayFields;
    protected ?string $additionalCondition = null;
    protected bool $debug = false;
    protected array $debugInfo = [];
    
    public function __construct(string $table, array $searchFields, string $idField, array $displayFields, ?string $additionalCondition = null) {
        $this->table = $table;
        $this->searchFields = $searchFields;
        $this->idField = $idField;
        $this->displayFields = $displayFields;
        $this->additionalCondition = $additionalCondition;
        $this->debug = $this->isDebug();
    }
    
    protected function isDebug(): bool {
        $debugValue = $_GET['debug'] ?? null;
        return $debugValue !== null && in_array(strtolower((string)$debugValue), ['1', 'true', 'on', 'yes'], true);
    }
    
    protected function addDebug(string $key, mixed $value): void {
        if ($this->debug) {
            $this->debugInfo[$key] = $value;
        }
    }
    
    protected function renderDebugResponse(array $results): array {
        if (!$this->debug) {
            return $results;
        }
        return [
            'debug' => $this->debugInfo,
            'results' => $results,
        ];
    }
    
    public function handleRequest(): void {
        header('Content-Type: application/json; charset=utf-8');
        
        $term = $_GET['term'] ?? '';
        $term = trim($term);
        $this->addDebug('term', $term);
        $this->addDebug('table', $this->table);
        $this->addDebug('searchFields', $this->searchFields);
        $this->addDebug('additionalCondition', $this->additionalCondition);
        
        if ($term === '') {
            echo json_encode($this->renderDebugResponse([]));
            exit;
        }
        
        try {
            $db = DB_GenericLog::getInstance();
            $pdo = $db->getPDO();
            
            // Teste die Datenbankverbindung
            $pdo->query('SELECT 1');
            
            $results = $this->queryDatabase($pdo, $term);
            $this->addDebug('resultCount', count($results));
            $this->addDebug('results', $results);
            
            echo json_encode($this->renderDebugResponse($results));
        } catch (Exception $e) {
            $this->addDebug('error', $e->getMessage());
            $this->addDebug('exceptionTrace', $e->getTraceAsString());
            Logger::logError('AutocompleteAPI Error: ' . $e->getMessage());
            
            if ($this->debug) {
                http_response_code(500);
                echo json_encode($this->renderDebugResponse([]));
                return;
            }
            /*
            // Fallback für Testzwecke - Dummy-Daten zurückgeben
            $dummyResults = [
                ['id' => 1, 'label' => 'Deutschland (DE)', 'value' => 'Deutschland'],
                ['id' => 2, 'label' => 'Österreich (AT)', 'value' => 'Österreich'],
                ['id' => 3, 'label' => 'Schweiz (CH)', 'value' => 'Schweiz'],
                ['id' => 4, 'label' => 'Deutschland (GER)', 'value' => 'Deutschland'],
                ['id' => 5, 'label' => 'Germany (GER)', 'value' => 'Germany'],
            ];
            */
            echo json_encode($this->renderDebugResponse(array_filter($dummyResults, function($item) use ($term) {
                return stripos($item['label'], $term) !== false;
            })));
        }
    }
    
    protected function queryDatabase(\PDO $pdo, string $term): array {
        $whereClauses = [];
        $params = [];
        
        foreach ($this->searchFields as $index => $field) {
            $paramName = ':term' . $index;
            $whereClauses[] = "$field LIKE $paramName";
            $params[$paramName] = $term . '%';
        }
        
        $whereSql = implode(' OR ', $whereClauses);
        if ($this->additionalCondition) {
            $whereSql .= ' AND ' . $this->additionalCondition;
        }
        
        $selectFields = array_merge([$this->idField], $this->displayFields);
        $sql = "SELECT " . implode(', ', $selectFields) . " FROM {$this->table} WHERE $whereSql ORDER BY {$this->displayFields[0]} ASC LIMIT 10";
        $this->addDebug('sql', $sql);
        $this->addDebug('params', $params);
        
        $stmt = $pdo->prepare($sql);
        foreach ($params as $param => $value) {
            $stmt->bindValue($param, $value, \PDO::PARAM_STR);
        }
        $stmt->execute();
        
        $results = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $label = $row[$this->displayFields[0]];
            if (count($this->displayFields) > 1) {
                $label .= ' (' . implode(', ', array_map(fn($f) => $row[$f], array_slice($this->displayFields, 1))) . ')';
            }
            $results[] = [
                'id' => $row[$this->idField],
                'label' => $label,
                'value' => $row[$this->displayFields[0]]
            ];
        }
        
        return $results;
    }
}

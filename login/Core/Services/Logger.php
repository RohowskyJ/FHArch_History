<?php
declare(strict_types=1);

namespace Fharch\Core\Services;

use DateTimeImmutable;
use DateTimeInterface;
use Throwable;

final class Logger
{
    private string $filePath;
    private bool $includeTrace;
    
    public function __construct(string $filePath, bool $includeTrace = false)
    {
        $this->filePath = $filePath;
        $this->includeTrace = $includeTrace;
    }
    
    public function error(string $message, array $context = []): void
    {
        $this->write('ERROR', $message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->write('WARNING', $message, $context);
    }

    public function info(string $message, array $context = []): void
    {
        $this->write('INFO', $message, $context);
    }
    
    private function write(string $level, string $message, array $context): void
    {
        $entry = [
            'timestamp' => (new DateTimeImmutable())->format(DateTimeInterface::ATOM),
            'level' => $level,
            'message' => $message,
            'context' => $this->sanitizeContext($context),
        ];
        
        $dir = dirname($this->filePath);
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        
        // JSON-Zeile anhängen
        @file_put_contents($this->filePath, json_encode($entry, JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
    
    private function sanitizeContext(array $context): array
    {
        // Sensible Keys maskieren
        $sensitiveKeys = ['pass', 'password', 'pwd', 'token', 'secret', 'authorization'];
        
        $clean = [];
        foreach ($context as $key => $value) {
            $keyLower = strtolower((string)$key);
            if (in_array($keyLower, $sensitiveKeys, true)) {
                $clean[$key] = '[REDACTED]';
            } elseif ($value instanceof Throwable) {
                $clean[$key] = [
                    'type' => get_class($value),
                    'message' => $value->getMessage(),
                    'code' => $value->getCode(),
                    'file' => $value->getFile(),
                    'line' => $value->getLine(),
                    'trace' => $this->includeTrace ? $value->getTraceAsString() : null,
                ];
            } else {
                $clean[$key] = $value;
            }
        }
        return $clean;
    }
}
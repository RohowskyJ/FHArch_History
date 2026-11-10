<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

ini_set('display_errors', '1');
error_reporting(E_ALL);
mb_internal_encoding('UTF-8');
set_time_limit(0);
ini_set('max_execution_time', '0');

/*
 |--------------------------------------------------------------------------
 | BILD-MIGRATION TOOL – NEU AUFGEBAUT
 |--------------------------------------------------------------------------
 | Funktionen:
 | - Tabellen-Records anzeigen
 | - nur offene Datensätze anzeigen
 | - leere Bildfelder ausblenden
 | - Tabellen-/Spaltendefinitionen beim Start aus INFORMATION_SCHEMA laden
 | - bereits migrierte Bild-DSNs ausblenden
 | - passende Bildkandidaten je Datensatz ohne globalen Dateiindex suchen
 | - PDFs und Nicht-Bilder ignorieren
 | - speichersparend: kein globaler Bildindex
 |--------------------------------------------------------------------------
 */

/* =========================
 KONFIGURATION
 ========================= */

$dbHost = 'localhost';
$dbName = 'fharch_new';
$dbUser = 'root';
$dbPass = 'b1teller';


$IMAGE_ROOT = 'c:/JProgs/htdocs/feuerwehr_archiv/FWHist/login/AOrd_Verz';
$ENABLE_IMAGE_PREVIEW = true;
$PER_PAGE = 50;

const ALLOWED_IMAGE_EXTENSIONS = [
    'jpg', 'jpeg', 'jpe', 'webp', 'png', 'gif', 'bmp', 'tif', 'tiff', 'heic', 'avif',
];
$ALLOWED_EXTENSIONS = ALLOWED_IMAGE_EXTENSIONS;

$alias = [
    'JR' => 124,
    'WS' => 177,
    'VK' => 158,
];

$TABLE_TEMPLATES = [
    'mi_ehrungen' => [
        'pk' => 'me_id',
        'label' => 'Ehrungen',
        'sort' => 'me_id DESC',
        'display' => ['mi_id', 'me_ehrung', 'me_eh_datum'],
        'image_fields' => ['me_bild1', 'me_bild2', 'me_bild3', 'me_bild4'],
        'changed_at' => 'me_changed_at',
    ],
];

$DEFAULT_TABLE = array_key_first($TABLE_TEMPLATES);

/* =========================
 HILFSFUNKTIONEN
 ========================= */

function h($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function normalizePath(string $path): string
{
    return str_replace('\\', '/', $path);
}

function quoteIdentifier(string $identifier): string
{
    return '`' . str_replace('`', '``', $identifier) . '`';
}

function getPdo(string $host, string $db, string $user, string $pass): PDO
{
    return new PDO(
        "mysql:host={$host};dbname={$db};charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
        );
}

function discoverTables(PDO $pdo, string $database, array $templates): array
{
    $stmt = $pdo->prepare(
        'SELECT c.TABLE_NAME, c.COLUMN_NAME, c.DATA_TYPE, c.COLUMN_KEY, c.ORDINAL_POSITION, c.COLUMN_COMMENT '
        . 'FROM INFORMATION_SCHEMA.COLUMNS c '
        . 'INNER JOIN INFORMATION_SCHEMA.TABLES t '
        . 'ON t.TABLE_SCHEMA = c.TABLE_SCHEMA AND t.TABLE_NAME = c.TABLE_NAME AND t.TABLE_TYPE = \'BASE TABLE\' '
        . 'WHERE c.TABLE_SCHEMA = :database '
        . 'ORDER BY c.TABLE_NAME, c.ORDINAL_POSITION'
    );
    $stmt->execute(['database' => $database]);

    $columnsByTable = [];
    foreach ($stmt->fetchAll() as $column) {
        $columnsByTable[$column['TABLE_NAME']][] = $column;
    }

    $tables = [];
    foreach ($columnsByTable as $tableName => $columns) {
        $templateKey = $tableName;
        if (preg_match('/^(\d+)(_.+)$/', $tableName, $matches)) {
            $templateKey = $matches[2];
        } elseif (!isset($templates[$tableName])) {
            continue;
        }
        if (str_contains(strtolower($templateKey), 'dmedien')) {
            continue;
        }

        $template = $templates[$templateKey] ?? [];
        $columnNames = array_column($columns, 'COLUMN_NAME');
        $primaryKeys = array_values(array_filter(
            $columns,
            static fn(array $column): bool => $column['COLUMN_KEY'] === 'PRI'
        ));
        $pk = $template['pk'] ?? ($primaryKeys[0]['COLUMN_NAME'] ?? null);
        if (!is_string($pk) || !in_array($pk, $columnNames, true)) {
            $pk = null;
        }
        if ($pk === null) {
            foreach ($columns as $column) {
                if (
                    strcasecmp($column['COLUMN_NAME'], 'id') === 0
                    || preg_match('/_id$/i', $column['COLUMN_NAME']) === 1
                ) {
                    $pk = $column['COLUMN_NAME'];
                    break;
                }
            }
        }
        $pk ??= $columnNames[0] ?? null;
        if ($pk === null) {
            continue;
        }

        $imageFields = $template['image_fields'] ?? [];
        if ($imageFields === []) {
            foreach ($columns as $column) {
                $isText = in_array(strtolower($column['DATA_TYPE']), [
                    'char', 'varchar', 'tinytext', 'text', 'mediumtext', 'longtext',
                ], true);
                $isDescription = preg_match(
                    '/(?:^|_)(?:komm(?:entar)?|beschr(?:eibung)?|text|caption|alt|art)(?:_|$)'
                    . '|beschreibung|kommentar|veröffentlichung|anzahl|art der bilder/i',
                    $column['COLUMN_NAME'] . ' ' . $column['COLUMN_COMMENT']
                ) === 1;
                $looksLikeImage = preg_match(
                    '/bild|foto|image|picture|photo|(^|_)img($|_)/iu',
                    $column['COLUMN_NAME'] . ' ' . $column['COLUMN_COMMENT']
                ) === 1;
                if ($isText && $looksLikeImage && !$isDescription) {
                    $imageFields[] = $column['COLUMN_NAME'];
                }
            }
        }
        $imageFields = array_values(array_intersect($imageFields, $columnNames));
        if ($imageFields === []) {
            continue;
        }

        $displayFields = $template['display'] ?? [];
        if ($displayFields === []) {
            foreach ($columns as $column) {
                if (
                    $column['COLUMN_NAME'] !== $pk
                    && !in_array($column['COLUMN_NAME'], $imageFields, true)
                    && !in_array(strtolower($column['DATA_TYPE']), [
                        'blob', 'tinyblob', 'mediumblob', 'longblob', 'text', 'mediumtext', 'longtext',
                    ], true)
                ) {
                    $displayFields[] = $column['COLUMN_NAME'];
                }
                if (count($displayFields) >= 6) {
                    break;
                }
            }
        }
        $displayFields = array_values(array_intersect($displayFields, $columnNames));

        $changedAt = $template['changed_at'] ?? null;
        if ($changedAt === null) {
            foreach ($columnNames as $columnName) {
                if (preg_match('/(?:changed_at|aenddat|modified_at)$/i', $columnName) === 1) {
                    $changedAt = $columnName;
                    break;
                }
            }
        }

        $tables[$tableName] = [
            'pk' => $pk,
            'label' => $template['label'] ?? ltrim(
                str_replace('_', ' ', preg_replace('/^\d+/', '', $tableName))
            ),
            'sort' => $template['sort'] ?? quoteIdentifier($pk) . ' DESC',
            'display' => $displayFields,
            'image_fields' => $imageFields,
            'changed_at' => $changedAt,
            'template' => $templateKey,
        ];
    }

    return $tables;
}

function splitNameAndExt(string $filename): array
{
    $info = pathinfo($filename);
    $name = $info['filename'] ?? $filename;
    $ext  = strtolower($info['extension'] ?? '');
    return [$name, $ext];
}

function imageSearchKey(string $name): string
{
    $key = mb_strtolower(pathinfo($name, PATHINFO_FILENAME));
    return preg_replace('/_c$/i', '', $key) ?? $key;
}

function parseDsn(string $dsn, array $alias): array
{
    $basename = basename($dsn);
    [$base, $ext] = splitNameAndExt($basename);
    
    $out = [
        'dsn'       => $basename,
        'base'      => $base,
        'ext'       => strtolower($ext),
        'urh_id'    => null,
        'date'      => null,
        'image_key' => mb_strtolower($base),
        'original_name' => $base,
        'is_new'    => false,
    ];
    
    if (preg_match('/^(\d+)-(\d{8})-[WN]-(.+)\.JPG$/', $basename, $m) === 1) {
        $out['is_new'] = true;
        $out['date'] = $m[2];
        $out['urh_id'] = $m[1];
        $out['original_name'] = pathinfo($m[3], PATHINFO_FILENAME);
        $out['image_key'] = imageSearchKey($out['original_name']);
        return $out;
    }

    if (preg_match('/^\d+_\d+_(\d{4}|\d{8})_(.+)$/i', $base, $m) === 1) {
        $out['date'] = $m[1];
        $out['original_name'] = $m[2];
    } elseif (preg_match('/^([^-]+)-(\d{4}|\d{8})-(?:[WN]-)?(.+)$/i', $base, $m) === 1) {
        $prefix = $m[1];
        $out['date'] = $m[2];
        $out['original_name'] = $m[3];
        if (isset($alias[strtoupper($prefix)])) {
            $out['urh_id'] = (string)$alias[strtoupper($prefix)];
        } elseif (ctype_digit($prefix) && strlen($prefix) !== 4) {
            $out['urh_id'] = $prefix;
        } elseif (ctype_digit($prefix) && strlen($prefix) === 4) {
            $out['date'] ??= $prefix;
            $out['original_name'] = $m[2] . '-' . $m[3];
        }
    } elseif (preg_match('/^(\d{4}|\d{8})[-_](.+)$/', $base, $m) === 1) {
        $out['date'] = $m[1];
        $out['original_name'] = $m[2];
    } elseif (preg_match('/^([^-]+)-(.+)$/', $base, $m) === 1) {
        $prefix = $m[1];
        if (isset($alias[strtoupper($prefix)])) {
            $out['urh_id'] = (string)$alias[strtoupper($prefix)];
            $out['original_name'] = $m[2];
        } elseif (ctype_digit($prefix) && strlen($prefix) !== 4) {
            $out['urh_id'] = $prefix;
            $out['original_name'] = $m[2];
        }
    }

    $out['image_key'] = imageSearchKey($out['original_name']);
    return $out;
}

function dateDirectories(string $baseDirectory, ?string $date): Generator
{
    if (!is_dir($baseDirectory)) {
        return;
    }

    if ($date !== null && preg_match('/^\d{8}$/', $date) === 1) {
        $exact = $baseDirectory . DIRECTORY_SEPARATOR . $date;
        if (is_dir($exact)) {
            yield $exact;
        }
        return;
    }

    foreach (new DirectoryIterator($baseDirectory) as $entry) {
        if (!$entry->isDir() || $entry->isDot()) {
            continue;
        }
        $directoryName = $entry->getFilename();
        if (
            preg_match('/^\d{8}$/', $directoryName) !== 1
            || ($date !== null && !str_starts_with($directoryName, $date))
        ) {
            continue;
        }
        yield $entry->getPathname();
    }
}

function candidateDirectories(string $imageRoot, array $target): Generator
{
    $ownerIds = [];
    if (!empty($target['urh_id'])) {
        $ownerIds[] = (string)$target['urh_id'];
    } elseif (is_dir($imageRoot)) {
        foreach (new DirectoryIterator($imageRoot) as $entry) {
            if ($entry->isDir() && !$entry->isDot() && ctype_digit($entry->getFilename())) {
                $ownerIds[] = $entry->getFilename();
            }
        }
    }

    foreach ($ownerIds as $ownerId) {
        $baseDirectory = rtrim($imageRoot, '\\/') . DIRECTORY_SEPARATOR . $ownerId
            . DIRECTORY_SEPARATOR . '09' . DIRECTORY_SEPARATOR . '06';
        yield from dateDirectories($baseDirectory, $target['date'] ?? null);
    }
}

function isPathInsideRoot(string $file, string $root): bool
{
    $realFile = realpath($file);
    $realRoot = realpath($root);
    
    if (!$realFile || !$realRoot) {
        return false;
    }
    
    $realFile = normalizePath($realFile);
    $realRoot = rtrim(normalizePath($realRoot), '/');
    
    return strpos($realFile, $realRoot . '/') === 0 || $realFile === $realRoot;
}

function servePreview(string $file, string $root): void
{
    if (!isPathInsideRoot($file, $root) || !is_file($file)) {
        http_response_code(404);
        echo 'Datei nicht gefunden';
        exit;
    }
    
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file) ?: 'application/octet-stream';
    
    header('Content-Type: ' . $mime);
    header('Content-Length: ' . (string)filesize($file));
    header('Cache-Control: private, max-age=3600');
    readfile($file);
    exit;
}

function buildPreviewUrl(string $filePath, string $scriptName): string
{
    return $scriptName . '?action=preview&file=' . rawurlencode($filePath);
}

function getAllowedTableConfig(array $tables, string $table): ?array
{
    return $tables[$table] ?? null;
}

function buildSelectColumns(array $config): array
{
    $columns = array_merge(
        [$config['pk']],
        $config['display'],
        $config['image_fields']
        );
    
    if (!empty($config['changed_at'])) {
        $columns[] = $config['changed_at'];
    }
    
    return array_values(array_unique($columns));
}

function fetchRecords(PDO $pdo, string $table, array $config, int $page, int $perPage): array
{
    $offset = max(0, ($page - 1) * $perPage);
    $cols = buildSelectColumns($config);
    $sqlCols = implode(', ', array_map('quoteIdentifier', $cols));
    
    $sql = sprintf(
        "SELECT %s FROM %s ORDER BY %s LIMIT %d OFFSET %d",
        $sqlCols,
        quoteIdentifier($table),
        $config['sort'],
        $perPage,
        $offset
        );
    
    return $pdo->query($sql)->fetchAll();
}

function fetchRecordById(PDO $pdo, string $table, array $config, $id): ?array
{
    $cols = buildSelectColumns($config);
    $sqlCols = implode(', ', array_map('quoteIdentifier', $cols));
    
    $sql = sprintf(
        "SELECT %s FROM %s WHERE %s = :id LIMIT 1",
        $sqlCols,
        quoteIdentifier($table),
        quoteIdentifier($config['pk'])
        );
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch();
    
    return $row ?: null;
}

function updateImageFields(PDO $pdo, string $table, array $config, $id, array $changes): void
{
    if ($changes === []) {
        throw new RuntimeException('Keine ausgewählten Änderungen zum Speichern.');
    }

    $assignments = [];
    $params = [':id' => $id];
    foreach ($changes as $field => $newName) {
        if (!in_array($field, $config['image_fields'], true)) {
            throw new RuntimeException('Ungültiges Bildfeld.');
        }
        $parameter = ':image_' . count($assignments);
        $assignments[] = quoteIdentifier($field) . ' = ' . $parameter;
        $params[$parameter] = $newName;
    }

    $sql = sprintf(
        'UPDATE %s SET %s WHERE %s = :id',
        quoteIdentifier($table),
        implode(', ', $assignments),
        quoteIdentifier($config['pk'])
    );
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
}

function validateStagedChanges(
    PDO $pdo,
    string $table,
    array $config,
    $id,
    array $changes,
    array $alias,
    string $imageRoot,
    array $allowedExtensions
): array {
    if ($changes === []) {
        throw new RuntimeException('Für diesen Datensatz sind keine Bildänderungen vorgemerkt.');
    }

    $record = fetchRecordById($pdo, $table, $config, $id);
    if ($record === null) {
        throw new RuntimeException('Datensatz wurde nicht gefunden.');
    }

    $oldValues = [];
    foreach ($changes as $field => $name) {
        if (
            !in_array($field, $config['image_fields'], true)
            || !is_string($name)
            || $name === ''
        ) {
            throw new RuntimeException('Ungültige vorgemerkte Bildänderung.');
        }
        $oldValue = trim((string)($record[$field] ?? ''));
        if ($oldValue === '' || !isImageFileReference($oldValue) || isMigratedPhotoDsn($oldValue)) {
            throw new RuntimeException("Das Bildfeld {$field} ist nicht mehr ersetzbar.");
        }

        $target = parseDsn($oldValue, $alias);
        $candidateExists = false;
        foreach (findCandidates($imageRoot, $target, $allowedExtensions) as $candidate) {
            if ($candidate['name'] === $name) {
                $candidateExists = true;
                break;
            }
        }
        if (!$candidateExists) {
            throw new RuntimeException("Das ausgewählte Bild für {$field} ist nicht mehr verfügbar.");
        }
        $oldValues[$field] = $oldValue;
    }

    return $oldValues;
}

function writeReplacementLog(string $logFile, string $table, string $id, array $oldValues, array $newValues): void
{
    $entry = [
        'timestamp' => date(DATE_ATOM),
        'table' => $table,
        'id' => $id,
        'changes' => [],
    ];
    foreach ($newValues as $field => $newValue) {
        $entry['changes'][$field] = [
            'old' => $oldValues[$field] ?? null,
            'new' => $newValue,
        ];
    }
    $written = file_put_contents(
        $logFile,
        json_encode($entry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL,
        FILE_APPEND | LOCK_EX
    );
    if ($written === false) {
        throw new RuntimeException('Datenbank wurde aktualisiert, das Änderungsprotokoll konnte aber nicht geschrieben werden.');
    }
}

/* =========================
 FILTER
 ========================= */

function isBlankValue($value): bool
{
    return trim((string)$value) === '';
}

function isImageFileReference(string $value): bool
{
    $extension = strtolower(pathinfo(basename(trim($value)), PATHINFO_EXTENSION));
    return in_array($extension, ALLOWED_IMAGE_EXTENSIONS, true);
}

function isMigratedPhotoDsn(string $value): bool
{
    $value = trim($value);
    if ($value === '') {
        return false;
    }
    
    return preg_match('/^\d+-\d{8}-[WN]-.+\.JPG$/', basename($value)) === 1;
}

function hasAnyNonEmptyImageField(array $row, array $imageFields): bool
{
    foreach ($imageFields as $field) {
        $value = trim((string)($row[$field] ?? ''));
        if ($value !== '' && isImageFileReference($value)) {
            return true;
        }
    }
    return false;
}

function allNonEmptyImageFieldsMigrated(array $row, array $imageFields): bool
{
    $hasAny = false;
    
    foreach ($imageFields as $field) {
        $value = trim((string)($row[$field] ?? ''));
        
        if ($value === '' || !isImageFileReference($value)) {
            continue;
        }
        
        $hasAny = true;
        
        if (!isMigratedPhotoDsn($value)) {
            return false;
        }
    }
    
    return $hasAny;
}

function filterRecords(array $records, array $config): array
{
    $filtered = [];
    
    foreach ($records as $row) {
        if (!hasAnyNonEmptyImageField($row, $config['image_fields'])) {
            continue;
        }
        
        if (allNonEmptyImageFieldsMigrated($row, $config['image_fields'])) {
            continue;
        }
        
        $filtered[] = $row;
    }
    
    return $filtered;
}

function fetchOpenRecordsPage(
    PDO $pdo,
    string $table,
    array $config,
    int $page,
    int $perPage,
    array $ignoredRecordIds = []
): array
{
    $batchSize = max($perPage, 250);
    $offset = 0;
    $openRecords = [];
    $targetCount = $page * $perPage + 1;
    $ignoredLookup = array_fill_keys(array_map('strval', $ignoredRecordIds), true);

    while (true) {
        $batch = fetchRecords($pdo, $table, $config, intdiv($offset, $batchSize) + 1, $batchSize);
        if ($batch === []) {
            break;
        }

        $openBatch = filterRecords($batch, $config);
        if ($ignoredLookup !== []) {
            $openBatch = array_values(array_filter(
                $openBatch,
                static fn(array $row): bool => !isset($ignoredLookup[(string)$row[$config['pk']]])
            ));
        }
        array_push($openRecords, ...$openBatch);
        $offset += count($batch);
        if (count($openRecords) >= $targetCount || count($batch) < $batchSize) {
            break;
        }
    }

    $hasNextPage = count($openRecords) > $page * $perPage;
    $pageRecords = array_slice($openRecords, ($page - 1) * $perPage, $perPage);

    return [$pageRecords, $hasNextPage];
}

/* =========================
 KANDIDATEN-SUCHE
 ========================= */

function findCandidates(string $imageRoot, array $target, array $allowedExtensions): array
{
    $hits = [];

    $targetKey = mb_strtolower(trim((string)($target['image_key'] ?? '')));
    if ($targetKey === '') {
        return [];
    }
    $targetOriginalKey = mb_strtolower((string)($target['original_name'] ?? ''));

    for ($pass = 0; $pass < 2; $pass++) {
        $searchTarget = $target;
        if ($pass === 1) {
            $searchTarget['date'] = null;
        }

        foreach (candidateDirectories($imageRoot, $searchTarget) as $searchDir) {
            foreach (new DirectoryIterator($searchDir) as $file) {
                if (!$file->isFile() || $file->isDot()) {
                    continue;
                }

                $name = $file->getFilename();
                $extension = strtolower($file->getExtension());
                if (
                    !in_array($extension, $allowedExtensions, true)
                    || $file->getSize() <= 0
                ) {
                    continue;
                }

                $candidate = parseDsn($name, []);
                if (!$candidate['is_new']) {
                    continue;
                }
                $candidateKey = mb_strtolower((string)$candidate['image_key']);
                if ($candidateKey !== $targetKey && !str_contains($candidateKey, $targetKey)) {
                    continue;
                }

                $hits[] = [
                    'score' => mb_strtolower((string)$candidate['original_name']) === $targetOriginalKey
                        ? 130
                        : ($candidateKey === $targetKey ? 120 : 100),
                    'name' => $name,
                    'path' => normalizePath($file->getPathname()),
                    'parsed' => $candidate,
                ];
            }
        }

        if ($hits !== []) {
            break;
        }
    }

    usort($hits, static function(array $a, array $b): int {
        return ($b['score'] <=> $a['score'])
            ?: ((str_contains($b['name'], '-W-') ? 1 : 0) <=> (str_contains($a['name'], '-W-') ? 1 : 0))
            ?: strcasecmp($a['name'], $b['name']);
    });

    return $hits;
}

/* =========================
 REQUEST / ROUTING
 ========================= */

$scriptName = basename($_SERVER['PHP_SELF']);
$logFile = __DIR__ . DIRECTORY_SEPARATOR . 'PictFileReplace.log';
$_SESSION['pictFileReplaceCsrf'] ??= bin2hex(random_bytes(32));
$csrfToken = $_SESSION['pictFileReplaceCsrf'];

$action = $_GET['action'] ?? '';
if ($action === 'preview') {
    $file = (string)($_GET['file'] ?? '');
    servePreview($file, $IMAGE_ROOT);
}

$pdo = getPdo($dbHost, $dbName, $dbUser, $dbPass);
$TABLES = discoverTables($pdo, $dbName, $TABLE_TEMPLATES);
$DEFAULT_TABLE = array_key_first($TABLES);

$message = null;
$messageType = 'ok';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAction = $_POST['post_action'] ?? '';
    
    try {
        if (!hash_equals($csrfToken, (string)($_POST['csrf_token'] ?? ''))) {
            throw new RuntimeException('Ungültige oder abgelaufene Formularsitzung. Bitte neu laden.');
        }

        $table = (string)($_POST['table'] ?? '');
        $id = (string)($_POST['id'] ?? '');
        $config = getAllowedTableConfig($TABLES, $table);
        if (!$config || (!in_array($postAction, ['save_page', 'clear_ignored'], true) && $id === '')) {
            throw new RuntimeException('Unbekannte Tabelle oder fehlende Datensatz-ID.');
        }
        if ($postAction === 'clear_ignored') {
            unset($_SESSION['pictFileReplaceIgnored'][$table]);
            $_SESSION['pictFileReplaceFlash'] = [
                'message' => 'Übersprungene Datensätze werden wieder angezeigt.',
                'type' => 'ok',
            ];
            header('Location: ?' . http_build_query([
                'table' => $table,
                'page' => 1,
            ]));
            exit;
        }

        $record = null;
        if ($postAction !== 'save_page') {
            $record = fetchRecordById($pdo, $table, $config, $id);
            if ($record === null) {
                throw new RuntimeException('Datensatz wurde nicht gefunden.');
            }
        }
        $stageKey = $table . ':' . $id;

        if ($postAction === 'ignore_record') {
            if (!empty($_SESSION['pictFileReplaceStaged'][$stageKey])) {
                throw new RuntimeException('Für diesen Datensatz sind Änderungen vorgemerkt. Bitte zuerst speichern oder verwerfen.');
            }
            $_SESSION['pictFileReplaceIgnored'][$table][$id] = true;
            $_SESSION['pictFileReplaceFlash'] = [
                'message' => "Datensatz {$id} wird in dieser Sitzung vorläufig übersprungen.",
                'type' => 'ok',
            ];
            header('Location: ?' . http_build_query([
                'table' => $table,
                'page' => max(1, (int)($_POST['page'] ?? 1)),
            ]));
            exit;
        }

        if ($postAction === 'stage_image') {
            $field = (string)($_POST['field'] ?? '');
            $name = trim((string)($_POST['new_name'] ?? ''));
            if (!in_array($field, $config['image_fields'], true) || $name === '') {
                throw new RuntimeException('Ungültiges Bildfeld oder fehlender Dateiname.');
            }
            $oldValue = trim((string)($record[$field] ?? ''));
            if ($oldValue === '' || !isImageFileReference($oldValue) || isMigratedPhotoDsn($oldValue)) {
                throw new RuntimeException('Dieses Bildfeld ist leer, enthält keine Bilddatei oder ist bereits im neuen Format.');
            }
            $target = parseDsn($oldValue, $alias);
            $candidatesForField = findCandidates($IMAGE_ROOT, $target, $ALLOWED_EXTENSIONS);
            $isValidCandidate = false;
            foreach ($candidatesForField as $candidate) {
                if ($candidate['name'] === $name) {
                    $isValidCandidate = true;
                    break;
                }
            }
            if (!$isValidCandidate) {
                throw new RuntimeException('Der ausgewählte Bildtreffer ist nicht mehr verfügbar.');
            }

            $_SESSION['pictFileReplaceStaged'][$stageKey][$field] = $name;
            $_SESSION['pictFileReplaceFlash'] = [
                'message' => "Auswahl für {$field} vorgemerkt. Noch nicht in der Datenbank gespeichert.",
                'type' => 'ok',
            ];
            header('Location: ?' . http_build_query([
                'table' => $table,
                'page' => max(1, (int)($_POST['page'] ?? 1)),
                'id' => $id,
                'field' => $field,
            ]));
            exit;
        }

        if ($postAction === 'save_record') {
            $changes = $_SESSION['pictFileReplaceStaged'][$stageKey] ?? [];
            if (!is_array($changes)) {
                throw new RuntimeException('Ungültige vorgemerkte Bildänderungen.');
            }
            $oldValues = validateStagedChanges(
                $pdo,
                $table,
                $config,
                $id,
                $changes,
                $alias,
                $IMAGE_ROOT,
                $ALLOWED_EXTENSIONS
            );

            $pdo->beginTransaction();
            try {
                updateImageFields($pdo, $table, $config, $id, $changes);
                $pdo->commit();
            } catch (Throwable $exception) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                throw $exception;
            }
            unset($_SESSION['pictFileReplaceStaged'][$stageKey]);
            writeReplacementLog($logFile, $table, $id, $oldValues, $changes);
            $_SESSION['pictFileReplaceFlash'] = [
                'message' => count($changes) . " Bildfeld(er) in {$table}, Datensatz {$id} gespeichert und protokolliert.",
                'type' => 'ok',
            ];
            header('Location: ?' . http_build_query([
                'table' => $table,
                'page' => max(1, (int)($_POST['page'] ?? 1)),
            ]));
            exit;
        }

        if ($postAction === 'save_page') {
            $recordIds = $_POST['record_ids'] ?? [];
            if (!is_array($recordIds)) {
                throw new RuntimeException('Ungültige Datensatzliste für die Seitenspeicherung.');
            }

            $batch = [];
            foreach ($recordIds as $recordId) {
                $recordId = (string)$recordId;
                $recordStageKey = $table . ':' . $recordId;
                $changes = $_SESSION['pictFileReplaceStaged'][$recordStageKey] ?? [];
                if (!is_array($changes) || $changes === []) {
                    continue;
                }
                $oldValues = validateStagedChanges(
                    $pdo,
                    $table,
                    $config,
                    $recordId,
                    $changes,
                    $alias,
                    $IMAGE_ROOT,
                    $ALLOWED_EXTENSIONS
                );
                $batch[$recordStageKey] = [
                    'id' => $recordId,
                    'changes' => $changes,
                    'oldValues' => $oldValues,
                ];
            }
            if ($batch === []) {
                throw new RuntimeException('Auf dieser Seite gibt es keine vorgemerkten Änderungen.');
            }

            $pdo->beginTransaction();
            try {
                foreach ($batch as $item) {
                    updateImageFields($pdo, $table, $config, $item['id'], $item['changes']);
                }
                $pdo->commit();
            } catch (Throwable $exception) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                throw $exception;
            }

            foreach ($batch as $stageKey => $item) {
                unset($_SESSION['pictFileReplaceStaged'][$stageKey]);
                writeReplacementLog($logFile, $table, $item['id'], $item['oldValues'], $item['changes']);
            }
            $_SESSION['pictFileReplaceFlash'] = [
                'message' => count($batch) . " geänderte Datensätze dieser Seite gespeichert und protokolliert.",
                'type' => 'ok',
            ];
            header('Location: ?' . http_build_query([
                'table' => $table,
                'page' => max(1, (int)($_POST['page'] ?? 1)),
            ]));
            exit;
        }

        if ($postAction === 'clear_staged') {
            unset($_SESSION['pictFileReplaceStaged'][$stageKey]);
            $_SESSION['pictFileReplaceFlash'] = [
                'message' => 'Vorgemerkte Änderungen wurden verworfen.',
                'type' => 'ok',
            ];
            header('Location: ?' . http_build_query([
                'table' => $table,
                'id' => $id,
            ]));
            exit;
        }

        if ($postAction === 'update_image') {
            throw new RuntimeException('Direktes Speichern ist deaktiviert. Bitte Änderungen vormerken und gesammelt speichern.');
        }
    } catch (Throwable $e) {
        $message = $e->getMessage();
        $messageType = 'error';
    }
}

$flash = $_SESSION['pictFileReplaceFlash'] ?? null;
unset($_SESSION['pictFileReplaceFlash']);
if (is_array($flash)) {
    $message = (string)($flash['message'] ?? '');
    $messageType = (string)($flash['type'] ?? 'ok');
}

$table = (string)($_GET['table'] ?? $DEFAULT_TABLE ?? '');
$config = getAllowedTableConfig($TABLES, $table);
if (!$config) {
    $table = (string)($DEFAULT_TABLE ?? '');
    $config = getAllowedTableConfig($TABLES, $table);
}
if (!$config) {
    http_response_code(500);
    die('Keine Tabelle mit erkannten Bildfeldern gefunden.');
}

$page = max(1, (int)($_GET['page'] ?? 1));
$ignoredRecordIds = array_keys($_SESSION['pictFileReplaceIgnored'][$table] ?? []);
[$records, $hasNextPage] = fetchOpenRecordsPage($pdo, $table, $config, $page, $PER_PAGE, $ignoredRecordIds);
while ($records === [] && $page > 1) {
    $page--;
    [$records, $hasNextPage] = fetchOpenRecordsPage($pdo, $table, $config, $page, $PER_PAGE, $ignoredRecordIds);
}
$ignoredRecordCount = count($ignoredRecordIds);
$stagedRecordIdsOnPage = [];
foreach ($records as $row) {
    $recordId = (string)$row[$config['pk']];
    if (!empty($_SESSION['pictFileReplaceStaged'][$table . ':' . $recordId])) {
        $stagedRecordIdsOnPage[] = $recordId;
    }
}

$selectedId = $_GET['id'] ?? null;
$selectedField = (string)($_GET['field'] ?? '');
$selectedRecord = null;
$currentValue = '';
$candidates = [];
$targetParsed = null;
$stagedKey = $table . ':' . (string)$selectedId;
$stagedChanges = is_array($_SESSION['pictFileReplaceStaged'][$stagedKey] ?? null)
    ? $_SESSION['pictFileReplaceStaged'][$stagedKey]
    : [];

if ($selectedId !== null && $selectedId !== '') {
    $selectedRecord = fetchRecordById($pdo, $table, $config, $selectedId);
    
    if ($selectedRecord) {
        if ($selectedField === '' || !in_array($selectedField, $config['image_fields'], true)) {
            foreach ($config['image_fields'] as $f) {
                if (
                    !isBlankValue($selectedRecord[$f] ?? '')
                    && isImageFileReference((string)$selectedRecord[$f])
                    && !isMigratedPhotoDsn((string)$selectedRecord[$f])
                ) {
                    $selectedField = $f;
                    break;
                }
            }
            if ($selectedField === '') {
                foreach ($config['image_fields'] as $f) {
                    if (
                        !isBlankValue($selectedRecord[$f] ?? '')
                        && isImageFileReference((string)$selectedRecord[$f])
                    ) {
                        $selectedField = $f;
                        break;
                    }
                }
            }
            if ($selectedField === '' && !empty($config['image_fields'][0])) {
                $selectedField = $config['image_fields'][0];
            }
        }
        
        $currentValue = trim((string)($selectedRecord[$selectedField] ?? ''));
        
        if ($currentValue !== '' && isImageFileReference($currentValue) && !isMigratedPhotoDsn($currentValue)) {
            $targetParsed = parseDsn($currentValue, $alias);
            $candidates = findCandidates($IMAGE_ROOT, $targetParsed, $ALLOWED_EXTENSIONS);
        }
    }
}

$debugInfo = null;
if (isset($_GET['debug']) && $_GET['debug'] === '1') {
    $debugInfo = [
        'IMAGE_ROOT' => $IMAGE_ROOT,
        'realpath_IMAGE_ROOT' => realpath($IMAGE_ROOT),
        'searchMode' => 'nur passende Bilddateien aus den aus dem Namen abgeleiteten Verzeichnissen',
        'record_count_visible' => count($records),
        'candidate_count' => count($candidates),
        'targetParsed' => $targetParsed,
    ];
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bild-Migration Tool</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.45; background:#fafafa; }
        .msg { padding: 10px 12px; border-radius: 6px; margin-bottom: 15px; }
        .ok { background: #e8f7eb; color: #205530; }
        .warn { background: #fff2db; color: #6e4c12; }
        .error { background: #fdeaea; color: #7a1f1f; }
        .wrap { display: grid; grid-template-columns: 420px 1fr; gap: 20px; }
        .box { border: 1px solid #ccc; border-radius: 8px; padding: 15px; background: #fff; }
        .rec { border-top: 1px solid #ddd; padding: 10px 0; }
        .rec:first-child { border-top: none; }
        .record-actions { margin-top: 8px; }
        .cand { display: grid; grid-template-columns: 240px 1fr; gap: 15px; border-top: 1px solid #ddd; padding: 12px 0; align-items: center; }
        .cand:first-child { border-top: none; }
        img { max-width: 220px; max-height: 160px; object-fit: contain; background:#f7f7f7; }
        .candidate-select { display: flex; flex-direction: column; align-items: center; gap: 6px; width: 100%; padding: 8px; cursor: pointer; }
        .candidate-select img { width: 220px; height: 160px; }
        #table-search, #table-select { box-sizing: border-box; width: 100%; margin: 4px 0; padding: 7px; }
        .page-nav { display: flex; justify-content: space-between; align-items: center; gap: 10px; margin: 14px 0; padding: 10px; background: #f2f5f8; border-radius: 6px; }
        .page-button, .page-save button { display: inline-block; padding: 8px 12px; border: 1px solid #52718a; border-radius: 4px; background: #e8f0f6; color: #173d58; text-decoration: none; font-weight: bold; }
        .page-save { margin-top: 18px; padding: 12px; border: 1px solid #b9c9d5; border-radius: 6px; background: #f4f8fb; }
        .page-save form { display: inline-block; margin-right: 12px; }
        .page-save button:disabled { opacity: .55; cursor: not-allowed; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; vertical-align: top; }
        code { background: #f3f3f3; padding: 2px 4px; }
        pre { background:#f6f6f6; padding:12px; overflow:auto; }
        .muted { color:#666; font-size:.92rem; }
        @media (max-width: 900px) {
            .wrap { grid-template-columns: 1fr; }
            .cand { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<h1>Bild-Migration Tool</h1>

<?php if ($message): ?>
    <div class="msg <?= h($messageType) ?>"><?= h($message) ?></div>
<?php endif; ?>

<div class="msg warn">
    <strong>Pfad:</strong> <code><?= h($IMAGE_ROOT) ?></code><br>
    <strong>realpath:</strong> <code><?= h((string)realpath($IMAGE_ROOT)) ?></code><br>
    <strong>Tabellen mit erkannten Bildfeldern:</strong> <strong><?= count($TABLES) ?></strong><br>
    <strong>Offene Records auf dieser Seite:</strong> <strong><?= count($records) ?></strong><br>
    <strong>Änderungsprotokoll:</strong> <code><?= h($logFile) ?></code><br>
    <strong>Kandidaten:</strong> <strong><?= count($candidates) ?></strong>
</div>

<div class="wrap">
    <div class="box">
        <h2>Tabelle wählen</h2>

        <form method="get">
            <label>Tabelle:</label><br>
            <input type="search" id="table-search" placeholder="Tabelle suchen ..." aria-label="Tabellen filtern">
            <select name="table" id="table-select" onchange="this.form.submit()">
                <?php foreach ($TABLES as $tableName => $cfg): ?>
                    <option value="<?= h($tableName) ?>" <?= $table === $tableName ? 'selected' : '' ?>>
                        <?= h($cfg['label'] . ' (' . $tableName . ')') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <h3>Datensätze</h3>

        <?php if ($ignoredRecordCount > 0): ?>
            <form method="post" style="margin:8px 0 12px;">
                <input type="hidden" name="csrf_token" value="<?= h($csrfToken) ?>">
                <input type="hidden" name="post_action" value="clear_ignored">
                <input type="hidden" name="table" value="<?= h($table) ?>">
                <button type="submit">Übersprungene Datensätze wieder anzeigen (<?= $ignoredRecordCount ?>)</button>
            </form>
        <?php endif; ?>

        <?php if (!$records): ?>
            <p class="muted">Keine offenen Datensätze gefunden. Übersprungene Einträge können oben wieder eingeblendet werden.</p>
        <?php endif; ?>

        <div class="page-nav" aria-label="Seitennavigation oben">
            <?php if ($page > 1): ?>
                <a class="page-button" href="?table=<?= urlencode($table) ?>&page=<?= $page - 1 ?>">← Vorige Seite</a>
            <?php endif; ?>
            <strong>Seite <?= $page ?></strong>
            <?php if ($hasNextPage): ?>
                <a class="page-button" href="?table=<?= urlencode($table) ?>&page=<?= $page + 1 ?>">Nächste Seite →</a>
            <?php endif; ?>
        </div>

        <?php foreach ($records as $row): ?>
            <div class="rec">
                <strong>ID: <?= h((string)$row[$config['pk']]) ?></strong><br>

                <?php foreach ($config['display'] as $dispField): ?>
                    <div>
                        <small><?= h($dispField) ?>:</small>
                        <?= h((string)($row[$dispField] ?? '')) ?>
                    </div>
                <?php endforeach; ?>

                <?php if (!empty($config['changed_at'])): ?>
                    <div>
                        <small><?= h($config['changed_at']) ?>:</small>
                        <?= h((string)($row[$config['changed_at']] ?? '')) ?>
                    </div>
                <?php endif; ?>

                <?php foreach ($config['image_fields'] as $imgField): ?>
                    <?php $imgVal = trim((string)($row[$imgField] ?? '')); ?>
                    <?php if ($imgVal === '' || !isImageFileReference($imgVal)) continue; ?>
                    <div style="margin-top:8px;">
                        <small><?= h($imgField) ?>:</small>
                        <code><?= h($imgVal) ?></code>
                        <?php if (isset($_SESSION['pictFileReplaceStaged'][$table . ':' . (string)$row[$config['pk']]][$imgField])): ?>
                            <br><span class="muted">Vorgemerkt: <?= h($_SESSION['pictFileReplaceStaged'][$table . ':' . (string)$row[$config['pk']]][$imgField]) ?></span>
                        <?php endif; ?>
                        <?php if (isMigratedPhotoDsn($imgVal)): ?>
                            <span class="muted"> – bereits migriert</span>
                        <?php else: ?>
                            <br>
                            <a href="?table=<?= urlencode($table) ?>&page=<?= $page ?>&id=<?= urlencode((string)$row[$config['pk']]) ?>&field=<?= urlencode($imgField) ?>">
                                prüfen
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>

                <form method="post" class="record-actions">
                    <input type="hidden" name="csrf_token" value="<?= h($csrfToken) ?>">
                    <input type="hidden" name="post_action" value="ignore_record">
                    <input type="hidden" name="table" value="<?= h($table) ?>">
                    <input type="hidden" name="id" value="<?= h((string)$row[$config['pk']]) ?>">
                    <input type="hidden" name="page" value="<?= $page ?>">
                    <button type="submit" <?= !empty($_SESSION['pictFileReplaceStaged'][$table . ':' . (string)$row[$config['pk']]]) ? 'disabled title="Vorgemerkte Änderungen zuerst speichern oder verwerfen."' : '' ?>>
                        Datensatz vorläufig überspringen
                    </button>
                </form>
            </div>
        <?php endforeach; ?>

        <div class="page-save">
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= h($csrfToken) ?>">
                <input type="hidden" name="post_action" value="save_page">
                <input type="hidden" name="table" value="<?= h($table) ?>">
                <input type="hidden" name="page" value="<?= $page ?>">
                <?php foreach ($records as $row): ?>
                    <input type="hidden" name="record_ids[]" value="<?= h((string)$row[$config['pk']]) ?>">
                <?php endforeach; ?>
                <button type="submit" <?= $stagedRecordIdsOnPage === [] ? 'disabled' : '' ?>>
                    Änderungen dieser Seite speichern (<?= count($stagedRecordIdsOnPage) ?>)
                </button>
            </form>
            <span class="muted">Nicht geänderte Datensätze werden übersprungen.</span>
        </div>

        <div class="page-nav" aria-label="Seitennavigation unten">
            <?php if ($page > 1): ?>
                <a class="page-button" href="?table=<?= urlencode($table) ?>&page=<?= $page - 1 ?>">← Vorige Seite</a>
            <?php endif; ?>
            <strong>Seite <?= $page ?></strong>
            <?php if ($hasNextPage): ?>
                <a class="page-button" href="?table=<?= urlencode($table) ?>&page=<?= $page + 1 ?>">Nächste Seite →</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="box">
        <h2>Auswahl / Vorschau</h2>

        <?php if (!$selectedRecord): ?>
            <p>Bitte links einen Datensatz und ein nicht-leeres Bildfeld wählen.</p>
        <?php else: ?>
            <p>
                <strong>Tabelle:</strong> <?= h($table) ?><br>
                <strong>ID:</strong> <?= h((string)$selectedRecord[$config['pk']]) ?><br>
                <strong>Vorgemerkt:</strong> <?= count($stagedChanges) ?> Bildfeld(er); noch nicht gespeichert.
            </p>

            <?php if ($stagedChanges !== []): ?>
                <form method="post" style="margin:12px 0;">
                    <input type="hidden" name="csrf_token" value="<?= h($csrfToken) ?>">
                    <input type="hidden" name="post_action" value="save_record">
                    <input type="hidden" name="table" value="<?= h($table) ?>">
                    <input type="hidden" name="id" value="<?= h((string)$selectedRecord[$config['pk']]) ?>">
                    <input type="hidden" name="page" value="<?= $page ?>">
                    <button type="submit">Vorgemerkte Felder speichern</button>
                </form>
                <form method="post" style="margin:12px 0;">
                    <input type="hidden" name="csrf_token" value="<?= h($csrfToken) ?>">
                    <input type="hidden" name="post_action" value="clear_staged">
                    <input type="hidden" name="table" value="<?= h($table) ?>">
                    <input type="hidden" name="id" value="<?= h((string)$selectedRecord[$config['pk']]) ?>">
                    <button type="submit">Vormerkungen verwerfen</button>
                </form>
            <?php endif; ?>

            <h3>Bildfelder dieses Datensatzes</h3>
            <ul>
                <?php foreach ($config['image_fields'] as $imageField): ?>
                    <?php
                    $imageValue = trim((string)($selectedRecord[$imageField] ?? ''));
                    $stagedValue = $stagedChanges[$imageField] ?? null;
                    ?>
                    <li>
                        <?php if ($imageValue !== '' && isImageFileReference($imageValue) && !isMigratedPhotoDsn($imageValue)): ?>
                            <a href="?table=<?= urlencode($table) ?>&id=<?= urlencode((string)$selectedRecord[$config['pk']]) ?>&field=<?= urlencode($imageField) ?>">
                                <?= h($imageField) ?>
                            </a>
                        <?php else: ?>
                            <?= h($imageField) ?>
                        <?php endif; ?>:
                        <?php if ($imageValue === ''): ?>
                            <span class="muted">leer</span>
                        <?php elseif (!isImageFileReference($imageValue)): ?>
                            <code><?= h($imageValue) ?></code> <span class="muted">keine Bilddatei</span>
                        <?php elseif (isMigratedPhotoDsn($imageValue)): ?>
                            <code><?= h($imageValue) ?></code> <span class="muted">bereits im neuen Format</span>
                        <?php else: ?>
                            <code><?= h($imageValue) ?></code>
                        <?php endif; ?>
                        <?php if ($stagedValue !== null): ?>
                            <br><strong>Vorgemerkt:</strong> <code><?= h($stagedValue) ?></code>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>

            <p>
                <strong>Ausgewähltes Feld:</strong> <?= h($selectedField) ?><br>
                <strong>Alter Wert:</strong> <code><?= h($currentValue) ?></code>
                <?php if (isset($stagedChanges[$selectedField])): ?>
                    <br><strong>Vorgemerkter neuer Wert:</strong> <code><?= h($stagedChanges[$selectedField]) ?></code>
                <?php endif; ?>
            </p>

            <?php if ($currentValue === '' || !isImageFileReference($currentValue) || isMigratedPhotoDsn($currentValue)): ?>
                <p class="muted">Dieses Bildfeld ist leer, enthält keine Bilddatei oder ist bereits im neuen Format.</p>
            <?php else: ?>
                <h3>Analyse des aktuellen Werts</h3>
                <table style="margin-bottom:20px;">
                    <tbody>
                    <?php if ($targetParsed): ?>
                        <tr><th>urh_id</th><td><?= h((string)$targetParsed['urh_id']) ?></td></tr>
                        <tr><th>date</th><td><?= h((string)$targetParsed['date']) ?></td></tr>
                        <tr><th>image_key</th><td><?= h((string)$targetParsed['image_key']) ?></td></tr>
                        <tr><th>ext</th><td><?= h((string)$targetParsed['ext']) ?></td></tr>
                        <tr><th>is_new</th><td><?= $targetParsed['is_new'] ? 'ja' : 'nein' ?></td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>

                <h3>Suchordner</h3>
                <p class="muted">Die Suche folgt UrhNr und Datum, soweit aus dem alten Dateinamen erkennbar; andernfalls durchsucht sie die Datumsordner aller UrhNr-Verzeichnisse. Es wird kein globaler Bildindex erstellt.</p>

                <h3>Treffer-Vorschau</h3>
                <?php if ($ENABLE_IMAGE_PREVIEW && !empty($candidates)): ?>
                    <?php $best = $candidates[0]; ?>
                    <div style="margin-bottom:20px;">
                        <img src="<?= h(buildPreviewUrl($best['path'], $scriptName)) ?>" alt=""><br>
                        <small><?= h($best['name']) ?></small>
                    </div>
                <?php else: ?>
                    <p>Keine Vorschau verfügbar oder keine Kandidaten gefunden.</p>
                <?php endif; ?>

                <h3>Gefundene Kandidaten (<?= count($candidates) ?>)</h3>

                <?php if (!$candidates): ?>
                    <p>Keine passenden Kandidaten gefunden.</p>
                <?php else: ?>
                    <?php foreach ($candidates as $cand): ?>
                        <div class="cand">
                            <form method="post">
                                <input type="hidden" name="csrf_token" value="<?= h($csrfToken) ?>">
                                <input type="hidden" name="post_action" value="stage_image">
                                <input type="hidden" name="table" value="<?= h($table) ?>">
                                <input type="hidden" name="id" value="<?= h((string)$selectedRecord[$config['pk']]) ?>">
                                <input type="hidden" name="page" value="<?= $page ?>">
                                <input type="hidden" name="field" value="<?= h($selectedField) ?>">
                                <input type="hidden" name="new_name" value="<?= h($cand['name']) ?>">
                                <button class="candidate-select" type="submit" title="Dieses Thumbnail für <?= h($selectedField) ?> auswählen">
                                    <?php if ($ENABLE_IMAGE_PREVIEW): ?>
                                        <img src="<?= h(buildPreviewUrl($cand['path'], $scriptName)) ?>" alt="Vorschau <?= h($cand['name']) ?>">
                                    <?php else: ?>
                                        <span>Vorschau aus</span>
                                    <?php endif; ?>
                                    <strong>Für <?= h($selectedField) ?> vormerken</strong>
                                    <span><?= h($cand['name']) ?></span>
                                </button>
                            </form>

                            <div>
                                <strong><?= h($cand['name']) ?></strong><br>
                                Trefferqualität: <?= $cand['score'] === 120 ? 'exakt' : 'Teiltreffer' ?><br>
                                urh_id: <code><?= h((string)($cand['parsed']['urh_id'] ?? '')) ?></code><br>
                                date: <code><?= h((string)($cand['parsed']['date'] ?? '')) ?></code><br>
                                image_key: <code><?= h((string)($cand['parsed']['image_key'] ?? '')) ?></code>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            <?php endif; ?>

            <h3 style="margin-top:24px;">Datensatz-Rohansicht</h3>
            <table>
                <tbody>
                <?php foreach ($selectedRecord as $k => $v): ?>
                    <tr>
                        <th><?= h((string)$k) ?></th>
                        <td><?= h((string)$v) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <?php if ($debugInfo): ?>
            <h3 style="margin-top:24px;">Debug</h3>
            <pre><?= h(print_r($debugInfo, true)) ?></pre>
        <?php endif; ?>
    </div>
</div>

<script>
const tableSearch = document.getElementById('table-search');
const tableSelect = document.getElementById('table-select');
tableSearch?.addEventListener('input', () => {
    const query = tableSearch.value.trim().toLocaleLowerCase();
    for (const option of tableSelect.options) {
        option.hidden = query !== '' && !option.text.toLocaleLowerCase().includes(query);
    }
});
</script>

</body>
</html>
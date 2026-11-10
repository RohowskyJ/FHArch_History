<?php
declare(strict_types=1);

ini_set('display_errors', '1');
error_reporting(E_ALL);
mb_internal_encoding('UTF-8');

/*
 |--------------------------------------------------------------------------
 | BILD-MIGRATION TOOL – SPEICHERSPARENDE VARIANTE
 |--------------------------------------------------------------------------
 |
 | Grundidee:
 | - KEIN globaler Vollindex mehr
 | - pro Datensatz wird der Suchpfad direkt aus dem DSN abgeleitet
 | - es wird nur das passende Verzeichnis gescannt:
 |   .../{urh_id}/09/06/{datum}/
 |
 | Beispiel:
 |   DSN: 124-20110218-IMG_3768.WebP
 |   Suchordner:
 |   c:/JProgs/htdocs/feuerwehr_archiv/FWHist/login/AOrd_Verz/124/09/06/20110218/
 |
 | Matching:
 | - urh_id + datum + image_key
 | - Extension wird ignoriert
 |
 |--------------------------------------------------------------------------
 | KONFIGURATION
 |--------------------------------------------------------------------------
 */

$dbHost = 'localhost';
$dbName = 'fharch_new';
$dbUser = 'root';
$dbPass = 'b1teller';

// Echter Dateisystempfad zum Archivroot
$IMAGE_ROOT = 'c:/JProgs/htdocs/feuerwehr_archiv/FWHist/login/AOrd_Verz';

// Cache-Datei nur noch für kleine Hilfsdaten / optional
$CACHE_FILE = __DIR__ . '/bild_index_cache.php';

$PER_PAGE = 20;

// Bildvorschau aktiv?
$ENABLE_IMAGE_PREVIEW = true;

// Erlaubte Bild-Extensions
$ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'webp', 'png', 'gif', 'bmp'];

// Alias-Mapping
$alias = [
    'JR' => 124,
    'WS' => 177,
    'VK' => 158,
];

// Tabellen-Whitelist
$TABLES = [
    'mi_ehrungen' => [
        'pk' => 'me_id',
        'label' => 'Ehrungen',
        'sort' => 'me_id DESC',
        'display' => ['mi_id', 'me_ehrung', 'me_eh_datum'],
        'image_fields' => ['me_bild1', 'me_bild2', 'me_bild3', 'me_bild4'],
    ],
];

$DEFAULT_TABLE = array_key_first($TABLES);

/*
 |--------------------------------------------------------------------------
 | HILFSFUNKTIONEN
 |--------------------------------------------------------------------------
 */

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

function splitNameAndExt(string $filename): array
{
    $info = pathinfo($filename);
    $name = $info['filename'] ?? $filename;
    $ext  = strtolower($info['extension'] ?? '');
    return [$name, $ext];
}

/**
 * Unterstützte Formate:
 * - IMG_2324.jpg
 * - 124-20110218-IMG_3768.WebP
 * - JR-20110218-IMG_3768.jpg
 */
function parseDsn(string $dsn, array $alias): array
{
    $basename = basename($dsn);
    $base = pathinfo($basename, PATHINFO_FILENAME);
    $ext = strtolower(pathinfo($basename, PATHINFO_EXTENSION) ?? '');
    
    $out = [
        'dsn' => $basename,
        'base' => $base,
        'ext' => $ext,
        'urh_id' => null,
        'date' => null,
        'image_key' => null,
        'is_new' => false,
    ];
    
    if (preg_match('/^([^-]+)-(\d{8})-(.+)$/u', $base, $m)) {
        $prefixRaw = $m[1];
        $prefixUpper = strtoupper($prefixRaw);
        
        $out['is_new'] = true;
        $out['date'] = $m[2];
        $out['image_key'] = mb_strtolower($m[3]);
        
        if (isset($alias[$prefixUpper])) {
            $out['urh_id'] = (string)$alias[$prefixUpper];
        } elseif (ctype_digit($prefixRaw)) {
            $out['urh_id'] = $prefixRaw;
        }
    } else {
        $out['image_key'] = mb_strtolower($base);
    }
    
    return $out;
}

function buildSearchDir(string $imageRoot, array $parsed): ?string
{
    if (empty($parsed['urh_id']) || empty($parsed['date'])) {
        return null;
    }
    
    return rtrim(normalizePath($imageRoot), '/')
    . '/'
        . $parsed['urh_id']
        . '/09/06/'
            . $parsed['date']
            . '/';
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
    finfo_close($finfo);
    
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
    return array_values(array_unique(array_merge(
        [$config['pk']],
        $config['display'],
        $config['image_fields']
        )));
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

function updateImageField(PDO $pdo, string $table, array $config, $id, string $field, string $newName): bool
{
    if (!in_array($field, $config['image_fields'], true)) {
        throw new RuntimeException('Ungültiges Bildfeld');
    }
    
    $sql = sprintf(
        "UPDATE %s SET %s = :val WHERE %s = :id",
        quoteIdentifier($table),
        quoteIdentifier($field),
        quoteIdentifier($config['pk'])
        );
    
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        ':val' => $newName,
        ':id'  => $id,
    ]);
}

/**
 * Sucht nur im Zielordner des Datensatzes.
 * Keine globale Indizierung.
 */
function findCandidatesInDirectory(string $searchDir, array $target, array $allowedExtensions): array
{
    $hits = [];
    
    if (!is_dir($searchDir)) {
        return [];
    }
    
    foreach (new DirectoryIterator($searchDir) as $file) {
        if ($file->isDot() || !$file->isFile()) {
            continue;
        }
        
        $name = $file->getFilename();
        $ext = strtolower($file->getExtension());
        
        if ($ext === '' || !in_array($ext, $allowedExtensions, true)) {
            continue;
        }
        
        $candidate = parseDsn($name, []);
        
        // WICHTIG: immer klein schreiben für Vergleich
        $targetKey = mb_strtolower((string)($target['image_key'] ?? ''));
        $candidateKey = mb_strtolower((string)($candidate['image_key'] ?? ''));
        
        $targetDate = (string)($target['date'] ?? '');
        $candidateDate = (string)($candidate['date'] ?? '');
        
        $targetId = (string)($target['urh_id'] ?? '');
        $candidateId = (string)($candidate['urh_id'] ?? '');
        
        $score = 0;
        
        if ($targetKey !== '' && $candidateKey !== '' && $targetKey === $candidateKey) {
            $score += 120;
        }
        
        if ($targetDate !== '' && $candidateDate !== '' && $targetDate === $candidateDate) {
            $score += 50;
        }
        
        if ($targetId !== '' && $candidateId !== '' && $targetId === $candidateId) {
            $score += 50;
        }
        
        // Bonus: exakter Name ohne Extension
        if (mb_strtolower(pathinfo($target['dsn'] ?? '', PATHINFO_FILENAME)) === mb_strtolower(pathinfo($name, PATHINFO_FILENAME))) {
            $score += 30;
        }
        
        if ($score > 0) {
            $hits[] = [
                'score' => $score,
                'name' => $name,
                'path' => rtrim(normalizePath($searchDir), '/') . '/' . $name,
                'parsed' => $candidate,
            ];
        }
    }
    
    usort($hits, static function(array $a, array $b): int {
        return $b['score'] <=> $a['score'];
    });
        
        return $hits;
}

/*
 |--------------------------------------------------------------------------
 | REQUEST / ROUTING
 |--------------------------------------------------------------------------
 */

$pdo = getPdo($dbHost, $dbName, $dbUser, $dbPass);
$scriptName = basename($_SERVER['PHP_SELF']);

$action = $_GET['action'] ?? '';
if ($action === 'preview') {
    $file = (string)($_GET['file'] ?? '');
    servePreview($file, $IMAGE_ROOT);
}

$message = null;
$messageType = 'ok';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAction = $_POST['post_action'] ?? '';
    
    try {
        if ($postAction === 'update_image') {
            $table = (string)($_POST['table'] ?? '');
            $id    = $_POST['id'] ?? '';
            $field = (string)($_POST['field'] ?? '');
            $name  = trim((string)($_POST['new_name'] ?? ''));
            
            $config = getAllowedTableConfig($TABLES, $table);
            if (!$config) {
                throw new RuntimeException('Unbekannte Tabelle');
            }
            if ($name === '') {
                throw new RuntimeException('Kein neuer Dateiname übergeben');
            }
            
            updateImageField($pdo, $table, $config, $id, $field, $name);
            $message = "Feld {$field} in {$table} für ID {$id} wurde aktualisiert.";
            $messageType = 'ok';
        }
    } catch (Throwable $e) {
        $message = $e->getMessage();
        $messageType = 'error';
    }
}

$table = (string)($_GET['table'] ?? $DEFAULT_TABLE);
$config = getAllowedTableConfig($TABLES, $table);
if (!$config) {
    $table = $DEFAULT_TABLE;
    $config = getAllowedTableConfig($TABLES, $table);
}
if (!$config) {
    die('Keine Tabellen-Konfiguration vorhanden.');
}

$page = max(1, (int)($_GET['page'] ?? 1));
$records = fetchRecords($pdo, $table, $config, $page, $PER_PAGE);

$selectedId = $_GET['id'] ?? null;
$selectedField = (string)($_GET['field'] ?? '');
$selectedRecord = null;
$currentValue = '';
$candidates = [];
$searchDir = null;
$targetParsed = null;

if ($selectedId !== null && $selectedId !== '') {
    $selectedRecord = fetchRecordById($pdo, $table, $config, $selectedId);
    
    if ($selectedRecord) {
        if ($selectedField === '' || !in_array($selectedField, $config['image_fields'], true)) {
            foreach ($config['image_fields'] as $f) {
                if (!empty($selectedRecord[$f])) {
                    $selectedField = $f;
                    break;
                }
            }
            if ($selectedField === '' && !empty($config['image_fields'][0])) {
                $selectedField = $config['image_fields'][0];
            }
        }
        
        $currentValue = (string)($selectedRecord[$selectedField] ?? '');
        if ($currentValue !== '') {
            $targetParsed = parseDsn($currentValue, $alias);
            $searchDir = buildSearchDir($IMAGE_ROOT, $targetParsed);
            if ($searchDir !== null) {
                $candidates = findCandidatesInDirectory($searchDir, $targetParsed, $ALLOWED_EXTENSIONS);
            }
        }
    }
}

function isAlreadyMigratedName(string $value): bool
{
    $value = trim($value);
    if ($value === '') {
        return false;
    }
    
    // Ziel-Format: {id}-{datum}-{W|N}-{original}.JPG
    return (bool)preg_match('/^\d+-\d{8}-[WN]-.+\.jpe?g$/i', $value);
}

function hasAnyNonEmptyImageField(array $row, array $imageFields): bool
{
    foreach ($imageFields as $field) {
        if (!empty(trim((string)($row[$field] ?? '')))) {
            return true;
        }
    }
    return false;
}

function allImageFieldsAlreadyMigrated(array $row, array $imageFields): bool
{
    $hasAny = false;
    
    foreach ($imageFields as $field) {
        $val = trim((string)($row[$field] ?? ''));
        
        if ($val === '') {
            continue;
        }
        
        $hasAny = true;
        
        if (!isAlreadyMigratedName($val)) {
            return false;
        }
    }
    
    return $hasAny;
}

function isChangedToday($changedAt): bool
{
    if (empty($changedAt)) {
        return false;
    }
    
    $changed = strtotime((string)$changedAt);
    if ($changed === false) {
        return false;
    }
    
    return date('Y-m-d', $changed) === date('Y-m-d');
}
/*
 |--------------------------------------------------------------------------
 | OPTIONALER DEBUG-BLOCK
 |--------------------------------------------------------------------------
 |
 | Mit ?debug=1 im Browser
 */

$debugInfo = null;
if (isset($_GET['debug']) && $_GET['debug'] === '1') {
    $debugInfo = [
        'IMAGE_ROOT' => $IMAGE_ROOT,
        'realpath_IMAGE_ROOT' => realpath($IMAGE_ROOT),
        'searchDir' => $searchDir,
        'searchDir_exists' => $searchDir ? is_dir($searchDir) : false,
        'record_count' => count($records),
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
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.45; }
        .msg { padding: 10px 12px; border-radius: 6px; margin-bottom: 15px; }
        .ok { background: #e8f7eb; color: #205530; }
        .warn { background: #fff2db; color: #6e4c12; }
        .error { background: #fdeaea; color: #7a1f1f; }
        .wrap { display: grid; grid-template-columns: 380px 1fr; gap: 20px; }
        .box { border: 1px solid #ccc; border-radius: 8px; padding: 15px; background: #fff; }
        .rec { border-top: 1px solid #ddd; padding: 10px 0; }
        .rec:first-child { border-top: none; }
        .cand { display: grid; grid-template-columns: 120px 1fr auto; gap: 15px; border-top: 1px solid #ddd; padding: 12px 0; }
        .cand:first-child { border-top: none; }
        img { max-width: 180px; max-height: 120px; object-fit: contain; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; vertical-align: top; }
        code { background: #f3f3f3; padding: 2px 4px; }
        pre { background:#f6f6f6; padding:12px; overflow:auto; }
        @media (max-width: 900px) { .wrap { grid-template-columns: 1fr; } .cand{grid-template-columns:1fr;} }
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
    <strong>Ausgewählter Suchordner:</strong> <code><?= h((string)$searchDir) ?></code><br>
    <strong>Kandidaten:</strong> <strong><?= count($candidates) ?></strong>
</div>

<form method="post" style="margin-bottom:20px;">
    <input type="hidden" name="post_action" value="update_image">
    <!-- Dieser Form-Block wird nur für Update pro Kandidat benutzt -->
</form>

<div class="wrap">
    <div class="box">
        <h2>Tabelle wählen</h2>

        <form method="get">
            <label>Tabelle:</label><br>
            <select name="table" onchange="this.form.submit()">
                <?php foreach ($TABLES as $tableName => $cfg): ?>
                    <option value="<?= h($tableName) ?>" <?= $table === $tableName ? 'selected' : '' ?>>
                        <?= h($cfg['label'] . ' (' . $tableName . ')') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <h3>Datensätze</h3>

        <?php foreach ($records as $row): ?>
            <div class="rec">
                <strong>ID: <?= h((string)$row[$config['pk']]) ?></strong><br>

                <?php foreach ($config['display'] as $dispField): ?>
                    <div>
                        <small><?= h($dispField) ?>:</small>
                        <?= h((string)($row[$dispField] ?? '')) ?>
                    </div>
                <?php endforeach; ?>

                <?php foreach ($config['image_fields'] as $imgField): ?>
                    <div style="margin-top:8px;">
                        <small><?= h($imgField) ?>:</small>
                        <code><?= h((string)($row[$imgField] ?? '')) ?></code><br>
                        <a href="?table=<?= urlencode($table) ?>&id=<?= urlencode((string)$row[$config['pk']]) ?>&field=<?= urlencode($imgField) ?>">
                            prüfen
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="box">
        <h2>Auswahl / Vorschau</h2>

        <?php if (!$selectedRecord): ?>
            <p>Bitte links einen Datensatz und ein Bildfeld wählen.</p>
        <?php else: ?>
            <p>
                <strong>Tabelle:</strong> <?= h($table) ?><br>
                <strong>ID:</strong> <?= h((string)$selectedRecord[$config['pk']]) ?><br>
                <strong>Feld:</strong> <?= h($selectedField) ?><br>
                <strong>Aktueller Wert:</strong> <code><?= h($currentValue) ?></code>
            </p>

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
            <p>
                <?php if ($searchDir): ?>
                    <code><?= h($searchDir) ?></code>
                <?php else: ?>
                    Kein Suchordner ableitbar.
                <?php endif; ?>
            </p>

            <h3>Aktuelle Bildvorschau</h3>
            <?php if ($ENABLE_IMAGE_PREVIEW && $currentValue && !empty($candidates)): ?>
                <?php
                    $best = $candidates[0];
                ?>
                <div style="margin-bottom:20px;">
                    <img src="<?= h(buildPreviewUrl($best['path'], $scriptName)) ?>" alt=""><br>
                    <small><?= h($best['path']) ?></small>
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
                        <div>
                            <?php if ($ENABLE_IMAGE_PREVIEW): ?>
                                <img src="<?= h(buildPreviewUrl($cand['path'], $scriptName)) ?>" alt="">
                            <?php else: ?>
                                <small>Vorschau aus</small>
                            <?php endif; ?>
                        </div>

                        <div>
                            <strong><?= h($cand['name']) ?></strong><br>
                            Score: <?= h((string)$cand['score']) ?><br>
                            Pfad: <code><?= h($cand['path']) ?></code><br>
                            urh_id: <code><?= h((string)($cand['parsed']['urh_id'] ?? '')) ?></code><br>
                            date: <code><?= h((string)($cand['parsed']['date'] ?? '')) ?></code><br>
                            image_key: <code><?= h((string)($cand['parsed']['image_key'] ?? '')) ?></code>
                        </div>

                        <div>
                            <form method="post">
                                <input type="hidden" name="post_action" value="update_image">
                                <input type="hidden" name="table" value="<?= h($table) ?>">
                                <input type="hidden" name="id" value="<?= h((string)$selectedRecord[$config['pk']]) ?>">
                                <input type="hidden" name="field" value="<?= h($selectedField) ?>">
                                <input type="hidden" name="new_name" value="<?= h($cand['name']) ?>">
                                <button type="submit">Übernehmen</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
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

</body>
</html>
<?php
/** 
 * lädt die Inhalte direkt in die homepage
 */
declare(strict_types=1);

$allowed_pages = ['vorstand', 'impressum', 'dsvgo', 'referate'];
$page = $_GET['seite'] ?? '';

if (!in_array($page, $allowed_pages, true)) {
    http_response_code(400);
    echo '<p>Ungültige Seite angefragt.</p>';
    exit;
}

switch ($page) {
    case 'vorstand':
        include __DIR__ . '/../src/Core/templates/pages/contents/vorstand_content.php';
        break;
    case 'impressum':
        include __DIR__ . '/../src/Core/templates/pages/contents/impressum_content.php';
        break;
    case 'dsvgo':
        include __DIR__ . '/../src/Core/templates/pages/contents/dsvgo_content.php';
        break;
    case 'referate':
        include __DIR__ . '/../src/Core/templates/pages/contents/referate_content.php';
        break;
}

<?php

declare(strict_types=1);

namespace FWHist\Modules\Oeffentlichkeitsarbeit\Lists;

use FWHist\Services\ListInterface;
use FWHist\Services\TableColumnMetadata;
use PDO;

final class ALList implements ListInterface
{
    public static function getListId(): string
    {
        return 'AL';
    }

    public function getColumns(PDO $pdo, ?string $project = null, ?int $selId = null, ?bool $editProtect = false): array
    {
        $meta = new TableColumnMetadata($pdo, 'fharch_new', false);
        $meta->getColumnsForTables(['oe_archlinks']);
        $comments = $meta->getCommentsMap();

        $fields = $editProtect ? ['fa_text', 'fa_link'] : ['fa_id', 'fa_text', 'fa_link'];
        $columns = [];
        foreach ($fields as $field) {
            $columns[] = [
                'title' => $comments[$field] ?? ucfirst($field),
                'field' => $field,
                'headerSort' => false,
                'formatter' => $field === 'fa_link' ? 'html' : 'plaintext',
                ...($field === 'fa_id' ? ['width' => 80, 'hozAlign' => 'center'] : []),
            ];
        }

        return $columns;
    }

    public function getData(PDO $pdo, ?string $project = null, ?int $selId = null, ?bool $editProtect = false): array
    {
        $statement = $pdo->query('SELECT * FROM oe_archlinks ORDER BY fa_text');
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as &$row) {
            $id = (int)($row['fa_id'] ?? 0);
            $label = $editProtect ? 'Anzeigen' : 'Bearbeiten';
            $row['action'] = "<a class='btn action-link' href='ArLinkEdit.php?ID={$id}'>{$label}</a>";

            $row['fa_link'] = $this->renderLink((string)($row['fa_link'] ?? ''));
            unset(
                $row['fa_url_chkd'],
                $row['fa_url_obsolete'],
                $row['fa_changed_id'],
                $row['fa_changed-id'],
                $row['fa_changed_at']
            );
        }
        unset($row);

        return $rows;
    }

    public function getFilters(PDO $pdo, ?string $project = null, ?int $selId = null, ?bool $editProtect = false): array
    {
        $meta = new TableColumnMetadata($pdo, 'fharch_new', false);
        $meta->getColumnsForTables(['oe_archlinks']);
        $comments = $meta->getCommentsMap();
        $label = trim((string)($comments['fa_text'] ?? ''));

        return [
            'selectFilters' => [],
            'textFilters' => ['fa_text'],
            'textFilterLabels' => ['fa_text' => $label !== '' ? $label : 'Beschreibung'],
        ];
    }

    private function renderLink(string $link): string
    {
        $link = trim($link);
        if ($link === '') {
            return '';
        }

        if (!preg_match('~^https?://~i', $link)) {
            $link = 'http://' . $link;
        }

        $safeUrl = htmlspecialchars($link, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeLabel = htmlspecialchars($link, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return "<a href='{$safeUrl}' target='_blank' rel='noopener noreferrer'>{$safeLabel}</a>";
    }
}

<?php

declare(strict_types=1);

namespace FWHist\Modules\Oeffentlichkeitsarbeit\Lists;

use FWHist\Services\ListInterface;
use FWHist\Services\TableColumnMetadata;
use PDO;

final class ZTList implements ListInterface
{
    public static function getListId(): string
    {
        return 'ZT';
    }

    public function getColumns(PDO $pdo, ?string $project = null, ?int $selId = null, ?bool $editProtect = false): array
    {
        $meta = new TableColumnMetadata($pdo, 'fharch_new', false);
        $meta->getColumnsForTables(['oe_zeitungen']);
        $comments = $meta->getCommentsMap();

        // Keep the contents link next to the common action column, as in the old list.
        $columns = [
            [
                'title' => 'Inhalt',
                'field' => 'inhalt',
                'width' => 100,
                'hozAlign' => 'center',
                'headerSort' => false,
                'formatter' => 'html',
            ],
        ];

        foreach (['zt_id', 'zt_name', 'zt_herausg', 'zt_internet', 'zt_email', 'zt_erstausgabe'] as $field) {
            $column = [
                'title' => $comments[$field] ?? ucfirst($field),
                'field' => $field,
                'headerSort' => false,
                'formatter' => $field === 'zt_internet' ? 'html' : 'plaintext',
            ];
            if ($field === 'zt_id') {
                $column['width'] = 80;
                $column['hozAlign'] = 'center';
            } elseif (in_array($field, ['zt_name', 'zt_herausg'], true)) {
                $column['minWidth'] = 180;
            }
            $columns[] = $column;
        }

        return $columns;
    }

    public function getData(PDO $pdo, ?string $project = null, ?int $selId = null, ?bool $editProtect = false): array
    {
        $statement = $pdo->query('SELECT * FROM oe_zeitungen ORDER BY zt_id');
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as &$row) {
            $id = (int)($row['zt_id'] ?? 0);
            $label = $editProtect ? 'Anzeigen' : 'Bearbeiten';
            $row['action'] = "<a class='btn action-link' href='ZeitungEdit.php?ID={$id}'>{$label}</a>";

            $row['inhalt'] = "<a class='btn action-link' href='Z_InhalteList.php?ID={$id}'>Inhalt</a>";
            $row['zt_internet'] = $this->renderWebsiteLink((string)($row['zt_internet'] ?? ''));
        }
        unset($row);

        return $rows;
    }

    public function getFilters(PDO $pdo, ?string $project = null, ?int $selId = null, ?bool $editProtect = false): array
    {
        $meta = new TableColumnMetadata($pdo, 'fharch_new', false);
        $meta->getColumnsForTables(['oe_zeitungen']);
        $comments = $meta->getCommentsMap();
        $fallbackLabels = [
            'zt_name' => 'Zeitung',
            'zt_herausg' => 'Herausgeber',
            'zt_erstausgabe' => 'Erstausgabe',
        ];
        $labels = [];
        foreach ($fallbackLabels as $field => $fallbackLabel) {
            $label = trim((string)($comments[$field] ?? ''));
            $labels[$field] = $label !== '' ? $label : $fallbackLabel;
        }

        return [
            'selectFilters' => [],
            'textFilters' => array_keys($fallbackLabels),
            'textFilterLabels' => $labels,
        ];
    }

    private function renderWebsiteLink(string $website): string
    {
        $website = trim($website);
        if ($website === '') {
            return '';
        }

        $url = preg_match('~^https?://~i', $website) === 1 ? $website : 'http://' . $website;
        $safeUrl = htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeLabel = htmlspecialchars($website, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return "<a href='{$safeUrl}' target='_blank' rel='noopener noreferrer'>{$safeLabel}</a>";
    }
}

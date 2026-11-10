<?php

declare(strict_types=1);

namespace FWHist\Modules\Oeffentlichkeitsarbeit\Lists;

use FWHist\Services\ListInterface;
use FWHist\Services\TableColumnMetadata;
use PDO;

final class MPList implements ListInterface
{
    private const IMAGE_EXTENSIONS = ['gif', 'ico', 'jpeg', 'jpg', 'png', 'tiff'];
    private const IMAGE_PATH = '../../../../FWHist/login/AOrd_Verz/Marktplatz/';

    public static function getListId(): string
    {
        return 'MP';
    }

    public function getColumns(PDO $pdo, ?string $project = null, ?int $selId = null, ?bool $editProtect = false): array
    {
        $meta = new TableColumnMetadata($pdo, 'fharch_new', false);
        $meta->getColumnsForTables(['oe_marktplatz']);
        $comments = $meta->getCommentsMap();

        $columns = [];
        foreach (['bs_id', 'bs_startdatum', 'bs_kurztext', 'bs_text', 'bs_email_1', 'bs_bild_1', 'bs_bild_2'] as $field) {
            $column = [
                'title' => $comments[$field] ?? ucfirst($field),
                'field' => $field,
                'headerSort' => false,
                'formatter' => 'plaintext',
            ];

            if ($field === 'bs_id') {
                $column['width'] = 80;
                $column['hozAlign'] = 'center';
            } elseif  ($field === 'bs_startdatum') {
                $column['width'] = 100;
                $column['hozAlign'] = 'center';
            } elseif (in_array($field, ['bs_bild_1', 'bs_bild_2'], true)) {
                $column['formatter'] = 'html';
            } elseif ($field === 'bs_text' || $field === 'bs_kurztext' ) {
                $column['minWidth'] = 250;
                $column['formatter'] = 'textarea';
            }

            $columns[] = $column;
        }

        return $columns;
    }

    public function getData(PDO $pdo, ?string $project = null, ?int $selId = null, ?bool $editProtect = false): array
    {
        $statement = $pdo->query('SELECT * FROM oe_marktplatz ORDER BY bs_id');
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
        $today = date('Y-m-d');
        $visibleRows = [];

        foreach ($rows as $row) {
            $endDate = trim((string)($row['bs_enddatum'] ?? ''));
            if ($endDate !== '' && $endDate <= $today) {
                continue;
            }

            $id = (int)($row['bs_id'] ?? 0);
            $label = $editProtect ? 'Anzeigen' : 'Bearbeiten';
            $row['action'] = "<a class='btn action-link' href='MarktplEdit.php?ID={$id}'>{$label}</a>";

            $emails = array_filter([
                trim((string)($row['bs_email_1'] ?? '')),
                trim((string)($row['bs_email_2'] ?? '')),
            ], static fn(string $email): bool => $email !== '');
            $row['bs_email_1'] = implode(', ', $emails);

            foreach (['bs_bild_1', 'bs_bild_2'] as $field) {
                $row[$field] = $this->renderFileLink((string)($row[$field] ?? ''));
            }

            unset(
                $row['bs_email_2'],
                $row['bs_bild_3'],
                $row['bs_bild_4'],
                $row['bs_changed_id'],
                $row['bs_changed_at']
            );
            $visibleRows[] = $row;
        }

        return $visibleRows;
    }

    public function getFilters(PDO $pdo, ?string $project = null, ?int $selId = null, ?bool $editProtect = false): array
    {
        $meta = new TableColumnMetadata($pdo, 'fharch_new', false);
        $meta->getColumnsForTables(['oe_marktplatz']);
        $comments = $meta->getCommentsMap();

        $fallbackLabels = [
            'bs_kurztext' => 'Kurztext',
            'bs_text' => 'Beschreibung',
            'bs_email_1' => 'E-Mail',
        ];
        $textFilterLabels = [];
        foreach ($fallbackLabels as $field => $fallbackLabel) {
            $label = trim((string)($comments[$field] ?? ''));
            $textFilterLabels[$field] = $label !== '' ? $label : $fallbackLabel;
        }

        return [
            'selectFilters' => [],
            'textFilters' => array_keys($fallbackLabels),
            'textFilterLabels' => $textFilterLabels,
        ];
    }

    private function renderFileLink(string $filename): string
    {
        $filename = basename(trim($filename));
        if ($filename === '') {
            return '';
        }

        $safeFilename = htmlspecialchars($filename, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $url = htmlspecialchars(self::IMAGE_PATH . rawurlencode($filename), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($extension, self::IMAGE_EXTENSIONS, true)) {
            return "<a href='{$url}' target='_blank' rel='noopener noreferrer'>"
                . "<img src='{$url}' alt='{$safeFilename}' height='200'></a>";
        }

        return "<a href='{$url}' target='_blank' rel='noopener noreferrer'>{$safeFilename}</a>";
    }
}

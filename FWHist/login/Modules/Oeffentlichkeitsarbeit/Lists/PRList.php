<?php

declare(strict_types=1);

namespace FWHist\Modules\Oeffentlichkeitsarbeit\Lists;

use FWHist\Services\ListInterface;
use FWHist\Services\TableColumnMetadata;
use PDO;

final class PRList implements ListInterface
{
    private const IMAGE_EXTENSIONS = ['gif', 'ico', 'jpeg', 'jpg', 'png', 'tiff'];
    private const IMAGE_PATH = '../../../login/AOrd_Verz/Presse/';

    public static function getListId(): string
    {
        return 'PR';
    }

    public function getColumns(PDO $pdo, ?string $project = null, ?int $selId = null, ?bool $editProtect = false): array
    {
        $meta = new TableColumnMetadata($pdo, 'fharch_new', false);
        $meta->getColumnsForTables(['oe_presse']);
        $comments = $meta->getCommentsMap();

        $columns = [];
        foreach (['pr_id', 'pr_datum', 'pr_name', 'pr_ausg', 'pr_teaser', 'pr_text', 'pr_bild_1', 'pr_bild_2'] as $field) {
            $column = [
                'title' => $comments[$field] ?? ucfirst($field),
                'field' => $field,
                'headerSort' => in_array($field, ['pr_id', 'pr_datum', 'pr_name'], true),
                'formatter' => 'plaintext',
            ];

            if ($field === 'pr_id') {
                $column['width'] = 80;
                $column['hozAlign'] = 'center';
            } elseif ($field === 'pr_datum' || $field === 'pr_ausg' ) {
                $column['width'] = 80;
                $column['hozAlign'] = 'center';
            } elseif ($field === 'pr_name') {
                $column['minWidth'] = 180;
                $column['formatter'] = 'html';
            } elseif (in_array($field, ['pr_teaser', 'pr_text'], true)) {
                $column['minWidth'] = 220;
                $column['formatter'] = 'textarea';
                $column['variableHeight'] = true;
            } elseif (in_array($field, ['pr_bild_1', 'pr_bild_2'], true)) {
                $column['minWidth'] = 120;
                $column['formatter'] = 'html';
            }

            $columns[] = $column;
        }

        return $columns;
    }

    public function getData(PDO $pdo, ?string $project = null, ?int $selId = null, ?bool $editProtect = false): array
    {
        $statement = $pdo->query('SELECT * FROM oe_presse ORDER BY pr_id');
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as &$row) {
            $id = (int)($row['pr_id'] ?? 0);
            $label = $editProtect ? 'Anzeigen' : 'Bearbeiten';
            $row['action'] = "<a class='btn action-link' href='PresseEdit.php?ID={$id}'>{$label}</a>";

            $name = htmlspecialchars((string)($row['pr_name'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            if (($row['pr_medium'] ?? '') === 'URL') {
                if (trim((string)($row['pr_text'] ?? '')) === '') {
                    $row['pr_text'] = $row['pr_web_text'] ?? '';
                }
                $webPage = trim((string)($row['pr_web_seite'] ?? ''));
                if ($webPage !== '') {
                    $name .= '<br>' . htmlspecialchars($webPage, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                }
            }
            $row['pr_name'] = $name;

            foreach (['pr_bild_1', 'pr_bild_2'] as $field) {
                $row[$field] = $this->renderFileLink((string)($row[$field] ?? ''));
            }

            unset(
                $row['pr_bild_3'],
                $row['pr_bild_4'],
                $row['pr_bild_5'],
                $row['pr_bild_6'],
                $row['pr_url_chkd'],
                $row['pr_url_obsolete'],
                $row['pr_changed-id'],
                $row['pr_changed_at'],
                $row['pr_web_seite'],
                $row['pr_web_text'],
                $row['pr_medium']
            );
        }
        unset($row);

        return $rows;
    }

    public function getFilters(PDO $pdo, ?string $project = null, ?int $selId = null, ?bool $editProtect = false): array
    {
        $meta = new TableColumnMetadata($pdo, 'fharch_new', false);
        $meta->getColumnsForTables(['oe_presse']);
        $comments = $meta->getCommentsMap();
        $fallbackLabels = [
            'pr_datum' => 'Datum',
            'pr_name' => 'Name',
            'pr_ausg' => 'Ausgabe',
            'pr_teaser' => 'Teaser',
            'pr_text' => 'Text',
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

    private function renderFileLink(string $filename): string
    {
        $filename = basename(trim($filename));
        if ($filename === '') {
            return '';
        }

        $safeFilename = htmlspecialchars($filename, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $url = htmlspecialchars(self::IMAGE_PATH . rawurlencode($filename), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        if (in_array(strtolower(pathinfo($filename, PATHINFO_EXTENSION)), self::IMAGE_EXTENSIONS, true)) {
            return "<a href='{$url}' target='_blank' rel='noopener noreferrer'>"
                . "<img src='{$url}' alt='{$safeFilename}' height='200'></a>";
        }

        return "<a href='{$url}' target='_blank' rel='noopener noreferrer'>{$safeFilename}</a>";
    }
}

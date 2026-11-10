<?php

declare(strict_types=1);

namespace FWHist\Modules\Oeffentlichkeitsarbeit\Lists;

use FWHist\Services\ListInterface;
use FWHist\Services\TableColumnMetadata;
use PDO;

final class TEList implements ListInterface
{
    private const IMAGE_EXTENSIONS = ['gif', 'ico', 'jpeg', 'jpg', 'png', 'tiff'];
    private const IMAGE_PATH = '../../../../FWHist/login/AOrd_Verz/Termine/';

    public static function getListId(): string
    {
        return 'TE';
    }

    public function getColumns(PDO $pdo, ?string $project = null, ?int $selId = null, ?bool $editProtect = false): array
    {
        $meta = new TableColumnMetadata($pdo, 'fharch_new', false);
        $meta->getColumnsForTables(['oe_va_termine']);
        $comments = $meta->getCommentsMap();

        $columns = [];
        foreach (['va_id', 'va_datum', 'va_titel', 'va_beschr', 'va_bild_1', 'va_bild_2'] as $field) {
            $column = [
                'title' => $comments[$field] ?? ucfirst($field),
                'field' => $field,
                'headerSort' => $field === 'va_datum' || $field === 'va_titel',
                'formatter' => 'plaintext',
            ];

            if ($field === 'va_id') {
                $column['width'] = 80;
                $column['hozAlign'] = 'center';
            } elseif (in_array($field, ['va_datum' ], true)) {
                $column['width'] = 120;
                $column['hozAlign'] = 'center';
            } elseif (in_array($field, ['va_bild_1', 'va_bild_2'], true)) {
                $column['formatter'] = 'html';
            } elseif (in_array($field, ['va_titel', 'va_beschr'], true)) {
                $column['minWidth'] = 220;
                $column['variableHeight'] = true;
                $column['formatter'] = 'textarea';
            }

            $columns[] = $column;
        }

        return $columns;
    }

    public function getData(PDO $pdo, ?string $project = null, ?int $selId = null, ?bool $editProtect = false): array
    {
        $statement = $pdo->query('SELECT * FROM oe_va_termine ORDER BY va_datum, va_id');
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as &$row) {
            $id = (int)($row['va_id'] ?? 0);
            $label = $editProtect ? 'Anzeigen' : 'Bearbeiten';
            $row['action'] = "<a class='btn action-link' href='TerminEdit.php?ID={$id}'>{$label}</a>";

            $date = trim((string)($row['va_datum'] ?? ''));
            $year = preg_match('/^\d{4}/', $date, $matches) ? $matches[0] : '0000';
            foreach (['va_bild_1', 'va_bild_2'] as $field) {
                $row[$field] = $this->renderFileLink((string)($row[$field] ?? ''), $year);
            }

            unset(
                $row['va_admin_email'],
                $row['va_abschluss_id'],
                $row['va_akt_pl'],
                $row['va_anm_erf'],
                $row['va_anm_text'],
                $row['va_anmeld_end'],
                $row['va_anz_anmeld'],
                $row['va_bdld'],
                $row['va_staat'],
                $row['va_kateg'],
                $row['va_link_einladung'],
                $row['va_ort'],
                $row['va_plaetze'],
                $row['va_url_chkd'],
                $row['va_va_url_obsolete'],
                $row['va_raum'],
                $row['va_umfang'],
                $row['va_warte'],
                $row['va_warte_pl'],
                $row['va_plz'],
                $row['va_bild_3'],
                $row['va_bild_4'],
                $row['va_freigabe_id'],
                $row['va_freigabe_at'],
                $row['va_abschluss_at'],
                $row['va_storno_id'],
                $row['va_storno_at'],
                $row['va_created_id'],
                $row['va_created_at'],
                $row['va_changed_id'],
                $row['va_changed_at']
            );
        }
        unset($row);

        return $rows;
    }

    public function getFilters(PDO $pdo, ?string $project = null, ?int $selId = null, ?bool $editProtect = false): array
    {
        $meta = new TableColumnMetadata($pdo, 'fharch_new', false);
        $meta->getColumnsForTables(['oe_va_termine']);
        $comments = $meta->getCommentsMap();
        $fallbackLabels = [
            'va_datum' => 'Datum',
            'va_titel' => 'Titel',
            'va_beschr' => 'Beschreibung',
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

    private function renderFileLink(string $filename, string $year): string
    {
        $filename = basename(trim($filename));
        if ($filename === '') {
            return '';
        }

        $safeFilename = htmlspecialchars($filename, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $url = htmlspecialchars(
            self::IMAGE_PATH . rawurlencode($year) . '/' . rawurlencode($filename),
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
        );
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($extension, self::IMAGE_EXTENSIONS, true)) {
            return "<a href='{$url}' target='_blank' rel='noopener noreferrer'>"
                . "<img src='{$url}' alt='{$safeFilename}' height='200'></a>";
        }

        return "<a href='{$url}' target='_blank' rel='noopener noreferrer'>{$safeFilename}</a>";
    }
}

<?php

declare(strict_types=1);

namespace FWHist\Modules\Oeffentlichkeitsarbeit\Lists;

use FWHist\Services\ListInterface;
use FWHist\Services\TableColumnMetadata;
use PDO;

final class DOList implements ListInterface
{
    private const DOCUMENT_PATH = '../../../login/AOrd_Verz/Dokumente/';

    public static function getListId(): string
    {
        return 'DO';
    }

    public function getColumns(PDO $pdo, ?string $project = null, ?int $selId = null, ?bool $editProtect = false): array
    {
        $meta = new TableColumnMetadata($pdo, 'fharch_new', false);
        $meta->getColumnsForTables(['oe_dokumente']);
        $comments = $meta->getCommentsMap();

        $columns = [];
        foreach (['dk_id', 'dk_thema', 'dk_titel', 'dk_author', 'dk_dsn_1'] as $field) {
            $column = [
                'title' => $comments[$field] ?? ucfirst($field),
                'field' => $field,
                'headerSort' => false,
                'formatter' => 'plaintext',
            ];

            if ($field === 'dk_id') {
                $column['width'] = 80;
                $column['hozAlign'] = 'center';
            } elseif (in_array($field, ['dk_titel', 'dk_author', 'dk_dsn_1'], true)) {
                $column['formatter'] = 'html';
            }

            if (in_array($field, ['dk_titel', 'dk_author'], true)) {
                $column['minWidth'] = 180;
                $column['variableHeight'] = true;
                $column['formatter'] = 'textarea';
            }

            $columns[] = $column;
        }

        return $columns;
    }

    public function getData(PDO $pdo, ?string $project = null, ?int $selId = null, ?bool $editProtect = false): array
    {
        $statement = $pdo->query('SELECT * FROM oe_dokumente ORDER BY dk_id');
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as &$row) {
            $id = (int)($row['dk_id'] ?? 0);
            $label = $editProtect ? 'Anzeigen' : 'Bearbeiten';
            $row['action'] = "<a class='btn action-link' href='DokuEdit.php?ID={$id}'>{$label}</a>";

            $path = trim((string)($row['dk_path2dsn'] ?? ''), "/\\");
            $documents = [];
            foreach (['dk_dsn_1', 'dk_dsn_2', 'dk_dsn_3', 'dk_dsn_4'] as $field) {
                $filename = basename(trim((string)($row[$field] ?? '')));
                if ($filename === '') {
                    continue;
                }
                $documents[] = $this->renderDocumentLink($path, $filename);
            }
            $row['dk_dsn_1'] = implode('<br>', $documents);

            unset(
                $row['dk_dsn_2'],
                $row['dk_dsn_3'],
                $row['dk_dsn_4'],
                $row['dk_jahrgang'],
                $row['dk_jahr'],
                $row['dk_nr'],
                $row['dk_ssg'],
                $row['dk_gruppe'],
                $row['dk_titelerw'],
                $row['dk_tel'],
                $row['dk_fax'],
                $row['dk_seite'],
                $row['dk_spalte'],
                $row['dk_fwehr'],
                $row['dk_path2dsn'],
                $row['dk_changed-id'],
                $row['dk_changed_at']
            );
        }
        unset($row);

        return $rows;
    }

    public function getFilters(PDO $pdo, ?string $project = null, ?int $selId = null, ?bool $editProtect = false): array
    {
        $meta = new TableColumnMetadata($pdo, 'fharch_new', false);
        $meta->getColumnsForTables(['oe_dokumente']);
        $comments = $meta->getCommentsMap();

        $fallbackLabels = [
            'dk_thema' => 'Thema',
            'dk_titel' => 'Titel',
            'dk_author' => 'Autor',
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

    private function renderDocumentLink(string $path, string $filename): string
    {
        $pathSegments = array_filter(
            explode('/', str_replace('\\', '/', $path)),
            static fn(string $segment): bool => $segment !== '' && $segment !== '.' && $segment !== '..'
        );
        $relativePath = $pathSegments === []
            ? ''
            : implode('/', array_map('rawurlencode', $pathSegments)) . '/';
        $url = htmlspecialchars(
            self::DOCUMENT_PATH . $relativePath . rawurlencode($filename),
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
        );
        $safeFilename = htmlspecialchars($filename, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return "<a href='{$url}' target='_blank' rel='noopener noreferrer'>{$safeFilename}</a>";
    }
}

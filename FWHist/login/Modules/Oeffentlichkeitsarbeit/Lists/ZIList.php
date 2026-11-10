<?php

declare(strict_types=1);

namespace FWHist\Modules\Oeffentlichkeitsarbeit\Lists;

use FWHist\Services\ListInterface;
use FWHist\Services\TableColumnMetadata;
use InvalidArgumentException;
use PDO;

final class ZIList implements ListInterface
{
    private const FIELDS = ['ih_id', 'ih_jahr', 'ih_kateg', 'ih_sg', 'ih_titel', 'ih_autor'];

    public static function getListId(): string
    {
        return 'ZI';
    }

    public function getColumns(PDO $pdo, ?string $project = null, ?int $selId = null, ?bool $editProtect = false): array
    {
        $tableName = $this->getTableName($selId);
        $meta = new TableColumnMetadata($pdo, 'fharch_new', false);
        $meta->getColumnsForTables([$tableName]);
        $comments = $meta->getCommentsMap();

        $columns = [];
        foreach (self::FIELDS as $field) {
            $column = [
                'title' => $comments[$field] ?? ucfirst($field),
                'field' => $field,
                'headerSort' => $field === 'ih_id' || $field === 'ih_jahr',
                'formatter' => 'plaintext',
            ];
            if ($field === 'ih_id') {
                $column['width'] = 80;
                $column['hozAlign'] = 'center';
            } elseif (in_array($field, ['ih_titel', 'ih_autor'], true)) {
                $column['minWidth'] = 180;
                $column['variableHeight'] = true;
            }
            $columns[] = $column;
        }

        return $columns;
    }

    public function getData(PDO $pdo, ?string $project = null, ?int $selId = null, ?bool $editProtect = false): array
    {
        $tableName = $this->getTableName($selId);
        $statement = $pdo->query("SELECT * FROM `{$tableName}` ORDER BY ih_id");
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as &$row) {
            $id = (int)($row['ih_id'] ?? 0);
            $label = $editProtect ? 'Anzeigen' : 'Bearbeiten';
            $row['action'] = "<a class='btn action-link' href='Z_InhalteEdit.php?ID={$id}&amp;ztNr={$selId}'>{$label}</a>";

            $visibleFields = self::FIELDS;
            $visibleFields[] = 'action';
            $row = array_intersect_key($row, array_flip($visibleFields));
        }
        unset($row);

        return $rows;
    }

    public function getFilters(PDO $pdo, ?string $project = null, ?int $selId = null, ?bool $editProtect = false): array
    {
        $tableName = $this->getTableName($selId);
        $meta = new TableColumnMetadata($pdo, 'fharch_new', false);
        $meta->getColumnsForTables([$tableName]);
        $comments = $meta->getCommentsMap();

        $fallbackLabels = [
            'ih_jahr' => 'Jahr',
            'ih_kateg' => 'Kategorie',
            'ih_sg' => 'Sachgebiet',
            'ih_titel' => 'Titel',
            'ih_autor' => 'Autor',
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

    private function getTableName(?int $selId): string
    {
        if ($selId === null || $selId < 1) {
            throw new InvalidArgumentException('Eine gültige Zeitungs-ID (sel_id) ist erforderlich.');
        }

        return 'oe_zeitung_' . $selId;
    }
}

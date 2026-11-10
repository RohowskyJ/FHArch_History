<?php

declare(strict_types=1);

namespace FWHist\Modules\Mitglieder\Lists;

use FWHist\Services\ListInterface;
use FWHist\Services\TableColumnMetadata;
use PDO;

final class UNList implements ListInterface
{
    private const TABLE = 'mi_unterstuetzer';

    private const COLUMNS = [
        'fu_id',
        'fu_kateg',
        'fu_aktiv',
        'fu_weihn_post',
        'fu_orgname',
        'fu_anrede',
        'fu_display_name',
        'fu_display_address',
        'fu_tel',
        'fu_email',
    ];

    public static function getListId(): string
    {
        return 'UN';
    }

    public function getColumns(PDO $pdo, ?string $project = null, ?int $selId = null, ?bool $editProtect = false): array
    {
        $meta = new TableColumnMetadata($pdo, 'fharch_new', false);
        $meta->getColumnsForTables([self::TABLE]);
        $comments = $meta->getCommentsMap();
        $labels = [
            'fu_id' => 'Nr.',
            'fu_kateg' => 'Kategorie',
            'fu_aktiv' => 'Aktiv',
            'fu_weihn_post' => 'Weihnachtspost',
            'fu_display_name' => 'Name',
            'fu_display_address' => 'Anschrift',
        ];
        $columns = [];

        foreach (self::COLUMNS as $field) {
            $sourceField = match ($field) {
                'fu_display_name' => 'fu_name',
                'fu_display_address' => 'fu_adresse',
                default => $field,
            };
            $column = [
                'title' => $labels[$field] ?? $comments[$sourceField] ?? ucfirst($sourceField),
                'field' => $field,
                'headerSort' => true,
                'formatter' => 'plaintext',
            ];

            if ($field === 'fu_id') {
                $column['width'] = 75;
                $column['hozAlign'] = 'center';
            } elseif (in_array($field, ['fu_kateg', 'fu_aktiv', 'fu_weihn_post'], true)) {
                $column['width'] = 125;
            } elseif (in_array($field, ['fu_display_name', 'fu_display_address', 'fu_orgname'], true)) {
                $column['minWidth'] = 180;
            } elseif ($field === 'fu_email') {
                $column['minWidth'] = 180;
            }

            $columns[] = $column;
        }

        return $columns;
    }

    public function getData(PDO $pdo, ?string $project = null, ?int $selId = null, ?bool $editProtect = false): array
    {
        $statement = $pdo->query('SELECT * FROM `' . self::TABLE . '` ORDER BY fu_name ASC, fu_vname ASC');
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as &$row) {
            $id = (int)($row['fu_id'] ?? 0);
            $row['action'] = "<a class='action-link' href='UnterstEdit.php?ID={$id}'>Bearbeiten</a>";

            $firstName = trim((string)($row['fu_vname'] ?? ''));
            $lastName = trim((string)($row['fu_name'] ?? ''));
            $titles = trim(implode(' ', array_filter([
                trim((string)($row['fu_tit_vor'] ?? '')),
                trim((string)($row['fu_tit_nach'] ?? '')),
            ])));
            $displayName = trim($lastName . ($firstName !== '' ? ', ' . $firstName : ''));
            if ($titles !== '') {
                $displayName .= ' (' . $titles . ')';
            }
            $row['fu_display_name'] = $displayName;

            $postalTown = trim(
                trim((string)($row['fu_plz'] ?? '')) . ' ' . trim((string)($row['fu_ort'] ?? ''))
            );
            $row['fu_display_address'] = implode(', ', array_filter(
                [$postalTown, trim((string)($row['fu_adresse'] ?? ''))],
                static fn(string $part): bool => $part !== ''
            ));
        }
        unset($row);

        return $rows;
    }

    public function getFilters(PDO $pdo, ?string $project = null, ?int $selId = null, ?bool $editProtect = false): array
    {
        return [
            'selectFilters' => [
                'fu_aktiv' => [
                    'label' => 'Aktivitätsstatus',
                    'options' => [
                        'J' => 'Aktive Unterstützer',
                        'N' => 'Inaktive Unterstützer',
                    ],
                ],
                'fu_weihn_post' => [
                    'label' => 'Weihnachtspost',
                    'options' => [
                        'J' => 'Für Versand vorgesehen',
                        'N' => 'Nicht vorgesehen',
                    ],
                ],
                'fu_kateg' => [
                    'label' => 'Kategorie',
                    'options' => [
                        'BP' => 'Besondere Persönlichkeiten',
                        'FA' => 'Zeitschriften',
                        'FF' => 'Befreundete Feuerwehren',
                        'FK' => 'Befreundete Kameraden',
                        'FG' => 'Landes-SB',
                        'SP' => 'Sponsoren',
                    ],
                ],
            ],
            'textFilters' => ['fu_name', 'fu_vname', 'fu_ort', 'fu_orgname', 'fu_email'],
            'textFilterLabels' => [
                'fu_name' => 'Nachname',
                'fu_vname' => 'Vorname',
                'fu_ort' => 'Ort',
                'fu_orgname' => 'Organisation',
                'fu_email' => 'E-Mail',
            ],
        ];
    }
}

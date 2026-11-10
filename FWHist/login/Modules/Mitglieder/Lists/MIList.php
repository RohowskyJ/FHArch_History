<?php

declare(strict_types=1);

namespace FWHist\Modules\Mitglieder\Lists;

use FWHist\Services\ListInterface;
use FWHist\Services\TableColumnMetadata;
use PDO;

final class MIList implements ListInterface
{
    private const TABLE = 'mi_mitglieder';

    private const COLUMNS = [
        'mi_id',
        'mi_mtyp',
        'mi_org_typ',
        'mi_org_name',
        'mi_display_name',
        'mi_display_address',
        'mi_gebtag_display',
        'mi_email',
        'mi_tel_handy',
        'mi_ref_int_2',
        'mi_status',
    ];

    public static function getListId(): string
    {
        return 'MI';
    }

    public function getColumns(PDO $pdo, ?string $project = null, ?int $selId = null, ?bool $editProtect = false): array
    {
        $meta = new TableColumnMetadata($pdo, 'fharch_new', false);
        $meta->getColumnsForTables([self::TABLE]);
        $comments = $meta->getCommentsMap();
        $labels = [
            'mi_display_name' => 'Name',
            'mi_display_address' => 'Anschrift',
            'mi_gebtag_display' => 'Geburtstag - Alter',
            'mi_status' => 'Mitgliedsstatus',
        ];

        $columns = [];
        foreach (self::COLUMNS as $field) {
            if ($field === 'mi_status') {
                continue;
            }

            $sourceField = match ($field) {
                'mi_display_name' => 'mi_name',
                'mi_display_address' => 'mi_anschr',
                'mi_gebtag_display' => 'mi_gebtag',
                default => $field,
            };
            $column = [
                'title' => $labels[$field] ?? $comments[$sourceField] ?? ucfirst($sourceField),
                'field' => $field,
                'headerSort' => false,
                'formatter' => $field === 'mi_gebtag_display' ? 'html' : 'plaintext',
            ];

            if ($field === 'mi_id') {
                $column['width'] = 90;
                $column['hozAlign'] = 'center';
                $column['headerSort'] = true;
            } elseif (in_array($field, ['mi_display_name', 'mi_display_address'], true)) {
                $column['minWidth'] = 180;
                $column['headerSort'] = true;
            } elseif ($field === 'mi_mtyp') {
                $column['minWidth'] = 120;
            } elseif ($field === 'mi_org_typ') {
                $column['width'] = 90;
            } elseif ($field === 'mi_ref_int_2') {
                $column['title'] = 'Int.Referate';
                $column['width'] = 70;
            }

            $columns[] = $column;
        }

        return $columns;
    }

    public function getData(PDO $pdo, ?string $project = null, ?int $selId = null, ?bool $editProtect = false): array
    {
        /**
         * Definition der Konstante der Mitglieder- Art
         *
         * @var array M_Typ
         */
        $M_Typ = array(
            "UM" => "Unterstützendes Mitglied",
            "FG" => "Sachbearbeiter Feuerwehrgeschichte",
            "EM" => "Ehrenmitglied",
            "OE" => "Ohne Erlagschein - Fahrzeughalter"
        );
        
        $statement = $pdo->query('SELECT * FROM `' . self::TABLE . '` ORDER BY mi_name ASC, mi_vname ASC');
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
        $currentYear = (int)date('Y');

        foreach ($rows as &$row) {
            $id = (int)($row['mi_id'] ?? 0);
            $row['action'] = "<a class='btn action-link' href='MitglEdit.php?ID={$id}'>Bearbeiten</a>";

            // Prüfen, ob $row['mi_mtyp'] nicht leer ist und ob der Schlüssel im Array existiert
            if (!empty($row['mi_mtyp']) && isset($M_Typ[$row['mi_mtyp']])) {
                // Ersetzen des K?rzels durch den entsprechenden Text
                $row['mi_mtyp'] = $M_Typ[$row['mi_mtyp']];
            }
            
            $lastName = trim((string)($row['mi_name'] ?? ''));
            $firstName = trim((string)($row['mi_vname'] ?? ''));
            $titles = trim(implode(' ', array_filter([
                trim((string)($row['mi_titel'] ?? '')),
                trim((string)($row['mi_n_titel'] ?? '')),
            ])));
            $displayName = trim($lastName . ($firstName !== '' ? ', ' . $firstName : ''));
            if ($titles !== '') {
                $displayName .= ' (' . $titles . ')';
            }
            $row['mi_display_name'] = $displayName;

            $postcode = trim((string)($row['mi_plz'] ?? ''));
            $town = trim((string)($row['mi_ort'] ?? ''));
            $address = trim((string)($row['mi_anschr'] ?? ''));
            $addressLine = trim($postcode . ' ' . $town);
            $row['mi_display_address'] = implode(', ', array_filter(
                [$addressLine, $address],
                static fn(string $part): bool => $part !== ''
            ));

            $birthday = trim((string)($row['mi_gebtag'] ?? ''));
            if ($birthday !== '' && $birthday !== '0000-00-00') {
                $birthTimestamp = strtotime($birthday);
                if ($birthTimestamp !== false) {
                    $age = $currentYear - (int)date('Y', $birthTimestamp);
                    $color = $age % 5 === 0 ? 'red' : 'inherit';
                    $safeBirthday = htmlspecialchars($birthday, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                    $row['mi_gebtag_display'] = $safeBirthday
                        . " - <span style='color: {$color}; font-weight: bold;'>{$age}</span>";
                } else {
                    $row['mi_gebtag_display'] = htmlspecialchars($birthday, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                }
            } else {
                $row['mi_gebtag_display'] = '';
            }

            if ($row['mi_ref_int_2'] != '' || $row['mi_ref_int_2'] != '' || $row['mi_ref_int_2'] != '' ) {
                $part2 = ($row['mi_ref_int_2'] === 'Y') ? 'J' : ' ';
                $part3 = ($row['mi_ref_int_3'] === 'Y') ? 'J' : ' ';
                $part4 = ($row['mi_ref_int_4'] === 'Y') ? 'J' : ' ';
                
                $row['mi_ref_int_2'] = $part2 . ' | ' . $part3 . ' | ' . $part4;
            }
            
            $isActive = ($row['mi_austrdat'] ?? null) === null
                && ($row['mi_sterbdat'] ?? null) === null;
            $row['mi_status'] = $isActive ? 'Aktiv' : 'Nicht aktiv';

            $row = array_intersect_key($row, array_flip([...self::COLUMNS, 'action', 'mi_name', 'mi_ort']));
        }
        unset($row);

        return $rows;
    }

    public function getFilters(PDO $pdo, ?string $project = null, ?int $selId = null, ?bool $editProtect = false): array
    {
        return [
            'selectFilters' => [
                'mi_status' => [
                    'label' => 'Mitgliedsstatus',
                    'options' => [
                        'Aktiv' => 'Aktive Mitglieder',
                        'Nicht aktiv' => 'Nicht aktive Mitglieder',
                    ],
                ],
            ],
            'textFilters' => ['mi_name', 'mi_ort', 'mi_email'],
            'textFilterLabels' => [
                'mi_name' => 'Name',
                'mi_ort' => 'Ort',
                'mi_email' => 'E-Mail',
            ],
        ];
    }
}

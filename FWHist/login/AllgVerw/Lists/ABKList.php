<?php

declare(strict_types=1);

namespace FWHist\AllgVerw\Lists;

use FWHist\Services\ListInterface;
use FWHist\Services\TableColumnMetadata;
use PDO;

final class ABKList implements ListInterface
{
    private const TABLE = 'hi_abk';

    private const GROUPS = [
        'MA_F' => 'Von Maschinen angetriebene Fahrzeuge',
        'MA_G' => 'Von Maschinen angetriebene Geräte',
        'MU_F' => 'Mit Muskeln bewegte Fahrzeuge',
        'MU_G' => 'Mit Muskeln betriebene Geräte',
        'PA_R' => 'Persönliche Ausrüstung',
        'ORG' => 'Organisatorisches',
    ];

    public static function getListId(): string
    {
        return 'ABK';
    }

    public function getColumns(PDO $pdo, ?string $project = null, ?int $selId = null, ?bool $editProtect = false): array
    {
        $meta = new TableColumnMetadata($pdo, 'fharch_new', false);
        $meta->getColumnsForTables([self::TABLE]);
        $comments = $meta->getCommentsMap();

        return [
            [
                'title' => 'Nr.',
                'field' => 'ab_id',
                'width' => 80,
                'hozAlign' => 'center',
                'headerSort' => true,
            ],
            [
                'title' => 'Abkürzungsgruppe',
                'field' => 'ab_grp_label',
                'minWidth' => 200,
                'headerSort' => true,
            ],
            [
                'title' => $comments['ab_abk'] ?? 'Abkürzung',
                'field' => 'ab_abk',
                'width' => 150,
                'headerSort' => true,
            ],
            [
                'title' => $comments['ab_bezeichn'] ?? 'Bezeichnung',
                'field' => 'ab_bezeichn',
                'minWidth' => 240,
                'headerSort' => true,
            ],
            [
                'title' => $comments['ab_gruppe'] ?? 'Untergruppe',
                'field' => 'ab_gruppe',
                'width' => 150,
                'headerSort' => true,
            ],
        ];
    }

    public function getData(PDO $pdo, ?string $project = null, ?int $selId = null, ?bool $editProtect = false): array
    {
        $statement = $pdo->query('SELECT * FROM `' . self::TABLE . '` ORDER BY ab_id ASC');
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as &$row) {
            $id = (int)($row['ab_id'] ?? 0);
            $row['action'] = "<a class='action-link' href='AbkuerzEdit.php?ID={$id}'>Bearbeiten</a>";
            $row['ab_grp_label'] = self::GROUPS[(string)($row['ab_grp'] ?? '')] ?? (string)($row['ab_grp'] ?? '');
        }
        unset($row);

        return $rows;
    }

    public function getFilters(PDO $pdo, ?string $project = null, ?int $selId = null, ?bool $editProtect = false): array
    {
        return [
            'selectFilters' => [
                'ab_grp' => [
                    'label' => 'Abkürzungsgruppe',
                    'options' => [
                        'MA_F' => 'Abk. Motorisierte Fahrzeuge',
                        'MA_G' => 'Abk. Motorisierte Geräte',
                        'MU_F' => 'Abk. Muskelgezogene Fahrzeuge',
                        'MU_G' => 'Abk. Muskelbetriebene Geräte',
                        'PA_R' => 'Abk. Persönliche Ausrüstung',
                        'ORG' => 'Abk. Organisation',
                    ],
                ],
            ],
            'textFilters' => ['ab_abk', 'ab_bezeichn', 'ab_gruppe'],
            'textFilterLabels' => [
                'ab_abk' => 'Abkürzung',
                'ab_bezeichn' => 'Bezeichnung',
                'ab_gruppe' => 'Untergruppe',
            ],
        ];
    }
}

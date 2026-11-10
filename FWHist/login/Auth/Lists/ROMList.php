<?php

declare(strict_types=1);

namespace FWHist\Auth\Lists;

use FWHist\Services\ListInterface;
use FWHist\Services\TableColumnMetadata;
use InvalidArgumentException;
use PDO;

/**
 * Mandanten-Erlaubnisse eines Benutzers (fv_benutzer_mandant): list_ID 'ROM', sel_id = be_id.
 * Nur für ADM-ZUGR/ADM-ALLE abrufbar (ListHostAPI.php, $protectedListRoles).
 */
final class ROMList implements ListInterface
{
    private const PERM_TABLE = 'fv_benutzer_mandant';
    private const MANDANT_TABLE = 'ma_mandanten';

    /** Spalten der Liste (zusätzlich action) - nur diese Felder gehen an den Browser */
    private const SHOW_COLS = ['fm_id', 'ei_id', 'mandant', 'fm_aktiv'];

    public static function getListId(): string
    {
        return 'ROM';
    }

    public function getColumns(PDO $pdo, ?string $project = null, ?int $selId = null, ?bool $editProtect = false): array
    {
        $this->requireUserId($selId);
        $meta = new TableColumnMetadata($pdo, 'fharch_new', false);
        $meta->getColumnsForTables([self::PERM_TABLE]);
        $comments = $meta->getCommentsMap();

        return [
            [
                'title' => $comments['fm_id'] ?? 'Zuordnungsnummer',
                'field' => 'fm_id',
                'width' => 115,
                'hozAlign' => 'center',
                'headerSort' => true,
            ],
            [
                'title' => $comments['ei_id'] ?? 'Mandant (Nr.)',
                'field' => 'ei_id',
                'width' => 110,
                'hozAlign' => 'center',
                'headerSort' => true,
            ],
            [
                'title' => 'Mandant',
                'field' => 'mandant',
                'minWidth' => 260,
                'headerSort' => true,
            ],
            [
                'title' => $comments['fm_aktiv'] ?? 'Status',
                'field' => 'fm_aktiv',
                'width' => 100,
                'hozAlign' => 'center',
                'headerSort' => true,
            ],
        ];
    }

    public function getData(PDO $pdo, ?string $project = null, ?int $selId = null, ?bool $editProtect = false): array
    {
        $userId = $this->requireUserId($selId);
        $statement = $pdo->prepare(
            'SELECT fm_id, be_id, ei_id, fm_aktiv
             FROM `' . self::PERM_TABLE . '`
             WHERE be_id = :be_id
             ORDER BY ei_id ASC, fm_id ASC'
        );
        $statement->execute(['be_id' => $userId]);
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        $labels = $this->mandantLabels($pdo);
        $keep = array_flip([...self::SHOW_COLS, 'action']);

        foreach ($rows as &$row) {
            $permId = (int)($row['fm_id'] ?? 0);
            $assignedUserId = (int)($row['be_id'] ?? $userId);
            $eiId = (int)($row['ei_id'] ?? 0);

            // Action-Link immer; "Anzeigen" ohne Schreibrecht (die Liste ist ohnehin nur für ADM-ZUGR abrufbar)
            $label = $editProtect ? 'Anzeigen' : 'Bearbeiten';
            $row['action'] = "<a class='action-link' href='MandErlEdit.php?ID={$permId}&amp;beId={$assignedUserId}'>{$label}</a>";
            $row['mandant'] = $labels[$eiId] ?? ('Mandant ' . $eiId);
            $row['fm_aktiv'] = ($row['fm_aktiv'] ?? '') === 'i' ? 'Inaktiv' : 'Aktiv';

            $row = array_intersect_key($row, $keep);
        }
        unset($row);

        return $rows;
    }

    public function getFilters(PDO $pdo, ?string $project = null, ?int $selId = null, ?bool $editProtect = false): array
    {
        $this->requireUserId($selId);

        return [
            'selectFilters' => [
                'fm_aktiv' => [
                    'label' => 'Erlaubnis-Status',
                    'options' => [
                        'Aktiv' => 'Aktive Erlaubnisse',
                        'Inaktiv' => 'Inaktive Erlaubnisse',
                    ],
                ],
            ],
            'textFilters' => ['mandant'],
            'textFilterLabels' => [
                'mandant' => 'Mandant',
            ],
        ];
    }

    /**
     * [ei_id => Anzeigename] aus ma_mandanten. Der Name wird defensiv aus ei_name/ei_vname gebildet,
     * fehlen die Spalten, steht "Mandant <Nr>" da.
     *
     * @return array<int,string>
     */
    private function mandantLabels(PDO $pdo): array
    {
        $out = [];
        foreach ($pdo->query('SELECT * FROM `' . self::MANDANT_TABLE . '`')->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $id = (int)($row['ei_id'] ?? 0);
            $name = trim((string)($row['ei_name'] ?? '') . ' ' . (string)($row['ei_vname'] ?? ''));
            $out[$id] = $name !== '' ? $name : 'Mandant ' . $id;
        }

        return $out;
    }

    private function requireUserId(?int $selId): int
    {
        if ($selId === null || $selId < 1) {
            throw new InvalidArgumentException('Für die Mandanten-Liste ist eine gültige Benutzer-ID erforderlich.');
        }

        return $selId;
    }
}

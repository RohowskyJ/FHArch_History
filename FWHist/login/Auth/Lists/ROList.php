<?php

declare(strict_types=1);

namespace FWHist\Auth\Lists;

use FWHist\Services\ListInterface;
use FWHist\Services\TableColumnMetadata;
use InvalidArgumentException;
use PDO;

final class ROList implements ListInterface
{
    private const ROLE_TABLE = 'fv_rolle';
    private const DESCRIPTION_TABLE = 'fv_rollen_beschr';

    public static function getListId(): string
    {
        return 'RO';
    }

    public function getColumns(PDO $pdo, ?string $project = null, ?int $selId = null, ?bool $editProtect = false): array
    {
        $this->requireUserId($selId);
        $meta = new TableColumnMetadata($pdo, 'fharch_new', false);
        $meta->getColumnsForTables([self::ROLE_TABLE, self::DESCRIPTION_TABLE]);
        $comments = $meta->getCommentsMap();

        return [
            [
                'title' => $comments['fr_id'] ?? 'Zuordnungsnummer',
                'field' => 'fr_id',
                'width' => 115,
                'hozAlign' => 'center',
                'headerSort' => true,
            ],
            [
                'title' => $comments['fr_aktiv'] ?? 'Status',
                'field' => 'fr_aktiv',
                'width' => 100,
                'hozAlign' => 'center',
                'headerSort' => true,
            ],
            [
                'title' => $comments['fl_beschreibung'] ?? 'Rolle',
                'field' => 'fl_beschreibung',
                'minWidth' => 220,
                'headerSort' => true,
            ],
            [
                'title' => $comments['fl_modules'] ?? 'Module / Berechtigungen',
                'field' => 'fl_modules',
                'minWidth' => 240,
                'headerSort' => false,
            ],
        ];
    }

    public function getData(PDO $pdo, ?string $project = null, ?int $selId = null, ?bool $editProtect = false): array
    {
        $userId = $this->requireUserId($selId);
        $statement = $pdo->prepare(
            'SELECT
                r.fr_id,
                r.be_id,
                r.fr_aktiv,
                r.fl_id,
                d.fl_beschreibung,
                d.fl_modules
             FROM `' . self::ROLE_TABLE . '` r
             LEFT JOIN `' . self::DESCRIPTION_TABLE . '` d ON d.fl_id = r.fl_id
             WHERE r.be_id = :be_id
             ORDER BY d.fl_id ASC, r.fr_id ASC'
        );
        $statement->execute(['be_id' => $userId]);
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as &$row) {
            $roleId = (int)($row['fr_id'] ?? 0);
            $assignedUserId = (int)($row['be_id'] ?? $userId);
            $row['action'] = "<a class='action-link' href='RollenEdit.php?ID={$roleId}&amp;beId={$assignedUserId}'>Bearbeiten</a>";
            $row['fr_aktiv'] = ($row['fr_aktiv'] ?? '') === 'i' ? 'Inaktiv' : 'Aktiv';
            unset($row['be_id'], $row['fl_id']);
        }
        unset($row);

        return $rows;
    }

    public function getFilters(PDO $pdo, ?string $project = null, ?int $selId = null, ?bool $editProtect = false): array
    {
        $this->requireUserId($selId);

        return [
            'selectFilters' => [
                'fr_aktiv' => [
                    'label' => 'Rollenstatus',
                    'options' => [
                        'Aktiv' => 'Aktive Rollen',
                        'Inaktiv' => 'Inaktive Rollen',
                    ],
                ],
            ],
            'textFilters' => ['fl_beschreibung', 'fl_modules'],
            'textFilterLabels' => [
                'fl_beschreibung' => 'Rollenbeschreibung',
                'fl_modules' => 'Module / Berechtigungen',
            ],
        ];
    }

    private function requireUserId(?int $selId): int
    {
        if ($selId === null || $selId < 1) {
            throw new InvalidArgumentException('Für die Rollenliste ist eine gültige Benutzer-ID erforderlich.');
        }

        return $selId;
    }
}

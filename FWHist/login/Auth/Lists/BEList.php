<?php

declare(strict_types=1);

namespace FWHist\Auth\Lists;

use FWHist\Services\ListInterface;
use FWHist\Services\TableColumnMetadata;
use PDO;

final class BEList implements ListInterface
{
    private const USER_TABLE = 'fv_benutzer';
    private const DATA_TABLE = 'fv_ben_dat';

    public static function getListId(): string
    {
        return 'BE';
    }

    public function getColumns(PDO $pdo, ?string $project = null, ?int $selId = null, ?bool $editProtect = false): array
    {
        $meta = new TableColumnMetadata($pdo, 'fharch_new', false);
        $meta->getColumnsForTables([self::USER_TABLE, self::DATA_TABLE]);
        $comments = $meta->getCommentsMap();

        return [
            [
                'title' => $comments['fd_id'] ?? 'Nr.',
                'field' => 'fd_id',
                'width' => 80,
                'hozAlign' => 'center',
                'headerSort' => true,
            ],
            [
                'title' => $comments['fd_anrede'] ?? 'Anrede',
                'field' => 'fd_anrede',
                'width' => 90,
                'headerSort' => true,
            ],
            [
                'title' => 'Name',
                'field' => 'fd_name',
                'minWidth' => 190,
                'headerSort' => true,
            ],
            [
                'title' => 'Adresse',
                'field' => 'fd_adresse',
                'minWidth' => 200,
                'headerSort' => false,
            ],
            [
                'title' => 'Ort',
                'field' => 'fd_ort',
                'minWidth' => 160,
                'headerSort' => true,
            ],
            [
                'title' => $comments['fd_tel'] ?? 'Telefon',
                'field' => 'fd_tel',
                'minWidth' => 140,
                'headerSort' => false,
            ],
            [
                'title' => $comments['fd_email'] ?? 'E-Mail',
                'field' => 'fd_email',
                'minWidth' => 190,
                'headerSort' => false,
            ],
            [
                'title' => 'Benutzerstatus',
                'field' => 'be_status',
                'visible' => false,
                'headerSort' => false,
            ],
        ];
    }

    public function getData(PDO $pdo, ?string $project = null, ?int $selId = null, ?bool $editProtect = false): array
    {
        $statement = $pdo->query(
            'SELECT
                b.be_id,
                b.be_act,
                d.fd_id,
                d.fd_anrede,
                d.fd_tit_vor,
                d.fd_vname,
                d.fd_name,
                d.fd_tit_nach,
                d.fd_adresse,
                d.fd_plz,
                d.fd_ort,
                d.fd_tel,
                d.fd_email
             FROM `' . self::USER_TABLE . '` b
             LEFT JOIN `' . self::DATA_TABLE . '` d ON d.be_id = b.be_id
             ORDER BY b.be_id ASC'
        );
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as &$row) {
            $profileId = (int)($row['fd_id'] ?? 0);
            $row['action'] = "<a class='action-link' href='BenEdit.php?ID={$profileId}'>Bearbeiten</a>";

            $firstName = trim((string)($row['fd_vname'] ?? ''));
            $lastName = trim((string)($row['fd_name'] ?? ''));
            $titles = trim(implode(' ', array_filter([
                trim((string)($row['fd_tit_vor'] ?? '')),
                trim((string)($row['fd_tit_nach'] ?? '')),
            ])));
            $row['fd_name'] = trim($lastName . ($firstName !== '' ? ', ' . $firstName : ''));
            if ($titles !== '') {
                $row['fd_name'] .= ' (' . $titles . ')';
            }

            $row['fd_ort'] = trim(trim((string)($row['fd_plz'] ?? '')) . ' ' . trim((string)($row['fd_ort'] ?? '')));
            $row['be_status'] = in_array((string)($row['be_act'] ?? ''), ['', 'a'], true)
                ? 'Aktiv'
                : 'Inaktiv';
            unset(
                $row['be_id'],
                $row['be_act'],
                $row['fd_tit_vor'],
                $row['fd_vname'],
                $row['fd_tit_nach'],
                $row['fd_plz']
            );
        }
        unset($row);

        return $rows;
    }

    public function getFilters(PDO $pdo, ?string $project = null, ?int $selId = null, ?bool $editProtect = false): array
    {
        return [
            'selectFilters' => [
                'be_status' => [
                    'label' => 'Benutzerstatus',
                    'options' => [
                        'Aktiv' => 'Aktive Benutzer',
                        'Inaktiv' => 'Nicht aktive Benutzer',
                    ],
                ],
            ],
            'textFilters' => ['fd_name', 'fd_ort', 'fd_email'],
            'textFilterLabels' => [
                'fd_name' => 'Name',
                'fd_ort' => 'Ort',
                'fd_email' => 'E-Mail',
            ],
        ];
    }
}

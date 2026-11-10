<?php

declare(strict_types=1);

namespace FWHist\Modules\Mitglieder\Lists;

use FWHist\Services\ListInterface;
use FWHist\Services\TableColumnMetadata;
use PDO;

final class MIEList implements ListInterface
{
    private const TABLE = 'mi_ehrungen';

    private const COLUMNS = [
        'me_id',
        'mi_id',
        'me_ehrung',
        'me_eh_datum',
        'me_begruendg',
        'me_bild1',
    ];

    public static function getListId(): string
    {
        return 'MIE';
    }

    public function getColumns(PDO $pdo, ?string $project = null, ?int $selId = null, ?bool $editProtect = false): array
    {
        $meta = new TableColumnMetadata($pdo, 'fharch_new', false);
        $meta->getColumnsForTables([self::TABLE]);
        $comments = $meta->getCommentsMap();
        $labels = [
            'me_id' => 'Fortl. Nr.',
            'mi_id' => 'Mitgl. Nr.',
            'me_ehrung' => 'Ehrung',
            'me_eh_datum' => 'Verleihungsdatum',
            'me_begruendg' => 'Begründung',
            'me_bild1' => 'Bild',
        ];

        $columns = [];
        foreach (self::COLUMNS as $field) {
            $column = [
                'title' => $labels[$field] ?? $comments[$field] ?? ucfirst($field),
                'field' => $field,
                'headerSort' => true,
                'formatter' => $field === 'me_bild1' ? 'html' : 'plaintext',
            ];

            if ($field === 'me_id' || $field === 'mi_id') {
                $column['width'] = 90;
                $column['hozAlign'] = 'center';
            } elseif ($field === 'me_begruendg') {
                $column['minWidth'] = 220;
            } elseif ($field === 'me_bild1') {
                $column['headerSort'] = false;
                $column['minWidth'] = 180;
            }

            $columns[] = $column;
        }

        return $columns;
    }

    public function getData(PDO $pdo, ?string $project = null, ?int $selId = null, ?bool $editProtect = false): array
    {
        if ($selId !== null) {
            $statement = $pdo->prepare(
                'SELECT ' . implode(', ', self::COLUMNS) . ' FROM `' . self::TABLE . '` WHERE mi_id = :mi_id ORDER BY me_id'
            );
            $statement->execute(['mi_id' => $selId]);
        } else {
            $statement = $pdo->query(
                'SELECT ' . implode(', ', self::COLUMNS) . ' FROM `' . self::TABLE . '` ORDER BY me_id'
            );
        }

        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
        $imageDirectory = dirname(__DIR__, 5) . DIRECTORY_SEPARATOR . 'FWHist' . DIRECTORY_SEPARATOR
            . 'login' . DIRECTORY_SEPARATOR . 'AOrd_Verz' . DIRECTORY_SEPARATOR . '1' . DIRECTORY_SEPARATOR . 'MITGL'
            . DIRECTORY_SEPARATOR;
        $imageUrl = '/FWHist/login/AOrd_Verz/1/MITGL/';

        foreach ($rows as &$row) {
            $id = (int)($row['me_id'] ?? 0);
            if (!$editProtect) {
                $row['action'] = "<a class='action-link' href='MitglEhrgEdit.php?ID={$id}'>Bearbeiten</a>";
            }

            $fileName = basename((string)($row['me_bild1'] ?? ''));
            $filePath = $imageDirectory . $fileName;
            if ($fileName !== '' && is_file($filePath)) {
                $safeName = htmlspecialchars($fileName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $safeUrl = htmlspecialchars($imageUrl . rawurlencode($fileName), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $row['me_bild1'] = "<a href='{$safeUrl}' target='_blank' rel='noopener'>"
                    . "<img src='{$safeUrl}' alt='{$safeName}' width='150'><br>{$safeName}</a>";
            } else {
                $row['me_bild1'] = '';
            }
        }
        unset($row);

        return $rows;
    }

    public function getFilters(PDO $pdo, ?string $project = null, ?int $selId = null, ?bool $editProtect = false): array
    {
        return [
            'textFilters' => ['me_ehrung', 'me_begruendg'],
            'textFilterLabels' => [
                'me_ehrung' => 'Ehrung',
                'me_begruendg' => 'Begründung',
            ],
        ];
    }
}

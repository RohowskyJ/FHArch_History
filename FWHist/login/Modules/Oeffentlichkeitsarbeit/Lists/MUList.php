<?php

declare(strict_types=1);

namespace FWHist\Modules\Oeffentlichkeitsarbeit\Lists;

use FWHist\Services\ListInterface;
use FWHist\Services\TableColumnMetadata;
use PDO;

final class MUList implements ListInterface
{
    private const MUSEUM_TYPES = [
        '0' => 'Sammlung, Depot',
        '1' => 'Schausammlung',
        '2' => 'Traditionsraum',
        '3' => 'Schauraum, Museum',
    ];
    private const IMAGE_PATH = '../../../../FWHist/login/AOrd_Verz/Museen/';
    private const IMAGE_EXTENSIONS = ['gif', 'ico', 'jpeg', 'jpg', 'png', 'tiff'];

    public static function getListId(): string
    {
        return 'MU';
    }

    public function getColumns(PDO $pdo, ?string $project = null, ?int $selId = null, ?bool $editProtect = false): array
    {
        $meta = new TableColumnMetadata($pdo, 'fharch_new', false);
        $meta->getColumnsForTables(['oe_museen']);
        $comments = $meta->getCommentsMap();

        $columns = [];
        foreach (['mu_id', 'mu_staat', 'mu_bdland', 'mu_name', 'mu_adresse_a', 'mu_plz_a', 'mu_ort_a', 'mu_mustyp', 'mu_bild_1'] as $field) {
            $column = [
                'title' => $comments[$field] ?? ucfirst($field),
                'field' => $field,
                'headerSort' => false,
                'formatter' => 'plaintext',
            ];

            if ($field === 'mu_id') {
                $column['width'] = 80;
                $column['hozAlign'] = 'center';
            } elseif ($field === 'mu_bild_1') {
                $column['formatter'] = 'html';
            }

            if (in_array($field, ['mu_name', 'mu_adresse_a', 'mu_ort_a'], true)) {
                $column['minWidth'] = 160;
            }

            $columns[] = $column;
        }

        return $columns;
    }

    public function getData(PDO $pdo, ?string $project = null, ?int $selId = null, ?bool $editProtect = false): array
    {
        $statement = $pdo->query('SELECT * FROM oe_museen ORDER BY mu_id');
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as &$row) {
            $id = (int)($row['mu_id'] ?? 0);
            $label = $editProtect ? 'Anzeigen' : 'Bearbeiten';
            $row['action'] = "<a class='btn action-link' href='MuseenEdit.php?ID={$id}'>{$label}</a>";

            $row['mu_mustyp'] = self::MUSEUM_TYPES[(string)($row['mu_mustyp'] ?? '')]
                ?? (string)($row['mu_mustyp'] ?? '');
            $row['mu_bild_1'] = $this->renderImageLink((string)($row['mu_bild_1'] ?? ''));

            unset(
                $row['mu_url_chkd'],
                $row['mu_url_obsolete'],
                $row['mu_changed-id'],
                $row['mu_changed_id'],
                $row['mu_changed_at']
            );
        }
        unset($row);

        return $rows;
    }

    public function getFilters(PDO $pdo, ?string $project = null, ?int $selId = null, ?bool $editProtect = false): array
    {
        $stateOptions = $this->getDistinctOptions($pdo, 'mu_staat');
        $regionOptions = $this->getDistinctOptions($pdo, 'mu_bdland');
        $typeOptions = array_combine(array_values(self::MUSEUM_TYPES), array_values(self::MUSEUM_TYPES));

        return [
            'selectFilters' => [
                'mu_staat' => [
                    'label' => 'Staat',
                    'options' => $stateOptions,
                ],
                'mu_bdland' => [
                    'label' => 'Bundesland',
                    'options' => $regionOptions,
                ],
                'mu_mustyp' => [
                    'label' => 'Museumstyp',
                    'options' => $typeOptions,
                ],
            ],
            'textFilters' => ['mu_name', 'mu_ort_a'],
            'textFilterLabels' => [
                'mu_name' => 'Museumsname',
                'mu_ort_a' => 'Ort',
            ],
        ];
    }

    private function getDistinctOptions(PDO $pdo, string $field): array
    {
        $allowedFields = ['mu_staat', 'mu_bdland'];
        if (!in_array($field, $allowedFields, true)) {
            throw new \InvalidArgumentException('Unzulässiges Museums-Filterfeld: ' . $field);
        }

        $statement = $pdo->query(
            "SELECT DISTINCT {$field} FROM oe_museen WHERE {$field} IS NOT NULL AND {$field} <> '' ORDER BY {$field}"
        );
        $values = $statement->fetchAll(PDO::FETCH_COLUMN);
        $options = [];
        foreach ($values as $value) {
            $options[(string)$value] = (string)$value;
        }

        return $options;
    }

    private function renderImageLink(string $filename): string
    {
        $filename = basename(trim($filename));
        if ($filename === '') {
            return '';
        }

        $safeFilename = htmlspecialchars($filename, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $url = htmlspecialchars(self::IMAGE_PATH . rawurlencode($filename), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($extension, self::IMAGE_EXTENSIONS, true)) {
            return "<a href='{$url}' target='_blank' rel='noopener noreferrer'>"
                . "<img src='{$url}' alt='{$safeFilename}' height='200'></a>";
        }

        return "<a href='{$url}' target='_blank' rel='noopener noreferrer'>{$safeFilename}</a>";
    }
}

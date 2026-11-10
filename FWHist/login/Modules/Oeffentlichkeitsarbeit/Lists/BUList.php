<?php

declare(strict_types=1);

namespace FWHist\Modules\Oeffentlichkeitsarbeit\Lists;

use FWHist\Auth\AccessGuard;
use FWHist\Services\ListInterface;
use FWHist\Services\TableColumnMetadata;
use PDO;

final class BUList implements ListInterface
{
    private const IMAGE_EXTENSIONS = ['gif', 'ico', 'jpeg', 'jpg', 'png', 'tiff'];
    private const IMAGE_PATH = '../../../login/AOrd_Verz/Buch/';
    /** Spalten der Liste = Felder, die an den Browser gehen (zusätzlich action und, für Bearbeiter/Freigeber, bu_frei_stat) */
    private const SHOW_COLS = ['bu_id', 'bu_titel', 'bu_teaser', 'bu_author', 'bu_verlag', 'bu_bild_1', 'bu_bild_2'];

    public static function getListId(): string
    {
        return 'BU';
    }

    public function getColumns(PDO $pdo, ?string $project = null, ?int $selId = null, ?bool $editProtect = false): array
    {
        $meta = new TableColumnMetadata($pdo, 'fharch_new', false);
        $meta->getColumnsForTables(['oe_buecher']);
        $comments = $meta->getCommentsMap();

        $columns = [];
        foreach (self::SHOW_COLS as $field) {
            $column = [
                'title' => $comments[$field] ?? ucfirst($field),
                'field' => $field,
                'headerSort' => false,
                'formatter' => 'plaintext',
            ];

            if ($field === 'bu_id') {
                $column['width'] = 95;
                $column['hozAlign'] = 'center';
            } elseif (in_array($field, ['bu_bild_1', 'bu_bild_2'], true)) { // nur serverseitig erzeugtes, escaptes HTML
                $column['formatter'] = 'html';
            }

            if (in_array($field, ['bu_teaser', 'bu_author', 'bu_verlag'], true)) {
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
        $guard = AccessGuard::current();

        // Wer Rezensionen schreiben oder freigeben darf (nie auf externen Seiten), sieht alle Einträge; alle anderen
        // (extern, ONU-ALL, ONU-GAST) nur freigegebene. Maßgeblich ist der angemeldete Benutzer, nicht $editProtect.
        $seeAll = $guard->canSeeUnreleasedReviews();

        // SELECT * : Inhalte werden teils in andere Felder eingebaut (Bild-Links, Formatierung). Ausgeliefert wird nur
        // die Allowlist am Ende der Schleife - bu_text, bu_editor, bu_*_id usw. verlassen den Server nicht.
        $sql = 'SELECT * FROM oe_buecher'
            . ($seeAll ? '' : " WHERE bu_frei_stat = 'F'")
            . ' ORDER BY bu_id';
        $rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $keep = array_flip([...self::SHOW_COLS, 'action', ...($seeAll ? ['bu_frei_stat'] : [])]);

        foreach ($rows as &$row) {
            $id = (int)($row['bu_id'] ?? 0);
            $released = ($row['bu_frei_stat'] ?? '') === 'F';

            // Action-Link immer (Detail-/Edit-Formular, dort steht der Volltext); ohne Bearbeitungsrecht "Anzeigen"
            $label = $guard->canEditReview($released) ? 'Bearbeiten' : 'Anzeigen';
            $row['action'] = "<a class='btn action-link' href='BuchEdit.php?ID={$id}'>{$label}</a>";

            foreach (['bu_bild_1', 'bu_bild_2'] as $field) {
                $row[$field] = $this->renderFileLink((string)($row[$field] ?? ''));
            }

            $row = array_intersect_key($row, $keep);
        }
        unset($row);

        return $rows;
    }

    public function getFilters(PDO $pdo, ?string $project = null, ?int $selId = null, ?bool $editProtect = false): array
    {
        $meta = new TableColumnMetadata($pdo, 'fharch_new', false);
        $meta->getColumnsForTables(['oe_buecher']);
        $comments = $meta->getCommentsMap();

        $fallbackLabels = [
            'bu_titel' => 'Titel',
            'bu_teaser' => 'Kurzbeschreibung',
            'bu_author' => 'Autor',
            'bu_verlag' => 'Verlag',
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

    private function renderFileLink(string $filename): string
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

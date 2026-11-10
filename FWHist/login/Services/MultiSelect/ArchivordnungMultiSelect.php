<?php

declare(strict_types=1);

namespace FWHist\Services\MultiSelect;

use FWHist\Database\DB_GenericLog;

/**
 * ArchivordnungMultiSelect
 * ------------------------
 * Kaskadierbare Auswahl der ÖBFV-Archivordnung, 6 Ebenen:
 *
 *   Ebene 1: hi_archivord               (Haupteintrag je Sachgebiet, ar_sub_sg = '0')
 *   Ebene 2: hi_archivord               (ar_sg + ar_sub_sg, ar_sub_sg != '0')
 *   Ebene 3: hi_archord_erw.al_lcsg     (al_lcsg > '00' AND al_lcssg = '00')
 *   Ebene 4: hi_archord_erw.al_lcssg    (al_lcssg > '00' AND al_lcssg_s0 = '00')
 *   Ebene 5: hi_archord_erw.al_lcssg_s0 (al_lcssg_s0 > '00' AND al_lcssg_s1 = '00')
 *   Ebene 6: hi_archord_erw.al_lcssg_s1 (al_lcssg_s1 > '00')
 *
 * Bestätigt (Josef, 08.09.): Ebene 1 entspricht genau der Hauptebene in
 * hi_archivord, d.h. den Zeilen mit ar_sub_sg = '0' (bisher als "Platzhalter"
 * bei Ebene 2 ausgeschlossen). Kein separater hartcodierter Array mehr nötig
 * -- Ebene 1 und Ebene 2 kommen aus derselben Tabelle/Abfrage-Familie.
 * Zur Doku die bestätigten 10 Hauptsachgebiete (Werte müssen exakt so auch
 * als ar_sgname in hi_archivord mit ar_sub_sg='0' stehen):
 *   01 Lokaler Feuerwehrdienst, Finanzen, Protokolle
 *   02 Behörden, andere Institutionen und Vereine
 *   03 Übergeordnete Dienststellen
 *   04 Gleichgestellte Dienststellen
 *   05 Gesetze, Richtlinien, TRVB
 *   06 Bewerbe, Leistungsprüfungen und Ausbildungsprüfungen
 *   07 Feuerwehr-Fahrzeuge, Häuser, Geräte
 *   08 Geschäftsverbindungen
 *   09 Bilder/CDs/Dias/Filme/Fotos/Negativablage/Pläne
 *   10 Literatur
 *
 * OFFENE PUNKTE (bitte weiter prüfen):
 * - hi_archord_erw ist laut Vorgabe NICHT mandantenspezifisch -> $project
 *   wird hier bewusst ignoriert (Parameter bleibt im Interface für andere
 *   Provider, die ihn brauchen).
 * - Composite-Werte (Ebene 2-6) werden intern weiterhin als String geführt
 *   ("{sg}.{sub} {lcsg} {lcssg} ..."), damit die Kaskade beim Weiterklicken
 *   funktioniert. Für die END-Auswertung (z.B. Speichern in ad_archivwert)
 *   bitte parseCompositeValue() nutzen, das daraus das gewünschte
 *   Einzelfeld-Array liefert (['07','01'] bzw. [ad_sg, ad_sub_sg, al_lcsg,
 *   al_lcssg, al_lcssg_0, al_lcssg_1] je nach Tiefe).
 * - BESTÄTIGT (Josef, 08.09.): al_sg in hi_archord_erw wird MIT Punkt
 *   gespeichert ($al_sg = $ar_sg.'.'.$ar_sub_sg) -- Bugfix in level3()
 *   eingearbeitet (vorher wurde der Punkt fälschlich entfernt, wodurch
 *   Ebene 3+ nie Treffer fand).
 */
final class ArchivordnungMultiSelect implements MultiSelectProviderInterface
{
    public function getConfigId(): string
    {
        return 'ARCHIVORDNUNG';
    }

    public function getLevelCount(): int
    {
        return 6;
    }

    public function getLevelLabel(int $level): string
    {
        return match ($level) {
            1 => 'Auswahl der obersten (ÖBFV-) Ebene',
            2 => 'Auswahl der 2. ÖBFV-Ebene',
            3 => 'Auswahl der lokalen Erweiterung (3. Ebene)',
            4 => 'Auswahl der 2. Erweiterung (4. Ebene)',
            5 => 'Auswahl der 3. Erweiterung (5. Ebene)',
            6 => 'Auswahl der 4. Erweiterung (6. Ebene)',
            default => "Ebene $level",
        };
    }

    public function getOptions(DB_GenericLog $db, int $level, ?string $parent, ?string $project): array
    {
        return match ($level) {
            1 => $this->level1($db),
            2 => $this->level2($db, $parent),
            3 => $this->level3($db, $parent),
            4 => $this->level4($db, $parent),
            5 => $this->level5($db, $parent),
            6 => $this->level6($db, $parent),
            default => [],
        };
    }

    /** Ebene 1: Hauptsachgebiete = hi_archivord-Zeilen mit ar_sub_sg = '0'. */
    private function level1(DB_GenericLog $db): array
    {
        $rows = $db->fetchAll(
            "SELECT ar_sg, ar_sgname
               FROM hi_archivord
              WHERE ar_sub_sg = '0'
              ORDER BY ar_sg ASC"
        );

        return array_map(
            static fn(array $r) => [
                'value' => (string)$r['ar_sg'],
                'text'  => (string)$r['ar_sgname'],
            ],
            $rows
        );
    }

    /** Ebene 2: Sub-Sachgebiete zu einem Hauptsachgebiet aus hi_archivord. */
    private function level2(DB_GenericLog $db, ?string $parent): array
    {
        if ($parent === null || $parent === '') {
            return [];
        }

        $rows = $db->fetchAll(
            "SELECT ar_sg, ar_sub_sg, ar_sgname
               FROM hi_archivord
              WHERE ar_sg = :sg AND ar_sub_sg != '0'
              ORDER BY ar_sub_sg ASC",
            [':sg' => $parent]
        );

        return array_map(
            static fn(array $r) => [
                'value' => $r['ar_sg'] . '.' . $r['ar_sub_sg'],
                'text'  => (string)$r['ar_sgname'],
            ],
            $rows
        );
    }

    /** Ebene 3: erste lokale Erweiterungsstufe (al_lcsg) aus hi_archord_erw. */
    private function level3(DB_GenericLog $db, ?string $parent): array
    {
        // BUGFIX (08.09., Josef): al_sg in hi_archord_erw wird MIT Punkt
        // gespeichert ($al_sg = $ar_sg.'.'.$ar_sub_sg), exakt im selben
        // Format wie der 'value' von Ebene 2 ("07.01"). Frühere Version
        // entfernte den Punkt fälschlich (normalizeSg()) -> dadurch fand
        // die Abfrage nie eine Zeile, Ebene 3+ blieb leer.
        if ($parent === null || $parent === '') {
            return [];
        }
        $sg = $parent;

        $rows = $db->fetchAll(
            "SELECT al_sg, al_lcsg, al_bezeich
               FROM hi_archord_erw
              WHERE al_sg = :sg AND al_lcsg > '00' AND al_lcssg = '00'
              ORDER BY al_lcsg ASC",
            [':sg' => $sg]
        );

        return array_map(
            static fn(array $r) => [
                'value' => $r['al_sg'] . ' ' . $r['al_lcsg'],
                'text'  => (string)$r['al_bezeich'],
            ],
            $rows
        );
    }

    /** Ebene 4: zweite Erweiterungsstufe (al_lcssg). */
    private function level4(DB_GenericLog $db, ?string $parent): array
    {
        $parts = $this->splitParent($parent, 2);
        if ($parts === null) {
            return [];
        }
        [$sg, $lcsg] = $parts;

        $rows = $db->fetchAll(
            "SELECT al_sg, al_lcsg, al_lcssg, al_bezeich
               FROM hi_archord_erw
              WHERE al_sg = :sg AND al_lcsg = :lcsg
                AND al_lcssg > '00' AND al_lcssg_s0 = '00'
              ORDER BY al_lcssg ASC",
            [':sg' => $sg, ':lcsg' => $lcsg]
        );

        return array_map(
            static fn(array $r) => [
                'value' => $r['al_sg'] . ' ' . $r['al_lcsg'] . ' ' . $r['al_lcssg'],
                'text'  => (string)$r['al_bezeich'],
            ],
            $rows
        );
    }

    /** Ebene 5: dritte Erweiterungsstufe (al_lcssg_s0). */
    private function level5(DB_GenericLog $db, ?string $parent): array
    {
        $parts = $this->splitParent($parent, 3);
        if ($parts === null) {
            return [];
        }
        [$sg, $lcsg, $lcssg] = $parts;

        $rows = $db->fetchAll(
            "SELECT al_sg, al_lcsg, al_lcssg, al_lcssg_s0, al_bezeich
               FROM hi_archord_erw
              WHERE al_sg = :sg AND al_lcsg = :lcsg AND al_lcssg = :lcssg
                AND al_lcssg_s0 > '00' AND al_lcssg_s1 = '00'
              ORDER BY al_lcssg_s0 ASC",
            [':sg' => $sg, ':lcsg' => $lcsg, ':lcssg' => $lcssg]
        );

        return array_map(
            static fn(array $r) => [
                'value' => $r['al_sg'] . ' ' . $r['al_lcsg'] . ' ' . $r['al_lcssg'] . ' ' . $r['al_lcssg_s0'],
                'text'  => (string)$r['al_bezeich'],
            ],
            $rows
        );
    }

    /** Ebene 6: vierte Erweiterungsstufe (al_lcssg_s1) – letztmögliche Ebene. */
    private function level6(DB_GenericLog $db, ?string $parent): array
    {
        $parts = $this->splitParent($parent, 4);
        if ($parts === null) {
            return [];
        }
        [$sg, $lcsg, $lcssg, $lcssg_s0] = $parts;

        $rows = $db->fetchAll(
            "SELECT al_sg, al_lcsg, al_lcssg, al_lcssg_s0, al_lcssg_s1, al_bezeich
               FROM hi_archord_erw
              WHERE al_sg = :sg AND al_lcsg = :lcsg AND al_lcssg = :lcssg
                AND al_lcssg_s0 = :lcssg_s0 AND al_lcssg_s1 > '00'
              ORDER BY al_lcssg_s1 ASC",
            [':sg' => $sg, ':lcsg' => $lcsg, ':lcssg' => $lcssg, ':lcssg_s0' => $lcssg_s0]
        );

        return array_map(
            static fn(array $r) => [
                'value' => $r['al_sg'] . ' ' . $r['al_lcsg'] . ' ' . $r['al_lcssg'] . ' '
                         . $r['al_lcssg_s0'] . ' ' . $r['al_lcssg_s1'],
                'text'  => (string)$r['al_bezeich'],
            ],
            $rows
        );
    }

    /**
     * Zerlegt einen final gewählten Composite-Wert (wie er im Hidden-Feld/
     * ad_archivwert landet) in seine Einzelfelder, statt ihn als String zu
     * belassen -- gewünscht (Josef, 08.09.), um die Werte direkt in einzelne
     * DB-Spalten schreiben zu können.
     *
     * Format-Beispiele:
     *   "07.01"                     -> ['07', '01']
     *   "07.01 01"                  -> ['07', '01', '01']
     *   "07.01 01 00 00"            -> ['07', '01', '01', '00', '00']
     *   "07.01 01 00 00 00"         -> ['07', '01', '01', '00', '00', '00']
     *
     * @return list<string> [ad_sg, ad_sub_sg, al_lcsg?, al_lcssg?, al_lcssg_0?, al_lcssg_1?]
     *                       -- je nach erreichter Tiefe 2 bis 6 Elemente.
     */
    public static function parseCompositeValue(string $value): array
    {
        $value = trim($value);
        if ($value === '') {
            return [];
        }

        $tokens = explode(' ', $value);

        // Erstes Token ist immer "{ar_sg}.{ar_sub_sg}" (Ebene 2) -> in zwei Felder aufsplitten
        $first = array_shift($tokens);
        $sgParts = explode('.', $first, 2);

        return array_merge($sgParts, $tokens);
    }

    /**
     * Zerlegt einen Ebene-3+-Parent-String ("{sg} {lcsg} ...", leerzeichengetrennt,
     * wie ihn level3()/level4()/level5() als 'value' zurückgeben) in genau
     * $expectedParts Teile. Liefert null, wenn das Format nicht passt (z.B. weil
     * noch ein Ebene-2-String "sg.sub" ankommt statt eines bereits kombinierten).
     *
     * @return list<string>|null
     */
    private function splitParent(?string $parent, int $expectedParts): ?array
    {
        if ($parent === null || $parent === '') {
            return null;
        }

        $parts = explode(' ', trim($parent));

        if (count($parts) !== $expectedParts) {
            return null;
        }

        return $parts;
    }
}

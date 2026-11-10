<?php

declare(strict_types=1);

namespace FWHist\Services\MultiSelect;

use FWHist\Database\DB_GenericLog;

/**
 * SammlungMultiSelect
 * --------------------
 * Kaskadierbare Auswahl der Sammlungs-Struktur aus hi_sammlung.
 *
 * ANDERS als ArchivordnungMultiSelect: hi_sammlung hat KEINE separaten
 * Ebenen-Tabellen (kein Äquivalent zu hi_archord_erw). Stattdessen bildet
 * die Tabelle sich selbst rekursiv ab (Adjazenzliste):
 *
 *   sa_grup     Elternschlüssel -- entspricht dem sa_sammlg-Wert der Elternzeile
 *   sa_sammlg   Eigener Schlüssel dieser Zeile (= "value" für die Kaskade)
 *   sa_name     Anzeigetext
 *
 * Ebene 1 zeigt die vorhandenen Gruppen aus sa_grup. Ebene 2 zeigt die
 * zugehörigen Sammlungen (sa_sammlg / sa_name). Ab Ebene 3 folgt die Kaskade
 * den Elternschlüsseln in sa_grup.
 *
 * Kinder einer Ebene erkennt man daran, dass sa_grup = sa_sammlg DER
 * ELTERNZEILE ist, z.B.:
 *   sa_grup='KA_A'  sa_sammlg='KA_A-A' -> Kind von Root "KA_A"
 *   sa_grup='KA_A'  sa_sammlg='KA_A-U' -> Kind von Root "KA_A"
 *
 * Da sa_sammlg selbst schon der vollständige, eindeutige Schlüssel ist (kein
 * zusammengesetzter String wie bei Archivordnung nötig), ist hier KEIN
 * parseCompositeValue()-Äquivalent erforderlich: value == sa_sammlg direkt,
 * kann 1:1 in ad_sammlung o.ä. gespeichert werden.
 *
 * Die Kaskade ist strukturell beliebig tief (jede Zeile kann wieder Kinder
 * haben, sofern eine Folgezeile sa_grup = diese sa_sammlg hat). getLevelCount()
 * liefert daher nur eine SOFT-Obergrenze für das Frontend/den Dispatcher;
 * die tatsächliche Tiefe pro Ast wird -- wie bei Archivordnung dokumentiert --
 * dadurch bestimmt, dass getOptions() für eine Ebene ein leeres Array liefert,
 * sobald ein Blatt erreicht ist.
 *
 * Die Verschachtelung folgt NICHT dem Namensstring, sondern rein dem
 * gespeicherten sa_grup-Wert. Beispiel:
 *   MU_F -> MU_F-WA (Wagen) -> MU_F-WA-PU (Pumpenwagen), MU_F-WA-PU-HD
 *   (Handdruckspritze), MU_F-WA-PU-HY (Hydrophor) und MU_F-WA-WW
 *   (Wasserwagen) sind alle VIER direkte Geschwister-Kinder von "MU_F-WA"
 *   (sa_grup='MU_F-WA'), OBWOHL "MU_F-WA-PU-HD" durch die 4 Namensteile wie
 *   ein Kind von "MU_F-WA-PU" aussieht. Genau deshalb wird hier bewusst NUR
 *   der sa_grup-Spaltenwert für die Kaskade ausgewertet, nie der Namensstring
 *   selbst -- levelChildren() folgt diesem Wert.
 * $project wird NICHT verwendet; die Auswahl ist nicht projekt-/mandanten-
 *   bezogen (bestätigt).
 *
 * Hinweis (fachlich, nicht relevant für diesen Code): jede Sammlungs-
 * Abkürzung muss laut Josef einen Eintrag in der Archivordnungs-Erweiterung
 * haben -- das ist aber ein reiner Datenpflege-Aspekt und hat keinen Einfluss
 * auf diesen Provider.
 */
final class SammlungMultiSelect implements MultiSelectProviderInterface
{
    /**
     * Soft-Obergrenze für die Kaskadentiefe (siehe Klassenkommentar). Die
     * Tabelle selbst kennt keine feste Tiefe -- 8 sollte für alle bisher
     * gesehenen Sammlungs-Äste (max. gesehen: 3, "MU_F-WA-PU-DA") komfortabel
     * reichen. Bei Bedarf einfach erhöhen, es ändert sich sonst nichts.
     */
    private const MAX_LEVEL = 8;

    public function getConfigId(): string
    {
        return 'SAMMLUNG';
    }

    public function getLevelCount(): int
    {
        return self::MAX_LEVEL;
    }

    public function getLevelLabel(int $level): string
    {
        return match ($level) {
            1 => 'Auswahl der Sammlungsgruppe',
            default => "Auswahl der Sammlungs-Unterebene $level",
        };
    }

    public function getOptions(DB_GenericLog $db, int $level, ?string $parent, ?string $project): array
    {
        if ($level < 1 || $level > self::MAX_LEVEL) {
            return [];
        }

        return match ($level) {
            1 => $this->level1($db),
            2 => $this->level2($db, $parent),
            default => $this->levelChildren($db, $parent),
        };
    }

    /**
     * Ebene 1: Tatsächlich vorhandene Sammlungsgruppen aus sa_grup.
     */
    private function level1(DB_GenericLog $db): array
    {
        $rows = $db->fetchAll(
            "SELECT DISTINCT sa_grup
               FROM hi_sammlung
              WHERE sa_grup IS NOT NULL AND sa_grup <> ''
              ORDER BY sa_grup ASC"
        );

        return array_map(
            static function (array $r): array {
                $group = (string)$r['sa_grup'];

                return [
                    'value' => $group,
                    'text'  => \VF_Sel_SA_Such[$group] ?? $group,
                ];
            },
            $rows
        );
    }

    /**
     * Ebene 2: Sammlungen der gewählten Gruppe.
     */
    private function level2(DB_GenericLog $db, ?string $group): array
    {
        if ($group === null || $group === '') {
            return [];
        }

        $rows = $db->fetchAll(
            "SELECT sa_sammlg, sa_name
               FROM hi_sammlung
              WHERE sa_grup = :group
              ORDER BY sa_sammlg ASC",
            [':group' => $group]
        );

        return array_map(
            static fn(array $r) => [
                'value' => (string)$r['sa_sammlg'],
                'text'  => (string)$r['sa_name'],
            ],
            $rows
        );
    }

    /**
     * Ebene 3+: Kinder der Elternzeile, erkennbar an sa_grup = parent
     * (= sa_sammlg der Elternzeile). Ein Selbstverweis wird ausgeschlossen.
     */
    private function levelChildren(DB_GenericLog $db, ?string $parent): array
    {
        if ($parent === null || $parent === '') {
            return [];
        }

        // PDO-Hinweis: :parent NICHT zweimal verwenden (auch nicht in zwei
        // verschiedenen Bedingungen) -- bei nativen Prepared Statements
        // (PDO::ATTR_EMULATE_PREPARES=false) führt die Mehrfachverwendung
        // desselben benannten Platzhalters zu "SQLSTATE[HY093]: Invalid
        // parameter number". Daher zwei Platzhalter (:parent, :parent2)
        // mit demselben Wert.
        $rows = $db->fetchAll(
            "SELECT sa_sammlg, sa_name
               FROM hi_sammlung
              WHERE sa_grup = :parent AND sa_sammlg != :parent2
              ORDER BY sa_sammlg ASC",
            [':parent' => $parent, ':parent2' => $parent]
        );

        return array_map(
            static fn(array $r) => [
                'value' => (string)$r['sa_sammlg'],
                'text'  => (string)$r['sa_name'],
            ],
            $rows
        );
    }
}

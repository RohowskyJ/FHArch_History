<?php

declare(strict_types=1);

namespace FWHist\Services\MultiSelect;

use FWHist\Database\DB_GenericLog;

/**
 * MultiSelectProviderInterface
 * -----------------------------
 * Vertrag für eine kaskadierbare Mehrfach-Dropdown-Auswahl (2..N Ebenen).
 * Jede konkrete Auswahl (Sammlung, Archivordnung, künftige weitere) implementiert
 * dieses Interface als eigene, kleine Klasse. Registrierung erfolgt zentral in
 * MultiSelectResolver::REGISTRY.
 *
 * Konventionen:
 * - Ebenen sind 1-basiert (Level 1 = oberste Ebene, ohne Parent).
 * - getOptions() liefert IMMER ein Array von ['value' => string, 'text' => string].
 *   Der Platzhalter-Eintrag ("Bitte wählen") wird zentral vom Endpoint (MultiSelectAPI)
 *   vorangestellt, NICHT vom Provider selbst.
 * - $parent ist der von der vorherigen Ebene gewählte value-String (roh, wie ihn der
 *   Client zurückschickt). Level 1 wird mit $parent = null aufgerufen.
 * - Rückgabe eines LEEREN Arrays für eine Ebene signalisiert dem Frontend "Ebene ist
 *   ein Blatt / hier endet die Kaskade" (auch wenn getLevelCount() theoretisch mehr
 *   Ebenen erlauben würde – z.B. weil in der Archivordnung nicht jeder Ast bis
 *   Ebene 6 durchgegliedert ist).
 */
interface MultiSelectProviderInterface
{
    /** Eindeutige Konfigurations-ID, wie sie im REGISTRY des Resolvers verwendet wird. */
    public function getConfigId(): string;

    /** Maximale Anzahl möglicher Ebenen für diese Konfiguration (Obergrenze, s.o.). */
    public function getLevelCount(): int;

    /** Anzeige-Label für eine Ebene (1-basiert), z.B. für <label>-Text im Frontend. */
    public function getLevelLabel(int $level): string;

    /**
     * Liefert die wählbaren Optionen für eine Ebene, abhängig vom gewählten Parent-Wert.
     *
     * @param DB_GenericLog $db      DB-Wrapper (Query-Logging/Masking eingebaut)
     * @param int           $level   1-basierte Ziel-Ebene
     * @param string|null   $parent  value der vorherigen Ebene (null bei Level 1)
     * @param string|null   $project optionaler Kontext-/Mandanten-Parameter (nicht bei
     *                                allen Providern relevant, z.B. Sammlungs-Gruppe)
     *
     * @return array<int, array{value: string, text: string}>
     */
    public function getOptions(DB_GenericLog $db, int $level, ?string $parent, ?string $project): array;
}

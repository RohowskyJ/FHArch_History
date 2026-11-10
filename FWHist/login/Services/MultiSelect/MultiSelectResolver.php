<?php

declare(strict_types=1);

namespace FWHist\Services\MultiSelect;

use InvalidArgumentException;

/**
 * MultiSelectResolver
 * --------------------
 * Analog zu FWHist\Services\ListResolver: explizite Registry (kein Verzeichnis-Scan),
 * damit ein Config-Fehler in einer Klasse nicht andere Konfigurationen mit reißt.
 *
 * Neue Konfiguration hinzufügen = 1 Zeile in REGISTRY + 1 neue Provider-Klasse.
 */
final class MultiSelectResolver
{
    /** @var array<string, class-string<MultiSelectProviderInterface>> */
    private const REGISTRY = [
        'SAMMLUNG'      => SammlungMultiSelect::class,
        'ARCHIVORDNUNG' => ArchivordnungMultiSelect::class,
        // weitere Konfigurationen hier eintragen
    ];

    public static function resolve(string $configId): MultiSelectProviderInterface
    {
        $fqcn = self::REGISTRY[$configId] ?? null;

        if ($fqcn === null) {
            throw new InvalidArgumentException("Unbekannte MultiSelect config_ID: $configId");
        }

        /** @var MultiSelectProviderInterface $instance */
        $instance = new $fqcn();

        return $instance;
    }

    /** @return string[] Alle bekannten config_IDs, z.B. für Debug/Übersicht. */
    public static function knownConfigIds(): array
    {
        return array_keys(self::REGISTRY);
    }
}

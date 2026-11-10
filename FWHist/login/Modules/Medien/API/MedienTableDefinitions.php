<?php

declare(strict_types=1);

namespace FWHist\Modules\Medien\API;

use InvalidArgumentException;
use PDO;

final class MedienTableDefinitions
{
    public static function ensureTenantTable(PDO $pdo, int $eiId, string $table): void
    {
        if ($eiId <= 0) {
            throw new InvalidArgumentException('Die Mandanten-ID muss größer als 0 sein.');
        }

        $tableName = match ($table) {
            "{$eiId}_dmedien" => "`{$eiId}_dmedien`",
            default => throw new InvalidArgumentException("Keine Tabellendefinition für '{$table}' vorhanden."),
        };

        if ($table === "{$eiId}_dmedien") {
            $pdo->exec(
                "CREATE TABLE IF NOT EXISTS {$tableName} (
                   `md_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Fortl. Nr.',
                   `ei_id` int(11) DEFAULT NULL COMMENT 'Urheber-Nr.',
                   `md_Urheber` varchar(50) DEFAULT NULL COMMENT 'Urheber- Name',
                   `md_dsn_1` varchar(255) DEFAULT NULL COMMENT 'Dateiname',
                   `md_aufn_datum` varchar(15) DEFAULT NULL COMMENT 'Aufnahme Datum = Verzeichnis',
                   `md_beschreibg` varchar(1024) DEFAULT NULL COMMENT 'Beschreibung',
                   `md_namen` varchar(1024) DEFAULT NULL COMMENT 'Foto Name',
                   `md_sammlg` varchar(40) DEFAULT NULL COMMENT 'Sammlung',
                   `md_feuerwehr` varchar(40) DEFAULT NULL COMMENT 'Eigner Name Fahrzeug',
                   `md_fw_id` varchar(10) DEFAULT NULL COMMENT 'Eigener- Nr. Fahrzeug',
                   `md_suchbegr` varchar(1024) DEFAULT NULL COMMENT 'Suchbegriffe',
                   `md_media` set('Audio','Foto','Video') DEFAULT NULL COMMENT 'Medium',
                   `md_Med_anz` int(11) DEFAULT NULL COMMENT 'Anzahl der Eintr.',
                   `md_changed_id` int(11) DEFAULT NULL COMMENT 'Geändert von',
                   `md_changed_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Geändert am',
                   PRIMARY KEY (`md_id`),
                   UNIQUE KEY `uq_aufn_dsn` (`md_aufn_datum`,`md_dsn_1`),
                   FULLTEXT KEY `md_sammlg` (`md_sammlg`),
                   FULLTEXT KEY `md_fw_id` (`md_fw_id`)
                   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Digitale- Medien- Archiv'"
            );
            return;
        }
        
    }
}

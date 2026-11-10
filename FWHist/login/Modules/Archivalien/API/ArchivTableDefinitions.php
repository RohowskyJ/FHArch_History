<?php

declare(strict_types=1);

namespace FWHist\Modules\Archivalien\API;

use InvalidArgumentException;
use PDO;

final class ArchivTableDefinitions
{
    public static function ensureTenantTable(PDO $pdo, int $eiId, string $table): void
    {
        if ($eiId <= 0) {
            throw new InvalidArgumentException('Die Mandanten-ID muss größer als 0 sein.');
        }

        $tableName = match ($table) {
            "{$eiId}_archivdat" => "`{$eiId}_archivdat`",
            "{$eiId}_arch_verl" => "`{$eiId}_arch_verl`",
            default => throw new InvalidArgumentException("Keine Tabellendefinition für '{$table}' vorhanden."),
        };

        if ($table === "{$eiId}_archivdat") {
            $pdo->exec(
                "CREATE TABLE IF NOT EXISTS {$tableName} (
                `ad_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Archiv- ID',
                `ei_id` int(11) DEFAULT NULL COMMENT 'Eigner-Nr.',
                `ad_sg` varchar(2) DEFAULT NULL COMMENT 'Sachgebiet',
                `ad_subsg` varchar(2) DEFAULT NULL COMMENT 'SubSachgeb.',
                `ad_lcsg` varchar(2) DEFAULT NULL COMMENT 'Lokales SG',
                `ad_lcssg` varchar(2) DEFAULT NULL COMMENT 'Lokales SubSG',
                `ad_lcssg_s0` varchar(2) DEFAULT NULL COMMENT 'Lokales SubSG Erw 0',
                `ad_lcssg_s1` varchar(2) DEFAULT NULL COMMENT 'Lokales SubSG Erw 1',
                `ad_ao_fortlnr` smallint(6) DEFAULT NULL COMMENT 'Fortl. Archivalien-Nr',
                `ad_sammlg` varchar(20) DEFAULT NULL COMMENT 'Sammlungs- Abkürzung',
                `ad_doc_date` varchar(12) DEFAULT NULL COMMENT 'Archvalien- Datum',
                `ad_type` varchar(50) DEFAULT NULL COMMENT 'Dokument- Typ',
                `ad_format` varchar(3) DEFAULT NULL COMMENT 'Format',
                `ad_keywords` varchar(512) DEFAULT NULL COMMENT 'Suchbegriffe (in Karteiformat, Trennung mit Beistrich)',
                `ad_beschreibg` varchar(1024) DEFAULT NULL COMMENT 'Beschreibung',
                `ad_wert_orig` decimal(10,2) DEFAULT NULL COMMENT 'Wert des Originals',
                `ad_orig_waehrung` varchar(50) DEFAULT NULL COMMENT 'Währung des Originalwertes',
                `ad_wert_kauf` decimal(10,2) DEFAULT NULL COMMENT 'Wert beim Ankauf',
                `ad_kauf_waehrung` varchar(50) DEFAULT NULL COMMENT 'Währung beim Kauf',
                `ad_wert_besch` decimal(10,2) DEFAULT NULL COMMENT 'Wiederbeschaffungs- Wert',
                `ad_besch_waehrung` varchar(50) DEFAULT NULL COMMENT 'Währung des Wiederbeschaffungswertes',
                `ad_namen` varchar(1024) DEFAULT NULL COMMENT 'Im Dokument vorkommende Namen, Trennung mit Beistrich',
                `ad_doc_1` varchar(100) DEFAULT NULL COMMENT 'Dokument 1',
                `ad_doc_2` varchar(100) DEFAULT NULL COMMENT 'Dokument 2',
                `ad_doc_3` varchar(100) DEFAULT NULL COMMENT 'Dokument 3',
                `ad_doc_4` varchar(100) CHARACTER SET utf32 COLLATE utf32_unicode_ci DEFAULT NULL COMMENT 'Dokument 4',
                `ad_isbn` varchar(15) DEFAULT NULL COMMENT 'ISBN- Nummer',
                `ad_lagerort` varchar(100) DEFAULT NULL COMMENT 'Lagerort',
                `ad_l_raum` varchar(50) DEFAULT NULL COMMENT 'Lagerraum',
                `ad_l_kasten` varchar(50) DEFAULT NULL COMMENT 'Kasten im Lager',
                `ad_l_fach` varchar(50) DEFAULT NULL COMMENT 'Fach im Kasten',
                `ad_l_pos_x` varchar(50) DEFAULT NULL COMMENT 'Position X',
                `ad_l_pos_y` varchar(50) DEFAULT NULL COMMENT 'Position Y',
                `ad_neueigner` varchar(100) DEFAULT NULL COMMENT 'Neuer Eigentuemer',
                `ad_changed_id` int(11) DEFAULT NULL COMMENT 'Geändert von',
                `ad_changed_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Geändert am',
                PRIMARY KEY (`ad_id`)
              ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Archivaliendaten'   "
            );
            return;
        }

        if ($table === "{$eiId}_arch_verl") {
            $pdo->exec(
                "CREATE TABLE IF NOT EXISTS {$tableName} (
                `al_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Archiv-ID',
                `ad_id` int(11) DEFAULT NULL COMMENT 'Archiv- ID',
                `ei_id` int(11) DEFAULT NULL COMMENT 'Eigentuemernummer',
                `al_verliehen_an` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_german2_ci DEFAULT NULL COMMENT 'Ausleiher',
                `al_verleihgrund` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_german2_ci DEFAULT NULL COMMENT 'Verleih- Grund',
                `al_verleih_beg` varchar(12) CHARACTER SET utf8mb3 COLLATE utf8mb3_german2_ci DEFAULT NULL COMMENT 'Verleih- Beginn',
                `al_verleih_end` varchar(12) CHARACTER SET utf8mb3 COLLATE utf8mb3_german2_ci DEFAULT NULL COMMENT 'Verleih- Ende',
                `al_zustand_aus` char(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_german2_ci DEFAULT NULL COMMENT 'Zustand bei Ausgabe',
                `al_zustand_ret` char(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_german2_ci DEFAULT NULL COMMENT 'Zustand bei Retourgabe',
                `al_ausg_bild` char(60) CHARACTER SET utf8mb3 COLLATE utf8mb3_german2_ci DEFAULT NULL COMMENT 'Bild Ausgabe',
                `al_ret_bild` char(60) CHARACTER SET utf8mb3 COLLATE utf8mb3_german2_ci DEFAULT NULL COMMENT 'Bild Retour',
                `al_verl_aus_dat` varchar(12) CHARACTER SET utf8mb3 COLLATE utf8mb3_german2_ci DEFAULT NULL COMMENT 'Ausgabe- Datum',
                `al_verl_ret_dat` varchar(12) CHARACTER SET utf8mb3 COLLATE utf8mb3_german2_ci DEFAULT NULL COMMENT 'Retour- Datum',
                `al_changed_at` int(11) DEFAULT NULL COMMENT 'Geändert von',
                `al_changed.at` timestamp NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Geändert am',
                PRIMARY KEY (`al_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Archivalien- Verleih'    "
            );
            return;
        }

    }
}

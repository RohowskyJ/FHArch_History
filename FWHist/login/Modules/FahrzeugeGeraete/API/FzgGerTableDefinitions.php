<?php

declare(strict_types=1);

namespace FWHist\Modules\FahrzeugeGeraete\API;

use InvalidArgumentException;
use PDO;

final class FzgGerTableDefinitions
{
    public static function ensureTenantTable(PDO $pdo, int $eiId, string $table): void
    {
        if ($eiId <= 0) {
            throw new InvalidArgumentException('Die Mandanten-ID muss größer als 0 sein.');
        }

        $tableName = match ($table) {
            "{$eiId}_fza_eigner" => "`{$eiId}_fza_eigner`",
            "{$eiId}_fza_dokumente" => "`{$eiId}_fza_dokumente`",
            "{$eiId}_fza_fahrzeug" => "`{$eiId}_fza_fahrzeug`",
            "{$eiId}_fzm_fahrzeug" => "`{$eiId}_fzm_fahrzeug`",
            "{$eiId}_fzm_geraet" => "`{$eiId}_fzm_geraet`",
            default => throw new InvalidArgumentException("Keine Tabellendefinition für '{$table}' vorhanden."),
        };

        if ($table === "{$eiId}_fza_eigner") {
            $pdo->exec(
                "CREATE TABLE IF NOT EXISTS {$tableName} (
                    `fz_eign_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Fortl. Nr.',
                    `fz_id` int(11) DEFAULT NULL COMMENT 'Fahrzeug Nr.',
                    `fz_docbez` char(100) DEFAULT NULL COMMENT 'Dokument',
                    `fz_zul_dat` char(10) DEFAULT NULL COMMENT 'Zulassung',
                    `fz_zul_end_dat` char(10) DEFAULT NULL COMMENT 'Abmeldung',
                    `fz_zuldaten` char(100) DEFAULT NULL COMMENT 'Besitzer',
                    `fz_changed_id` int(11) DEFAULT NULL COMMENT 'Geändert von',
                    `fz_changed_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Geändert am',
                    PRIMARY KEY (`fz_eign_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Zulassungsdaten'"
            );
            return;
        }

        if ($table === "{$eiId}_fza_fahrzeug") {
            $pdo->exec(
                "CREATE TABLE IF NOT EXISTS {$tableName} (
                    `fz_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Fortlaufende Nummer',
                    `ei_id` int(11) DEFAULT NULL COMMENT 'Eigner-Nr.',
                    `in_id` int(11) DEFAULT NULL COMMENT 'Inventar-RecNr.',
                    `fz_sammlg` varchar(20) DEFAULT NULL COMMENT 'Sammlung',
                    `fz_name` varchar(30) DEFAULT NULL COMMENT 'Rufname',
                    `fz_taktbez` varchar(50) DEFAULT NULL COMMENT 'Taktische Bezeichnung',
                    `fz_hist_bezeichng` varchar(50) DEFAULT NULL COMMENT 'Historische Bezeichnung',
                    `fz_baujahr` varchar(4) DEFAULT NULL COMMENT 'Baujahr',
                    `fz_indienstst` varchar(10) DEFAULT NULL COMMENT 'Indienst- Stellung',
                    `fz_ausdienst` varchar(10) DEFAULT NULL COMMENT 'Ausser Dienst Datum/Jahr',
                    `fz_zeitraum` varchar(5) DEFAULT NULL COMMENT 'Zeitraum',
                    `fz_allg_beschr` varchar(500) DEFAULT NULL COMMENT 'Allgem. Beschreibung',
                    `fz_herstell_fg` varchar(100) DEFAULT NULL COMMENT 'Fahrgestell Hersteller',
                    `fz_motor` varchar(100) DEFAULT NULL COMMENT 'Motor',
                    `fz_typ` varchar(100) DEFAULT NULL COMMENT 'Typ',
                    `fz_modell` varchar(100) DEFAULT NULL COMMENT 'Modell',
                    `fz_aufbauer` varchar(100) DEFAULT NULL COMMENT 'Aufbauer',
                    `fz_aufb_typ` varchar(100) DEFAULT NULL COMMENT 'Aufbau Typ',
                    `fz_bild_1` varchar(50) DEFAULT NULL COMMENT 'Bild 1',
                    `fz_b_1_komm` varchar(200) DEFAULT NULL COMMENT 'Kommentar zu Bild 1',
                    `fz_bild_2` varchar(50) DEFAULT NULL COMMENT 'Bild 2',
                    `fz_b_2_komm` varchar(200) DEFAULT NULL COMMENT 'Kommentar zu Bild 2',
                    `fz_bild_3` varchar(50) DEFAULT NULL COMMENT 'Bild 3',
                    `fz_b_3_komm` varchar(200) DEFAULT NULL COMMENT 'Kommentar zu Bild 3',
                    `fz_bild_4` varchar(50) DEFAULT NULL COMMENT 'Bild 4',
                    `fz_b_4_komm` varchar(200) DEFAULT NULL COMMENT 'Kommentar zu Bild 4',
                    `fz_zustand` varchar(5) DEFAULT NULL COMMENT 'Gesamt Zustand',
                    `fz_ctif_klass` varchar(5) DEFAULT NULL COMMENT 'Klassifizierungs Stufe',
                    `fz_ctif_date` varchar(10) DEFAULT NULL COMMENT 'Juroren',
                    `fz_ctif_juroren` varchar(100) DEFAULT NULL COMMENT 'Datum der Klassifizierung',
                    `fz_ctif_darst_jahr` varchar(4) DEFAULT NULL COMMENT 'Darstellungsjahr',
                    `fz_beschreibg_det` text DEFAULT NULL COMMENT 'Detail- Beschreibung',
                    `fz_eigent_freig` varchar(50) DEFAULT NULL COMMENT 'Eigentümer Freigabe',
                    `fz_verfueg_freig` varchar(50) DEFAULT NULL COMMENT 'Besitzer Freigabe',
                    `fz_pruefg_id` varchar(10) DEFAULT NULL COMMENT 'Prüfer ID',
                    `fz_l_tank` varchar(100) DEFAULT NULL COMMENT 'Tank',
                    `fz_pruefg` varchar(12) DEFAULT NULL COMMENT 'Prüfungs Datum',
                    `fz_l_monitor` varchar(100) DEFAULT NULL COMMENT 'Monitor',
                    `fz_antrieb` varchar(100) DEFAULT NULL COMMENT 'Antrieb',
                    `fz_l_pumpe` varchar(100) DEFAULT NULL COMMENT 'Pumpe',
                    `fz_t_kran` varchar(100) DEFAULT NULL COMMENT 'Kran',
                    `fz_t_winde` varchar(100) DEFAULT NULL COMMENT 'Seilwinde',
                    `fz_t_leiter` varchar(100) DEFAULT NULL COMMENT 'Leiter',
                    `fz_t_abschlepp` varchar(100) DEFAULT NULL COMMENT 'Abschleppvorrichtung',
                    `fz_t_geraet` varchar(100) DEFAULT NULL COMMENT 'Geräte',
                    `fz_geschwindigkeit` varchar(100) DEFAULT NULL COMMENT 'Fahrgeschwindigkeit',
                    `fz_besatzung` varchar(100) DEFAULT NULL COMMENT 'Besatzung',
                    `fz_g_atemsch` varchar(100) DEFAULT NULL COMMENT 'Atemschutz',
                    `fz_t_beleuchtg` varchar(100) DEFAULT NULL COMMENT 'Beleuchtung',
                    `fz_t_strom` varchar(100) DEFAULT NULL COMMENT 'Generator',
                    `fz_changed_id` int(11) DEFAULT NULL COMMENT 'Geändert von',
                    `fz_changed_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Geändert am',
                    PRIMARY KEY (`fz_id`),
                    KEY `fz_ctif_date` (`fz_ctif_date`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Fahrzeugdaten, Motorisiert'"
            );
            return;
        }

   
        
        if ($table === "{$eiId}_fza_dokumente") {
            $pdo->exec(
                "CREATE TABLE IF NOT EXISTS {$tableName} (
                `fb_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Fortl. Nr.',
                `ei_id` int(11) DEFAULT NULL COMMENT 'Mandant. Nr.',
                `fz_id` int(11) DEFAULT NULL COMMENT 'Fahrzeug Nr.',
                `fb_arch_nr` int(11) DEFAULT NULL COMMENT 'Dok- Nr.',
                `fb_changed_id` int(11) DEFAULT NULL COMMENT 'Geändert von',
                `fb_changed_at` timestamp NULL DEFAULT NULL COMMENT 'Geändert am',
                KEY `fb_id` (`fb_id`),
                KEY `ei_id` (`ei_id`),
                CONSTRAINT `{$eiId}_fza_dokumente_ibfk_1` FOREIGN KEY (`fb_id`) REFERENCES `hi_firmen` (`fi_id`),
                CONSTRAINT `{$eiId}_fza_dokumente_ibfk_2` FOREIGN KEY (`ei_id`) REFERENCES `ma_mandanten` (`ei_id`) ON DELETE NO ACTION
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
            );
            return;
        }
        
        if ($table === "{$eiId}_fzm_fahrzeug") {
            $pdo->exec(
                "CREATE TABLE IF NOT EXISTS {$tableName} (
                `fm_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Fortl. Nr.',
                `ei_id` int(11) DEFAULT NULL COMMENT 'Eigner-Nr.',
                `in_id` int(11) DEFAULT NULL COMMENT 'Inventar-RecNr.',
                `fm_bezeich` char(60) DEFAULT NULL COMMENT 'Bezeichnung',
                `fm_komment` text DEFAULT NULL COMMENT 'Kommentar',
                `fm_type` char(60) DEFAULT NULL COMMENT 'Type',
                `fm_leistung` int(11) DEFAULT NULL COMMENT 'Leistung',
                `fm_lei_bed` char(60) DEFAULT NULL COMMENT 'Leistungs- Bedingung',
                `fm_herst` char(60) DEFAULT NULL COMMENT 'Hersteller',
                `fm_baujahr` char(10) DEFAULT NULL COMMENT 'Baujahr',
                `fm_fgstnr` char(60) DEFAULT NULL COMMENT 'Fahrgestell- Nr.',
                `fm_indienst` char(10) DEFAULT NULL COMMENT 'Indienst Stellung',
                `fm_ausdienst` char(10) DEFAULT NULL COMMENT 'Aussesienst- Stellung',
                `fm_zustand` varchar(2) DEFAULT NULL COMMENT 'Zustand',
                `fm_gew` int(11) DEFAULT NULL COMMENT 'Gewicht',
                `fm_zug` char(10) DEFAULT NULL COMMENT 'Zugeinrichtung',
                `fm_foto_1` char(50) DEFAULT NULL COMMENT 'Foto 1',
                `fm_komm_1` text DEFAULT NULL COMMENT 'Kommentar 1',
                `fm_foto_2` char(50) DEFAULT NULL COMMENT 'Foto 2',
                `fm_komm_2` text DEFAULT NULL COMMENT 'Kommentar 2',
                `fm_foto_3` char(50) DEFAULT NULL COMMENT 'Foto 3',
                `fm_komm_3` text DEFAULT NULL COMMENT 'Kommentar 3',
                `fm_foto_4` char(50) DEFAULT NULL COMMENT 'Foto 4',
                `fm_komm_4` text DEFAULT NULL COMMENT 'Kommentar 4',
                `fm_sammlg` varchar(20) DEFAULT NULL COMMENT 'Sammlung',
                `fm_changed_id` int(11) DEFAULT NULL COMMENT 'Geändert von',
                `fm_changed_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Geändert am',
                PRIMARY KEY (`fm_id`),
                FULLTEXT KEY `fm_sammlg` (`fm_sammlg`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Fahrzeuge, Muskelkraft'"
            );
               
            return;
        }
        
        if ($table === "{$eiId}_fzm_geraet") {
            $pdo->exec(
                "CREATE TABLE IF NOT EXISTS {$tableName} (
                `mg_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Fortl. Nr.',
                `ei_id` int(11) DEFAULT NULL COMMENT 'Eigner-Nr.',
                `in_id` int(11) DEFAULT NULL COMMENT 'Inventar-ReNo.',
                `mg_bezeich` char(60) DEFAULT NULL COMMENT 'Bezeichnung',
                `mg_komment` text DEFAULT NULL COMMENT 'Kommentar',
                `mg_type` char(60) DEFAULT NULL COMMENT 'Type',
                `mg_herst` char(60) DEFAULT NULL COMMENT 'Hersteller',
                `mg_baujahr` char(10) DEFAULT NULL COMMENT 'Baujahr',
                `mg_fgstnr` char(60) DEFAULT NULL COMMENT 'Serien- Nummer',
                `mg_indienst` char(10) DEFAULT NULL COMMENT 'Indienst- Stellung',
                `mg_ausdienst` char(10) DEFAULT NULL COMMENT 'Ausserdienst- Stellung',
                `mg_zustand` varchar(2) DEFAULT NULL COMMENT 'Zustand',
                `mg_gew` varchar(10) DEFAULT NULL COMMENT 'Gewicht',
                `mg_zug` varchar(20) DEFAULT NULL,
                `mg_foto_1` char(50) DEFAULT NULL COMMENT 'Foto 1',
                `mg_komm_1` text DEFAULT NULL COMMENT 'Kommentar Foto 1',
                `mg_foto_2` char(50) DEFAULT NULL COMMENT 'Foto 2',
                `mg_komm_2` text DEFAULT NULL COMMENT 'Kommentar Foto 2',
                `mg_foto_3` char(50) DEFAULT NULL COMMENT 'Foto 3',
                `mg_komm_3` text DEFAULT NULL COMMENT 'Kommentar Foto 3',
                `mg_foto_4` char(50) DEFAULT NULL COMMENT 'Foto 4',
                `mg_komm_4` text DEFAULT NULL COMMENT 'Kommentar Foto 4',
                `mg_sammlg` varchar(20) DEFAULT NULL COMMENT 'Sammlungs. Abk.',
                `mg_fzg` varchar(15) DEFAULT NULL COMMENT 'In Fahrzeug',
                `mg_raum` varchar(15) DEFAULT NULL COMMENT 'in Raum',
                `mg_ort` varchar(15) DEFAULT NULL COMMENT 'Wo?',
                `mg_pruef_id` varchar(15) DEFAULT NULL COMMENT 'Prüfer',
                `mg_pruef_dat` varchar(12) DEFAULT NULL COMMENT 'Prüfungs- Datum',
                `mg_changed_id` int(11) DEFAULT NULL COMMENT 'Geändert von',
                `mg_changed_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Geändert am',
                 PRIMARY KEY (`mg_id`),
                 FULLTEXT KEY `mg_sammlg` (`mg_sammlg`)
                 ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Geräte, Muskelkraft'" 
            );
            return;
        }
        
    }
}

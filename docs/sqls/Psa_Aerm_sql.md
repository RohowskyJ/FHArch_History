```sql

-- Adminer 5.4.1 MariaDB 11.7.2-MariaDB dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `pso_ort_ref`;
CREATE TABLE `pso_ort_ref` (
  `fw_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Fortl. Nummer',
  `st_abkzg` char(10) DEFAULT NULL COMMENT 'Landes Code (2Stellig)',
  `fw_st_name` char(100) DEFAULT NULL COMMENT 'Landes Name',
  `bl_blabkz` char(3) DEFAULT NULL COMMENT 'Bundesl.  Abk.',
  `fw_bd_name` char(100) DEFAULT NULL COMMENT 'Bundesl. Name',
  `fw_bz_abk` char(10) DEFAULT NULL COMMENT 'Bezirks- Nr',
  `fw_bz_name` char(100) DEFAULT NULL COMMENT 'Bezirks- Name',
  `fw_ab_nr` char(2) DEFAULT NULL COMMENT 'Abschnitts- Nr',
  `fw_ab_name` char(100) DEFAULT NULL COMMENT 'Abschnitts- Name',
  `fw_gd_nr` char(3) DEFAULT NULL COMMENT 'Gemeinde- Nr',
  `fw_gd_name` char(100) DEFAULT NULL COMMENT 'Gemeinde- Name',
  `fw_gd_art` char(3) DEFAULT NULL COMMENT 'Gde. Art',
  `fw_fw_nr` char(2) DEFAULT NULL COMMENT 'FW Nr (im Bezirk)',
  `fw_fw_name` char(100) DEFAULT NULL COMMENT 'Feuerwehr- Name',
  `fw_fw_typ` char(10) DEFAULT NULL COMMENT 'Feuerwehr- Typ (FF, Btf, BF)',
  `fw_grdg_dat` char(20) DEFAULT NULL COMMENT 'Gründungs- Datum',
  `fw_end_dat` char(20) DEFAULT NULL COMMENT 'Auflösungs- Datum',
  `fw_kommentar` text DEFAULT NULL COMMENT 'Feuerwehr- Kommentar',
  `fw_ort_komm` text DEFAULT NULL COMMENT 'Gemeinde- Kommentar',
  `fw_auszeich` varchar(5) DEFAULT NULL COMMENT 'Auszeichnungen',
  `fw_aermelw` varchar(5) DEFAULT NULL COMMENT 'Ärmel- Abzeichen',
  `fw_changed_id` int(11) DEFAULT NULL COMMENT 'Geändert von',
  `fw_changed_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp() COMMENT 'Geändert am',
  PRIMARY KEY (`fw_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Orts- Verzeichnis';


DROP TABLE IF EXISTS `psw_aermel_abz`;
CREATE TABLE `psw_aermel_abz` (
  `fo_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Fortl. Nummer',
  `fo_fw_id` int(11) DEFAULT NULL COMMENT 'Nummer der Feuerwehr',
  `fo_ff_abzeich` char(60) DEFAULT NULL COMMENT 'Bild- Dateiname',
  `fo_ff_a_sort` int(11) DEFAULT NULL COMMENT 'Sortierfolge',
  `fo_ff_a_typ_a` char(5) DEFAULT NULL COMMENT 'Typ',
  `fo_ff_abz_typ` char(250) DEFAULT NULL COMMENT 'Beschreibung',
  `fo_changed_id` int(11) DEFAULT NULL COMMENT 'Geändert von',
  `fo_changed_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp() COMMENT 'Geändert am',
  PRIMARY KEY (`fo_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Ärmelabzeichen';


DROP TABLE IF EXISTS `psw_ff_wappen`;
CREATE TABLE `psw_ff_wappen` (
  `fo_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Fortlaufende Nummer',
  `fo_fw_id` int(11) DEFAULT NULL COMMENT 'Feuerwehr- Nummer',
  `fo_ff_wappen` char(60) DEFAULT NULL COMMENT 'Bild- Dateiname',
  `fo_ff_w_sort` int(11) DEFAULT NULL COMMENT 'Sortierfolge',
  `fo_ff_w_komm` text DEFAULT NULL COMMENT 'Beschreibung',
  `fo_changed_id` int(11) DEFAULT NULL COMMENT 'Geändert von',
  `fo_changed_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp() COMMENT 'Geändert am',
  PRIMARY KEY (`fo_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Wappen bei Wehren';


DROP TABLE IF EXISTS `psw_ort_wappen`;
CREATE TABLE `psw_ort_wappen` (
  `fo_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Datensatz Nummer',
  `fo_fw_id` int(11) DEFAULT NULL COMMENT 'Feuerwehr Nummer',
  `fo_gde_wappen` char(60) DEFAULT NULL COMMENT 'Bild- Dateiname',
  `fo_gde_w_sort` int(11) DEFAULT NULL COMMENT 'Sortierfolge',
  `fo_gde_w_komment` text DEFAULT NULL COMMENT 'Beschreibung',
  `fo_changed_id` int(11) DEFAULT NULL COMMENT 'Geändert von',
  `fo_changed_at` timestamp NULL DEFAULT NULL COMMENT 'Geändert am',
  PRIMARY KEY (`fo_id`),
  KEY `fo_id` (`fo_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Wappen, Stiftung, Geschichte';


-- 2026-10-31 13:12:07 UTC

```
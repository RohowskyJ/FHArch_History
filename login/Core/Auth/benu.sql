-- Adminer 5.4.1 MariaDB 11.7.2-MariaDB dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `fv_benutzer`;
CREATE TABLE `fv_benutzer` (
  `be_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Fortl. Nr.',
  `be_uid` varchar(255) NOT NULL COMMENT 'User- ID',
  `be_act` enum('','a','i') NOT NULL DEFAULT '' COMMENT 'Aktiv, Inaktiv',
  `be_2fa_secret` varchar(64) DEFAULT NULL COMMENT '2FA Code',
  `be_2fa_enabled` tinyint(1) DEFAULT 0 COMMENT '2FA aktiviert',
  `be_2fa_email` varchar(255) DEFAULT NULL COMMENT 'E-Mail für Bestätigung',
  `be_created_id` int(11) NOT NULL COMMENT 'Erstellt von',
  `be_created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Erstellt am',
  `be_changed_id` varchar(50) NOT NULL COMMENT 'Geändert von',
  `be_changed_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Geändert am',
  PRIMARY KEY (`be_id`),
  UNIQUE KEY `be_uid` (`be_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Benutzer';


DROP TABLE IF EXISTS `fv_ben_dat`;
CREATE TABLE `fv_ben_dat` (
  `fd_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Fortl. Nr.',
  `be_id` int(11) NOT NULL COMMENT 'Benutzer- ID',
  `be_mi_id` int(11) DEFAULT NULL COMMENT 'Mitglieds- ID',
  `fd_anrede` varchar(25) DEFAULT NULL COMMENT 'Anrede',
  `fd_tit_vor` varchar(30) DEFAULT NULL COMMENT 'Vorgestellter Titel',
  `fd_vname` varchar(50) NOT NULL COMMENT 'Vorname',
  `fd_name` varchar(50) NOT NULL COMMENT 'Nachname',
  `fd_tit_nach` varchar(30) DEFAULT NULL COMMENT 'Nachgestellter Titel',
  `fd_adresse` varchar(50) NOT NULL COMMENT 'Adresse',
  `fd_plz` varchar(10) NOT NULL COMMENT 'Post- LZ',
  `fd_ort` varchar(50) NOT NULL COMMENT 'Ort',
  `fd_staat_abk` varchar(10) NOT NULL COMMENT 'Staat',
  `fd_tel` varchar(100) NOT NULL COMMENT 'Telefon, Handy,..',
  `fd_email` varchar(100) DEFAULT NULL COMMENT 'E-Mail(s)',
  `fd_email_status` varchar(10) DEFAULT NULL,
  `fd_hp` varchar(100) DEFAULT NULL COMMENT 'Home- Page',
  `fd_geb_dat` date DEFAULT NULL COMMENT 'Geburtstag',
  `fd_sterb_dat` date DEFAULT NULL COMMENT 'Verstorben am:',
  `fd_austr_dat` date DEFAULT NULL COMMENT 'Beendet am:',
  `fd_created_id` int(11) NOT NULL COMMENT 'Erstellt von',
  `fd_created_at` timestamp NOT NULL COMMENT 'Erstellt am',
  `fd_changed_id` int(11) NOT NULL COMMENT 'Geändert von',
  `fd_changed_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE current_timestamp() COMMENT 'Geändert am',
  PRIMARY KEY (`fd_id`),
  KEY `be_id` (`be_id`),
  CONSTRAINT `fv_ben_dat_ibfk_1` FOREIGN KEY (`be_id`) REFERENCES `fv_benutzer` (`be_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Benutzer- Daten';

DROP TABLE IF EXISTS `fv_erlauben`;
CREATE TABLE `fv_erlauben` (
  `fe_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Forl. Nr.',
  `be_id` int(11) NOT NULL COMMENT 'Benutzer-ID',
  `fe_pw` varchar(256) NOT NULL COMMENT 'Passwort',
  `fe_pw_chgd_id` int(11) NOT NULL COMMENT 'PW geändert von',
  `fe_pw_chgd_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'PW geändert am',
  `fe_created_id` int(11) NOT NULL COMMENT 'Erstellt von:',
  `fe_created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Erstellt am :',
  `fe_changed_id` int(11) NOT NULL COMMENT 'Geändert von:',
  `fe_changed_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Geändert am:',
  PRIMARY KEY (`fe_id`),
  KEY `be_id` (`be_id`),
  CONSTRAINT `fv_erlauben_ibfk_1` FOREIGN KEY (`be_id`) REFERENCES `fv_benutzer` (`be_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Zulassung';

DROP TABLE IF EXISTS `fv_password_resets`;
CREATE TABLE `fv_password_resets` (
  `pw_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Fortl. Nr.',
  `be_id` int(11) NOT NULL COMMENT 'Benutzer- ID',
  `token` varchar(64) NOT NULL COMMENT 'Token für Reset PW',
  `pw_expires` datetime NOT NULL COMMENT 'Ablauf Zeit',
  `pw_used` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Benutzt',
  `pw_created` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Geändert am:',
  PRIMARY KEY (`pw_id`),
  KEY `be_id` (`be_id`),
  CONSTRAINT `fv_password_resets_ibfk_1` FOREIGN KEY (`be_id`) REFERENCES `fv_benutzer` (`be_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `fv_rolle`;
CREATE TABLE `fv_rolle` (
  `fr_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Fortl. Nr.',
  `be_id` int(11) NOT NULL COMMENT 'Benutzer- ID',
  `fl_id` int(11) NOT NULL COMMENT 'Rollen- ID',
  `fr_aktiv` enum('i','a','') NOT NULL DEFAULT 'a' COMMENT 'in- /Aktiv',
  `fr_created_id` int(11) NOT NULL COMMENT 'Erstellt von',
  `fr_created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Erstellt am',
  `fr_changed_id` int(11) NOT NULL COMMENT 'Geändert von',
  `fr_changed_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Geändert  am',
  PRIMARY KEY (`fr_id`),
  KEY `be_id` (`be_id`),
  KEY `fl_id` (`fl_id`),
  CONSTRAINT `fv_rolle_ibfk_1` FOREIGN KEY (`be_id`) REFERENCES `fv_benutzer` (`be_id`),
  CONSTRAINT `fv_rolle_ibfk_2` FOREIGN KEY (`fl_id`) REFERENCES `fv_rollen_beschr` (`fl_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `fv_rolle` (`fr_id`, `be_id`, `fl_id`, `fr_aktiv`, `fr_created_id`, `fr_created_at`, `fr_changed_id`, `fr_changed_at`) VALUES
(1,  1,   1,   'a', 1,   '2024-06-15 08:05:19',   0,   '0000-00-00 00:00:00'),
(2,  2,   14,  'a', 1,   '2024-05-17 16:15:51',   0,   '0000-00-00 00:00:00'),
(3,  3,   13,  'a', 1,   '2026-04-10 15:51:22',   1,   '2026-04-10 13:51:22'),
(4,  4,   14,  'a', 1,   '2016-07-03 11:49:38',   0,   '0000-00-00 00:00:00'),
(5,  5,   14,  'a', 1,   '2021-06-09 11:09:33',   0,   '0000-00-00 00:00:00'),
(6,  6,   14,  'a', 1,   '2016-07-03 11:49:38',   0,   '0000-00-00 00:00:00'),
(7,  7,   14,  'a', 1,   '2021-06-09 11:09:33',   0,   '0000-00-00 00:00:00'),
(8,  8,   14,  'a', 1,   '2024-12-03 10:15:17',   0,   '0000-00-00 00:00:00'),
(225,     225, 14,  'a', 99999,    '2026-03-14 13:37:44',   99999,    '2026-03-09 07:27:22');

DROP TABLE IF EXISTS `fv_rollen_beschr`;
CREATE TABLE `fv_rollen_beschr` (
  `fl_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Fortl. Nr.',
  `fl_beschreibung` varchar(100) NOT NULL COMMENT 'Beschreibung',
  `fl_modules` longtext NOT NULL COMMENT 'Berechtigungen für Module',
  `fl_created_id` int(11) NOT NULL COMMENT 'Ersteller ID:',
  `fl_created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Erstellt am:',
  `fl_changed_id` int(11) NOT NULL COMMENT 'Geändert von:',
  `fl_changed_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Geändert am:',
  PRIMARY KEY (`fl_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `fv_rollen_beschr` (`fl_id`, `fl_beschreibung`, `fl_modules`, `fl_created_id`, `fl_created_at`, `fl_changed_id`, `fl_changed_at`) VALUES
(1,  'Super- Administrator, darf alles und Überall auf diesen Daten',     'ADM-ALLE',    1,   '2026-01-31 14:32:15',   1,   '2026-01-06 17:07:39'),
(2,  'Sachbearbeiter  Fahrzeuge und Geräte',     'ADM-FZG,ADM-BER,ADM-MA,OEF_FOTO,ADM-AB,ADM-FI',  1,   '2026-04-11 05:33:05',   1,   '2026-01-06 17:11:51'),
(3,  'Sachbearbeiter Medien', 'ADM-MEDIA',   1,   '2026-03-16 14:48:16',   1,   '2026-01-06 17:15:13'),
(4,  'Sachbearbeiter Öffentlichkeitsarbeit',     'ADM-OEF',     1,   '2026-03-16 14:48:39',   1,   '2026-01-13 16:08:13'),
(5,  'Sachbearbeiter  Archiv',     'ADM-ARC',     1,   '2026-03-16 14:48:48',   1,   '2026-01-13 16:09:14'),
(6,  'Sachbearbeiter  Inventar',   'ADM-INV',     1,   '2026-03-16 14:49:22',   1,   '2026-01-13 16:11:17'),
(7,  'Sachbearbeiter Persönl. Schutzausrüstung',     'ADM-PSA',     1,   '2026-03-16 14:49:46',   1,   '2026-01-13 16:12:32'),
(8,  'Sachbearbeiter für Mitgliederverwaltung',  'ADM-MI', 1,   '2026-03-16 14:49:56',   1,   '2026-01-17 12:18:35'),
(9,  'Kassier, Berechtigung Mitgliederverwaltung',     'ADM-MB,ADM-MI',    1,   '2026-01-24 12:32:38',   1,   '2026-01-24 12:30:26'),
(10, 'Berechtigung zur Eigentümer- Verwaltung, Benutzer und Berechtigungen',   'ADM-MA', 1,   '2026-02-01 11:58:03',   1,   '2026-01-31 16:32:19'),
(11, 'Schreibrechte Foto- Bearbeitung ',     'OEF-FOTO',    1,   '2026-02-01 11:57:50',   1,   '2026-01-17 13:05:39'),
(12, 'Schreibrechte für Berichte Erstellung',    'OEF-BERI',    1,   '2026-02-01 11:57:27',   1,   '2026-01-17 13:07:08'),
(13, 'Normale Benutzer ohne besondere Rechte (Default- Brechtigung)', 'ONU-ALL',     1,   '2026-02-01 11:57:15',   1,   '2026-01-17 13:08:56'),
(14, 'Gäste - darf fast alles lesen',  'ONU-GAST',    1,   '2026-02-01 11:57:03',   1,   '2026-01-18 13:43:58'),
(15, 'Berechtigung für die Firmen- Liste',  'ADM-FI', 1,   '2026-04-11 05:32:06',   1,   '2026-04-10 13:14:21'),
(16, 'Berechtigugn für die Abkürzungs- Liste',  'ADM-AB', 1,   '2026-04-11 05:32:26',   1,   '2026-04-10 13:15:03');

-- 2026-04-12 13:46:06 UTC

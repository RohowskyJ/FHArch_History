
```sql
 Adminer 5.4.1 MariaDB 11.7.2-MariaDB dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `fv_abk`;
CREATE TABLE `fv_abk` (
  `ab_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Fortl. Nr.',
  `ab_grp` set('MA_F','MU_F','MA_G','MU_G','PA_R','ORG') NOT NULL COMMENT 'Gruppe ',
  `ab_abk` varchar(40) DEFAULT NULL COMMENT 'Abkürzung',
  `ab_bezeichn` varchar(100) DEFAULT NULL COMMENT 'Bezeichnung',
  `ab_gruppe` varchar(10) DEFAULT NULL COMMENT 'Obergruppe',
  `ab_changed_id` int(11) DEFAULT NULL COMMENT 'Geändert von',
  `ab_changed_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Geändert am',
  PRIMARY KEY (`ab_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Abkürzungen für Suchhilfen';


DROP TABLE IF EXISTS `fv_adm_mail`;
CREATE TABLE `fv_adm_mail` (
  `em_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Fortl. Nr.',
  `be_ids` int(11) NOT NULL COMMENT 'Benutzer- ID',
  `em_mail_grp` varchar(5) NOT NULL COMMENT 'Mail- Gruppe',
  `em_active` set('a','i','') NOT NULL COMMENT 'Status a=Aktiv, i =Inaktiv',
  `em_created_uid` int(11) NOT NULL COMMENT 'Erstellt von',
  `em_created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE current_timestamp() COMMENT 'Erstellt am',
  `em_changed_id` varchar(10) NOT NULL COMMENT 'Geändert von',
  `em_changed_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Geändert am',
  PRIMARY KEY (`em_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Admin- Infos';


DROP TABLE IF EXISTS `fv_archivord`;
CREATE TABLE `fv_archivord` (
  `ar_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `ar_sg` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_german2_ci DEFAULT NULL COMMENT 'Sachgeb',
  `ar_sub_sg` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_german2_ci DEFAULT NULL COMMENT 'SubSachgeb',
  `ar_usg` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_german2_ci DEFAULT NULL COMMENT 'Unter-Sachgebiet',
  `ar_sgname` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_german2_ci DEFAULT NULL COMMENT 'SachgebName',
  PRIMARY KEY (`ar_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Archivordnung ÖBFV';


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


DROP TABLE IF EXISTS `fv_bundesld`;
CREATE TABLE `fv_bundesld` (
  `bl_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Fortl. Nummer',
  `bl_stabkz` char(2) NOT NULL DEFAULT '' COMMENT 'Staatenabkürzung',
  `bl_blabkz` char(3) NOT NULL DEFAULT '' COMMENT 'Landesabkürzung',
  `bl_name` varchar(35) NOT NULL DEFAULT '' COMMENT 'Landesname',
  `bl_changed_id` varchar(5) NOT NULL COMMENT 'Geändert von',
  `bl_changed_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE current_timestamp() COMMENT 'Geändert am',
  PRIMARY KEY (`bl_id`),
  KEY `bl_stabkz` (`bl_stabkz`,`bl_blabkz`,`bl_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bundesländer';


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


DROP TABLE IF EXISTS `fv_falinks`;
CREATE TABLE `fv_falinks` (
  `fa_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Fortl. Nummer',
  `fa_link` varchar(145) DEFAULT NULL COMMENT 'Link',
  `fa_url_chkd` varchar(12) NOT NULL COMMENT 'URL Checked',
  `fa_url_obsolete` set('','J','N') NOT NULL COMMENT 'Url Obsolete',
  `fa_text` varchar(100) DEFAULT NULL COMMENT 'Beschreibung',
  `fa_changed_id` int(11) DEFAULT NULL COMMENT 'Änderer',
  `fa_changed_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp() COMMENT 'Änderungsdatum',
  PRIMARY KEY (`fa_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bibliotheken, Archive';


DROP TABLE IF EXISTS `fv_firmen`;
CREATE TABLE `fv_firmen` (
  `fi_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Fortl. Nr.',
  `fi_abk` varchar(30) NOT NULL COMMENT 'Abkürzung',
  `fi_name` varchar(100) NOT NULL COMMENT 'Name',
  `fi_ort` varchar(100) DEFAULT NULL COMMENT 'Ort',
  `fi_Bereich` varchar(200) DEFAULT NULL COMMENT 'Produkte',
  `fi_beginn` varchar(12) DEFAULT NULL COMMENT 'Gründung',
  `fi_ende` varchar(12) DEFAULT NULL COMMENT 'Ende',
  `fi_nachfolger` varchar(300) DEFAULT NULL COMMENT 'Nachfolgefirma',
  `fi_vorgaenger` varchar(100) DEFAULT NULL COMMENT 'Vorgänger-Fa.',
  `fi_funkt` set('','A','F') DEFAULT NULL COMMENT 'Funktion',
  `fi_inet` varchar(100) DEFAULT NULL COMMENT 'URL',
  `fi_url_chkd` varchar(12) NOT NULL COMMENT 'URL Checked',
  `fi_url_obsolete` set('','J','N') NOT NULL COMMENT 'URL Obsolete',
  `fi_changed_ids` varchar(20) NOT NULL DEFAULT ' ' COMMENT 'Geändert von (alt)',
  `fi_changed_id` int(11) NOT NULL COMMENT 'Geändert von:',
  `fi_changed_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE current_timestamp() COMMENT 'Geändert am:',
  PRIMARY KEY (`fi_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `fv_firmen_def`;
CREATE TABLE `fv_firmen_def` (
  `fi_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Fortl. Nr.',
  `fi_abk` varchar(30) NOT NULL COMMENT 'Abkürzung',
  `fi_name` varchar(100) NOT NULL COMMENT 'Name',
  `fi_ort` varchar(100) DEFAULT NULL COMMENT 'Ort',
  `fi_Bereich` varchar(200) DEFAULT NULL COMMENT 'Produkte',
  `fi_beginn` varchar(12) DEFAULT NULL COMMENT 'Gründung',
  `fi_ende` varchar(12) DEFAULT NULL COMMENT 'Ende',
  `fi_nachfolge` varchar(300) DEFAULT NULL COMMENT 'Nachfolge',
  `fi_nachfolger` varchar(300) DEFAULT NULL COMMENT 'Nachfilgefirma',
  `fi_vorgaenger` varchar(100) DEFAULT NULL COMMENT 'Vorgänger-Fa.',
  `fi_funkt` set('','A','F') DEFAULT NULL COMMENT 'Funktion',
  `fi_inet` varchar(100) DEFAULT NULL COMMENT 'URL',
  `fi_url_chkd` varchar(12) NOT NULL COMMENT 'URL Checked',
  `fi_url_obsolete` set('','J','N') NOT NULL COMMENT 'URL Obsolete',
  `fi_changed_id` varchar(20) NOT NULL COMMENT 'Geändert von',
  `fi_changed_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Geändert am',
  PRIMARY KEY (`fi_id`),
  FULLTEXT KEY `fi_name` (`fi_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Firmen';


DROP TABLE IF EXISTS `fv_mi_ehrung`;
CREATE TABLE `fv_mi_ehrung` (
  `me_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Fortl. Nr.',
  `mi_id` int(11) NOT NULL COMMENT 'Mitgl. Nr.',
  `me_ehrung` varchar(100) NOT NULL COMMENT 'Ehrung',
  `me_eh_datum` date NOT NULL COMMENT 'Verleih- Datum',
  `me_begruendg` varchar(256) NOT NULL COMMENT 'Begründung',
  `me_bild1` varchar(50) DEFAULT NULL COMMENT 'Bild 1',
  `me_bild2` varchar(50) DEFAULT NULL COMMENT 'Bild 2',
  `me_bild3` varchar(50) DEFAULT NULL COMMENT 'Bild 3',
  `me_bild4` varchar(50) DEFAULT NULL COMMENT 'Bild 4',
  `me_changed_id` int(11) NOT NULL COMMENT 'Geändert von:',
  `me_changed_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Geändert am:',
  PRIMARY KEY (`me_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Verleihungen';


DROP TABLE IF EXISTS `fv_module`;
CREATE TABLE `fv_module` (
  `fm_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Fortl. Nr.',
  `be_id` int(11) NOT NULL COMMENT 'Benutzer- ID',
  `fm_mod_abk` varchar(30) NOT NULL COMMENT 'Module- Abkürzung | Sub-Mod.',
  `fm_mod_name` varchar(100) NOT NULL COMMENT 'Module Name, Untergruppe',
  `fm_new_id` int(11) NOT NULL COMMENT 'Erstellt von',
  `fm_new_at` timestamp NOT NULL COMMENT 'Erstellt am',
  `fm_changed_id` int(11) NOT NULL COMMENT 'Geändert von',
  `fm_changed_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE current_timestamp() COMMENT 'Geändert am',
  PRIMARY KEY (`fm_id`),
  KEY `be_id` (`be_id`),
  CONSTRAINT `fv_module_ibfk_1` FOREIGN KEY (`be_id`) REFERENCES `fv_benutzer` (`be_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `fv_ord_local`;
CREATE TABLE `fv_ord_local` (
  `al_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `al_sg` varchar(5) DEFAULT NULL COMMENT 'Haupt-SG',
  `al_lcsg` varchar(2) DEFAULT NULL COMMENT 'Loc. Sg',
  `al_lcssg` varchar(2) DEFAULT NULL COMMENT 'Loc Sub SG',
  `al_lcssg_s0` varchar(5) DEFAULT NULL COMMENT 'Sub-Gruppe 0',
  `al_lcssg_s1` varchar(5) DEFAULT NULL COMMENT 'Sub Gruppe 1',
  `al_neu` varchar(30) DEFAULT '07.01 .. .. .. ..' COMMENT 'Neueinteilung',
  `al_bezeich` varchar(100) DEFAULT NULL COMMENT 'Bezeichnung',
  `al_sammlung` varchar(20) DEFAULT NULL COMMENT 'Sammlung',
  `al_changed_id` varchar(10) DEFAULT NULL COMMENT 'Geändert von',
  `al_changed_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Geändert am',
  PRIMARY KEY (`al_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `fv_password_resets`;
CREATE TABLE `fv_password_resets` (
  `pw_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Fortl. Nr.',
  `be_id` int(11) NOT NULL COMMENT 'Benutzer- ID',
  `pw_token` varchar(64) NOT NULL COMMENT 'Token für Reset PW',
  `pw_expires` datetime NOT NULL COMMENT 'Ablauf Zeit',
  `pw_used` int(11) NOT NULL DEFAULT 0 COMMENT 'Benutzt',
  `pw_created` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Geändert am:',
  PRIMARY KEY (`pw_id`),
  KEY `be_id` (`be_id`),
  CONSTRAINT `fv_password_resets_ibfk_1` FOREIGN KEY (`be_id`) REFERENCES `fv_benutzer` (`be_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Token für PW-Reset';


DROP TABLE IF EXISTS `fv_proj_config`;
CREATE TABLE `fv_proj_config` (
  `c_flnr` int(11) NOT NULL COMMENT 'Fortlaufende Nummer',
  `c_Institution` varchar(100) DEFAULT NULL COMMENT 'Name des Betreibers',
  `c_Vereinsreg` varchar(15) DEFAULT NULL COMMENT 'Vereinsregister Nummer',
  `c_Verantwortl` varchar(100) DEFAULT NULL COMMENT 'Name des Verantwortlichen',
  `c_Ver_Tel` varchar(30) DEFAULT NULL COMMENT 'Tel.Nr. des Veratwortlichen',
  `c_email` varchar(60) DEFAULT NULL COMMENT 'E-Mail',
  `c_mode` set('Single','Mandanten') DEFAULT NULL COMMENT 'Betriebsmodus',
  `c_Wartung` set('N','J','U') DEFAULT NULL COMMENT 'Wartungsmodus',
  `c_Wart_Grund` varchar(100) DEFAULT NULL COMMENT 'Wartungsgrund',
  `c_bild_1` varchar(50) DEFAULT NULL COMMENT 'Logo Datei',
  `c_bild_2` varchar(50) DEFAULT NULL COMMENT 'Bild auf 1. Seite',
  `c_Eignr` varchar(100) DEFAULT NULL COMMENT 'Eigentümer wenn Single Mode',
  `c_Homepage` varchar(100) DEFAULT NULL COMMENT 'URL',
  `c_ptyp` varchar(100) DEFAULT NULL COMMENT 'Type',
  `c_store` varchar(100) DEFAULT NULL COMMENT 'Speicherplatz der Daten',
  `c_def_pw` varchar(100) DEFAULT NULL COMMENT 'Default- PW (Neues Mitgl.)',
  `c_data` varchar(10) DEFAULT NULL COMMENT 'DBKenng',
  `c_Perr` varchar(50) DEFAULT NULL COMMENT 'PHP Err-Log-File',
  `c_Debug` varchar(50) DEFAULT NULL COMMENT 'PHP Debog- Log_File',
  `c_bpath` varchar(50) DEFAULT NULL COMMENT 'Basis- Pad',
  `c_miBeitr` varchar(50) DEFAULT NULL COMMENT 'Anz. Beiträge',
  `c_changed_id` int(11) DEFAULT NULL COMMENT 'lezter Änderer',
  `c_changed_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Letzte Änderung',
  PRIMARY KEY (`c_flnr`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='URL Configuration';


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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Rollen je Benutzer';


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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Rollen- Beschreibung';


DROP TABLE IF EXISTS `fv_sammlung`;
CREATE TABLE `fv_sammlung` (
  `sa_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Fortl. Nr',
  `sa_grup` varchar(15) NOT NULL COMMENT 'Sammlg. Gruppe',
  `sa_sammlg` varchar(20) NOT NULL COMMENT 'Sammlungs- Abk.',
  `sa_name` varchar(50) NOT NULL COMMENT 'Sammlungs Name',
  `sa_changed_id` varchar(5) NOT NULL COMMENT 'Geändert von',
  `sa_changed_am` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Geändert am',
  PRIMARY KEY (`sa_id`),
  FULLTEXT KEY `sa_sammlg` (`sa_sammlg`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `fv_staaten`;
CREATE TABLE `fv_staaten` (
  `st_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Fortl. Nr.',
  `st_abkzg` char(10) NOT NULL DEFAULT '' COMMENT 'Staatenabkuerzg lt ISO',
  `st_name` varchar(40) NOT NULL DEFAULT '' COMMENT 'Name des Staates',
  `st_vorwahl` varchar(6) NOT NULL DEFAULT '' COMMENT 'Internationale Vorwahl',
  `st_changed_id` varchar(4) NOT NULL DEFAULT '' COMMENT 'Geändert von',
  `st_changed_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp() COMMENT 'Geändert am',
  PRIMARY KEY (`st_id`),
  KEY `st_abkzg` (`st_abkzg`,`st_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Länder Definitionen';


DROP TABLE IF EXISTS `fv_unterst`;
CREATE TABLE `fv_unterst` (
  `fu_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Fortl. Nr',
  `fu_aktiv` set('J','N') DEFAULT NULL COMMENT 'Aktiv',
  `fu_kateg` set('FG','FA','FF','FK','BP','LB','SP') DEFAULT NULL COMMENT 'Kategorie',
  `fu_weihn_post` set('J','N') DEFAULT NULL COMMENT ' Weihnachts- Post',
  `fu_zugr` set('J','N') DEFAULT NULL COMMENT 'HP- Zugriff',
  `fu_tit_vor` varchar(50) DEFAULT NULL COMMENT 'Vorgesteller Titel',
  `fu_tit_nach` varchar(50) DEFAULT NULL COMMENT 'Nachstehender Titel',
  `fu_anrede` set('Hr.','Fr.','') DEFAULT NULL COMMENT 'Anrede',
  `fu_dgr` varchar(20) DEFAULT NULL COMMENT 'Dienstgrad',
  `fu_vname` varchar(40) DEFAULT NULL COMMENT 'Vorname',
  `fu_name` varchar(50) DEFAULT NULL COMMENT 'Name',
  `fu_plz` varchar(15) DEFAULT NULL COMMENT 'Postleitzahl',
  `fu_ort` varchar(50) DEFAULT NULL COMMENT 'Ort',
  `fu_adresse` varchar(50) DEFAULT NULL COMMENT 'Adresse',
  `fu_tel` varchar(50) DEFAULT NULL COMMENT 'Telefon, Handy',
  `fu_email` varchar(50) DEFAULT NULL COMMENT 'E-Mail Adresse',
  `fu_orgname` varchar(100) DEFAULT NULL COMMENT 'Organisations- Name',
  `fu_changed_ids` varchar(15) DEFAULT ' ' COMMENT 'Geändert von: (alt)',
  `fu_changed_id` int(11) DEFAULT 0 COMMENT 'Geändert von:',
  `fu_changed_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Geändert am.',
  PRIMARY KEY (`fu_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- 2026-10-27 14:19:07 UTC

```
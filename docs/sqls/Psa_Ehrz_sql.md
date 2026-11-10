CREATE TABLE `psa_ausz` (
  `az_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Fortlaufende Nummer',
  `az_fw_id` int(11) DEFAULT NULL COMMENT 'Feuerwehr- Nummer',
  `az_ad_id` int(11) DEFAULT NULL COMMENT 'Ausz-Beschreibungs-Nummer',
  `az_stufe` varchar(15) DEFAULT NULL COMMENT 'Auszeichnungsstufe',
  `az_mat` varchar(15) DEFAULT NULL COMMENT 'Auszeichnungs- Material (Farbe)',
  `az_beschr` varchar(500) DEFAULT NULL COMMENT 'Spezial- Beschreibung',
  `az_bild_v` varchar(50) DEFAULT NULL COMMENT 'Bild Vorderseite',
  `az_bild_r` varchar(50) DEFAULT NULL COMMENT 'Bild Rückseite',
  `az_bild_m` varchar(50) DEFAULT NULL COMMENT 'Bild Miniatur',
  `az_bild_m_r` varchar(50) DEFAULT NULL COMMENT 'Bild Miniatur, Rückseite',
  `az_bild_klsp` varchar(50) DEFAULT NULL COMMENT 'Bild Kleine Spange',
  `az_urkund_1` varchar(50) DEFAULT NULL COMMENT 'Urkunde 1',
  `az_urkund_2` varchar(50) DEFAULT NULL COMMENT 'Urkunde 2',
  `az_changed_id` int(11) DEFAULT NULL COMMENT 'Geändert von',
  `az_changed_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Geändert am',
  PRIMARY KEY (`az_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Auszeichnungen, Obrigkeit, Details';


CREATE TABLE `psa_ausz_ctif` (
  `ac_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Fortlaufende Nummer',
  `ac_fw_id` int(11) DEFAULT NULL COMMENT 'Feuerwehr- Nummer',
  `ac_ab_id` int(11) DEFAULT NULL COMMENT 'Ausz-Beschreibungs-Nummer',
  `ac_stufe` varchar(15) DEFAULT NULL COMMENT 'Auszeichnungsstufe',
  `ac_mat` varchar(15) DEFAULT NULL COMMENT 'Auszeichnungs- Material (Farbe)',
  `ac_beschr` varchar(500) DEFAULT NULL COMMENT 'Spezial- Beschreibung',
  `ac_wettbsp_v` varchar(50) DEFAULT NULL COMMENT 'Wettbewerbsspange',
  `ac_wettb_dok_v` varchar(500) DEFAULT NULL COMMENT 'Beschreibung Vorderseite',
  `ac_wettbsp_r` varchar(50) DEFAULT NULL COMMENT 'Wettbewerbsspange',
  `ac_wettb_dok_r` varchar(500) DEFAULT NULL COMMENT 'Beschreibung Rückseite',
  `ac_gr_med_go_v` varchar(50) DEFAULT NULL COMMENT 'Große Medaille, Gold',
  `ac_gr_med_g_dok_v` varchar(500) DEFAULT NULL COMMENT 'Berschreibung Vorderseite',
  `ac_gr_med_go_r` varchar(50) DEFAULT NULL COMMENT 'Große Medaille, Gold',
  `ac_gr_med_g_dok_r` varchar(500) DEFAULT NULL COMMENT 'Berschreibung Rückseite',
  `ac_gr_med_si_v` varchar(50) DEFAULT NULL COMMENT 'Große Medaille, Silber',
  `ac_gr_med_si_r` varchar(50) DEFAULT NULL COMMENT 'Große Medaille, Silber',
  `ac_gr_med_br_v` varchar(50) DEFAULT NULL COMMENT 'Große Medaille, Bronze',
  `ac_gr_med_br_r` varchar(50) DEFAULT NULL COMMENT 'Große Medaille, Bronze',
  `ac_kl_med_go_v` varchar(50) DEFAULT NULL COMMENT 'Kleine Medaille, Gold',
  `ac_kl_med_g_dok_v` varchar(500) DEFAULT NULL COMMENT 'Berschreibung Vorderseite',
  `ac_kl_med_go_r` varchar(50) DEFAULT NULL COMMENT 'Kleine Medaille, Gold',
  `ac_kl_med_g_dok_r` varchar(500) DEFAULT NULL COMMENT 'Berschreibung Rückseite',
  `ac_kl_med_si_v` varchar(50) DEFAULT NULL COMMENT 'Kleine Medaille, Silber',
  `ac_kl_med_si_r` varchar(50) DEFAULT NULL COMMENT 'Kleine Medaille, Silber',
  `ac_kl_med_br_v` varchar(50) DEFAULT NULL COMMENT 'Kleine Medaille, Bronze',
  `ac_kl_med_br_r` varchar(50) DEFAULT NULL COMMENT 'Kleine Medaille, Bronze',
  `ac_so_med_go_v` varchar(50) DEFAULT NULL COMMENT 'Sonder- Ausgabe, Gold',
  `ac_so_med_g_dok_v` varchar(500) DEFAULT NULL COMMENT 'Berschreibung Vorderseite',
  `ac_so_med_go_r` varchar(50) DEFAULT NULL COMMENT 'Sonder- Ausgabe, Gold',
  `ac_so_med_g_dok_r` varchar(500) DEFAULT NULL COMMENT 'Berschreibung Rückseite',
  `ac_so_med_si_v` varchar(50) DEFAULT NULL COMMENT 'Sonder- Ausgabeg, Silber',
  `ac_so_med_si_r` varchar(50) DEFAULT NULL COMMENT 'Sonder- Ausgabe, Silber',
  `ac_so_med_br_v` varchar(50) DEFAULT NULL COMMENT 'Sonder- Ausgabe, Bronze',
  `ac_so_med_br_r` varchar(50) DEFAULT NULL COMMENT 'Sonder- Ausgabe, Bronze',
  `ac_so_beschr_1` varchar(1000) DEFAULT NULL COMMENT 'Beschreibung Sonderausgabe',
  `ac_fabz_v` varchar(50) DEFAULT NULL COMMENT 'Festabzeichen',
  `ac_fabz_dok_v` varchar(500) DEFAULT NULL COMMENT 'Berschreibung Vorderseite',
  `ac_fabz_r` varchar(50) DEFAULT NULL COMMENT 'Festabzeichen',
  `ac_fabz_dok_r` varchar(500) DEFAULT NULL COMMENT 'Berschreibung Rückseite',
  `ac_teiln_v` varchar(50) DEFAULT NULL COMMENT 'Teilnehmerabzeichen',
  `ac_teiln_dok_v` varchar(500) DEFAULT NULL COMMENT 'Berschreibung Vorderseite',
  `ac_teiln_r` varchar(50) DEFAULT NULL COMMENT 'Teilnehmerabzeichen',
  `ac_teiln_dok_r` varchar(500) DEFAULT NULL COMMENT 'Berschreibung Rückseite',
  `ac_urkund_1` varchar(50) DEFAULT NULL COMMENT 'Urkunde 1',
  `ac_urk_beschr_1` varchar(1000) DEFAULT NULL COMMENT 'Beschreibung Urkunde 1',
  `ac_urkund_2` varchar(50) DEFAULT NULL COMMENT 'Urkunde 2',
  `ac_urk_beschr_2` varchar(1000) DEFAULT NULL COMMENT 'Beschreibung Urkunde 2',
  `ac_changed_id` int(11) DEFAULT NULL COMMENT 'Geändert von',
  `ac_changed_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Geändert am',
  PRIMARY KEY (`ac_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='CTIF Auszeichnungen, Detail';


CREATE TABLE `psa_ausz_ve` (
  `av_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Fortlaufende Nummer',
  `av_fw_id` int(11) DEFAULT NULL COMMENT 'Feuerwehr- Nummer',
  `av_ab_id` int(11) DEFAULT NULL COMMENT 'Ausz-Beschreibungs-Nummer',
  `av_sort` varchar(15) DEFAULT NULL COMMENT 'Sortierung',
  `av_mat` varchar(15) DEFAULT NULL COMMENT 'Auszeichnungs- Material (Farbe)',
  `av_beschr` varchar(500) DEFAULT NULL COMMENT 'Spezial- Beschreibung',
  `av_bild_v` varchar(50) DEFAULT NULL COMMENT 'Bild Vorderseite',
  `av_bild_r` varchar(50) DEFAULT NULL COMMENT 'Bild Rückseite',
  `av_beschr_v` varchar(500) DEFAULT NULL COMMENT 'Beschreibung Vorderseite',
  `av_beschr_r` varchar(500) DEFAULT NULL COMMENT 'Beschreibung Rückseite',
  `av_urkund_1` varchar(50) DEFAULT NULL COMMENT 'Urkunde 1',
  `av_urkund_2` varchar(50) DEFAULT NULL COMMENT 'Urkunde 2',
  `av_changed_id` int(11) DEFAULT NULL COMMENT 'Geändert von',
  `av_changed_at` timestamp NULL DEFAULT current_timestamp() COMMENT 'Geändert am',
  PRIMARY KEY (`av_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Auszeichnungen Vereine, Details';


CREATE TABLE `psa_az_gruppe` (
  `ad_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Fortl. Nummer',
  `ad_fw_id` int(11) DEFAULT NULL COMMENT 'FW- Nummer',
  `ad_ab_id` int(11) DEFAULT NULL COMMENT 'Ausz. -Nummer',
  `ad_name` varchar(500) DEFAULT NULL COMMENT 'Name der Ausz.',
  `ad_detail` varchar(1000) DEFAULT NULL COMMENT 'Verleihung',
  `ad_extern` varchar(100) DEFAULT NULL COMMENT 'Externe Bechreibung',
  `ad_stufe` varchar(10) DEFAULT NULL COMMENT 'Sortierung',
  `ad_bez` varchar(50) DEFAULT NULL COMMENT 'Ausz. in B/S/G',
  `ad_abmesg` varchar(30) DEFAULT NULL COMMENT 'Abmessung in mm',
  `ad_band` varchar(1000) DEFAULT NULL COMMENT 'Bandbeschreibung',
  `ad_vorderseite` varchar(1000) DEFAULT NULL COMMENT 'Beschreibung Vorderseite',
  `ad_rueckseite` varchar(1000) DEFAULT NULL COMMENT 'Beschreibung Rückseite',
  `ad_stiftg_date` date DEFAULT NULL COMMENT 'Stiftungsdatum',
  `ad_statut` varchar(250) DEFAULT NULL COMMENT 'Statuten',
  `ad_erklaer` varchar(250) DEFAULT NULL COMMENT 'Erklärung',
  `ad_changed_id` int(11) DEFAULT NULL COMMENT 'Geändert von',
  `ad_changed_at` timestamp NULL DEFAULT current_timestamp() COMMENT 'Geändert am',
  PRIMARY KEY (`ad_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Auszeichnungen je Stiftung';


CREATE TABLE `psa_stifter` (
  `ab_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Fortlaufende Nummer',
  `ab_fw_id` int(11) DEFAULT NULL COMMENT 'Stifter-(FW)- Nummer',
  `ab_art` varchar(10) DEFAULT NULL COMMENT 'Auszeichnungs- Art',
  `ab_beschreibg` varchar(1000) DEFAULT NULL COMMENT 'Name der Auszeichnung',
  `ab_stifter` varchar(10) DEFAULT NULL COMMENT 'Stifter: Zivile Verwaltung / FW-Verwaltung',
  `ab_stiftg_datum` date DEFAULT NULL COMMENT 'Stiftungs- Datum',
  `ab_statut` varchar(250) DEFAULT NULL COMMENT 'Link zu den Statuten',
  `ab_erklaerung` varchar(250) DEFAULT NULL COMMENT 'Link zur Erklärung',
  `ab_changed_id` int(11) DEFAULT NULL COMMENT 'Geändert von',
  `ab_changed_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Geändert am',
  PRIMARY KEY (`ab_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Auszeichnungen, Stifter';


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

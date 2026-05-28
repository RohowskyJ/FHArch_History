<?php
/**
 * Defintion der Konstanten aller Bereiche
 *
 * 2024-05-02 neu Josef Rohowsky
 *
 */

/**
 * config_m.ini Parameter- Bedeutung
 *  Funktion
 * J .. Aktiv, N .. Inaktiv
 *
 * m_1  Archiv
 * m_2  Buchbesprechung
 * m_3  Fzg/Geräte
 * m_4
 * m_5  Inventur
 * m_6  Terminplan
 * m_7  Biete/Suche
 * m_8  Presseberichte
 * m_9  Suchen nach Suchbegriffen
 * m_10
 * m_11
 * m_12 Protokolle
 * m_13 Persönl. Ausrüstung
 * m_14 Dokumente
 * m_15 Feuerwehrzeitschriften
 *
 */

/**
 * Mandanten- Module - braucht Berechtigung für Mandanten
 *
 */
const MANDANTEN_MODS = "ADM-FZG, ADM-FOTO,ADM_BER, ADM-ARC, ADM-INV, ADM-MEDIA";

/**
 * Definition der Konstante für Ja/Nein Abfragen
 *
 * @var array  VF_JN
 */
const VF_JN = array(
    'J' => 'Ja',
    'N' => 'Nein'
);

/**
 * Konstante für Berichte erstellen: Unterseiten J/N
 */
const VF_Unterseiten = array('J' => 'Bericht mit Unterseiten','N' => 'Bericht Einseitig');

/**
 * Konstante für die Nutzung der Fahrzeugbeschreibungen
 */
const VF_Fahrzeugbeschr = array('J' => 'Fahzeugbeschreibungen nutzen','N' => 'keine zusätliche Beschreibung nutzen');

/**
 * Konstante für Urheber- Zuodnung, Media- Definition
 * @var array
 */
const VF_Foto_Video = array("A" => "Audio","F" => "Foto","I" => "Film","V" => "Video");

/**
 * Definiton der Konstante für die Einteilung der abgespeicherten, herunterladbarer Dokumente
 *
 * ersetzt func vF_Sel_ Doc_thema
 *
 * @var array VF_Doku_Art
 */
const VF_Doku_Art = array(
    'ab' => 'Aus- und Weiterbildung',
    'as' => 'Atemchutz',
    'aut' => 'Automobile',
    'az' => 'Auszeichnungen, Ehrungen, Leistungsabzeichen',
    'bew' => 'Bewerbe',
    'fa' =>  'Fahrzeuge allgemein',
    'fg' => 'Firmengeschchte',
    'fw' => 'Feuerwehr. allgemein',
    'ge' => 'Feuerwehrgeschichte',
    'ka' => 'Kataloge (Werbematerial)',
    'kb' => 'Katalog der FG (Broschüren)',
    'mus' => 'Muskelbewegte Fahrzeuge und Geräte',
    'pa' => 'Persönliche Ausrüstung',
    'pl' => 'Persönlichkeiten und Lebensläufe',
    'sa' => 'Sammlungen betreffend',
    'sp' => 'Sprengdienst',
    'td' => 'Tauchdienst',
    'wd' => 'Wasserdienst'
    
);

const VF_Doku_SG = array(
    '1' => 'mit Muskelkraft bewegte Fahrzeuge und Geräte',
    '2' => 'mit Motorkraft bewegte Fzg und Geräte',
    '3' => 'Sonstige Geräte',
    '4' => 'Persönliche Ausrüstung',
    '5' => 'Dokumentation',
    '6' => 'Museen',
    '7' => 'Kataloge'
    
);

/**
 * Fahrzeug-Beschaffungs- Zeiträume
 *
 * @var array VF_FZG_Aera
 */
const VF_FZG_Aera = array(
    '1' => 'bis 1929 - Motorisierungs- Beginn',
    '2' => '1930 - 1937 - Motorisierung, 2. Phase',
    '3' => '1938 - 1945 - Normierte Fahrzeuge - 2. Weltkrieg - Deutsches Reich',
    '4' => '1946 - 1950 - Aufbau auf Fahrgestellen aus alliierten Restbeständen' ,
    '5' => '1951 - 1975 - Fahrzeug Neubeschaffungen',
    '6' => '1976 - 2000 - ',
    '7' => '2001 - 2025 - ',
    '8' => '2026 - 2050 - ',
    '9' => '2051 - 2075 - ',
    '10' => '2076 - '
);

/**
 * Definiton der Konstante für die Inbetriebnahme oder Anschaffung von Geräten und Fahrzeugen
 *
 * @var array VF_Epoche
 */
const VF_Epoche = array(
    "999" => "Nicht Bekannt",
    "0" => "Vorrömische Zeit (bis ca. 500 v. Chr.)",
    "1" => "Römische Zeit (300 v.Chr - 488 n. Chr.)",
    "2" => "Mittelalter (489 - 1500 n. Chr.",
    "3" => "Neuzeit (1500 - 18xx n.Chr",
    "4" => "Gründungszeit der 1. Freiwilligen Feuerwehren",
    "5" => "1. Weltkrieg und Zwischenkriegszeit (1914 - 1918 - 1938)",
    "6" => "2. Weltkrieg (1939 - 1945)",
    "7" => "Nachkriegszeit (ab 1946)"
);


const VF_Sammlung_Typ_Ausw = array(
    "MA_F" => 'Maschinen- getriebene Fahrzeuge',
    'MA_G' => 'Maschinen- betriebene Geräte',
    'MU_F' => 'Muskel- gezogene Fahrzeuge',
    'MU_G' => 'Muskel- betriebene Geräte'
);

/**
 * Neuer Versuch, Arrsays trennen
 *
 */
const MA = [
    'MA_F' => 'Maschinengetriebene Fahrzeuge',
    'MA_G' => 'Maschinengetriebene Geräte'
];
const MU = [
    'MU_F' => 'Muskel- gezogene Fahrzeuge',
    'MU_G' => 'Muskelbetriebene Geräte'
];

/**
 * Definitionen zur Sammlung- Auswahl, wie im´n der Archivordnung ar_ord_loc_xy definiert,
 * und mittel MUlti-Select anzuwenden in FZG und Geräte- Wartung und Suche
 *
 *
 * @var array $sammlung
 */
const VF_Sel_SA_Such = array(  // für Suchfunktionen und Inventar
    'MA_F' => 'von Maschinen angetriebene Fahrzeuge',
    'MA_G' => 'Von Maschinen angetriebene Geräte',
    'MU_F' => 'Mit Muskeln bewegte Transportmittel',
    'MU_G' => 'Mit Muskeln betriebene Geräte',
    'PA_R' => 'Persönliche Ausrüstung',
    'AR_U' => 'Geschichte in Wort und Bild',
    'KA_A' => 'Kaiserreich Österreich (bis 1918)',
    'WW_2' => '2. Weltkrieg'
);


const VF_Sel_SA_MA = array(  // für Fahrzeuge ud Geräte
    'MA_F' => 'von Maschinen angetriebene Fahrzeuge',
    'MA_G' => 'Von Maschinen angetriebene Geräte'
);
const VF_Sel_SA_MU = array(  // für Fahrzeuge ud Geräte
    'MU_F' => 'Mit Muskeln bewegte Transportmittel',
    'MU_G' => 'Mit Muskel betriebene Geräte'
);

const VF_Sel_SA_MA_F = array(  // für Fahrzeuge ud Geräte
    'MA_F' => 'von Maschinen angetriebene Fahrzeuge'
);

const VF_Sel_SA_MU_F = array(  // für Fahrzeuge ud Geräte
    'MU_F' => 'Mit Muskeln bewegte Transportmittel'
);
const VF_Sel_SA_MA_G = array(  // für Fahrzeuge ud Geräte
    'MA_G' => 'Von Maschinen angetriebene Geräte'
);
const VF_Sel_SA_MU_G = array(  // für Fahrzeuge ud Geräte
    'MU_G' => 'Mit Muskeln betriebene Geräte'
);

const VF_Sel_SA_PA = array(   // für Perönliche Schutzausrüstung
    'PA_R' => 'Persönliche Ausrüstung'
);

/**
 * Definitionen für Glossary
 *
 *
 * @var array $sammlung
 */
const VF_Abk = array(  // Glossar
    'MA_F' => 'von Maschinen angetriebene Fahrzeuge',
    'MA_G' => 'Von Maschinen angetriebene Geräte',
    'MU_F' => 'Mit Muskeln bewegte Fahrzeuge',
    'MU_G' => 'Mit Muskeln betriebene Geräte',
    'PA_R' => 'Persönliche Ausrüstung'
);

/**
 * Definitionen für Abkürzunges- Gruppen
 *
 *
 * @var array $sammlung
 */
const VF_Abk_Grp = array(  // Glossar
    'MA_F' => 'von Maschinen angetriebene Fahrzeuge',
    'MA_G' => 'Von Maschinen angetriebene Geräte',
    'MU_F' => 'Mit Muskeln bewegte Fahrzeuge',
    'MU_G' => 'Mit Muskeln betriebene Geräte',
    'PA_R' => 'Persönliche Ausrüstung',
    'ORG'  => 'Organisatorisches'
);

### alte Definitionen, nach Umstellung löschen
/**
 * Definition der Konstante für die Sammlungen zur Auswahl der Tabellen für die Beschreibungen
 *
 * @var array VF_Sammlung_TAusw
 */
const VF_Sammlung_TAusw = array(
    'MU_F' => 'Mit Muskelkraft bewegte Geräte und Fahrzeuge',
    'MA_F' => 'Mit Motorkraft bewegte Fahrzeuge und Geräte',
    'PA_R' => 'Geräte und Ausrüstung',
    'D0' => 'persönliche Ausrüstung (Uniformen, Auszeichnungen, ...',
    'F0' => 'Geschichte in Wort und Bild',
    'K0' => 'Kaiserreich Österrreich (bis 1918)',
    'W0' => 'Zweiter Weltkrieg, Deutsches Reich, 1936 - 1945'
);

const VF_Sel_AOrd = array(     // Für Archivalien
    '01' => 'Lokaler Feuerwehrdienst, Finanzen, Protokolle',
    '02' => 'Behörden, andere Institutionen und Vereine',
    '03' => 'Übergeordnete Dienststellen',
    '04' => 'Gleichgestellte Dienststellen',
    '05' => 'Gesetze, Richtlinien, TRVB',
    '06' => 'Bewerbe, Leistungspüfungen und Ausbildungsprüfungen',
    '07' => 'Feuerwehr- Fahrzeuge, Häuser, Geräte',
    '08' => 'Geschäftsverbindungen',
    '09' => 'Bilder/CDs/Dias/Filme/Fotos/Negativablage/Plaene',
    '10' => 'Literatur'
);

/**
 * Definition der Konstante für Anmeldung zu einer Termin-Plan- Veranstaltung
 *
 * @var array VF_Term_Umfang
 */
const VF_Term_Umfang = array(
    'G' => 'mit Gästen',
    'M' => 'nur Mitglieder',
    '9' => 'Keine Angaben'
);

/**
 * Definition der Konstanten für die Terminplanung
 *
 * @var array VF_Term_Kateg
 */
const VF_Term_Kateg = array(
    'A' => 'Allgemein',
    'F' => 'mit Oldtimern',
    'L' => 'Lehrgang',
    '9' => 'Unspezifiziert'
);

/**
 * Definition der Konstanten für das Hochladen von Dateien
 *
 * @var array VF_zuldateitypen
 */
const VF_zuldateitypen = array(
    'image/png',
    'image/jpeg',
    'image/gif',
    'image/JPG',
    'image/PNG',
    'image/GIF',
    'image/bmp',
    'image/BMP',
    'application/pdf',
    'application/PDF'
);

/**
 * Definition Zertifizierungsklasse CTIF
 */
const VF_CTIF_Class = array("" => "","I" => "1. Klasse","II" => "2. Klasse","III" => "3. Klasse");

/**
 * mime- Typen für Dokumente- Upload
 *
 * @var array VF_upl_DOC Mime Typen für upload von Dokumenten
 */
const VF_upl_DOC = array(
    'application/pdf'
);

/**
 * Mime-- Typen für upload von Bild- Materialien (Bild/Video)
 *
 * @var array
 */
const VF_upl_PICT = array(
    'image/png',
    'image/jpg',
    'image/jpeg',
    'image/gif',
    'image/tiff',
    'image/bmp',
    'video/mp4',
    'video/mpeg'
);

/**
 * Definition der Konstante für den Mailversand
 *
 * @var string VF_Spam_Text
 */
const VF_Spam_Text = '<p>
  <span style="color:red; font-weight:bold;">Achtung:</span>
  Manche Provider klassifizieren die an Sie gesendete systemgenerierte E-Mail Nachricht als SPAM
  (<span style="font-size:small; font-style:italic;">Als Spam oder Junk (Englisch für <q>Müll</q>) werden unerwünschte,
  in der Regel auf elektronischem Weg übertragene Nachrichten (Informationen) bezeichnet,
  die dem Empfänger unverlangt zugestellt werden mit häufig unerwünschten werbenden Inhalt.</span>).
  Sehen Sie also auch in Ihrem SPAM-Ordner nach!
</p>';

/**
 * Defintion der Konstante für Formular, Hinweise
 *
 * @var string
 */
const VF_Hinweis_Felder = "<div class='white'>
   Füllen Sie bitte die Felder des Formulars aus.
   Die mit <span style='color:red'>*</span> gekennzeichneten Felder sind Pflichtfelder.
</div>";

/**
 * Dfinition der Konstante für Mailersand
 *
 * @var string VF_Gruesse
 */
const VF_Gruesse = '<p style="margin-top:1em">Mit freundlichen Grüßen<br/>Das Feuerwehrhistoriker Team</p>';

/**
 * Fahrzeuge und Geräte
 */
const VF_Zustand = array(
    '' => 'Unbekannt',
    'eb' => 'Einsatzbereit',
    'fb' => 'Fahrbereit',
    'ok' => 'keine Beanstandung, alles OK',
    'rp' => 'Reparaturbedürftig',
    'rs' => 'Restauration notwendig',
    'vk' => 'Abgegeben',
    'xx' => 'Verschrottet'
);

/**
 * Persönliche Ausrüstung
 *
 */
const VF_Ausz = array(
    'AE' => 'Auszeichnungen und Ehrungen',
    'AV' => 'Verdienstzeichen',
    'AV-A' => 'Ausbilder- Verdienstzeichen',
    'AV-B' => 'Bewerter- Verdienstzeichen',
    'JU' => 'Jugend',
    'FL-A' => 'Leistungsabzeichen Klassisch (Land)',
    'FL-F' => 'Leistungsabzeichen Funk',
    'FL-SP' => 'Leistungsabzeichen Sprengen',
    'FL-ST' => 'Leistungsabzeichen Strahlensch.',
    'FL-WA' => 'Leistungsabzeichen Wasser',
    'FL-A-AS' => 'Ausbildungsprüfung Atemchutz',
    'FL-A-FB' => 'Ausbildungsprüfung Feuerwehrboote',
    'FL-A-LE' => 'Ausbildungsprüfung Löscheinsatz',
    'FL-A-TE' => 'Ausbildungsprüfung Technicher Einsatz',
    'VE' => 'Vereinsabzeichen',
    'VG' => 'Gemeinde-/FF- Auszeichnungen',
    'VO' => 'ÖRK - Österr. Rotes Kreuz',
    'EM' => 'Erinnerungs- Medaillen' ,
    'UN' => 'Unbekannte Verwendung',
    'ZI' => 'Zivilabzeichen'
    
);

const Aermelabz_text  = array('TE' => 'Spezielle Beschreibung notwendig',
    'BR' => 'Ärmelabzeichen für die Braune Uniform',
    'BB' => 'Ärmelabzeichen für die Braune oder Blaue Uniform',
    'BG' => 'Ärmelabzeichen für die Braune oder Grüne Uniform',
    'BL' => 'Ärmelabzeichen für die Blaue Uniform',
    'HE' => 'Ärmelabzeichen für das Hemd',
    'PO' => 'Abzeichen für das Poloshirt, Fleecejacke, ...',
    'GR' => 'Ärmelabzeichen für die Grüne Uniorm',
    'FR' => 'Zugehörigkeitsabzeichen auf Freizeitkleidung (ev. Verein)',
    'XX' => 'Noch nicht näher bestimmte Verwendung'
);

const Stifter = array('  ' => 'keine Definition',
    'LA' => 'Land, Staat, Gemeinde - Ziviler Stifter',
    'LF' => 'Feuerwehr als Stifter (LF- Bez-, FF- KDO)'
);

/**
 * Öffentlichkeitsarbeit
 *
 */
const Arc_Type = array(
    'AU' => 'Ausweis, Pass'
    ,'DO' => 'Dokument'
    ,'EI' => 'Einladung'
    ,'PL' => 'Plakat'
    ,'PR' => 'Protokoll'
    ,'PRU' => 'Protokoll, Übersetzt'
    ,'PRB' => 'Protokollbuch'
    ,'PRBU' => 'aus Protokollbuch, Übersetzt'
    
);

const VF_Arc_Format = array(
    'A0' => 'DIN A0'
    ,'A1' => 'DIN A1'
    ,'A2' => 'DIN A2'
    ,'A3' => 'DIN A3'
    ,'A4' => 'DIN A4'
    ,'A5' => 'DIN A5'
    ,'BU' => 'Buch'
    ,'HE' => 'Heft'
);

const VF_Unterst = array(
    'BP' => 'Besondere Persönlichkeiten'
    ,'FA' => 'Zeitschriften'
    ,'FF' => 'Befreundete Feuerwehren'
    ,'FK' => 'Befreundete Kameraden'
    ,'FG' => 'Landes- SB'
    ,'SP' => 'Sponsoren'
);

const VF_Anrede = array( "Fr." => "Frau", "Hr." => "Herr","" => 'Keine');

const VF_Mus_Typ = array(
    '0' => 'Sammlung, Depot'
    ,'1' => 'Schausammlung'
    ,'2' => 'Traditionsraum'
    ,'3' => 'Schauraum, Museum'
);

const VF_Mus_Oeffzeit = array(
    'G' => 'Ganzjährig','S' => 'Saison','V' => 'nur nach Vereinbarung', ' ' => 'Nicht definiert'
);


/**
 * von XRF, für Hochladen
 *
 */
const DocFiles = array("pdf"); // nach Archivordnung
const GrafFiles = array("gif","ico","jpeg","jpg","png","tiff"); // 09/06
const VideoFiles = array("mp4"); // 09/10
const ScrFiles  = array("css","htm","php","js","inc");
const TxtFiles  = array("csv","dbf","doc","log","pps","odp","odt","txt","xml");
const AudioFiles = array("mp3", "aac", "wav"); // 09/02

/**
 * Verzeichnisse für die OEF- Daten
 */
const SubMod_Dirs = array(
    'AN' => 'Biete_Suche',
    'BU' => 'Buch',
    'MP' =>  'Marktplatz',
    'MUS' =>  'Museen',
    'PR'  =>  'Presse',
    'TE'  =>  'Termine',
    'PSA|AERM' => 'PSA/AERM',
    'PSA|AUSZ' => 'PSA/AUSZ',
    'PSA|DA'   => 'PSA/DA'
);
/**
 * Organisaionen
 *
 *
 */
const Org_Typ = array("P" => "Prvat","V" => "Verein","F" => "Feuerwehr","GV" => "Behörde","KI" => "Kirchliche Struktur","L" => "Feuerwehrstruktur AFKDO bis CTIF");

/**
 * Definition der Konstante der Mitglieder- Art
 *
 * @var array M_Typ
 */
const M_Typ = array(
    "UM" => "Unterstützendes Mitglied",
    "FG" => "Sachbearbeiter Feuerwehrgeschichte",
    "EM" => "Ehrenmitglied",
    "OE" => "Ohne Erlagschein - Fahrzeughalter"
);

/**
 * Definition der Konstante für die Vorstands- Funktion des Mitgliedes im Verein
 *
 * @var M_Funktion
 */
const V_Funktion = array(
    " " => "keine Funktion",
    "O" => "Vereinsobmann",
    "P" => "1. Obmann Stellvertreter",
    "Q" => "2. Obmann Stellvertreter",
    "F" => "Finanzen",
    "G" => "Finanz Stellvertreter",
    "J" => "Jurist",
    "S" => "Schriftführer",
    "T" => "Schriftführer Stellvertreter"
);

/**
 * Definition der Konstante für die Referats-Leiter Funktion und des bekundeten Interesses (Mitarbeit oder Info) des Mitgliedes im Verein
 *
 * @var M_Funktion
 */
const L_Funktion = array(
    " " => "keine Funktion",
    "1" => "Referatsleiter 1",
    "2" => "Referatsleiter 2 - Fahrzeuge",
    "3" => "Referatsleiter 3 - Öffentlichkeitsarbeit",
    "4" => "Referatsleiter 4 - Persönliche Ausrüstung"
);

const I_Funktion = array(
    " " => "keine Preferenz",
    "2" => "Fahrzeuge und Geräte",
    "3" => "Öffentlichkeitsarbeit",
    "4" => "Persönliche Ausrüstung"
);

/**
 * Definition der Konstante für die Organisation des Mitgliedes
 *
 * @var M_Org
 */
const M_Org = array(
    "Privat" => "Privatperson",
    "FM" => "Feuerwehrmuseum",
    "FF" => "Feuerwehr",
    "V" => "Verein",
    "AFKDO" => "AFKDO",
    "BFKDO" => "BFKDO",
    "LFKDO" => "LFKDO"
);

/**
 * Definition der Konstante für die zur Erlaubnis der Verarbeitung der persönlichen Daten
 *
 * @var M_Einv
 */
const M_Einv = array(
    "ONL" => "Online abgegeben",
    "PAP" => "Unterschriebenes Dokument"
);

/**
 * Definition der Egentümer- Orgnisation
 *
 * @var VF_Eig_Org_Typ
 */
const VF_Eig_Org_Typ = array(
    "" => "Kein Eintrag",
    "Privat" => "Privatperson",
    "BtF" => "Betries- Feuerwehr",
    "Fa."  => "Firma, Betrieb",
    "Fa"  => "Firma, Betrieb",
    "FM" => "Feuerwehrmuseum",
    "FF" => "Feuerwehr",
    "V" => "Verein",
    "AFKDO" => "AFKDO",
    "BFKDO" => "BFKDO",
    "LFKDO" => "LFKDO",
    "GV" => "Organisation, Staat/Land/Gde, Kirche"
);

/**
 * Definition der Konstante für die Zugriffs- Berechtigungen
 * Umstellung 202402 bei Reorg ser HP
 */
const VF_Berechtig = array(
    'V' => 'Administrator = alle Rechte',
    'Q' => 'Schreiben, Ändern',
    'A' => 'nur Lesen',
    'N' => 'keine Rechte'
);

/**
 * Definition der altenZugriffs- Berechtigungen
 * wird nach Abschluß der Umstellung nicht mehr gebraucht
 */
const VF_Berechtig_Z = array(
    'V' => 'Administrator = alle Rechte',
    'Q' => 'Schreiben, Ändern',
    'A' => 'nur Lesen',
    'N' => 'keine Rechte',
    'Z' => 'Administrator = Superuser'
);

/**
 * Definition der Refrate (neu seit 4.5.2024)
 *
 * @var array
 */

const VF_Referate_anmeld = array(
    '2' => 'Fahrzeuge und Geräte',
    '3' => 'Öffentlichkeitsarbeit und Archiv',
    '4' => 'Uniformierung und Persönliche Ausrüstung'
);


/**
 * Definition der Fachbereiche
 *
 * @var VF_Fachbereich
 */
const VF_Fachbereich = array(
    ' ' => "Keinem Fachbereich zugeordnet",
    'SUC' => 'Suche nach Suchbegriffen',
    'F_G' => "Muskelbewegte Fahrzeuge und Geräte",
    'F_M' => "Motorbewegte Fahrzeuge und Geräte",
    "S_G" => "Sonstige Gerätschafte, ausser persönlicher Ausrüstung",
    'PSA' => "persönliche Ausrüstung",
    'ARC' => "Archivalien",
    'INV' => "Inventar",
    'OEF' => "Öffentlichkeitsarbeit und Dokumentation",
    'ADM' => "Admnistration",
    'MVW' => "Mitgliederverwaltung"
);

/**
 * Definiton der Admin- Verständiungs- E-Mail- Gruppen
 *
 * @var VF_Mail_Grup
 */
const VF_Mail_Grup = array(
    'Mitgl' => 'Mitgliederverwaltung',
    'Vorst' => 'Vorstand',
    'eVors' => 'erweiterter Vorstand',
    'Bezah' => 'Bezahl-Info',
    'Tech' => 'Technische Infos, Programmierer',
    'Test' => 'Mails bei Test (Local)'
);

const VF_ZT_Kategorie  = array(
    'A' => 'Artikel',
    'W' => 'Werbung',
    'V' => 'Veranstaltung'
);

const VF_ZT_Sachgeb = array(
    'A' => 'Atemschutz',
    'L' => 'Ausbildung',
    'E' => 'Einsatz/Übung',
    'F' => 'Fahrzeuge/Geräte',
    'G' => 'Gefahrengut',
    'S' => 'Strahlenschutz',
    'T' => 'Tauchdienst',
    'V' => 'Vorbeugender Brandschutz',
    'W' => 'Wasserdienst',
    'Z' => 'Zeugmeisterei',
    'H' => 'Historisches, Archiv',
    'R' => 'Rettungsdienste',
    'P' => 'Personelles',
    'B' => 'Fehler B !' ,
    'K' => 'Fehler K !'
);

const VF_ZT_Sub_Sachg = array(
    'B' => 'Brandeinsatz/Übung',
    'T' => 'Technischer Einsatz/Übung',
    'G' => 'Gefahrengut',
    'S' => 'Strahlenschutz',
    'W' => 'Wasserdienst',
    'K' => 'Katastrophenchutz',
    "P" => 'Persönliche Ausrüstung'
);

const VF_Fahrz_Herst = array(
    'F' => 'Fahrzeughersteller'
    ,'A' => 'Aufbauer (Karosseur)'
    ,'G' => 'Geräte- Hersteller'
);

/**
 * Ende der Bibliothek
 *
 * @author Josef Rohowsky - 20250108
 */



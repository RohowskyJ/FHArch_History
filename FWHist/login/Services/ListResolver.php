<?php

declare(strict_types=1);

namespace FWHist\Services;

use RuntimeException;

/**
 * Löst eine list_ID auf die zuständige ListInterface-Implementierung auf.
 *
 * Bewusst KEIN Verzeichnis-Scan mehr: die alte Variante hat beim Aufbau der Registry
 * jede Datei unter Modules/**\/Lists/ per require_once eager geladen, nur um an
 * getListId() zu kommen. Ein Fehler (Signatur, doppelte Konstante, ...) in irgendeiner
 * Listenklasse hat dadurch JEDE Liste lahmgelegt, auch völlig unbeteiligte.
 *
 * Stattdessen: eine explizite, von Hand gepflegte Zuordnung list_ID => Klassenname.
 * Composers PSR-4-Autoloader lädt darüber GENAU EINE Datei - erst in dem Moment, in dem
 * new $className() unten aufgerufen wird. Ein Fehler in einer nicht angefragten Liste
 * bleibt dadurch vollständig folgenlos, weil deren Datei nie geladen wird.
 *
 * Neue Liste hinzufügen = eine Zeile in REGISTRY ergänzen. Keine Auto-Discovery mehr,
 * dafür vorhersagbar und isoliert - für den aktuellen Umstellungsstand der bewusst
 * einfachere Weg.
 */
final class ListResolver
{
    private const REGISTRY = [
        // PSA: Auszeichnungnen Wappen, Ärmelabzeichen
        'ORT' => \FWHist\Modules\Psa\Lists\OrtsList::class,              // Orts- Auswahlliste für Auszeichnungne und Ärmelabzeichen, Wappen
        'OW'  => \FWHist\Modules\Psa\Lists\OWAList::class,               // Orts- Wappen Beschreibungen und Bilder
        'FW'  => \FWHist\Modules\Psa\Lists\FWAList::class,               // Wappen in der Nutzung der Feuerwehr
        'AWA' => \FWHist\Modules\Psa\Lists\AWAList::class,               // Ärmelwappen      
        'MA'  => \FWHist\Modules\Mandanten\Lists\MandList::Class,        // Mandanten
        'AR'  => \FWHist\Modules\Archivalien\Lists\ArchList::Class,      // Archiv
        'AD'  => \FWHist\Modules\Archivalien\Lists\ArchCntList::Class,   // Archiv Anzahl der DOkumente je Aord- Gruppe
        'IN'  => \FWHist\Modules\Inventar\Lists\InventList::Class,       // Inventar- Listen 
        'MV'  => \FWHist\Modules\Medien\Lists\MedVerzList::Class,        // Medien - Verzeichnis- Liste
        'MD'  => \FWHist\Modules\Medien\Lists\MedDatenList::Class,       // Medien - Daten- Liste
        'BR'  => \FWHist\Modules\Medien\Lists\BerList::Class,            // Berichte - Verezchnis
        'MM'  => \FWHist\Modules\Mitglieder\Lists\MMList::Class,         // Mail von Mitglied an Mitglied
        'NM'  => \FWHist\Modules\Mitglieder\Lists\NMList::Class,         // neuangemeldete Mitglieder aktivieren
        'MI'  => \FWHist\Modules\Mitglieder\Lists\MIList::class,         // Mitgliederverwaltung
        'MIE' => \FWHist\Modules\Mitglieder\Lists\MIEList::class,        // Ehrungen
        'MIB' => \FWHist\Modules\Mitglieder\Lists\MIBList::class,        // Mitgliedsbeiträge
        'UN'  => \FWHist\Modules\Mitglieder\Lists\UNList::class,         // Unterstützerverwaltung
        'FIR' => \FWHist\AllgVerw\Lists\FIRList::class,                  // Firmenverwaltung
        'ABK' => \FWHist\AllgVerw\Lists\ABKList::class,                  // Abkürzungsverwaltung
        'EMA' => \FWHist\AllgVerw\Lists\EMAList::class,                  // Admin-E-Mail-Empfänger
        'BE'  => \FWHist\Auth\Lists\BEList::class,                       // Benutzerverwaltung
        'RO'  => \FWHist\Auth\Lists\ROList::class,                       // Benutzerrollen
        'ROM'  => \FWHist\Auth\Lists\ROMList::class,                      // Edit- Berechtigung für Mandantendaten
        // Fahrzeuge und Geräte
        'FAA' => \FWHist\Modules\FahrzeugeGeraete\Lists\FAAList::class,  // Motorfahrzeuge, Archivalien
        'FAE' => \FWHist\Modules\FahrzeugeGeraete\Lists\FAEList::class,  // Motorfahrzeuge, Eigentümer
        'FAF' => \FWHist\Modules\FahrzeugeGeraete\Lists\FAFList::class,  // Motorfahrzeuge
        'FAG' => \FWHist\Modules\FahrzeugeGeraete\Lists\FAGList::class,  // Motorgeräte
        'FMF' => \FWHist\Modules\FahrzeugeGeraete\Lists\FMFList::class,  // Muskelfahrzeuge
        'FMG' => \FWHist\Modules\FahrzeugeGeraete\Lists\FMGList::class,  // Muskelgeräte
        'BU'  => \FWHist\Modules\Oeffentlichkeitsarbeit\Lists\BUList::class, // Buchbesprechungen
        'DO'  => \FWHist\Modules\Oeffentlichkeitsarbeit\Lists\DOList::class, // Dokumentationen
        'AL'  => \FWHist\Modules\Oeffentlichkeitsarbeit\Lists\ALList::class, // Archiv- und Bibliothekslinks
        'MP'  => \FWHist\Modules\Oeffentlichkeitsarbeit\Lists\MPList::class, // Marktplatz
        'MU'  => \FWHist\Modules\Oeffentlichkeitsarbeit\Lists\MUList::class, // Museen
        'TE'  => \FWHist\Modules\Oeffentlichkeitsarbeit\Lists\TEList::class, // Termine
        'ZT'  => \FWHist\Modules\Oeffentlichkeitsarbeit\Lists\ZTList::class, // Zeitungsindex
        'ZI'  => \FWHist\Modules\Oeffentlichkeitsarbeit\Lists\ZIList::class, // Zeitungsinhalte
        'PR'  => \FWHist\Modules\Oeffentlichkeitsarbeit\Lists\PRList::class, // Pressespiegel
        
        /*
        'ST'  => \FWHist\Modules\Psa\Lists\StiList::Class,   // Stifter- Daten
        'EB'  => \FWHist\Modules\Psa\Lists\EGrList::class,   // Allgem Beschreibung des EZ
        'AC'  => \FWHist\Modules\Psa\Lists\CtifList::Class,  // CTIF Bewerbs- Auszeichnungen
        'AV'  => \FWHist\Modules\Psa\Lists\VerList::C lass,  // Auszeichnungen auf Vereins- Basis ( Unterstützungsverein, ÖRK, ..)
        'AZ'  => \FWHist\Modules\Psa\Lists\EzeList::Class,   // Auszeichnungen Stattlicher Art
        
        */
        // Weitere Listen hier ergänzen, z.B.:
        // 'XYZ' => \FWHist\Modules\SomeModule\Lists\XyzList::class,
    ];

    public static function resolve(string $listId): ListInterface
    {
        if (!isset(self::REGISTRY[$listId])) {
            throw new RuntimeException('Unbekannte Liste: ' . $listId);
        }

        $className = self::REGISTRY[$listId];

        // Löst über den Composer-Autoloader NUR diese eine Klassendatei ein.
        // Ein Fatal Error hier betrifft ausschließlich diese Liste.
        if (!class_exists($className)) {
            throw new RuntimeException('Listenklasse nicht gefunden: ' . $className);
        }

        $list = new $className();

        if (!$list instanceof ListInterface) {
            throw new RuntimeException('Listenklasse implementiert ListInterface nicht: ' . $className);
        }

        // Konsistenz-Check: Registry-Key und Klassen-eigene ID müssen übereinstimmen,
        // damit sich Registry und Klasse nicht auseinanderentwickeln.
        if ($className::getListId() !== $listId) {
            throw new RuntimeException(
                "Registry-Inkonsistenz: '$listId' zeigt auf $className, dessen getListId() aber '"
                . $className::getListId() . "' liefert."
            );
        }

        return $list;
    }
}
<?php

declare(strict_types=1);

namespace FWHist\Services;

use FWHist\Auth\Authz;

/**
 * Zugriffsregeln je list_ID (Gegenstück zu ListResolver::REGISTRY).
 *
 * Jede ID aus ListResolver::REGISTRY braucht hier einen Eintrag. Unbekannte IDs werden abgewiesen
 * (deny by default). Neue Liste = eine Zeile in ListResolver UND eine Zeile hier.
 *
 *  read   : 'public' (auch ohne Login, externe Ansicht), 'login' (alle eingeloggten Benutzer)
 *           oder ein lesebeschränkter Bereich aus Authz::READ_RESTRICTED (MA, MI, MIB, ZUGR, EMA)
 *  write  : Modul-Schlüssel aus Authz::MODULES oder null (Liste ohne Schreibfunktionen)
 *  tenant : true = sel_id ist die Mandanten-Nr. (ei_id) und geht in die Schreibprüfung ein
 *
 * editProtect wird ausschließlich hier berechnet. Ein vom Client gesendeter Parameter edit_Protect wird ignoriert.
 */
final class ListPolicy
{
    private const POLICIES = [
        // PSA: Ärmelabzeichen/Wappen (öffentliche, historische Daten)
        'ORT' => ['read' => 'public', 'write' => 'PSA',  'tenant' => false],
        'OW'  => ['read' => 'public', 'write' => 'PSA',  'tenant' => false],
        'FW'  => ['read' => 'public', 'write' => 'PSA',  'tenant' => false],
        'AWA' => ['read' => 'public', 'write' => 'PSA',  'tenant' => false],

        // Öffentlichkeitsarbeit (extern lesbar; nicht freigegebene Einträge filtert die jeweilige Liste)
        'BU'  => ['read' => 'public', 'write' => 'OEF',  'tenant' => false],
        'DO'  => ['read' => 'public', 'write' => 'OEF',  'tenant' => false],
        'AL'  => ['read' => 'public', 'write' => 'OEF',  'tenant' => false],
        'MP'  => ['read' => 'public', 'write' => 'OEF',  'tenant' => false],
        'MU'  => ['read' => 'public', 'write' => 'OEF',  'tenant' => false],
        'TE'  => ['read' => 'public', 'write' => 'OEF',  'tenant' => false],
        'ZT'  => ['read' => 'public', 'write' => 'OEF',  'tenant' => false],
        'ZI'  => ['read' => 'public', 'write' => 'OEF',  'tenant' => false],
        'PR'  => ['read' => 'public', 'write' => 'OEF',  'tenant' => false],

        // Mandantenbezogene Module: alle Eingeloggten lesen, Schreiben nur Fachrolle bzw. Mandantenrecht
        'AR'  => ['read' => 'login', 'write' => 'ARC',  'tenant' => true],
        'AD'  => ['read' => 'login', 'write' => 'ARC',  'tenant' => true],
        'IN'  => ['read' => 'login', 'write' => 'INV',  'tenant' => true],
        'MV'  => ['read' => 'login', 'write' => 'FOTO', 'tenant' => true],  // sel_id prüfen: Mandant?
        'MD'  => ['read' => 'login', 'write' => 'FOTO', 'tenant' => true],  // sel_id prüfen: Mandant?
        'FAA' => ['read' => 'login', 'write' => 'FZG',  'tenant' => true],
        'FAE' => ['read' => 'login', 'write' => 'FZG',  'tenant' => true],
        'FAF' => ['read' => 'login', 'write' => 'FZG',  'tenant' => true],
        'FAG' => ['read' => 'login', 'write' => 'FZG',  'tenant' => true],
        'FMF' => ['read' => 'login', 'write' => 'FZG',  'tenant' => true],
        'FMG' => ['read' => 'login', 'write' => 'FZG',  'tenant' => true],

        // Allgemeine Verwaltung und Sonstiges
        'BR'  => ['read' => 'login', 'write' => 'BER',  'tenant' => false],
        'ABK' => ['read' => 'login', 'write' => 'ABK',  'tenant' => false],
        'FIR' => ['read' => 'login', 'write' => 'FIR',  'tenant' => false],
        'MM'  => ['read' => 'login', 'write' => null,   'tenant' => false],
        'EMA' => ['read' => 'EMA',   'write' => 'EMA',  'tenant' => false],

        // Mandanten-, Mitglieder-, Benutzer- und Zugriffsverwaltung: auch Lesen nur mit Rolle
        'MA'  => ['read' => 'MA',    'write' => 'MA',   'tenant' => false],
        'MI'  => ['read' => 'MI',    'write' => 'MI',   'tenant' => false],
        'MIE' => ['read' => 'MI',    'write' => 'MI',   'tenant' => false],
        'UN'  => ['read' => 'MI',    'write' => 'MI',   'tenant' => false],
        'NM'  => ['read' => 'MI',    'write' => 'MI',   'tenant' => false],
        'MIB' => ['read' => 'MIB',   'write' => 'MIB',  'tenant' => false],
        'BE'  => ['read' => 'ZUGR',  'write' => 'ZUGR', 'tenant' => false],
        'RO'  => ['read' => 'ZUGR',  'write' => 'ZUGR', 'tenant' => false],
    ];

    /**
     * @return array{allowed: bool, status: int, error: string, editProtect: bool}
     */
    public static function authorize(string $listId, ?int $selId): array
    {
        $deny = static fn (int $status, string $error): array
            => ['allowed' => false, 'status' => $status, 'error' => $error, 'editProtect' => true];

        $policy = self::POLICIES[$listId] ?? null;
        if ($policy === null) {
            // Unbekannt und nicht freigegeben werden bewusst nicht unterschieden.
            return $deny(403, 'Keine Berechtigung.');
        }

        $read = $policy['read'];
        if ($read === 'login') {
            if (!Authz::isLoggedIn()) {
                return $deny(403, 'Anmeldung erforderlich.');
            }
        } elseif ($read !== 'public') {
            if (!Authz::isLoggedIn()) {
                return $deny(403, 'Anmeldung erforderlich.');
            }
            if (!Authz::canRead($read)) {
                return $deny(403, 'Für diese Liste besteht keine Berechtigung.');
            }
        }

        $eiId = $policy['tenant'] ? $selId : null;
        $canWrite = $policy['write'] !== null && Authz::canWrite($policy['write'], $eiId);

        return ['allowed' => true, 'status' => 200, 'error' => '', 'editProtect' => !$canWrite];
    }

    /** @return string[] alle list_IDs mit Policy (für Tests: muss ListResolver::REGISTRY abdecken) */
    public static function knownIds(): array
    {
        return array_keys(self::POLICIES);
    }
}

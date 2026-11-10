<?php

declare(strict_types=1);

namespace FWHist\Modules\Mitglieder\API;

use PDO;

/**
 * Empfänger für "E-Mail an den Vorstand": Mitglieder mit eingetragener Vorstandsfunktion
 * (mi_mitglieder.mi_vorst_funct <> ''), E-Mail-Adresse aus mi_mitglieder.mi_email.
 *
 * Die Adressen verlassen den Server nicht: Im Formular erscheinen nur die Funktionen, ausgewählt wird über einen
 * Schlüssel (key()), die Adressen werden beim Senden serverseitig nachgeschlagen.
 *
 * Nicht berücksichtigt werden Mitglieder ohne gültige E-Mail-Adresse sowie verstorbene oder ausgetretene
 * Mitglieder (mi_sterbdat / mi_austrdat gesetzt, falls die Spalten existieren).
 */
final class VorstandRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /** Stabiler, kurzer Schlüssel zu einer Funktion (kommt in das Formular, nicht die Funktion selbst). */
    public static function key(string $funct): string
    {
        return substr(hash('sha256', mb_strtolower(trim($funct))), 0, 12);
    }

    /**
     * @return array<string,array{funct:string,emails:string[]}> Schlüssel => Funktion und Adressen, nach Funktion sortiert
     */
    public function groups(): array
    {
        $groups = [];
        foreach ($this->activeRows() as $row) {
            $funct = trim((string) $row['mi_vorst_funct']);
            $key = self::key($funct);
            $groups[$key]['funct'] = $funct;
            $groups[$key]['emails'][] = (string) $row['mi_email'];
        }

        uasort($groups, static fn(array $a, array $b): int => strnatcasecmp($a['funct'], $b['funct']));

        return $groups;
    }

    /**
     * Adressen (ohne Doppelte) aller Vorstandsmitglieder oder, mit $key, nur der Mitglieder mit dieser Funktion.
     *
     * @return string[]
     */
    public function addresses(?string $key = null): array
    {
        $out = [];
        foreach ($this->groups() as $groupKey => $group) {
            if ($key !== null && $key !== '' && $groupKey !== $key) {
                continue;
            }
            foreach ($group['emails'] as $email) {
                $out[strtolower($email)] = $email;
            }
        }

        return array_values($out);
    }

    /** @return array<int,array<string,mixed>> */
    private function activeRows(): array
    {
        $statement = $this->pdo->query(
            "SELECT * FROM mi_mitglieder WHERE mi_vorst_funct IS NOT NULL AND TRIM(mi_vorst_funct) <> ''"
        );
        $rows = [];
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $email = trim((string) ($row['mi_email'] ?? ''));
            if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
                continue;
            }
            if ($this->isSet($row['mi_sterbdat'] ?? null) || $this->isSet($row['mi_austrdat'] ?? null)) {
                continue;
            }
            $row['mi_email'] = $email;
            $rows[] = $row;
        }

        return $rows;
    }

    private function isSet(mixed $date): bool
    {
        $d = trim((string) $date);

        return $d !== '' && $d !== '0000-00-00' && $d !== '0000-00-00 00:00:00';
    }
}

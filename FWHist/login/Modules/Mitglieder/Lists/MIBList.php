<?php

declare(strict_types=1);

namespace FWHist\Modules\Mitglieder\Lists;

use FWHist\Services\ListInterface;
use FWHist\Services\TableColumnMetadata;
use PDO;

final class MIBList implements ListInterface
{
    private const MEMBER_TABLE = 'mi_mitglieder';
    private const PAYMENT_TABLE = 'mi_bezahlt';

    public static function getListId(): string
    {
        return 'MIB';
    }

    public function getColumns(PDO $pdo, ?string $project = null, ?int $selId = null, ?bool $editProtect = false): array
    {
        $meta = new TableColumnMetadata($pdo, 'fharch_new', false);
        $meta->getColumnsForTables([self::MEMBER_TABLE, self::PAYMENT_TABLE]);
        $comments = $meta->getCommentsMap();
        $year = (int)date('Y');
        $month = (int)date('n');
        $columns = [
            [
                'title' => $comments['mi_name'] ?? 'Name',
                'field' => 'mi_name',
                'sorter' => 'string',
                'minWidth' => 190,
            ],
            [
                'title' => $comments['mi_id'] ?? 'Mitgliedsnummer',
                'field' => 'mi_id',
                'sorter' => 'number',
                'width' => 95,
                'hozAlign' => 'center',
            ],
            [
                'title' => $comments['mi_mtyp'] ?? 'Mitgliedstyp',
                'field' => 'mi_mtyp',
                'width' => 115,
            ],
        ];

        if ($month <= 10) {
            $columns[] = [
                'title' => (string)($year - 1),
                'columns' => [
                    self::paymentColumn('M_1', 'MB Bez.'),
                    self::paymentColumn('A_1', 'Abo Bez.'),
                ],
            ];
        }

        $columns[] = [
            'title' => (string)$year,
            'columns' => [
                self::paymentColumn('M_0', 'MB Bez.'),
                self::paymentColumn('A_0', 'Abo Bez.'),
                self::paymentColumn('MA_0', 'MB + Abo'),
            ],
        ];

        if ($month >= 8) {
            $columns[] = [
                'title' => (string)($year + 1),
                'columns' => [
                    self::paymentColumn('M_p', 'MB Bez.'),
                    self::paymentColumn('A_p', 'Abo Bez.'),
                    self::paymentColumn('MA_p', 'MB + Abo'),
                ],
            ];
        }

        foreach ([
            'mi_m_beitr_bez_bis' => 'Mitgliedsbeitrag bezahlt bis',
            'mi_m_abo_bez_bis' => 'Abo bezahlt bis',
            'mi_m_beitr_bez' => 'Mitgliedsbeitrag bezahlt am',
            'mi_m_abo_bez' => 'Abo bezahlt am',
        ] as $field => $fallback) {
            $columns[] = [
                'title' => $comments[$field] ?? $fallback,
                'field' => $field,
                'headerSort' => false,
                'minWidth' => 135,
            ];
        }

        $columns[] = [
            'title' => 'Zahlung stornieren',
            'field' => 'Korrektur',
            'formatter' => 'korrekturFormatter',
            'headerSort' => false,
            'hozAlign' => 'center',
            'width' => 110,
        ];

        return $columns;
    }

    public function getData(PDO $pdo, ?string $project = null, ?int $selId = null, ?bool $editProtect = false): array
    {
        $memberStatement = $pdo->query(
            "SELECT * FROM `" . self::MEMBER_TABLE . "`
             WHERE mi_austrdat IS NULL AND mi_sterbdat IS NULL
             ORDER BY mi_name ASC, mi_vname ASC"
        );
        $rows = $memberStatement->fetchAll(PDO::FETCH_ASSOC);
        if ($rows === []) {
            return [];
        }

        $memberIds = array_map(static fn(array $row): int => (int)$row['mi_id'], $rows);
        $placeholders = implode(', ', array_fill(0, count($memberIds), '?'));
        $paymentStatement = $pdo->prepare(
            'SELECT mi_id, mb_bez_mb_bis, mb_bez_abo_bis, mb_bez_mb_datum, mb_bez_abo_datum
             FROM `' . self::PAYMENT_TABLE . '`
             WHERE mb_cancel IS NULL AND mi_id IN (' . $placeholders . ')
             ORDER BY mb_changed_at DESC'
        );
        $paymentStatement->execute($memberIds);
        $paymentsByMember = [];

        foreach ($paymentStatement->fetchAll(PDO::FETCH_ASSOC) as $payment) {
            $memberId = (int)$payment['mi_id'];
            $paymentsByMember[$memberId][] = $payment;
        }

        $year = (int)date('Y');
        $previousYear = $year - 1;

        foreach ($rows as &$row) {
            $memberId = (int)($row['mi_id'] ?? 0);
            $row['action'] = "<a class='action-link' href='MitglEdit.php?ID={$memberId}'>Mitglied bearbeiten</a>";
            $firstName = trim((string)($row['mi_vname'] ?? ''));
            $lastName = trim((string)($row['mi_name'] ?? ''));
            $titles = trim(implode(' ', array_filter([
                trim((string)($row['mi_titel'] ?? '')),
                trim((string)($row['mi_n_titel'] ?? '')),
            ])));
            $row['mi_name'] = trim($lastName . ($firstName !== '' ? ', ' . $firstName : ''));
            if ($titles !== '') {
                $row['mi_name'] .= ' (' . $titles . ')';
            }

            $row['M_1'] = 'notpaid';
            $row['A_1'] = 'notpaid';
            $row['M_0'] = 'unpaid';
            $row['A_0'] = 'unpaid';
            $row['M_p'] = 'unpaid';
            $row['A_p'] = 'unpaid';
            $row['Korrektur'] = '';
            $row['justUpdated'] = false;
            $row['mi_m_beitr_bez_bis'] = '';
            $row['mi_m_abo_bez_bis'] = '';
            $row['mi_m_beitr_bez'] = '';
            $row['mi_m_abo_bez'] = '';

            foreach ($paymentsByMember[$memberId] ?? [] as $payment) {
                $memberUntil = (int)($payment['mb_bez_mb_bis'] ?? 0);
                $aboUntil = (int)($payment['mb_bez_abo_bis'] ?? 0);
                if ($memberUntil >= $previousYear && $row['mi_m_beitr_bez_bis'] === '') {
                    $row['mi_m_beitr_bez_bis'] = $memberUntil;
                    $row['mi_m_beitr_bez'] = $payment['mb_bez_mb_datum'] ?? '';
                }
                if ($aboUntil >= $previousYear && $row['mi_m_abo_bez_bis'] === '') {
                    $row['mi_m_abo_bez_bis'] = $aboUntil;
                    $row['mi_m_abo_bez'] = $payment['mb_bez_abo_datum'] ?? '';
                }
                if ($memberUntil === $previousYear) {
                    $row['M_1'] = 'paid';
                }
                if ($aboUntil === $previousYear) {
                    $row['A_1'] = 'paid';
                }
                if ($memberUntil >= $year) {
                    $row['M_0'] = 'paid';
                }
                if ($aboUntil >= $year) {
                    $row['A_0'] = 'paid';
                }
                if ($memberUntil > $year) {
                    $row['M_p'] = 'paid';
                }
                if ($aboUntil > $year) {
                    $row['A_p'] = 'paid';
                }
            }

            $row['MA_0'] = $row['M_0'] === 'unpaid' && $row['A_0'] === 'unpaid' ? 'unpaid' : '';
            $row['MA_p'] = $row['M_p'] === 'unpaid' && $row['A_p'] === 'unpaid' ? 'unpaid' : '';
            $row['mi_payment_status'] = match (true) {
                $row['M_0'] === 'paid' && $row['A_0'] === 'paid' => 'Bezahlt',
                $row['M_0'] === 'paid' || $row['A_0'] === 'paid' => 'Teilweise bezahlt',
                default => 'Offen',
            };
        }
        unset($row);

        return $rows;
    }

    public function getFilters(PDO $pdo, ?string $project = null, ?int $selId = null, ?bool $editProtect = false): array
    {
        $memberTypesStatement = $pdo->query(
            "SELECT DISTINCT mi_mtyp FROM `" . self::MEMBER_TABLE . "`
             WHERE mi_austrdat IS NULL AND mi_sterbdat IS NULL AND mi_mtyp IS NOT NULL AND mi_mtyp <> ''
             ORDER BY mi_mtyp"
        );
        $memberTypes = [];
        foreach ($memberTypesStatement->fetchAll(PDO::FETCH_COLUMN) as $type) {
            $type = (string)$type;
            if (!self::hasExcludedMemberType($type)) {
                $memberTypes[$type] = $type;
            }
        }

        return [
            'selectFilters' => [
                'mi_mtyp' => [
                    'label' => 'Mitgliedstyp',
                    'options' => $memberTypes,
                ],
                'mi_payment_status' => [
                    'label' => 'Beitragsstatus ' . date('Y'),
                    'options' => [
                        'Offen' => 'Offen',
                        'Teilweise bezahlt' => 'Teilweise bezahlt',
                        'Bezahlt' => 'Vollständig bezahlt',
                    ],
                ],
            ],
            'textFilters' => ['mi_name', 'mi_id'],
            'textFilterLabels' => [
                'mi_name' => 'Name',
                'mi_id' => 'Mitgliedsnummer',
            ],
        ];
    }

    private static function paymentColumn(string $field, string $title): array
    {
        return [
            'title' => $title,
            'field' => $field,
            'hozAlign' => 'center',
            'headerSort' => false,
            'formatter' => 'paymentFormatter',
            'width' => 90,
        ];
    }

    private static function hasExcludedMemberType(string $memberType): bool
    {
        $types = array_map('trim', explode('|', $memberType));
        return array_intersect(['OE', 'EM'], $types) !== [];
    }
}

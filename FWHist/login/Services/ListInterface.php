<?php

declare(strict_types=1);

namespace FWHist\Services;

use PDO;

interface ListInterface
{
    public static function getListId(): string;

    public function getColumns(PDO $pdo, ?string $project = null, ?int $selId = null): array;

    public function getData(PDO $pdo, ?string $project = null, ?int $selId = null): array;

    public function getFilters(PDO $pdo, ?string $project = null, ?int $selId = null): array;
}
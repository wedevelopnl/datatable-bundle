<?php

declare(strict_types=1);

namespace Webmen\DataTableBundle;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Webmen\DataTableBundle\Column\ColumnTypeInterface;

interface DataTableExportInterface
{
    public function canExport(): bool;

    /**
     * @param array<string, ColumnTypeInterface> $columns
     */
    public function export(QueryBuilder $queryBuilder, Request $request, string $dataTableName, array $columns): string;
}

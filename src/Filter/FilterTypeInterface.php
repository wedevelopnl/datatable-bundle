<?php

declare(strict_types=1);

namespace Webmen\DataTableBundle\Filter;

use Webmen\DataTableBundle\Column\ColumnTypeInterface;
use Doctrine\ORM\QueryBuilder;

interface FilterTypeInterface
{
    /**
     * @param array<string, mixed> $config
     */
    public function init(ColumnTypeInterface $column, array $config): void;

    public function apply(QueryBuilder $queryBuilder, string $value): void;

    public function getType(): string;

    /**
     * @return array<string, array|string|bool|int|null>
     */
    public function getFrontendConfiguration(): array;
}

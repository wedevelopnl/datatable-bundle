<?php

declare(strict_types=1);

namespace Webmen\DataTableBundle\Filter;

use Doctrine\ORM\QueryBuilder;

final class TextFilterType extends AbstractType
{
    public function apply(QueryBuilder $queryBuilder, string $value): void
    {
        $queryBuilder->andWhere($queryBuilder->expr()->like((string)$queryBuilder->expr()->lower($this->column->getPropertyPath()), ':' . $this->column->getKey()));
        $queryBuilder->setParameter($this->column->getKey(), '%' . mb_strtolower($value) . '%');
    }

    public function getFrontendConfiguration(): array
    {
        return [];
    }

    public function getType(): string
    {
        return 'text';
    }
}

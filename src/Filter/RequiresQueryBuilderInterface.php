<?php

declare(strict_types=1);

namespace Webmen\DataTableBundle\Filter;

use Doctrine\ORM\QueryBuilder;

interface RequiresQueryBuilderInterface
{
    public function setQueryBuilder(QueryBuilder $queryBuilder): void;
}

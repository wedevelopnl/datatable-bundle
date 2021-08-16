<?php

declare(strict_types=1);

namespace Webmen\DataTableBundle;

use Closure;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Select;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Webmen\DataTableBundle\Column\ColumnTypeInterface;
use Webmen\DataTableBundle\Column\TextType;
use Webmen\DataTableBundle\Filter\FilterTypeInterface;
use Webmen\DataTableBundle\Filter\RequiresQueryBuilderInterface;
use Webmen\DataTableBundle\Modal\ModalTypeInterface;

final class DataTableBuilder
{
    private ?string $name;
    /** @var array<string, callable> */
    private array $columns = [];
    /** @var array<string, callable> */
    private array $modals = [];
    private Closure $createQueryBuilder;
    private ?string $sortColumn = null;
    private ?string $sortDirection = null;
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param array<string, mixed> $config
     */
    public function add(string $column, string $type = TextType::class, array $config = []): self
    {
        if (!is_a($type, ColumnTypeInterface::class, true)) {
            throw new \LogicException('Must provide type class implementing ' . ColumnTypeInterface::class . ' for column ' . $column);
        }

        $this->columns[$column] = function (string $entityAlias, QueryBuilder $queryBuilder) use ($column, $config, $type) {
            /** @var ColumnTypeInterface $columnType */
            $columnType = $this->container->get($type);

            if (array_key_exists('filter', $config) && is_callable($config['filter'])) {
                $config['filter'] = $config['filter']($columnType, $queryBuilder);
            }

            $columnType->init($column, $config, $entityAlias);

            return $columnType;
        };

        return $this;
    }

    /**
     * @param array<string, mixed> $config
     */
    public function filter(string $type, array $config = []): callable
    {
        if (!is_a($type, FilterTypeInterface::class, true)) {
            throw new \LogicException('Must provide type class implementing ' . FilterTypeInterface::class);
        }

        /** @var FilterTypeInterface $filterType */
        $filterType = $this->container->get($type);

        return function (ColumnTypeInterface $columnType, QueryBuilder $queryBuilder) use ($filterType, $config) {
            $filterType->init($columnType, $config);

            if ($filterType instanceof RequiresQueryBuilderInterface) {
                $filterType->setQueryBuilder($queryBuilder);
            }

            return $filterType;
        };
    }

    public function query(callable $createQueryBuilder): self
    {
        $this->createQueryBuilder = Closure::fromCallable($createQueryBuilder);

        return $this;
    }

    public function sortBy(string $column, string $direction = DataTable::ORDER_ASC): self
    {
        if (!in_array($direction, [DataTable::ORDER_ASC, DataTable::ORDER_DESC], true)) {
            throw new \LogicException('Invalid sort direction "' . $direction . '"');
        }

        $this->sortColumn = $column;
        $this->sortDirection = $direction;

        return $this;
    }

    /**
     * @param array<string, mixed> $config
     */
    public function modal(string $modal, string $type, array $config = []): self
    {
        if (!is_a($type, ModalTypeInterface::class, true)) {
            throw new \LogicException('Must provide type class implementing ' . ModalTypeInterface::class . ' for modal ' . $modal);
        }

        $this->modals[$modal] = function () use ($modal, $config, $type) {
            /** @var ModalTypeInterface $modalType */
            $modalType = $this->container->get($type);

            $modalType->init($modal, $config);

            return $modalType;
        };

        return $this;
    }

    public function name(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function createDataTable(EntityManagerInterface $entityManager, TranslatorInterface $translator): DataTable
    {
        if (empty($this->name)) {
            throw new \LogicException('Data Table must have a name: $builder->name(\'example\')');
        }

        $queryBuilder = $this->createQueryBuilder($entityManager);

        $entityAlias = $this->getEntityAlias($queryBuilder);

        /** @var array<string,ColumnTypeInterface> */
        $columns = [];

        foreach ($this->columns as $column => $columnInitializer) {
            $columns[$column] = $columnInitializer($entityAlias, $this->createQueryBuilder($entityManager));
        }

        /** @var array<string,ColumnTypeInterface> */
        $modals = [];

        foreach ($this->modals as $modal => $modalsInitializer) {
            $modals[$modal] = $modalsInitializer();
        }

        return new DataTable(
            $this->name,
            $columns,
            $modals,
            $queryBuilder,
            $this->sortColumn,
            $this->sortDirection,
            $translator
        );
    }

    private function getEntityAlias(QueryBuilder $queryBuilder): string
    {
        $select = $queryBuilder->getDQLPart('select');
        /** @var Select $selectExpression */
        $selectExpression = $select[0];

        return $selectExpression->getParts()[0];
    }

    private function createQueryBuilder(EntityManagerInterface $entityManager): QueryBuilder
    {
        $queryBuilder = $entityManager->createQueryBuilder();
        $this->createQueryBuilder->call($this, $queryBuilder);

        return $queryBuilder;
    }
}

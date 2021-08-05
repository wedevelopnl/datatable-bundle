<?php

declare(strict_types=1);

namespace Webmen\DataTableBundle;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class DataTableFactory
{
    private EntityManagerInterface $entityManager;
    private TranslatorInterface $translator;
    private ContainerInterface $container;

    public function __construct(
        EntityManagerInterface $entityManager,
        TranslatorInterface $translator,
        ContainerInterface $container
    ) {
        $this->entityManager = $entityManager;
        $this->translator = $translator;
        $this->container = $container;
    }

    /**
     * @param array<string, mixed> $options
     */
    public function create(string $type, array $options = []): DataTable
    {
        if (!is_a($type, DataTableTypeInterface::class, true)) {
            throw new \LogicException('Must provide type class implementing ' . DataTableTypeInterface::class);
        }

        $dataTableBuilder = new DataTableBuilder($this->container);

        /** @var DataTableTypeInterface $dataTableType */
        $dataTableType = $this->container->get($type);
        $dataTableType->configure($dataTableBuilder, $options);

        return $dataTableBuilder->createDataTable($this->entityManager, $this->translator);
    }
}

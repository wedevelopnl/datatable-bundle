<?php

declare(strict_types=1);

namespace Webmen\DataTableBundle\Column;

use Symfony\Contracts\Translation\TranslatorInterface;
use Webmen\DataTableBundle\Filter\FilterTypeInterface;

interface ColumnTypeInterface
{
    /**
     * @param array<string, mixed> $config
     */
    public function init(string $column, array $config, string $entityAlias): void;

    public function getKey(): string;

    public function getType(): string;

    public function getFilter(): FilterTypeInterface;

    public function getLabel(): string;

    public function getPropertyPath(): string;

    public function isSortable(): bool;

    public function isFilterable(): bool;

    public function isExportable(): bool;

    /**
     * @param array<string, mixed>|object $row
     */
    public function getValue($row): string;

    /**
     * @return array<string, array|string|bool|int|null>
     */
    public function getFrontendConfiguration(TranslatorInterface $translator): array;
}

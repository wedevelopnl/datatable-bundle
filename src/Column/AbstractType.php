<?php

declare(strict_types=1);

namespace Webmen\DataTableBundle\Column;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use Webmen\DataTableBundle\Filter\FilterTypeInterface;

abstract class AbstractType implements ColumnTypeInterface
{
    protected string $column;
    /** @var array<string,mixed> */
    protected array $config;
    protected string $entityAlias;

    public function init(string $column, array $config, string $entityAlias): void
    {
        $this->column = $column;
        $this->entityAlias = $entityAlias;

        $optionsResolver = new OptionsResolver();
        $this->configureOptions($optionsResolver);
        $this->config = $optionsResolver->resolve($config);
    }

    public function getKey(): string
    {
        return $this->column;
    }

    public function getFilter(): FilterTypeInterface
    {
        return $this->config['filter'];
    }

    public function getLabel(): string
    {
        return $this->config['label'];
    }

    public function getPropertyPath(): string
    {
        return $this->config['propertyPath'];
    }

    public function isSortable(): bool
    {
        return $this->config['sortable'];
    }

    public function isFilterable(): bool
    {
        return $this->config['filter'] instanceof FilterTypeInterface;
    }

    public function isSticky(): bool
    {
        return $this->config['sticky'];
    }

    public function isVisible(): bool
    {
        return $this->config['visible'];
    }

    public function getFrontendConfiguration(TranslatorInterface $translator): array
    {
        return [
            'key' => $this->getKey(),
            'type' => $this->getType(),
            'label' => $translator->trans($this->getLabel()),
            'stickyColumn' => $this->isSticky(),
            'sortable' => $this->isSortable(),
            'visible' => $this->isVisible(),
            'filterable' => $this->isFilterable(),
            'filterType' => $this->isFilterable() ? $this->getFilter()->getType() : null,
            'filterOptions' => $this->isFilterable() ? $this->getFilter()->getFrontendConfiguration() : null,
        ];
    }

    /**
     * @param array<string, mixed>|object|mixed $row
     *
     * @return mixed
     */
    public function resolveValue($row)
    {
        if (is_callable($this->config['value'])) {
            return $this->config['value']($row);
        }

        if (is_array($row)) {
            if (!array_key_exists($this->column, $row)) {
                return '';
            }

            return $row[$this->column];
        }

        if (is_object($row)) {
            $propertyPathParts = explode('.', $this->config['propertyPath']);
            $propertyPathPart = array_shift($propertyPathParts);

            // Ignore default entity alias, this probably needs a cleaner solution in the future.
            if ($propertyPathPart === $this->entityAlias) {
                $propertyPathPart = array_shift($propertyPathParts);
            }

            return $this->getObjectValue($row, $propertyPathPart, $propertyPathParts);
        }

        return '';
    }

    /**
     * @param array<string> $remainingPath
     *
     * @return mixed
     */
    private function getObjectValue(object $row, string $property, array $remainingPath)
    {
        if (!method_exists($row, 'get' . $property)) {
            return '';
        }

        $value = $row->{'get' . $property}();

        if (count($remainingPath) > 0 && is_object($value)) {
            return $this->getObjectValue($value, array_shift($remainingPath), $remainingPath);
        }

        return $value;
    }

    protected function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->setDefaults([
            'propertyPath' => $this->entityAlias . '.' . $this->column,
            'label' => ucfirst(strtolower(preg_replace('/([A-Z])/', ' \1', $this->column))),
            'value' => null,
            'sortable' => false,
            'visible' => true,
            'sticky' => false,
            'filter' => null,
        ]);
    }
}

<?php

declare(strict_types=1);

namespace Webmen\DataTableBundle\Filter;

use Webmen\DataTableBundle\Column\ColumnTypeInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractType implements FilterTypeInterface
{
    protected ColumnTypeInterface $column;

    /** @var array<string, mixed> */
    protected array $config;

    public function init(ColumnTypeInterface $column, array $config): void
    {
        $this->column = $column;

        $optionsResolver = new OptionsResolver();
        $this->configureOptions($optionsResolver);
        $this->config = $optionsResolver->resolve($config);
    }

    protected function configureOptions(OptionsResolver $optionsResolver): void
    {
    }
}

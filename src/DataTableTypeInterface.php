<?php

declare(strict_types=1);

namespace Webmen\DataTableBundle;

interface DataTableTypeInterface
{
    /**
     * @param array<string, mixed> $options
     */
    public function configure(DataTableBuilder $builder, array $options): void;
}

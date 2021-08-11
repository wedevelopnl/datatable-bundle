<?php

declare(strict_types=1);

namespace Webmen\DataTableBundle\Column;

final class BoolType extends AbstractType
{
    public function getType(): string
    {
        return 'bool';
    }

    public function getValue($row): string
    {
        if ($this->resolveValue($row)) {
            return '<i class="fa fa-check"></i>';
        }

        return '<i class="fa fa-times"></i>';
    }
}

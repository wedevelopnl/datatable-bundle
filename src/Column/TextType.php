<?php

declare(strict_types=1);

namespace Webmen\DataTableBundle\Column;

final class TextType extends AbstractType
{
    public function getValue($row): string
    {
        return (string)$this->resolveValue($row);
    }

    public function getType(): string
    {
        return 'text';
    }

    public function isExportable(): bool
    {
        return true;
    }
}

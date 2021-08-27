<?php

declare(strict_types=1);

namespace Webmen\DataTableBundle\Column;

use DateTime;
use DateTimeInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class DateTimeType extends AbstractType
{
    public function getValue($row): string
    {
        $value = $this->resolveValue($row);

        if (empty($value)) {
            return $value;
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format($this->config['format']);
        }

        return (new DateTime($value))->format($this->config['format']);
    }

    public function getType(): string
    {
        return 'date_time';
    }

    public function isExportable(): bool
    {
        return true;
    }

    protected function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->setDefaults([
            'format' => 'Y-m-d H:i:s',
        ]);

        parent::configureOptions($optionsResolver);
    }
}

<?php

declare(strict_types=1);

namespace Webmen\DataTableBundle\Filter;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class SimpleChoiceFilterType extends AbstractType
{
    public function apply(QueryBuilder $queryBuilder, string $value): void
    {
        if (!array_key_exists($value, $this->config['choices'])) {
            return;
        }

        $queryBuilder->andWhere($queryBuilder->expr()->eq($this->column->getPropertyPath(), ':' . $this->column->getKey()));
        $queryBuilder->setParameter($this->column->getKey(), $value);
    }

    public function getFrontendConfiguration(): array
    {
        return [
            'choices' => $this->config['choices'],
        ];
    }

    public function getType(): string
    {
        return 'simple_choice';
    }

    protected function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->setDefaults([
            'choices' => [],
        ]);
    }
}

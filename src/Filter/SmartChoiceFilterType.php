<?php

declare(strict_types=1);

namespace Webmen\DataTableBundle\Filter;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class SmartChoiceFilterType extends AbstractType implements RequiresQueryBuilderInterface
{
    private ?QueryBuilder $queryBuilder;

    /** @var array<string|int, string> */
    private array $choices = [];

    public function apply(QueryBuilder $queryBuilder, string $value): void
    {
        if (!$this->allowPartialMatch() && !in_array($value, array_keys($this->getChoices()), false)) {
            return;
        }

        if ($this->allowPartialMatch()) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->like(
                    (string)$queryBuilder->expr()->lower($this->column->getPropertyPath()),
                    ':' . $this->column->getKey()
                )
            )->setParameter($this->column->getKey(), '%' . strtolower($value) . '%');
        } else {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->eq($this->column->getPropertyPath(), ':' . $this->column->getKey())
            )->setParameter($this->column->getKey(), $value);
        }
    }

    public function getFrontendConfiguration(): array
    {
        return [
            'choices' => $this->getChoices(),
        ];
    }

    public function getType(): string
    {
        return 'smart_choice';
    }

    public function setQueryBuilder(QueryBuilder $queryBuilder): void
    {
        $this->queryBuilder = $queryBuilder;
    }

    protected function configureOptions(OptionsResolver $optionsResolver): void
    {
        parent::configureOptions($optionsResolver);

        $optionsResolver->setDefaults([
            'allow_partial_match' => false,
        ]);
    }

    private function getQueryBuilder(): QueryBuilder
    {
        if (!$this->queryBuilder instanceof QueryBuilder) {
            throw new \LogicException('Uninitialized filter');
        }

        return $this->queryBuilder;
    }

    /**
     * @return array<string|int, string>
     */
    private function getChoices(): array
    {
        if (count($this->choices) > 0) {
            return $this->choices;
        }

        $result = $this->getQueryBuilder()
            ->select($this->column->getPropertyPath())
            ->groupBy($this->column->getPropertyPath())
            ->orderBy($this->column->getPropertyPath())
            ->getQuery()
            ->getScalarResult()
        ;

        if (count($result) === 0) {
            return [];
        }

        $choices = array_column($result, array_key_first($result[0]));

        $this->choices = array_combine($choices, $choices);

        return $this->choices;
    }

    private function allowPartialMatch(): bool
    {
        return $this->config['allow_partial_match'];
    }
}

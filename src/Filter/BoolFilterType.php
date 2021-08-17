<?php

declare(strict_types=1);

namespace Webmen\DataTableBundle\Filter;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class BoolFilterType extends AbstractType
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function apply(QueryBuilder $queryBuilder, string $value): void
    {
        if (!in_array($value, ['0', '1'], true)) {
            return;
        }

        $queryBuilder->andWhere($queryBuilder->expr()->eq($this->column->getPropertyPath(), ':' . $this->column->getKey()));
        $queryBuilder->setParameter($this->column->getKey(), (bool)$value);
    }

    public function getType(): string
    {
        return 'bool';
    }

    public function getFrontendConfiguration(): array
    {
        return [
            'choices' => [
                true => $this->translator->trans($this->config['trueLabel']),
                false => $this->translator->trans($this->config['falseLabel']),
            ],
        ];
    }

    protected function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->setDefaults([
            'trueLabel' => 'datatable.true_label',
            'falseLabel' => 'datatable.false_label',
        ]);
    }
}

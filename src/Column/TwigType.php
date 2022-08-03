<?php

declare(strict_types=1);

namespace Webmen\DataTableBundle\Column;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;

final class TwigType extends AbstractType
{
    private Environment $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function getValue($row): string
    {
        return $this->twig->render($this->config['template'], ['row' => $row] + $this->getVariables($row));
    }

    public function getExportValue($row): string
    {
        $exportValue = $this->config['exportValue'];

        if (!is_callable($exportValue)) {
            throw new \LogicException('Invalid config exportValue, must be callable.');
        }

        return (string)$exportValue($row);
    }

    protected function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->setDefaults([
            'template' => '',
            'variables' => [],
            'exportValue' => null,
        ]);

        parent::configureOptions($optionsResolver);
    }

    public function getType(): string
    {
        return 'twig';
    }

    public function isExportable(): bool
    {
        return $this->config['exportValue'] !== null;
    }

    /**
     * @param mixed $row
     *
     * @return array<string, mixed>
     */
    private function getVariables($row): array
    {
        $variables = $this->config['variables'];

        if (is_callable($variables)) {
            $variables = $variables($row);
        }

        if (!is_array($variables)) {
            throw new \LogicException('Invalid config variables, must be array or callable.');
        }

        return $variables;
    }
}

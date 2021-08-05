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

    protected function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->setDefaults([
            'template' => '',
            'variables' => [],
        ]);

        parent::configureOptions($optionsResolver);
    }

    public function getType(): string
    {
        return 'twig';
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

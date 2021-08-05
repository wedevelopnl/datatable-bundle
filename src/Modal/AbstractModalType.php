<?php

declare(strict_types=1);

namespace Webmen\DataTableBundle\Modal;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

abstract class AbstractModalType implements ModalTypeInterface
{
    private string $name;
    /** @var array<string,mixed> */
    private array $config;
    private Environment $twig;
    private TranslatorInterface $translator;

    public function __construct(Environment $twig, TranslatorInterface $translator)
    {
        $this->twig = $twig;
        $this->translator = $translator;
    }

    public function init(string $name, array $config): void
    {
        $this->name = $name;
        $optionsResolver = new OptionsResolver();
        $this->configureOptions($optionsResolver);
        $config = $optionsResolver->resolve($config);

        $buttonOptionsResolver = new OptionsResolver();
        $this->configureButtonOptions($buttonOptionsResolver);
        $config['buttons'] = array_map(fn ($button) => $buttonOptionsResolver->resolve($button), $config['buttons']);

        $this->config = $config;
    }

    public function getTitle(): string
    {
        return $this->config['title'];
    }

    public function getTemplate(): string
    {
        return $this->config['template'];
    }

    /**
     * @return array<int, array>
     */
    public function getButtons(): array
    {
        return $this->config['buttons'];
    }

    public function render(object $row): string
    {
        return $this->twig->render($this->getTemplate(), ['row' => $row]);
    }

    public function getFrontendConfiguration(TranslatorInterface $translator): array
    {
        return [
            'name' => $this->name,
            'title' => $translator->trans($this->getTitle()),
            'buttons' => $this->getButtons(),
        ];
    }

    protected function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->setRequired([
            'title',
            'template',
            'buttons',
        ]);
    }

    protected function configureButtonOptions(OptionsResolver $optionsResolver): void
    {
        $translator = $this->translator;
        $optionsResolver
            ->setDefaults([
                'behavior' => ModalTypeInterface::BEHAVIOR_CLOSE,
                'cssClass' => null,
                'method' => null,
            ])
            ->setRequired([
                'label',
            ])
            ->setNormalizer('label', function (Options $options, $value) use ($translator) {
                return $translator->trans($value);
            })
        ;
    }
}

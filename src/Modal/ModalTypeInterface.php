<?php

declare(strict_types=1);

namespace Webmen\DataTableBundle\Modal;

use Symfony\Contracts\Translation\TranslatorInterface;

interface ModalTypeInterface
{
    public const BEHAVIOR_CLOSE = 'close';
    public const BEHAVIOR_CONFIRM = 'confirm';

    /**
     * @param array<string, mixed> $config
     */
    public function init(string $name, array $config): void;

    /**
     * @return array<string, array|string|bool|int|null>
     */
    public function getFrontendConfiguration(TranslatorInterface $translator): array;

    public function render(object $row): string;
}

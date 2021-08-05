<?php

declare(strict_types=1);

namespace Webmen\DataTableBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class WebmenDataTableBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}

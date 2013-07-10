<?php

namespace Tg\GeneratorBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Okoa generator bundle
 */
class TgGeneratorBundle extends Bundle
{
    public function getParent()
    {
        return 'SensioGeneratorBundle';
    }
}

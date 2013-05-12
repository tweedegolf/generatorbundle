<?php

namespace Tg\OkoaGeneratorBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Okoa generator bundle
 */
class TgOkoaGeneratorBundle extends Bundle
{
    public function getParent()
    {
        return 'SensioGeneratorBundle';
    }
}

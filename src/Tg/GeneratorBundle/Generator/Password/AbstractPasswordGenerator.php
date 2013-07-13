<?php

namespace Tg\GeneratorBundle\Generator\Password;

use Symfony\Component\Security\Core\Util\SecureRandomInterface;

abstract class AbstractPasswordGenerator implements PasswordGenerator
{
    private $rng;

    public function __construct(SecureRandomInterface $rng)
    {
        $this->rng = $rng;
    }

    public function getRandomBytes($nBytes)
    {
        return $this->rng->nextBytes($nBytes);
    }

    public function getRandomInt($min = 0, $max = PHP_INT_MAX)
    {
        $range = $max - $min;
        if ($range === 0) {
            return $min;
        }
        $log = log($range, 2);
        $bytes = (int) ($log / 8) + 1;
        $bits = (int) $log + 1;
        if ($bytes === PHP_INT_SIZE) {
            $filter = -1;
        } else {
            $filter = (int) (1 << $bits) - 1;
        }

        do {
            $rnd = hexdec(bin2hex($this->getRandomBytes($bytes)));
            $rnd = $rnd & $filter;
        } while ($rnd >= $range);
        return $min + $rnd;
    }

    public function chooseRandom($options)
    {
        if (is_array($options)) {
            $max = count($options) - 1;
        } else {
            $max = strlen($options) - 1;
        }
        return $options[$this->getRandomInt(0, $max)];
    }
}

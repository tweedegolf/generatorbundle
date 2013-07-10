<?php

namespace Tg\GeneratorBundle\Generator\Password;

abstract class AbstractPasswordGenerator implements PasswordGenerator
{
    private $rng;

    public function __construct($rng = null)
    {
        if ($rng === null) {
            if (function_exists('openssl_random_pseudo_bytes')) {
                $rng = function ($min = 0, $max = PHP_INT_MAX) {
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
                        $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes, $s)));
                        $rnd = $rnd & $filter;
                    } while ($rnd >= $range);
                    return $min + $rnd;
                };
            }
        }
        $this->rng = $rng;
    }

    public function getRandomInt($min = 0, $max = PHP_INT_MAX)
    {
        $rng = $this->rng;
        $rand = $rng();
        var_dump($rand);
        exit;

    }

    public function chooseRandom(array $options)
    {
        return $options[$this->getRandomInt(0, count($options) - 1)];
    }
}

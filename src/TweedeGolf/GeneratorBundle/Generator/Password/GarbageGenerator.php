<?php

namespace TweedeGolf\GeneratorBundle\Generator\Password;

use LogicException;
use Symfony\Component\Security\Core\Util\SecureRandomInterface;

class GarbageGenerator extends AbstractPasswordGenerator
{
    const ALL = -1;

    const ALPHA_LOWER = 1;
    const ALPHA_UPPER = 2;
    const ALPHA = 3;

    const NUMBER = 4;
    const ALNUM = 7;

    const SPECIALS = 8;

    private $chars = [];

    public function __construct(SecureRandomInterface $rng, $set = self::ALL)
    {
        parent::__construct($rng);

        $chars = "";
        if (is_int($set)) {
            if ($set & self::ALPHA_LOWER) {
                $chars .= "abcdefghijklmnopqrstuvwxyz";
            }

            if ($set & self::ALPHA_UPPER) {
                $chars .= "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
            }

            if ($set & self::NUMBER) {
                $chars .= "0123456789";
            }

            if ($set & self::SPECIALS) {
                $chars .= ".-+=_,!@$#*%<>[]{}";
            }
        } else if (is_string($set)) {
            $chars = $set;
        }
        if (count($chars) < 1) {
            throw new LogicException("Too few characters to use for generating passwords");
        }
        $this->chars = $chars;
    }

    public function generate($length)
    {
        $pwd = "";
        while (strlen($pwd) < $length) {
            $pwd .= $this->chooseRandom($this->chars);
        }
        return $pwd;
    }
}

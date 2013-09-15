<?php

namespace Tg\GeneratorBundle\Generator\Password;

use LogicException;
use Symfony\Component\Security\Core\Util\SecureRandomInterface;
use Tg\OkoaBundle\Util\PathUtil;

class Fips181Generator extends AbstractPasswordGenerator
{
    // flags
    const MAX_UNACCEPTABLE   = 20;

    // gram rules:
    const NOT_BEGIN_SYLLABLE = 0x08;
    const NO_FINAL_SPLIT     = 0x04;
    const VOWEL              = 0x02;
    const ALTERNATE_VOWEL    = 0x01;
    const NO_SPECIAL_RULE    = 0x00;

    // digram rules:
    const BEGIN              = 0x80;
    const NOT_BEGIN          = 0x40;
    const BREAK_GRAM         = 0x40;
    const PREFIX             = 0x20;
    const ILLEGAL_PAIR       = 0x10;
    const SUFFIX             = 0x04;
    const END                = 0x02;
    const NOT_END            = 0x01;
    const ANY_COMBINATION    = 0x00;


    private $grams;

    private $vowelGrams;

    private $gramRules;

    private $digramRules;

    private $separator;

    public function __construct(SecureRandomInterface $rng, $separator = '-')
    {
        parent::__construct($rng);
        $this->setSeparator($separator);
        $this->grams = [
            'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j',
            'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u',
            'v', 'w', 'x', 'y', 'z', 'ch', 'gh', 'ph', 'rh',
            'sh', 'th', 'wh', 'qu', 'ck',
        ];

        $this->vowelGrams = [
            'a', 'e', 'i', 'o', 'u', 'y',
        ];

        $this->generateGramRules();
        $this->generateDigramRules();
    }

    public function generateGramRules()
    {
        // gram rules
        $this->gramRules = [];
        foreach ($this->grams as $gram) {
            $this->gramRules[$gram] = self::NO_SPECIAL_RULE;
        }
        foreach ($this->vowelGrams as $gram) {
            $this->gramRules[$gram] = self::VOWEL;
        }
        $this->gramRules['e'] |= self::NO_FINAL_SPLIT;
        $this->gramRules['y'] |= self::ALTERNATE_VOWEL;
        $this->gramRules['x'] = self::NOT_BEGIN_SYLLABLE;
        $this->gramRules['ck'] = self::NOT_BEGIN_SYLLABLE;
    }

    public function generateDigramRules()
    {
        // digram rules
        $this->digramRules = [];
        $this->digramRules['a'] = [];
        $this->digramRules['a']['a'] = self::ILLEGAL_PAIR;
        $this->digramRules['a']['b'] = self::ANY_COMBINATION;
        $this->digramRules['a']['c'] = self::ANY_COMBINATION;
        $this->digramRules['a']['d'] = self::ANY_COMBINATION;
        $this->digramRules['a']['e'] = self::ILLEGAL_PAIR;
        $this->digramRules['a']['f'] = self::ANY_COMBINATION;
        $this->digramRules['a']['g'] = self::ANY_COMBINATION;
        $this->digramRules['a']['h'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['a']['i'] = self::ANY_COMBINATION;
        $this->digramRules['a']['j'] = self::ANY_COMBINATION;
        $this->digramRules['a']['k'] = self::ANY_COMBINATION;
        $this->digramRules['a']['l'] = self::ANY_COMBINATION;
        $this->digramRules['a']['m'] = self::ANY_COMBINATION;
        $this->digramRules['a']['n'] = self::ANY_COMBINATION;
        $this->digramRules['a']['o'] = self::ILLEGAL_PAIR;
        $this->digramRules['a']['p'] = self::ANY_COMBINATION;
        $this->digramRules['a']['r'] = self::ANY_COMBINATION;
        $this->digramRules['a']['s'] = self::ANY_COMBINATION;
        $this->digramRules['a']['t'] = self::ANY_COMBINATION;
        $this->digramRules['a']['u'] = self::ANY_COMBINATION;
        $this->digramRules['a']['v'] = self::ANY_COMBINATION;
        $this->digramRules['a']['w'] = self::ANY_COMBINATION;
        $this->digramRules['a']['x'] = self::ANY_COMBINATION;
        $this->digramRules['a']['y'] = self::ANY_COMBINATION;
        $this->digramRules['a']['z'] = self::ANY_COMBINATION;
        $this->digramRules['a']['ch'] = self::ANY_COMBINATION;
        $this->digramRules['a']['gh'] = self::ILLEGAL_PAIR;
        $this->digramRules['a']['ph'] = self::ANY_COMBINATION;
        $this->digramRules['a']['rh'] = self::ILLEGAL_PAIR;
        $this->digramRules['a']['sh'] = self::ANY_COMBINATION;
        $this->digramRules['a']['th'] = self::ANY_COMBINATION;
        $this->digramRules['a']['wh'] = self::ILLEGAL_PAIR;
        $this->digramRules['a']['qu'] = self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['a']['ck'] = self::ANY_COMBINATION;

        $this->digramRules['b'] = [];
        $this->digramRules['b']['a'] = self::ANY_COMBINATION;
        $this->digramRules['b']['b'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['b']['c'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['b']['d'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['b']['e'] = self::ANY_COMBINATION;
        $this->digramRules['b']['f'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['b']['g'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['b']['h'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['b']['i'] = self::ANY_COMBINATION;
        $this->digramRules['b']['j'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['b']['k'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['b']['l'] = self::BEGIN | self::SUFFIX | self::NOT_END;
        $this->digramRules['b']['m'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['b']['n'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['b']['o'] = self::ANY_COMBINATION;
        $this->digramRules['b']['p'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['b']['r'] = self::BEGIN | self::END;
        $this->digramRules['b']['s'] = self::NOT_BEGIN;
        $this->digramRules['b']['t'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['b']['u'] = self::ANY_COMBINATION;
        $this->digramRules['b']['v'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['b']['w'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['b']['x'] = self::ILLEGAL_PAIR;
        $this->digramRules['b']['y'] = self::ANY_COMBINATION;
        $this->digramRules['b']['z'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['b']['ch'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['b']['gh'] = self::ILLEGAL_PAIR;
        $this->digramRules['b']['ph'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['b']['rh'] = self::ILLEGAL_PAIR;
        $this->digramRules['b']['sh'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['b']['th'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['b']['wh'] = self::ILLEGAL_PAIR;
        $this->digramRules['b']['qu'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['b']['ck'] = self::ILLEGAL_PAIR;

        $this->digramRules['c'] = [];
        $this->digramRules['c']['a'] = self::ANY_COMBINATION;
        $this->digramRules['c']['b'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['c']['c'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['c']['d'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['c']['e'] = self::ANY_COMBINATION;
        $this->digramRules['c']['f'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['c']['g'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['c']['h'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['c']['i'] = self::ANY_COMBINATION;
        $this->digramRules['c']['j'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['c']['k'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['c']['l'] = self::SUFFIX | self::NOT_END;
        $this->digramRules['c']['m'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['c']['n'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['c']['o'] = self::ANY_COMBINATION;
        $this->digramRules['c']['p'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['c']['r'] = self::NOT_END;
        $this->digramRules['c']['s'] = self::NOT_BEGIN | self::END;
        $this->digramRules['c']['t'] = self::NOT_BEGIN | self::PREFIX;
        $this->digramRules['c']['u'] = self::ANY_COMBINATION;
        $this->digramRules['c']['v'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['c']['w'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['c']['x'] = self::ILLEGAL_PAIR;
        $this->digramRules['c']['y'] = self::ANY_COMBINATION;
        $this->digramRules['c']['z'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['c']['ch'] = self::ILLEGAL_PAIR;
        $this->digramRules['c']['gh'] = self::ILLEGAL_PAIR;
        $this->digramRules['c']['ph'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['c']['rh'] = self::ILLEGAL_PAIR;
        $this->digramRules['c']['sh'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['c']['th'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['c']['wh'] = self::ILLEGAL_PAIR;
        $this->digramRules['c']['qu'] = self::NOT_BEGIN | self::SUFFIX | self::NOT_END;
        $this->digramRules['c']['ck'] = self::ILLEGAL_PAIR;

        $this->digramRules['d'] = [];
        $this->digramRules['d']['a'] = self::ANY_COMBINATION;
        $this->digramRules['d']['b'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['d']['c'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['d']['d'] = self::NOT_BEGIN;
        $this->digramRules['d']['e'] = self::ANY_COMBINATION;
        $this->digramRules['d']['f'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['d']['g'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['d']['h'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['d']['i'] = self::ANY_COMBINATION;
        $this->digramRules['d']['j'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['d']['k'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['d']['l'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['d']['m'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['d']['n'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['d']['o'] = self::ANY_COMBINATION;
        $this->digramRules['d']['p'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['d']['r'] = self::BEGIN | self::NOT_END;
        $this->digramRules['d']['s'] = self::NOT_BEGIN | self::END;
        $this->digramRules['d']['t'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['d']['u'] = self::ANY_COMBINATION;
        $this->digramRules['d']['v'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['d']['w'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['d']['x'] = self::ILLEGAL_PAIR;
        $this->digramRules['d']['y'] = self::ANY_COMBINATION;
        $this->digramRules['d']['z'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['d']['ch'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['d']['gh'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['d']['ph'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['d']['rh'] = self::ILLEGAL_PAIR;
        $this->digramRules['d']['sh'] = self::NOT_BEGIN | self::NOT_END;
        $this->digramRules['d']['th'] = self::NOT_BEGIN | self::PREFIX;
        $this->digramRules['d']['wh'] = self::ILLEGAL_PAIR;
        $this->digramRules['d']['qu'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['d']['ck'] = self::ILLEGAL_PAIR;

        $this->digramRules['e'] = [];
        $this->digramRules['e']['a'] = self::ANY_COMBINATION;
        $this->digramRules['e']['b'] = self::ANY_COMBINATION;
        $this->digramRules['e']['c'] = self::ANY_COMBINATION;
        $this->digramRules['e']['d'] = self::ANY_COMBINATION;
        $this->digramRules['e']['e'] = self::ANY_COMBINATION;
        $this->digramRules['e']['f'] = self::ANY_COMBINATION;
        $this->digramRules['e']['g'] = self::ANY_COMBINATION;
        $this->digramRules['e']['h'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['e']['i'] = self::NOT_END;
        $this->digramRules['e']['j'] = self::ANY_COMBINATION;
        $this->digramRules['e']['k'] = self::ANY_COMBINATION;
        $this->digramRules['e']['l'] = self::ANY_COMBINATION;
        $this->digramRules['e']['m'] = self::ANY_COMBINATION;
        $this->digramRules['e']['n'] = self::ANY_COMBINATION;
        $this->digramRules['e']['o'] = self::BREAK_GRAM;
        $this->digramRules['e']['p'] = self::ANY_COMBINATION;
        $this->digramRules['e']['r'] = self::ANY_COMBINATION;
        $this->digramRules['e']['s'] = self::ANY_COMBINATION;
        $this->digramRules['e']['t'] = self::ANY_COMBINATION;
        $this->digramRules['e']['u'] = self::ANY_COMBINATION;
        $this->digramRules['e']['v'] = self::ANY_COMBINATION;
        $this->digramRules['e']['w'] = self::ANY_COMBINATION;
        $this->digramRules['e']['x'] = self::ANY_COMBINATION;
        $this->digramRules['e']['y'] = self::ANY_COMBINATION;
        $this->digramRules['e']['z'] = self::ANY_COMBINATION;
        $this->digramRules['e']['ch'] = self::ANY_COMBINATION;
        $this->digramRules['e']['gh'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['e']['ph'] = self::ANY_COMBINATION;
        $this->digramRules['e']['rh'] = self::ILLEGAL_PAIR;
        $this->digramRules['e']['sh'] = self::ANY_COMBINATION;
        $this->digramRules['e']['th'] = self::ANY_COMBINATION;
        $this->digramRules['e']['wh'] = self::ILLEGAL_PAIR;
        $this->digramRules['e']['qu'] = self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['e']['ck'] = self::ANY_COMBINATION;

        $this->digramRules['f'] = [];
        $this->digramRules['f']['a'] = self::ANY_COMBINATION;
        $this->digramRules['f']['b'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['f']['c'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['f']['d'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['f']['e'] = self::ANY_COMBINATION;
        $this->digramRules['f']['f'] = self::NOT_BEGIN;
        $this->digramRules['f']['g'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['f']['h'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['f']['i'] = self::ANY_COMBINATION;
        $this->digramRules['f']['j'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['f']['k'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['f']['l'] = self::BEGIN | self::SUFFIX | self::NOT_END;
        $this->digramRules['f']['m'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['f']['n'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['f']['o'] = self::ANY_COMBINATION;
        $this->digramRules['f']['p'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['f']['r'] = self::BEGIN | self::NOT_END;
        $this->digramRules['f']['s'] = self::NOT_BEGIN;
        $this->digramRules['f']['t'] = self::NOT_BEGIN;
        $this->digramRules['f']['u'] = self::ANY_COMBINATION;
        $this->digramRules['f']['v'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['f']['w'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['f']['x'] = self::ILLEGAL_PAIR;
        $this->digramRules['f']['y'] = self::NOT_BEGIN;
        $this->digramRules['f']['z'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['f']['ch'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['f']['gh'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['f']['ph'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['f']['rh'] = self::ILLEGAL_PAIR;
        $this->digramRules['f']['sh'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['f']['th'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['f']['wh'] = self::ILLEGAL_PAIR;
        $this->digramRules['f']['qu'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['f']['ck'] = self::ILLEGAL_PAIR;

        $this->digramRules['g'] = [];
        $this->digramRules['g']['a'] = self::ANY_COMBINATION;
        $this->digramRules['g']['b'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['g']['c'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['g']['d'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['g']['e'] = self::ANY_COMBINATION;
        $this->digramRules['g']['f'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['g']['g'] = self::NOT_BEGIN;
        $this->digramRules['g']['h'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['g']['i'] = self::ANY_COMBINATION;
        $this->digramRules['g']['j'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['g']['k'] = self::ILLEGAL_PAIR;
        $this->digramRules['g']['l'] = self::BEGIN | self::SUFFIX | self::NOT_END;
        $this->digramRules['g']['m'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['g']['n'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['g']['o'] = self::ANY_COMBINATION;
        $this->digramRules['g']['p'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['g']['r'] = self::BEGIN | self::NOT_END;
        $this->digramRules['g']['s'] = self::NOT_BEGIN | self::END;
        $this->digramRules['g']['t'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['g']['u'] = self::ANY_COMBINATION;
        $this->digramRules['g']['v'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['g']['w'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['g']['x'] = self::ILLEGAL_PAIR;
        $this->digramRules['g']['y'] = self::NOT_BEGIN;
        $this->digramRules['g']['z'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['g']['ch'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['g']['gh'] = self::ILLEGAL_PAIR;
        $this->digramRules['g']['ph'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['g']['rh'] = self::ILLEGAL_PAIR;
        $this->digramRules['g']['sh'] = self::NOT_BEGIN;
        $this->digramRules['g']['th'] = self::NOT_BEGIN;
        $this->digramRules['g']['wh'] = self::ILLEGAL_PAIR;
        $this->digramRules['g']['qu'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['g']['ck'] = self::ILLEGAL_PAIR;

        $this->digramRules['h'] = [];
        $this->digramRules['h']['a'] = self::ANY_COMBINATION;
        $this->digramRules['h']['b'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['h']['c'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['h']['d'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['h']['e'] = self::ANY_COMBINATION;
        $this->digramRules['h']['f'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['h']['g'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['h']['h'] = self::ILLEGAL_PAIR;
        $this->digramRules['h']['i'] = self::ANY_COMBINATION;
        $this->digramRules['h']['j'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['h']['k'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['h']['l'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['h']['m'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['h']['n'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['h']['o'] = self::ANY_COMBINATION;
        $this->digramRules['h']['p'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['h']['r'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['h']['s'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['h']['t'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['h']['u'] = self::ANY_COMBINATION;
        $this->digramRules['h']['v'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['h']['w'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['h']['x'] = self::ILLEGAL_PAIR;
        $this->digramRules['h']['y'] = self::ANY_COMBINATION;
        $this->digramRules['h']['z'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['h']['ch'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['h']['gh'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['h']['ph'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['h']['rh'] = self::ILLEGAL_PAIR;
        $this->digramRules['h']['sh'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['h']['th'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['h']['wh'] = self::ILLEGAL_PAIR;
        $this->digramRules['h']['qu'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['h']['ck'] = self::ILLEGAL_PAIR;

        $this->digramRules['i'] = [];
        $this->digramRules['i']['a'] = self::ANY_COMBINATION;
        $this->digramRules['i']['b'] = self::ANY_COMBINATION;
        $this->digramRules['i']['c'] = self::ANY_COMBINATION;
        $this->digramRules['i']['d'] = self::ANY_COMBINATION;
        $this->digramRules['i']['e'] = self::NOT_BEGIN;
        $this->digramRules['i']['f'] = self::ANY_COMBINATION;
        $this->digramRules['i']['g'] = self::ANY_COMBINATION;
        $this->digramRules['i']['h'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['i']['i'] = self::ILLEGAL_PAIR;
        $this->digramRules['i']['j'] = self::ANY_COMBINATION;
        $this->digramRules['i']['k'] = self::ANY_COMBINATION;
        $this->digramRules['i']['l'] = self::ANY_COMBINATION;
        $this->digramRules['i']['m'] = self::ANY_COMBINATION;
        $this->digramRules['i']['n'] = self::ANY_COMBINATION;
        $this->digramRules['i']['o'] = self::BREAK_GRAM;
        $this->digramRules['i']['p'] = self::ANY_COMBINATION;
        $this->digramRules['i']['r'] = self::ANY_COMBINATION;
        $this->digramRules['i']['s'] = self::ANY_COMBINATION;
        $this->digramRules['i']['t'] = self::ANY_COMBINATION;
        $this->digramRules['i']['u'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['i']['v'] = self::ANY_COMBINATION;
        $this->digramRules['i']['w'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['i']['x'] = self::ANY_COMBINATION;
        $this->digramRules['i']['y'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['i']['z'] = self::ANY_COMBINATION;
        $this->digramRules['i']['ch'] = self::ANY_COMBINATION;
        $this->digramRules['i']['gh'] = self::NOT_BEGIN;
        $this->digramRules['i']['ph'] = self::ANY_COMBINATION;
        $this->digramRules['i']['rh'] = self::ILLEGAL_PAIR;
        $this->digramRules['i']['sh'] = self::ANY_COMBINATION;
        $this->digramRules['i']['th'] = self::ANY_COMBINATION;
        $this->digramRules['i']['wh'] = self::ILLEGAL_PAIR;
        $this->digramRules['i']['qu'] = self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['i']['ck'] = self::ANY_COMBINATION;

        $this->digramRules['j'] = [];
        $this->digramRules['j']['a'] = self::ANY_COMBINATION;
        $this->digramRules['j']['b'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['j']['c'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['j']['d'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['j']['e'] = self::ANY_COMBINATION;
        $this->digramRules['j']['f'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['j']['g'] = self::ILLEGAL_PAIR;
        $this->digramRules['j']['h'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['j']['i'] = self::ANY_COMBINATION;
        $this->digramRules['j']['j'] = self::ILLEGAL_PAIR;
        $this->digramRules['j']['k'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['j']['l'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['j']['m'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['j']['n'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['j']['o'] = self::ANY_COMBINATION;
        $this->digramRules['j']['p'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['j']['r'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['j']['s'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['j']['t'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['j']['u'] = self::ANY_COMBINATION;
        $this->digramRules['j']['v'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['j']['w'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['j']['x'] = self::ILLEGAL_PAIR;
        $this->digramRules['j']['y'] = self::NOT_BEGIN;
        $this->digramRules['j']['z'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['j']['ch'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['j']['gh'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['j']['ph'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['j']['rh'] = self::ILLEGAL_PAIR;
        $this->digramRules['j']['sh'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['j']['th'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['j']['wh'] = self::ILLEGAL_PAIR;
        $this->digramRules['j']['qu'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['j']['ck'] = self::ILLEGAL_PAIR;

        $this->digramRules['k'] = [];
        $this->digramRules['k']['a'] = self::ANY_COMBINATION;
        $this->digramRules['k']['b'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['k']['c'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['k']['d'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['k']['e'] = self::ANY_COMBINATION;
        $this->digramRules['k']['f'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['k']['g'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['k']['h'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['k']['i'] = self::ANY_COMBINATION;
        $this->digramRules['k']['j'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['k']['k'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['k']['l'] = self::SUFFIX | self::NOT_END;
        $this->digramRules['k']['m'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['k']['n'] = self::BEGIN | self::SUFFIX | self::NOT_END;
        $this->digramRules['k']['o'] = self::ANY_COMBINATION;
        $this->digramRules['k']['p'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['k']['r'] = self::SUFFIX | self::NOT_END;
        $this->digramRules['k']['s'] = self::NOT_BEGIN | self::END;
        $this->digramRules['k']['t'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['k']['u'] = self::ANY_COMBINATION;
        $this->digramRules['k']['v'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['k']['w'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['k']['x'] = self::ILLEGAL_PAIR;
        $this->digramRules['k']['y'] = self::NOT_BEGIN;
        $this->digramRules['k']['z'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['k']['ch'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['k']['gh'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['k']['ph'] = self::NOT_BEGIN | self::PREFIX;
        $this->digramRules['k']['rh'] = self::ILLEGAL_PAIR;
        $this->digramRules['k']['sh'] = self::NOT_BEGIN;
        $this->digramRules['k']['th'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['k']['wh'] = self::ILLEGAL_PAIR;
        $this->digramRules['k']['qu'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['k']['ck'] = self::ILLEGAL_PAIR;

        $this->digramRules['l'] = [];
        $this->digramRules['l']['a'] = self::ANY_COMBINATION;
        $this->digramRules['l']['b'] = self::NOT_BEGIN | self::PREFIX;
        $this->digramRules['l']['c'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['l']['d'] = self::NOT_BEGIN | self::PREFIX;
        $this->digramRules['l']['e'] = self::ANY_COMBINATION;
        $this->digramRules['l']['f'] = self::NOT_BEGIN | self::PREFIX;
        $this->digramRules['l']['g'] = self::NOT_BEGIN | self::PREFIX;
        $this->digramRules['l']['h'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['l']['i'] = self::ANY_COMBINATION;
        $this->digramRules['l']['j'] = self::NOT_BEGIN | self::PREFIX;
        $this->digramRules['l']['k'] = self::NOT_BEGIN | self::PREFIX;
        $this->digramRules['l']['l'] = self::NOT_BEGIN | self::PREFIX;
        $this->digramRules['l']['m'] = self::NOT_BEGIN | self::PREFIX;
        $this->digramRules['l']['n'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['l']['o'] = self::ANY_COMBINATION;
        $this->digramRules['l']['p'] = self::NOT_BEGIN | self::PREFIX;
        $this->digramRules['l']['r'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['l']['s'] = self::NOT_BEGIN;
        $this->digramRules['l']['t'] = self::NOT_BEGIN | self::PREFIX;
        $this->digramRules['l']['u'] = self::ANY_COMBINATION;
        $this->digramRules['l']['v'] = self::NOT_BEGIN | self::PREFIX;
        $this->digramRules['l']['w'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['l']['x'] = self::ILLEGAL_PAIR;
        $this->digramRules['l']['y'] = self::ANY_COMBINATION;
        $this->digramRules['l']['z'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['l']['ch'] = self::NOT_BEGIN | self::PREFIX;
        $this->digramRules['l']['gh'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['l']['ph'] = self::NOT_BEGIN | self::PREFIX;
        $this->digramRules['l']['rh'] = self::ILLEGAL_PAIR;
        $this->digramRules['l']['sh'] = self::NOT_BEGIN | self::PREFIX;
        $this->digramRules['l']['th'] = self::NOT_BEGIN | self::PREFIX;
        $this->digramRules['l']['wh'] = self::ILLEGAL_PAIR;
        $this->digramRules['l']['qu'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['l']['ck'] = self::ILLEGAL_PAIR;

        $this->digramRules['m'] = [];
        $this->digramRules['m']['a'] = self::ANY_COMBINATION;
        $this->digramRules['m']['b'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['m']['c'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['m']['d'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['m']['e'] = self::ANY_COMBINATION;
        $this->digramRules['m']['f'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['m']['g'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['m']['h'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['m']['i'] = self::ANY_COMBINATION;
        $this->digramRules['m']['j'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['m']['k'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['m']['l'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['m']['m'] = self::NOT_BEGIN;
        $this->digramRules['m']['n'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['m']['o'] = self::ANY_COMBINATION;
        $this->digramRules['m']['p'] = self::NOT_BEGIN;
        $this->digramRules['m']['r'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['m']['s'] = self::NOT_BEGIN;
        $this->digramRules['m']['t'] = self::NOT_BEGIN;
        $this->digramRules['m']['u'] = self::ANY_COMBINATION;
        $this->digramRules['m']['v'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['m']['w'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['m']['x'] = self::ILLEGAL_PAIR;
        $this->digramRules['m']['y'] = self::ANY_COMBINATION;
        $this->digramRules['m']['z'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['m']['ch'] = self::NOT_BEGIN | self::PREFIX;
        $this->digramRules['m']['gh'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['m']['ph'] = self::NOT_BEGIN;
        $this->digramRules['m']['rh'] = self::ILLEGAL_PAIR;
        $this->digramRules['m']['sh'] = self::NOT_BEGIN;
        $this->digramRules['m']['th'] = self::NOT_BEGIN;
        $this->digramRules['m']['wh'] = self::ILLEGAL_PAIR;
        $this->digramRules['m']['qu'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['m']['ck'] = self::ILLEGAL_PAIR;

        $this->digramRules['n'] = [];
        $this->digramRules['n']['a'] = self::ANY_COMBINATION;
        $this->digramRules['n']['b'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['n']['c'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['n']['d'] = self::NOT_BEGIN;
        $this->digramRules['n']['e'] = self::ANY_COMBINATION;
        $this->digramRules['n']['f'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['n']['g'] = self::NOT_BEGIN | self::PREFIX;
        $this->digramRules['n']['h'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['n']['i'] = self::ANY_COMBINATION;
        $this->digramRules['n']['j'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['n']['k'] = self::NOT_BEGIN | self::PREFIX;
        $this->digramRules['n']['l'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['n']['m'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['n']['n'] = self::NOT_BEGIN;
        $this->digramRules['n']['o'] = self::ANY_COMBINATION;
        $this->digramRules['n']['p'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['n']['r'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['n']['s'] = self::NOT_BEGIN;
        $this->digramRules['n']['t'] = self::NOT_BEGIN;
        $this->digramRules['n']['u'] = self::ANY_COMBINATION;
        $this->digramRules['n']['v'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['n']['w'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['n']['x'] = self::ILLEGAL_PAIR;
        $this->digramRules['n']['y'] = self::NOT_BEGIN;
        $this->digramRules['n']['z'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['n']['ch'] = self::NOT_BEGIN | self::PREFIX;
        $this->digramRules['n']['gh'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['n']['ph'] = self::NOT_BEGIN | self::PREFIX;
        $this->digramRules['n']['rh'] = self::ILLEGAL_PAIR;
        $this->digramRules['n']['sh'] = self::NOT_BEGIN;
        $this->digramRules['n']['th'] = self::NOT_BEGIN;
        $this->digramRules['n']['wh'] = self::ILLEGAL_PAIR;
        $this->digramRules['n']['qu'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['n']['ck'] = self::NOT_BEGIN | self::PREFIX;

        $this->digramRules['o'] = [];
        $this->digramRules['o']['a'] = self::ANY_COMBINATION;
        $this->digramRules['o']['b'] = self::ANY_COMBINATION;
        $this->digramRules['o']['c'] = self::ANY_COMBINATION;
        $this->digramRules['o']['d'] = self::ANY_COMBINATION;
        $this->digramRules['o']['e'] = self::ILLEGAL_PAIR;
        $this->digramRules['o']['f'] = self::ANY_COMBINATION;
        $this->digramRules['o']['g'] = self::ANY_COMBINATION;
        $this->digramRules['o']['h'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['o']['i'] = self::ANY_COMBINATION;
        $this->digramRules['o']['j'] = self::ANY_COMBINATION;
        $this->digramRules['o']['k'] = self::ANY_COMBINATION;
        $this->digramRules['o']['l'] = self::ANY_COMBINATION;
        $this->digramRules['o']['m'] = self::ANY_COMBINATION;
        $this->digramRules['o']['n'] = self::ANY_COMBINATION;
        $this->digramRules['o']['o'] = self::ANY_COMBINATION;
        $this->digramRules['o']['p'] = self::ANY_COMBINATION;
        $this->digramRules['o']['r'] = self::ANY_COMBINATION;
        $this->digramRules['o']['s'] = self::ANY_COMBINATION;
        $this->digramRules['o']['t'] = self::ANY_COMBINATION;
        $this->digramRules['o']['u'] = self::ANY_COMBINATION;
        $this->digramRules['o']['v'] = self::ANY_COMBINATION;
        $this->digramRules['o']['w'] = self::ANY_COMBINATION;
        $this->digramRules['o']['x'] = self::ANY_COMBINATION;
        $this->digramRules['o']['y'] = self::ANY_COMBINATION;
        $this->digramRules['o']['z'] = self::ANY_COMBINATION;
        $this->digramRules['o']['ch'] = self::ANY_COMBINATION;
        $this->digramRules['o']['gh'] = self::NOT_BEGIN;
        $this->digramRules['o']['ph'] = self::ANY_COMBINATION;
        $this->digramRules['o']['rh'] = self::ILLEGAL_PAIR;
        $this->digramRules['o']['sh'] = self::ANY_COMBINATION;
        $this->digramRules['o']['th'] = self::ANY_COMBINATION;
        $this->digramRules['o']['wh'] = self::ILLEGAL_PAIR;
        $this->digramRules['o']['qu'] = self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['o']['ck'] = self::ANY_COMBINATION;

        $this->digramRules['p'] = [];
        $this->digramRules['p']['a'] = self::ANY_COMBINATION;
        $this->digramRules['p']['b'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['p']['c'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['p']['d'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['p']['e'] = self::ANY_COMBINATION;
        $this->digramRules['p']['f'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['p']['g'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['p']['h'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['p']['i'] = self::ANY_COMBINATION;
        $this->digramRules['p']['j'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['p']['k'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['p']['l'] = self::SUFFIX | self::NOT_END;
        $this->digramRules['p']['m'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['p']['n'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['p']['o'] = self::ANY_COMBINATION;
        $this->digramRules['p']['p'] = self::NOT_BEGIN | self::PREFIX;
        $this->digramRules['p']['r'] = self::NOT_END;
        $this->digramRules['p']['s'] = self::NOT_BEGIN | self::END;
        $this->digramRules['p']['t'] = self::NOT_BEGIN | self::END;
        $this->digramRules['p']['u'] = self::NOT_BEGIN | self::END;
        $this->digramRules['p']['v'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['p']['w'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['p']['x'] = self::ILLEGAL_PAIR;
        $this->digramRules['p']['y'] = self::ANY_COMBINATION;
        $this->digramRules['p']['z'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['p']['ch'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['p']['gh'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['p']['ph'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['p']['rh'] = self::ILLEGAL_PAIR;
        $this->digramRules['p']['sh'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['p']['th'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['p']['wh'] = self::ILLEGAL_PAIR;
        $this->digramRules['p']['qu'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['p']['ck'] = self::ILLEGAL_PAIR;

        $this->digramRules['r'] = [];
        $this->digramRules['r']['a'] = self::ANY_COMBINATION;
        $this->digramRules['r']['b'] = self::NOT_BEGIN | self::PREFIX;
        $this->digramRules['r']['c'] = self::NOT_BEGIN | self::PREFIX;
        $this->digramRules['r']['d'] = self::NOT_BEGIN | self::PREFIX;
        $this->digramRules['r']['e'] = self::ANY_COMBINATION;
        $this->digramRules['r']['f'] = self::NOT_BEGIN | self::PREFIX;
        $this->digramRules['r']['g'] = self::NOT_BEGIN | self::PREFIX;
        $this->digramRules['r']['h'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['r']['i'] = self::ANY_COMBINATION;
        $this->digramRules['r']['j'] = self::NOT_BEGIN | self::PREFIX;
        $this->digramRules['r']['k'] = self::NOT_BEGIN | self::PREFIX;
        $this->digramRules['r']['l'] = self::NOT_BEGIN | self::PREFIX;
        $this->digramRules['r']['m'] = self::NOT_BEGIN | self::PREFIX;
        $this->digramRules['r']['n'] = self::NOT_BEGIN | self::PREFIX;
        $this->digramRules['r']['o'] = self::ANY_COMBINATION;
        $this->digramRules['r']['p'] = self::NOT_BEGIN | self::PREFIX;
        $this->digramRules['r']['r'] = self::NOT_BEGIN | self::PREFIX;
        $this->digramRules['r']['s'] = self::NOT_BEGIN | self::PREFIX;
        $this->digramRules['r']['t'] = self::NOT_BEGIN | self::PREFIX;
        $this->digramRules['r']['u'] = self::ANY_COMBINATION;
        $this->digramRules['r']['v'] = self::NOT_BEGIN | self::PREFIX;
        $this->digramRules['r']['w'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['r']['x'] = self::ILLEGAL_PAIR;
        $this->digramRules['r']['y'] = self::ANY_COMBINATION;
        $this->digramRules['r']['z'] = self::NOT_BEGIN | self::PREFIX;
        $this->digramRules['r']['ch'] = self::NOT_BEGIN | self::PREFIX;
        $this->digramRules['r']['gh'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['r']['ph'] = self::NOT_BEGIN | self::PREFIX;
        $this->digramRules['r']['rh'] = self::ILLEGAL_PAIR;
        $this->digramRules['r']['sh'] = self::NOT_BEGIN | self::PREFIX;
        $this->digramRules['r']['th'] = self::NOT_BEGIN | self::PREFIX;
        $this->digramRules['r']['wh'] = self::ILLEGAL_PAIR;
        $this->digramRules['r']['qu'] = self::NOT_BEGIN | self::PREFIX | self::NOT_END;
        $this->digramRules['r']['ck'] = self::NOT_BEGIN | self::PREFIX;

        $this->digramRules['s'] = [];
        $this->digramRules['s']['a'] = self::ANY_COMBINATION;
        $this->digramRules['s']['b'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['s']['c'] = self::NOT_END;
        $this->digramRules['s']['d'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['s']['e'] = self::ANY_COMBINATION;
        $this->digramRules['s']['f'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['s']['g'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['s']['h'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['s']['i'] = self::ANY_COMBINATION;
        $this->digramRules['s']['j'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['s']['k'] = self::ANY_COMBINATION;
        $this->digramRules['s']['l'] = self::BEGIN | self::SUFFIX | self::NOT_END;
        $this->digramRules['s']['m'] = self::SUFFIX | self::NOT_END;
        $this->digramRules['s']['n'] = self::PREFIX | self::SUFFIX | self::NOT_END;
        $this->digramRules['s']['o'] = self::ANY_COMBINATION;
        $this->digramRules['s']['p'] = self::ANY_COMBINATION;
        $this->digramRules['s']['r'] = self::NOT_BEGIN | self::NOT_END;
        $this->digramRules['s']['s'] = self::NOT_BEGIN | self::PREFIX;
        $this->digramRules['s']['t'] = self::ANY_COMBINATION;
        $this->digramRules['s']['u'] = self::ANY_COMBINATION;
        $this->digramRules['s']['v'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['s']['w'] = self::BEGIN | self::SUFFIX | self::NOT_END;
        $this->digramRules['s']['x'] = self::ILLEGAL_PAIR;
        $this->digramRules['s']['y'] = self::ANY_COMBINATION;
        $this->digramRules['s']['z'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['s']['ch'] = self::BEGIN | self::SUFFIX | self::NOT_END;
        $this->digramRules['s']['gh'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['s']['ph'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['s']['rh'] = self::ILLEGAL_PAIR;
        $this->digramRules['s']['sh'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['s']['th'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['s']['wh'] = self::ILLEGAL_PAIR;
        $this->digramRules['s']['qu'] = self::SUFFIX | self::NOT_END;
        $this->digramRules['s']['ck'] = self::NOT_BEGIN;

        $this->digramRules['t'] = [];
        $this->digramRules['t']['a'] = self::ANY_COMBINATION;
        $this->digramRules['t']['b'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['t']['c'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['t']['d'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['t']['e'] = self::ANY_COMBINATION;
        $this->digramRules['t']['f'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['t']['g'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['t']['h'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['t']['i'] = self::ANY_COMBINATION;
        $this->digramRules['t']['j'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['t']['k'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['t']['l'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['t']['m'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['t']['n'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['t']['o'] = self::ANY_COMBINATION;
        $this->digramRules['t']['p'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['t']['r'] = self::NOT_END;
        $this->digramRules['t']['s'] = self::NOT_BEGIN | self::END;
        $this->digramRules['t']['t'] = self::NOT_BEGIN | self::PREFIX;
        $this->digramRules['t']['u'] = self::ANY_COMBINATION;
        $this->digramRules['t']['v'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['t']['w'] = self::BEGIN | self::SUFFIX | self::NOT_END;
        $this->digramRules['t']['x'] = self::ILLEGAL_PAIR;
        $this->digramRules['t']['y'] = self::ANY_COMBINATION;
        $this->digramRules['t']['z'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['t']['ch'] = self::NOT_BEGIN;
        $this->digramRules['t']['gh'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['t']['ph'] = self::NOT_BEGIN | self::END;
        $this->digramRules['t']['rh'] = self::ILLEGAL_PAIR;
        $this->digramRules['t']['sh'] = self::NOT_BEGIN | self::END;
        $this->digramRules['t']['th'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['t']['wh'] = self::ILLEGAL_PAIR;
        $this->digramRules['t']['qu'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['t']['ck'] = self::ILLEGAL_PAIR;

        $this->digramRules['u'] = [];
        $this->digramRules['u']['a'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['u']['b'] = self::ANY_COMBINATION;
        $this->digramRules['u']['c'] = self::ANY_COMBINATION;
        $this->digramRules['u']['d'] = self::ANY_COMBINATION;
        $this->digramRules['u']['e'] = self::NOT_BEGIN;
        $this->digramRules['u']['f'] = self::ANY_COMBINATION;
        $this->digramRules['u']['g'] = self::ANY_COMBINATION;
        $this->digramRules['u']['h'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['u']['i'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['u']['j'] = self::ANY_COMBINATION;
        $this->digramRules['u']['k'] = self::ANY_COMBINATION;
        $this->digramRules['u']['l'] = self::ANY_COMBINATION;
        $this->digramRules['u']['m'] = self::ANY_COMBINATION;
        $this->digramRules['u']['n'] = self::ANY_COMBINATION;
        $this->digramRules['u']['o'] = self::NOT_BEGIN | self::BREAK_GRAM;
        $this->digramRules['u']['p'] = self::ANY_COMBINATION;
        $this->digramRules['u']['r'] = self::ANY_COMBINATION;
        $this->digramRules['u']['s'] = self::ANY_COMBINATION;
        $this->digramRules['u']['t'] = self::ANY_COMBINATION;
        $this->digramRules['u']['u'] = self::ILLEGAL_PAIR;
        $this->digramRules['u']['v'] = self::ANY_COMBINATION;
        $this->digramRules['u']['w'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['u']['x'] = self::ANY_COMBINATION;
        $this->digramRules['u']['y'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['u']['z'] = self::ANY_COMBINATION;
        $this->digramRules['u']['ch'] = self::ANY_COMBINATION;
        $this->digramRules['u']['gh'] = self::NOT_BEGIN | self::PREFIX;
        $this->digramRules['u']['ph'] = self::ANY_COMBINATION;
        $this->digramRules['u']['rh'] = self::ILLEGAL_PAIR;
        $this->digramRules['u']['sh'] = self::ANY_COMBINATION;
        $this->digramRules['u']['th'] = self::ANY_COMBINATION;
        $this->digramRules['u']['wh'] = self::ILLEGAL_PAIR;
        $this->digramRules['u']['qu'] = self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['u']['ck'] = self::ANY_COMBINATION;

        $this->digramRules['v'] = [];
        $this->digramRules['v']['a'] = self::ANY_COMBINATION;
        $this->digramRules['v']['b'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['v']['c'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['v']['d'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['v']['e'] = self::ANY_COMBINATION;
        $this->digramRules['v']['f'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['v']['g'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['v']['h'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['v']['i'] = self::ANY_COMBINATION;
        $this->digramRules['v']['j'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['v']['k'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['v']['l'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['v']['m'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['v']['n'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['v']['o'] = self::ANY_COMBINATION;
        $this->digramRules['v']['p'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['v']['r'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['v']['s'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['v']['t'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['v']['u'] = self::ANY_COMBINATION;
        $this->digramRules['v']['v'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['v']['w'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['v']['x'] = self::ILLEGAL_PAIR;
        $this->digramRules['v']['y'] = self::NOT_BEGIN;
        $this->digramRules['v']['z'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['v']['ch'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['v']['gh'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['v']['ph'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['v']['rh'] = self::ILLEGAL_PAIR;
        $this->digramRules['v']['sh'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['v']['th'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['v']['wh'] = self::ILLEGAL_PAIR;
        $this->digramRules['v']['qu'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['v']['ck'] = self::ILLEGAL_PAIR;

        $this->digramRules['w'] = [];
        $this->digramRules['w']['a'] = self::ANY_COMBINATION;
        $this->digramRules['w']['b'] = self::NOT_BEGIN | self::PREFIX;
        $this->digramRules['w']['c'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['w']['d'] = self::NOT_BEGIN | self::PREFIX | self::END;
        $this->digramRules['w']['e'] = self::ANY_COMBINATION;
        $this->digramRules['w']['f'] = self::NOT_BEGIN | self::PREFIX;
        $this->digramRules['w']['g'] = self::NOT_BEGIN | self::PREFIX | self::END;
        $this->digramRules['w']['h'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['w']['i'] = self::ANY_COMBINATION;
        $this->digramRules['w']['j'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['w']['k'] = self::NOT_BEGIN | self::PREFIX;
        $this->digramRules['w']['l'] = self::NOT_BEGIN | self::PREFIX | self::SUFFIX;
        $this->digramRules['w']['m'] = self::NOT_BEGIN | self::PREFIX;
        $this->digramRules['w']['n'] = self::NOT_BEGIN | self::PREFIX;
        $this->digramRules['w']['o'] = self::ANY_COMBINATION;
        $this->digramRules['w']['p'] = self::NOT_BEGIN | self::PREFIX;
        $this->digramRules['w']['r'] = self::BEGIN | self::SUFFIX | self::NOT_END;
        $this->digramRules['w']['s'] = self::NOT_BEGIN | self::PREFIX;
        $this->digramRules['w']['t'] = self::NOT_BEGIN | self::PREFIX;
        $this->digramRules['w']['u'] = self::ANY_COMBINATION;
        $this->digramRules['w']['v'] = self::NOT_BEGIN | self::PREFIX;
        $this->digramRules['w']['w'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['w']['x'] = self::NOT_BEGIN | self::PREFIX;
        $this->digramRules['w']['y'] = self::ANY_COMBINATION;
        $this->digramRules['w']['z'] = self::NOT_BEGIN | self::PREFIX;
        $this->digramRules['w']['ch'] = self::NOT_BEGIN;
        $this->digramRules['w']['gh'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['w']['ph'] = self::NOT_BEGIN;
        $this->digramRules['w']['rh'] = self::ILLEGAL_PAIR;
        $this->digramRules['w']['sh'] = self::NOT_BEGIN;
        $this->digramRules['w']['th'] = self::NOT_BEGIN;
        $this->digramRules['w']['wh'] = self::ILLEGAL_PAIR;
        $this->digramRules['w']['qu'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['w']['ck'] = self::NOT_BEGIN;

        $this->digramRules['x'] = [];
        $this->digramRules['x']['a'] = self::NOT_BEGIN;
        $this->digramRules['x']['b'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['x']['c'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['x']['d'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['x']['e'] = self::NOT_BEGIN;
        $this->digramRules['x']['f'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['x']['g'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['x']['h'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['x']['i'] = self::NOT_BEGIN;
        $this->digramRules['x']['j'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['x']['k'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['x']['l'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['x']['m'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['x']['n'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['x']['o'] = self::NOT_BEGIN;
        $this->digramRules['x']['p'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['x']['r'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['x']['s'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['x']['t'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['x']['u'] = self::NOT_BEGIN;
        $this->digramRules['x']['v'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['x']['w'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['x']['x'] = self::ILLEGAL_PAIR;
        $this->digramRules['x']['y'] = self::NOT_BEGIN;
        $this->digramRules['x']['z'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['x']['ch'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['x']['gh'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['x']['ph'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['x']['rh'] = self::ILLEGAL_PAIR;
        $this->digramRules['x']['sh'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['x']['th'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['x']['wh'] = self::ILLEGAL_PAIR;
        $this->digramRules['x']['qu'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['x']['ck'] = self::ILLEGAL_PAIR;

        $this->digramRules['y'] = [];
        $this->digramRules['y']['a'] = self::ANY_COMBINATION;
        $this->digramRules['y']['b'] = self::NOT_BEGIN;
        $this->digramRules['y']['c'] = self::NOT_BEGIN | self::NOT_END;
        $this->digramRules['y']['d'] = self::NOT_BEGIN;
        $this->digramRules['y']['e'] = self::ANY_COMBINATION;
        $this->digramRules['y']['f'] = self::NOT_BEGIN | self::NOT_END;
        $this->digramRules['y']['g'] = self::NOT_BEGIN;
        $this->digramRules['y']['h'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['y']['i'] = self::BEGIN | self::NOT_END;
        $this->digramRules['y']['j'] = self::NOT_BEGIN | self::NOT_END;
        $this->digramRules['y']['k'] = self::NOT_BEGIN;
        $this->digramRules['y']['l'] = self::NOT_BEGIN | self::NOT_END;
        $this->digramRules['y']['m'] = self::NOT_BEGIN;
        $this->digramRules['y']['n'] = self::NOT_BEGIN;
        $this->digramRules['y']['o'] = self::ANY_COMBINATION;
        $this->digramRules['y']['p'] = self::NOT_BEGIN;
        $this->digramRules['y']['r'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['y']['s'] = self::NOT_BEGIN;
        $this->digramRules['y']['t'] = self::NOT_BEGIN;
        $this->digramRules['y']['u'] = self::ANY_COMBINATION;
        $this->digramRules['y']['v'] = self::NOT_BEGIN | self::NOT_END;
        $this->digramRules['y']['w'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['y']['x'] = self::NOT_BEGIN;
        $this->digramRules['y']['y'] = self::ILLEGAL_PAIR;
        $this->digramRules['y']['z'] = self::NOT_BEGIN;
        $this->digramRules['y']['ch'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['y']['gh'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['y']['ph'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['y']['rh'] = self::ILLEGAL_PAIR;
        $this->digramRules['y']['sh'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['y']['th'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['y']['wh'] = self::ILLEGAL_PAIR;
        $this->digramRules['y']['qu'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['y']['ck'] = self::ILLEGAL_PAIR;

        $this->digramRules['z'] = [];
        $this->digramRules['z']['a'] = self::ANY_COMBINATION;
        $this->digramRules['z']['b'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['z']['c'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['z']['d'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['z']['e'] = self::ANY_COMBINATION;
        $this->digramRules['z']['f'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['z']['g'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['z']['h'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['z']['i'] = self::ANY_COMBINATION;
        $this->digramRules['z']['j'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['z']['k'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['z']['l'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['z']['m'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['z']['n'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['z']['o'] = self::ANY_COMBINATION;
        $this->digramRules['z']['p'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['z']['r'] = self::NOT_BEGIN | self::NOT_END;
        $this->digramRules['z']['s'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['z']['t'] = self::NOT_BEGIN;
        $this->digramRules['z']['u'] = self::ANY_COMBINATION;
        $this->digramRules['z']['v'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['z']['w'] = self::SUFFIX | self::NOT_END;
        $this->digramRules['z']['x'] = self::ILLEGAL_PAIR;
        $this->digramRules['z']['y'] = self::ANY_COMBINATION;
        $this->digramRules['z']['z'] = self::NOT_BEGIN;
        $this->digramRules['z']['ch'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['z']['gh'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['z']['ph'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['z']['rh'] = self::ILLEGAL_PAIR;
        $this->digramRules['z']['sh'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['z']['th'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['z']['wh'] = self::ILLEGAL_PAIR;
        $this->digramRules['z']['qu'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['z']['ck'] = self::ILLEGAL_PAIR;

        $this->digramRules['ch'] = [];
        $this->digramRules['ch']['a'] = self::ANY_COMBINATION;
        $this->digramRules['ch']['b'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['ch']['c'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['ch']['d'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['ch']['e'] = self::ANY_COMBINATION;
        $this->digramRules['ch']['f'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['ch']['g'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['ch']['h'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['ch']['i'] = self::ANY_COMBINATION;
        $this->digramRules['ch']['j'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['ch']['k'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['ch']['l'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['ch']['m'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['ch']['n'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['ch']['o'] = self::ANY_COMBINATION;
        $this->digramRules['ch']['p'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['ch']['r'] = self::NOT_END;
        $this->digramRules['ch']['s'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['ch']['t'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['ch']['u'] = self::ANY_COMBINATION;
        $this->digramRules['ch']['v'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['ch']['w'] = self::NOT_BEGIN | self::NOT_END;
        $this->digramRules['ch']['x'] = self::ILLEGAL_PAIR;
        $this->digramRules['ch']['y'] = self::ANY_COMBINATION;
        $this->digramRules['ch']['z'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['ch']['ch'] = self::ILLEGAL_PAIR;
        $this->digramRules['ch']['gh'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['ch']['ph'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['ch']['rh'] = self::ILLEGAL_PAIR;
        $this->digramRules['ch']['sh'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['ch']['th'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['ch']['wh'] = self::ILLEGAL_PAIR;
        $this->digramRules['ch']['qu'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['ch']['ck'] = self::ILLEGAL_PAIR;

        $this->digramRules['gh'] = [];
        $this->digramRules['gh']['a'] = self::ANY_COMBINATION;
        $this->digramRules['gh']['b'] = self::NOT_BEGIN | self::BREAK_GRAM | self::PREFIX | self::NOT_END;
        $this->digramRules['gh']['c'] = self::NOT_BEGIN | self::BREAK_GRAM | self::PREFIX | self::NOT_END;
        $this->digramRules['gh']['d'] = self::NOT_BEGIN | self::BREAK_GRAM | self::PREFIX | self::NOT_END;
        $this->digramRules['gh']['e'] = self::ANY_COMBINATION;
        $this->digramRules['gh']['f'] = self::NOT_BEGIN | self::BREAK_GRAM | self::PREFIX | self::NOT_END;
        $this->digramRules['gh']['g'] = self::NOT_BEGIN | self::BREAK_GRAM | self::PREFIX | self::NOT_END;
        $this->digramRules['gh']['h'] = self::NOT_BEGIN | self::BREAK_GRAM | self::PREFIX | self::NOT_END;
        $this->digramRules['gh']['i'] = self::BEGIN | self::NOT_END;
        $this->digramRules['gh']['j'] = self::NOT_BEGIN | self::BREAK_GRAM | self::PREFIX | self::NOT_END;
        $this->digramRules['gh']['k'] = self::NOT_BEGIN | self::BREAK_GRAM | self::PREFIX | self::NOT_END;
        $this->digramRules['gh']['l'] = self::NOT_BEGIN | self::BREAK_GRAM | self::PREFIX | self::NOT_END;
        $this->digramRules['gh']['m'] = self::NOT_BEGIN | self::BREAK_GRAM | self::PREFIX | self::NOT_END;
        $this->digramRules['gh']['n'] = self::NOT_BEGIN | self::BREAK_GRAM | self::PREFIX | self::NOT_END;
        $this->digramRules['gh']['o'] = self::BEGIN | self::NOT_END;
        $this->digramRules['gh']['p'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['gh']['r'] = self::NOT_BEGIN | self::BREAK_GRAM | self::PREFIX | self::NOT_END;
        $this->digramRules['gh']['s'] = self::NOT_BEGIN | self::PREFIX;
        $this->digramRules['gh']['t'] = self::NOT_BEGIN | self::PREFIX;
        $this->digramRules['gh']['u'] = self::NOT_BEGIN | self::BREAK_GRAM | self::PREFIX | self::NOT_END;
        $this->digramRules['gh']['v'] = self::NOT_BEGIN | self::BREAK_GRAM | self::PREFIX | self::NOT_END;
        $this->digramRules['gh']['w'] = self::NOT_BEGIN | self::BREAK_GRAM | self::PREFIX | self::NOT_END;
        $this->digramRules['gh']['x'] = self::ILLEGAL_PAIR;
        $this->digramRules['gh']['y'] = self::NOT_BEGIN | self::BREAK_GRAM | self::PREFIX | self::NOT_END;
        $this->digramRules['gh']['z'] = self::NOT_BEGIN | self::BREAK_GRAM | self::PREFIX | self::NOT_END;
        $this->digramRules['gh']['ch'] = self::NOT_BEGIN | self::BREAK_GRAM | self::PREFIX | self::NOT_END;
        $this->digramRules['gh']['gh'] = self::ILLEGAL_PAIR;
        $this->digramRules['gh']['ph'] = self::NOT_BEGIN | self::BREAK_GRAM | self::PREFIX | self::NOT_END;
        $this->digramRules['gh']['rh'] = self::ILLEGAL_PAIR;
        $this->digramRules['gh']['sh'] = self::NOT_BEGIN | self::BREAK_GRAM | self::PREFIX | self::NOT_END;
        $this->digramRules['gh']['th'] = self::NOT_BEGIN | self::BREAK_GRAM | self::PREFIX | self::NOT_END;
        $this->digramRules['gh']['wh'] = self::ILLEGAL_PAIR;
        $this->digramRules['gh']['qu'] = self::NOT_BEGIN | self::BREAK_GRAM | self::PREFIX | self::NOT_END;
        $this->digramRules['gh']['ck'] = self::ILLEGAL_PAIR;

        $this->digramRules['ph'] = [];
        $this->digramRules['ph']['a'] = self::ANY_COMBINATION;
        $this->digramRules['ph']['b'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['ph']['c'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['ph']['d'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['ph']['e'] = self::ANY_COMBINATION;
        $this->digramRules['ph']['f'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['ph']['g'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['ph']['h'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['ph']['i'] = self::ANY_COMBINATION;
        $this->digramRules['ph']['j'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['ph']['k'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['ph']['l'] = self::BEGIN | self::SUFFIX | self::NOT_END;
        $this->digramRules['ph']['m'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['ph']['n'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['ph']['o'] = self::ANY_COMBINATION;
        $this->digramRules['ph']['p'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['ph']['r'] = self::NOT_END;
        $this->digramRules['ph']['s'] = self::NOT_BEGIN;
        $this->digramRules['ph']['t'] = self::NOT_BEGIN;
        $this->digramRules['ph']['u'] = self::ANY_COMBINATION;
        $this->digramRules['ph']['v'] = self::NOT_BEGIN | self::NOT_END;
        $this->digramRules['ph']['w'] = self::NOT_BEGIN | self::NOT_END;
        $this->digramRules['ph']['x'] = self::ILLEGAL_PAIR;
        $this->digramRules['ph']['y'] = self::NOT_BEGIN;
        $this->digramRules['ph']['z'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['ph']['ch'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['ph']['gh'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['ph']['ph'] = self::ILLEGAL_PAIR;
        $this->digramRules['ph']['rh'] = self::ILLEGAL_PAIR;
        $this->digramRules['ph']['sh'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['ph']['th'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['ph']['wh'] = self::ILLEGAL_PAIR;
        $this->digramRules['ph']['qu'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['ph']['ck'] = self::ILLEGAL_PAIR;

        $this->digramRules['rh'] = [];
        $this->digramRules['rh']['a'] = self::BEGIN | self::NOT_END;
        $this->digramRules['rh']['b'] = self::ILLEGAL_PAIR;
        $this->digramRules['rh']['c'] = self::ILLEGAL_PAIR;
        $this->digramRules['rh']['d'] = self::ILLEGAL_PAIR;
        $this->digramRules['rh']['e'] = self::BEGIN | self::NOT_END;
        $this->digramRules['rh']['f'] = self::ILLEGAL_PAIR;
        $this->digramRules['rh']['g'] = self::ILLEGAL_PAIR;
        $this->digramRules['rh']['h'] = self::ILLEGAL_PAIR;
        $this->digramRules['rh']['i'] = self::BEGIN | self::NOT_END;
        $this->digramRules['rh']['j'] = self::ILLEGAL_PAIR;
        $this->digramRules['rh']['k'] = self::ILLEGAL_PAIR;
        $this->digramRules['rh']['l'] = self::ILLEGAL_PAIR;
        $this->digramRules['rh']['m'] = self::ILLEGAL_PAIR;
        $this->digramRules['rh']['n'] = self::ILLEGAL_PAIR;
        $this->digramRules['rh']['o'] = self::BEGIN | self::NOT_END;
        $this->digramRules['rh']['p'] = self::ILLEGAL_PAIR;
        $this->digramRules['rh']['r'] = self::ILLEGAL_PAIR;
        $this->digramRules['rh']['s'] = self::ILLEGAL_PAIR;
        $this->digramRules['rh']['t'] = self::ILLEGAL_PAIR;
        $this->digramRules['rh']['u'] = self::BEGIN | self::NOT_END;
        $this->digramRules['rh']['v'] = self::ILLEGAL_PAIR;
        $this->digramRules['rh']['w'] = self::ILLEGAL_PAIR;
        $this->digramRules['rh']['x'] = self::ILLEGAL_PAIR;
        $this->digramRules['rh']['y'] = self::BEGIN | self::NOT_END;
        $this->digramRules['rh']['z'] = self::ILLEGAL_PAIR;
        $this->digramRules['rh']['ch'] = self::ILLEGAL_PAIR;
        $this->digramRules['rh']['gh'] = self::ILLEGAL_PAIR;
        $this->digramRules['rh']['ph'] = self::ILLEGAL_PAIR;
        $this->digramRules['rh']['rh'] = self::ILLEGAL_PAIR;
        $this->digramRules['rh']['sh'] = self::ILLEGAL_PAIR;
        $this->digramRules['rh']['th'] = self::ILLEGAL_PAIR;
        $this->digramRules['rh']['wh'] = self::ILLEGAL_PAIR;
        $this->digramRules['rh']['qu'] = self::ILLEGAL_PAIR;
        $this->digramRules['rh']['ck'] = self::ILLEGAL_PAIR;

        $this->digramRules['sh'] = [];
        $this->digramRules['sh']['a'] = self::ANY_COMBINATION;
        $this->digramRules['sh']['b'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['sh']['c'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['sh']['d'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['sh']['e'] = self::ANY_COMBINATION;
        $this->digramRules['sh']['f'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['sh']['g'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['sh']['h'] = self::ILLEGAL_PAIR;
        $this->digramRules['sh']['i'] = self::ANY_COMBINATION;
        $this->digramRules['sh']['j'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['sh']['k'] = self::NOT_BEGIN;
        $this->digramRules['sh']['l'] = self::BEGIN | self::SUFFIX | self::NOT_END;
        $this->digramRules['sh']['m'] = self::BEGIN | self::SUFFIX | self::NOT_END;
        $this->digramRules['sh']['n'] = self::BEGIN | self::SUFFIX | self::NOT_END;
        $this->digramRules['sh']['o'] = self::ANY_COMBINATION;
        $this->digramRules['sh']['p'] = self::NOT_BEGIN;
        $this->digramRules['sh']['r'] = self::BEGIN | self::SUFFIX | self::NOT_END;
        $this->digramRules['sh']['s'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['sh']['t'] = self::SUFFIX;
        $this->digramRules['sh']['u'] = self::ANY_COMBINATION;
        $this->digramRules['sh']['v'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['sh']['w'] = self::SUFFIX | self::NOT_END;
        $this->digramRules['sh']['x'] = self::ILLEGAL_PAIR;
        $this->digramRules['sh']['y'] = self::ANY_COMBINATION;
        $this->digramRules['sh']['z'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['sh']['ch'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['sh']['gh'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['sh']['ph'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['sh']['rh'] = self::ILLEGAL_PAIR;
        $this->digramRules['sh']['sh'] = self::ILLEGAL_PAIR;
        $this->digramRules['sh']['th'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['sh']['wh'] = self::ILLEGAL_PAIR;
        $this->digramRules['sh']['qu'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['sh']['ck'] = self::ILLEGAL_PAIR;

        $this->digramRules['th'] = [];
        $this->digramRules['th']['a'] = self::ANY_COMBINATION;
        $this->digramRules['th']['b'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['th']['c'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['th']['d'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['th']['e'] = self::ANY_COMBINATION;
        $this->digramRules['th']['f'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['th']['g'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['th']['h'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['th']['i'] = self::ANY_COMBINATION;
        $this->digramRules['th']['j'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['th']['k'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['th']['l'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['th']['m'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['th']['n'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['th']['o'] = self::ANY_COMBINATION;
        $this->digramRules['th']['p'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['th']['r'] = self::NOT_END;
        $this->digramRules['th']['s'] = self::NOT_BEGIN | self::END;
        $this->digramRules['th']['t'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['th']['u'] = self::ANY_COMBINATION;
        $this->digramRules['th']['v'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['th']['w'] = self::SUFFIX | self::NOT_END;
        $this->digramRules['th']['x'] = self::ILLEGAL_PAIR;
        $this->digramRules['th']['y'] = self::ANY_COMBINATION;
        $this->digramRules['th']['z'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['th']['ch'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['th']['gh'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['th']['ph'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['th']['rh'] = self::ILLEGAL_PAIR;
        $this->digramRules['th']['sh'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['th']['th'] = self::ILLEGAL_PAIR;
        $this->digramRules['th']['wh'] = self::ILLEGAL_PAIR;
        $this->digramRules['th']['qu'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['th']['ck'] = self::ILLEGAL_PAIR;

        $this->digramRules['wh'] = [];
        $this->digramRules['wh']['a'] = self::BEGIN | self::NOT_END;
        $this->digramRules['wh']['b'] = self::ILLEGAL_PAIR;
        $this->digramRules['wh']['c'] = self::ILLEGAL_PAIR;
        $this->digramRules['wh']['d'] = self::ILLEGAL_PAIR;
        $this->digramRules['wh']['e'] = self::BEGIN | self::NOT_END;
        $this->digramRules['wh']['f'] = self::ILLEGAL_PAIR;
        $this->digramRules['wh']['g'] = self::ILLEGAL_PAIR;
        $this->digramRules['wh']['h'] = self::ILLEGAL_PAIR;
        $this->digramRules['wh']['i'] = self::BEGIN | self::NOT_END;
        $this->digramRules['wh']['j'] = self::ILLEGAL_PAIR;
        $this->digramRules['wh']['k'] = self::ILLEGAL_PAIR;
        $this->digramRules['wh']['l'] = self::ILLEGAL_PAIR;
        $this->digramRules['wh']['m'] = self::ILLEGAL_PAIR;
        $this->digramRules['wh']['n'] = self::ILLEGAL_PAIR;
        $this->digramRules['wh']['o'] = self::BEGIN | self::NOT_END;
        $this->digramRules['wh']['p'] = self::ILLEGAL_PAIR;
        $this->digramRules['wh']['r'] = self::ILLEGAL_PAIR;
        $this->digramRules['wh']['s'] = self::ILLEGAL_PAIR;
        $this->digramRules['wh']['t'] = self::ILLEGAL_PAIR;
        $this->digramRules['wh']['u'] = self::ILLEGAL_PAIR;
        $this->digramRules['wh']['v'] = self::ILLEGAL_PAIR;
        $this->digramRules['wh']['w'] = self::ILLEGAL_PAIR;
        $this->digramRules['wh']['x'] = self::ILLEGAL_PAIR;
        $this->digramRules['wh']['y'] = self::BEGIN | self::NOT_END;
        $this->digramRules['wh']['z'] = self::ILLEGAL_PAIR;
        $this->digramRules['wh']['ch'] = self::ILLEGAL_PAIR;
        $this->digramRules['wh']['gh'] = self::ILLEGAL_PAIR;
        $this->digramRules['wh']['ph'] = self::ILLEGAL_PAIR;
        $this->digramRules['wh']['rh'] = self::ILLEGAL_PAIR;
        $this->digramRules['wh']['sh'] = self::ILLEGAL_PAIR;
        $this->digramRules['wh']['th'] = self::ILLEGAL_PAIR;
        $this->digramRules['wh']['wh'] = self::ILLEGAL_PAIR;
        $this->digramRules['wh']['qu'] = self::ILLEGAL_PAIR;
        $this->digramRules['wh']['ck'] = self::ILLEGAL_PAIR;

        $this->digramRules['qu'] = [];
        $this->digramRules['qu']['a'] = self::ANY_COMBINATION;
        $this->digramRules['qu']['b'] = self::ILLEGAL_PAIR;
        $this->digramRules['qu']['c'] = self::ILLEGAL_PAIR;
        $this->digramRules['qu']['d'] = self::ILLEGAL_PAIR;
        $this->digramRules['qu']['e'] = self::ANY_COMBINATION;
        $this->digramRules['qu']['f'] = self::ILLEGAL_PAIR;
        $this->digramRules['qu']['g'] = self::ILLEGAL_PAIR;
        $this->digramRules['qu']['h'] = self::ILLEGAL_PAIR;
        $this->digramRules['qu']['i'] = self::ANY_COMBINATION;
        $this->digramRules['qu']['j'] = self::ILLEGAL_PAIR;
        $this->digramRules['qu']['k'] = self::ILLEGAL_PAIR;
        $this->digramRules['qu']['l'] = self::ILLEGAL_PAIR;
        $this->digramRules['qu']['m'] = self::ILLEGAL_PAIR;
        $this->digramRules['qu']['n'] = self::ILLEGAL_PAIR;
        $this->digramRules['qu']['o'] = self::ANY_COMBINATION;
        $this->digramRules['qu']['p'] = self::ILLEGAL_PAIR;
        $this->digramRules['qu']['r'] = self::ILLEGAL_PAIR;
        $this->digramRules['qu']['s'] = self::ILLEGAL_PAIR;
        $this->digramRules['qu']['t'] = self::ILLEGAL_PAIR;
        $this->digramRules['qu']['u'] = self::ILLEGAL_PAIR;
        $this->digramRules['qu']['v'] = self::ILLEGAL_PAIR;
        $this->digramRules['qu']['w'] = self::ILLEGAL_PAIR;
        $this->digramRules['qu']['x'] = self::ILLEGAL_PAIR;
        $this->digramRules['qu']['y'] = self::ILLEGAL_PAIR;
        $this->digramRules['qu']['z'] = self::ILLEGAL_PAIR;
        $this->digramRules['qu']['ch'] = self::ILLEGAL_PAIR;
        $this->digramRules['qu']['gh'] = self::ILLEGAL_PAIR;
        $this->digramRules['qu']['ph'] = self::ILLEGAL_PAIR;
        $this->digramRules['qu']['rh'] = self::ILLEGAL_PAIR;
        $this->digramRules['qu']['sh'] = self::ILLEGAL_PAIR;
        $this->digramRules['qu']['th'] = self::ILLEGAL_PAIR;
        $this->digramRules['qu']['wh'] = self::ILLEGAL_PAIR;
        $this->digramRules['qu']['qu'] = self::ILLEGAL_PAIR;
        $this->digramRules['qu']['ck'] = self::ILLEGAL_PAIR;

        $this->digramRules['ck'] = [];
        $this->digramRules['ck']['a'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['ck']['b'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['ck']['c'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['ck']['d'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['ck']['e'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['ck']['f'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['ck']['g'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['ck']['h'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['ck']['i'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['ck']['j'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['ck']['k'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['ck']['l'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['ck']['m'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['ck']['n'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['ck']['o'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['ck']['p'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['ck']['r'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['ck']['s'] = self::NOT_BEGIN;
        $this->digramRules['ck']['t'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['ck']['u'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['ck']['v'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['ck']['w'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['ck']['x'] = self::ILLEGAL_PAIR;
        $this->digramRules['ck']['y'] = self::NOT_BEGIN;
        $this->digramRules['ck']['z'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['ck']['ch'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['ck']['gh'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['ck']['ph'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['ck']['rh'] = self::ILLEGAL_PAIR;
        $this->digramRules['ck']['sh'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['ck']['th'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['ck']['wh'] = self::ILLEGAL_PAIR;
        $this->digramRules['ck']['qu'] = self::NOT_BEGIN | self::BREAK_GRAM | self::NOT_END;
        $this->digramRules['ck']['ck'] = self::ILLEGAL_PAIR;
    }

    public function setSeparator($sep)
    {
        $this->separator = $sep;
    }

    public function generate($length)
    {
        return $this->generateWord($length);
    }

    private function generateWord($length)
    {
        $word = '';
        $syllables = [];
        $maxRetries = (4 * $length) + count($this->grams);
        $tries = 0;

        $wordUnits = [];
        $savedPair = [];

        while (strlen($word) < $length) {
            list($new, $units, $savedPair) = $this->getSyllable($length - strlen($word), $savedPair);
            $wordUnits = array_merge($wordUnits, $units);

            if (!($this->isImproperWord($wordUnits) || ($word === '' && $this->haveInitialY($units)) || (strlen($word . $new) === $length && $this->haveFinalSplit($units)))) {
                $word = $word . $new;
                $syllables[] = $new;
            }

            $tries += 1;
            if ($tries > $maxRetries) {
                $tries = 0;
                $word = '';
                $syllables = [];
                $wordUnits = [];
            }
        }

        return implode($this->separator, $syllables);
    }

    private function getSyllable($length, $savedPair)
    {
        $holdSavedPair = $savedPair;
        $maxRetries = (4 * $length) + count($this->grams);

        do {
            $tries = 0;
            $unit = '';
            $lastUnit = '';
            $savedPair = $holdSavedPair;
            $syllable = '';
            $unitsInSyllable = [];
            $vowelCount = 0;
            $currentUnit = 0;
            $lengthLeft = $length;
            $wantAnotherUnit = true;
            $wantVowel = false;
            $ruleBroken = false;

            do {
                $wantVowel = false;
                do {
                    if (count($savedPair) === 2) {
                        $syllable = array_pop($savedPair);
                        $unitsInSyllable[0] = $syllable;
                        $currentUnit += 1;
                        $lengthLeft -= strlen($syllable);
                        if ($this->gramRules[$syllable] & self::VOWEL) {
                            $vowelCount += 1;
                        }
                    }

                    if (count($savedPair) > 0) {
                        $unit = array_pop($savedPair);
                    } else {
                        if ($wantVowel) {
                            $unit = $this->getRandomUnit(self::VOWEL);
                        } else {
                            $unit = $this->getRandomUnit(self::NO_SPECIAL_RULE);
                        }
                    }
                    $lengthLeft -= strlen($unit);
                    $ruleBroken = $lengthLeft < 0;
                    if ($currentUnit === 0) {
                        if ($this->gramRules[$unit] & self::NOT_BEGIN_SYLLABLE) {
                            $ruleBroken = true;
                        } elseif ($lengthLeft === 0) {
                            if ($this->gramRules[$unit] & self::VOWEL) {
                                $wantAnotherUnit = false;
                            } else {
                                $ruleBroken = true;
                            }
                        }
                    } else {
                        $digrams = $this->digramRules;
                        $allowed = function ($flag) use ($digrams, $unitsInSyllable, $currentUnit, $unit) {
                            return $digrams[$unitsInSyllable[$currentUnit - 1]][$unit] & $flag;
                        };

                        if (
                            $allowed(self::ILLEGAL_PAIR) ||
                            ($allowed(self::BREAK_GRAM) && $vowelCount === 0) ||
                            ($allowed(self::END) && $vowelCount === 0 && !($this->gramRules[$unit] & self::VOWEL))
                        ) {
                            $ruleBroken = true;
                        }

                        if ($currentUnit === 1) {
                            if ($allowed(self::NOT_BEGIN)) {
                                $ruleBroken = true;
                            }
                        } else {
                            $lastUnit = $unitsInSyllable[$currentUnit - 1];
                            if (
                                ($currentUnit === 2 && $allowed(self::BEGIN) && ($this->gramRules[$unitsInSyllable[0]] & self::ALTERNATE_VOWEL)) ||
                                ($allowed(self::NOT_END) && $lengthLeft === 0) ||
                                ($allowed(self::BREAK_GRAM) || $this->digramRules[$unitsInSyllable[$currentUnit - 2]][$lastUnit] & self::NOT_END) ||
                                ($allowed(self::PREFIX) && !($this->gramRules[$unitsInSyllable[$currentUnit - 2]] & self::VOWEL))
                            ) {
                                $ruleBroken = true;
                            }

                            if (
                                !$ruleBroken &&
                                $this->gramRules[$unit] & self::VOWEL &&
                                ($lengthLeft > 0 || !($this->gramRules[$lastUnit] & self::NO_FINAL_SPLIT))
                            ) {
                                if ($vowelCount > 0 && $this->gramRules[$lastUnit] & self::VOWEL) {
                                    $ruleBroken = true;
                                } elseif ($vowelCount !== 0 && !($this->gramRules[$lastUnit] && self::VOWEL)) {
                                    if ($this->digramRules[$unitsInSyllable[$currentUnit - 2]][$lastUnit] & self::NOT_END) {
                                        $ruleBroken = true;
                                    } else {
                                        $savedPair = [$unit];
                                        $wantAnotherUnit = false;
                                    }
                                }
                            }
                        }

                        if (!$ruleBroken && $wantAnotherUnit) {
                            if (
                                (
                                    $vowelCount !== 0 &&
                                    ($this->gramRules[$unit] & self::NO_FINAL_SPLIT) &&
                                    $lengthLeft === 0 &&
                                    ($lastUnit && !($this->gramRules[$lastUnit] & self::VOWEL))
                                ) || ($allowed(self::END) || $lengthLeft === 0)
                            ) {
                                $wantAnotherUnit = false;
                            } elseif ($vowelCount !== 0 && $lengthLeft > 0) {
                                if (
                                    $allowed(self::BEGIN) &&
                                    $currentUnit > 1 &&
                                    !($vowelCount === 1 && $this->gramRules[$lastUnit] & self::VOWEL)
                                ) {
                                    $savedPair = [$unit, $lastUnit];
                                    $wantAnotherUnit = false;
                                } elseif ($allowed(self::BREAK_GRAM)) {
                                    $savedPair = [$unit];
                                    $wantAnotherUnit = false;
                                }
                            } elseif ($allowed(self::SUFFIX)) {
                                $wantVowel = true;
                            }
                        }
                    }

                    $tries += 1;
                    if ($ruleBroken) {
                        $lengthLeft += strlen($unit);
                    }
                } while ($ruleBroken && $tries < $maxRetries);

                if ($tries <= $maxRetries) {
                    if (
                        ($this->gramRules[$unit] & self::VOWEL) &&
                        ($currentUnit > 0 || !($this->gramRules[$unit] & self::ALTERNATE_VOWEL))
                    ) {
                        $vowelCount += 1;
                    }

                    if (count($savedPair) === 2) {
                        $syllable = substr($syllable, 0, strlen($syllable) - strlen($lastUnit));
                        $lengthLeft += strlen($lastUnit);
                        $currentUnit -= 2;
                    } elseif (count($savedPair) === 1) {
                        $currentUnit -= 1;
                    } else {
                        $unitsInSyllable[$currentUnit] = $unit;
                        $syllable .= $unit;
                    }
                } else {
                    $roleBroken = true;
                }
                $currentUnit += 1;
            } while ($tries <= $maxRetries && $wantAnotherUnit);
        } while ($ruleBroken || $this->isIllegalPlacement($unitsInSyllable));
        return [$syllable, $unitsInSyllable, $savedPair];
    }

    private function isIllegalPlacement($units)
    {
        $vowelCount = 0;
        $failure = false;
        foreach ($units as $unitCount => $unit) {
            if ($failure) {
                break;
            }

            if ($unitCount >= 1) {
                if (
                    (
                        !($this->gramRules[$units[$unitCount - 1]] & self::VOWEL) &&
                        ($this->gramRules[$unit] & self::VOWEL) &&
                        !($this->gramRules[$unit] & self::NO_FINAL_SPLIT && $unitCount === count($unit)) &&
                        $vowelCount > 0
                    )
                    ||
                    (
                        $unitCount >= 2 &&
                        (
                            (
                                !($this->gramRules[$units[$unitCount - 2]] & self::VOWEL) &&
                                !($this->gramRules[$units[$unitCount - 1]] & self::VOWEL) &&
                                !($this->gramRules[$unit] & self::VOWEL)
                            )
                            ||
                            (
                                ($this->gramRules[$units[$unitCount - 2]] & self::VOWEL) &&
                                !(($this->gramRules[$units[0]] & self::ALTERNATE_VOWEL) && $unitCount === 2) &&
                                ($this->gramRules[$units[$unitCount - 1]] & self::VOWEL) &&
                                ($this->gramRules[$unit] & self::VOWEL)
                            )
                        )
                    )
                ) {
                    $failure = true;
                }
            }

            if (
                $this->gramRules[$unit] & self::VOWEL &&
                !(
                    $this->gramRules[$units[0]] & self::ALTERNATE_VOWEL &&
                    $unitCount === 0 &&
                    count($units) > 1
                )
            ) {
                $vowelCount += 1;
            }
        }
        return $failure;
    }

    private function getRandomUnit($type)
    {
        // TODO
        if ($type & self::VOWEL) {
            return $this->chooseRandom($this->vowelGrams);
        } else {
            return $this->chooseRandom($this->grams);
        }
    }

    private function isImproperWord($units)
    {
        $failure = false;
        foreach ($units as $unitCount => $unit) {
            if ($unitCount > 0 && $this->digramRules[$units[$unitCount - 1]][$unit] & self::ILLEGAL_PAIR) {
                return true;
            }

            if ($unitCount >= 2) {
                if (
                    (
                        ($this->gramRules[$units[$unitCount - 2]] & self::VOWEL) &&
                        !($this->gramRules[$units[$unitCount - 2]] & self::ALTERNATE_VOWEL) &&
                        ($this->gramRules[$units[$unitCount - 1]] & self::VOWEL) &&
                        ($this->gramRules[$unit] & self::VOWEL)
                    )
                    ||
                    (
                        !($this->gramRules[$units[$unitCount - 2]] & self::VOWEL) &&
                        !($this->gramRules[$units[$unitCount - 1]] & self::VOWEL) &&
                        !($this->gramRules[$units[$unitCount]] & self::VOWEL)
                    )
                ) {
                    return true;
                }
            }
        }
        return false;
    }

    private function haveInitialY($units)
    {
        $vowelCount = 0;
        $normalVowelCount = 0;
        foreach ($units as $unitCount => $unit) {
            if ($this->gramRules[$unit] & self::VOWEL) {
                $vowelCount += 1;
                if (!($this->gramRules[$unit] && self::ALTERNATE_VOWEL) || $unitCount > 0) {
                    $normalVowelCount += 1;
                }
            }
        }
        return $vowelCount <= 1 && $normalVowelCount === 0;
    }

    private function haveFinalSplit($units)
    {
        $vowelCount = 0;
        foreach ($units as $unitCount => $unit) {
            if ($this->gramRules[$unit] & self::VOWEL) {
                $vowelCount += 1;
            }
        }

        return $vowelCount === 1 && ($this->gramRules[$units[count($units) - 1]] & self::NO_FINAL_SPLIT);
    }
}

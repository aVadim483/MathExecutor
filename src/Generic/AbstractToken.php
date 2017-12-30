<?php
/**
 * This file is part of the MathExecutor package
 * https://github.com/aVadim483/MathExecutor
 *
 * Based on NeonXP/MathExecutor by Alexander Kiryukhin
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace avadim\MathExecutor\Generic;

use avadim\MathExecutor\Calculator;

/**
 * Class AbstractToken
 *
 * @package avadim\MathExecutor
 */
abstract class AbstractToken
{
    const MATCH_STRING   = 0;
    const MATCH_NUMERIC  = 1;
    const MATCH_REGEX    = 2;
    const MATCH_CALLBACK = 3;

    /** @var null|string  */
    protected static $pattern;

    /** @var int  */
    protected static $matching = self::MATCH_STRING;

    /** @var int  */
    protected static $callback = 'isMatch';

    /** @var string  */
    protected $value;

    /** @var array  */
    protected $options;

    /** @var  Calculator */
    protected $calculator;

    /**
     * @param string $value
     * @param array  $options
     */
    public function __construct($value, array $options = [])
    {
        $this->value = $value;
        $this->options = $options;
    }

    /**
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param string $pattern
     *
     * @return array
     */
    public static function getMatching($pattern = null)
    {
        return [
            'pattern'  => (null === $pattern) ? static::$pattern : $pattern,
            'matching' => static::$matching,
            'callback' => static::$callback,
            ];
    }

    /**
     * @param string           $tokenStr
     * @param AbstractToken[] $prevTokens
     *
     * @return bool
     */
    public static function isMatch($tokenStr, $prevTokens)
    {
        return static::$pattern === $tokenStr;
    }

    /**
     * @param Calculator $calculator
     */
    public function setCalculator($calculator)
    {
        $this->calculator = $calculator;
    }

    /**
     * @return Calculator
     */
    public function getCalculator()
    {
        return $this->calculator;
    }
}

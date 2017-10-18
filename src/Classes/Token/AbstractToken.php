<?php
/**
 * This file is part of the MathExecutor package
 *
 * (c) Alexander Kiryukhin
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace avadim\MathExecutor\Classes\Token;

/**
 * @author Alexander Kiryukhin <alexander@symdev.org>
 */
abstract class AbstractToken implements InterfaceToken
{
    const MATCH_STRING   = 0;
    const MATCH_NUMERIC  = 1;
    const MATCH_REGEX    = 2;
    const MATCH_CALLBACK = 3;

    /** @var null|string  */
    protected static $pattern = null;

    /** @var int  */
    protected static $matching = self::MATCH_STRING;

    /** @var int  */
    protected static $callback = 'isMatch';

    /** @var string  */
    protected $value;

    /** @var array  */
    protected $options;

    /**
     * @param string $value
     * @param array  $options
     */
    public function __construct($value, $options = [])
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
     * @param string $sPattern
     *
     * @return array
     */
    public static function getMatching($sPattern = null)
    {
        return [
            'pattern'  => static::$pattern,
            'matching' => static::$matching,
            'callback' => static::$callback,
            ];
    }

    /**
     * @param string           $tokenStr
     * @param InterfaceToken[] $prevTokens
     *
     * @return bool
     */
    public static function isMatch($tokenStr, $prevTokens)
    {
        return static::$pattern === $tokenStr;
    }

}

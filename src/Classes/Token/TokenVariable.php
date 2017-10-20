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

use avadim\MathExecutor\Classes\Generic\AbstractToken;

/**
 * @author Alexander Kiryukhin <alexander@symdev.org>
 */
class TokenVariable extends AbstractToken
{
    protected static $matching = self::MATCH_REGEX;

    /**
     * @param string $sPattern
     *
     * @return array
     */
    public static function getMatching($sPattern = null)
    {
        return [
            'pattern'  => '/' . preg_quote($sPattern, '/') . '[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/',
            'matching' => static::$matching,
            'callback' => static::$callback,
        ];
    }

}

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

namespace avadim\MathExecutor\Token;

use avadim\MathExecutor\Generic\AbstractToken;

/**
 * Class TokenVariable
 *
 * @package avadim\MathExecutor
 */
class TokenVariable extends AbstractToken
{
    protected static $matching = self::MATCH_REGEX;

    /**
     * @param string $pattern
     *
     * @return array
     */
    public static function getMatching($pattern = null)
    {
        return [
            'pattern'  => '/' . preg_quote($pattern, '/') . '[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/',
            'matching' => static::$matching,
            'callback' => static::$callback,
        ];
    }

}

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

use avadim\MathExecutor\Classes\Generic\AbstractTokenScalar;
use avadim\MathExecutor\Classes\Generic\InterfaceToken;

/**
 * @author Alexander Kiryukhin <alexander@symdev.org>
 */
class TokenScalarNumber extends AbstractTokenScalar
{
    protected static $matching = self::MATCH_NUMERIC;

    /**
     * @param string           $tokenStr
     * @param InterfaceToken[] $prevTokens
     *
     * @return bool
     */
    public static function isMatch($tokenStr, $prevTokens)
    {
        return is_numeric($tokenStr);
    }
}

<?php
/**
 * This file is part of the MathExecutor package
 *
 * (c) Alexander Kiryukhin
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace avadim\MathExecutor\Token;

use avadim\MathExecutor\Generic\AbstractTokenScalar;
use avadim\MathExecutor\Generic\AbstractToken;

/**
 * Class TokenScalarNumber
 *
 * @package avadim\MathExecutor\Token
 */
class TokenScalarNumber extends AbstractTokenScalar
{
    protected static $matching = self::MATCH_NUMERIC;

    /**
     * @param string           $tokenStr
     * @param AbstractToken[] $prevTokens
     *
     * @return bool
     */
    public static function isMatch($tokenStr, $prevTokens)
    {
        return is_numeric($tokenStr);
    }
}

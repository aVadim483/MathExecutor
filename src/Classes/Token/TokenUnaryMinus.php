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
class TokenUnaryMinus extends AbstractOperator
{
    /**
     * @return string
     */
    public static function getRegex()
    {
        return '/\-/';
    }

    /**
     * @param string           $tokenStr
     * @param InterfaceToken[] $prevTokens
     *
     * @return bool
     */
    public static function isMatch($tokenStr, $prevTokens)
    {
        $prevToken = end($prevTokens);
        if ($prevToken instanceof AbstractOperator || $prevToken instanceof TokenLeftBracket || $prevToken instanceof TokenComma) {
            if (preg_match(static::getRegex(), $tokenStr)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return 4;
    }

    /**
     * @return string
     */
    public function getAssociation()
    {
        return self::RIGHT_ASSOC;
    }

    /**
     * @param InterfaceToken[] $stack
     *
     * @return TokenNumber
     */
    public function execute(&$stack)
    {
        $op1 = array_pop($stack);

        return new TokenNumber(-$op1->getValue());
    }

}

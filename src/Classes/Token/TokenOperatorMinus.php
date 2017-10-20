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

use avadim\MathExecutor\Classes\Generic\AbstractTokenOperator;
use avadim\MathExecutor\Classes\Generic\InterfaceToken;

/**
* @author Alexander Kiryukhin <alexander@symdev.org>
*/
class TokenOperatorMinus extends AbstractTokenOperator
{
    protected static $pattern = '-';
    protected static $matching = self::MATCH_CALLBACK;

    /**
     * @param string           $tokenStr
     * @param InterfaceToken[] $prevTokens
     *
     * @return bool
     */
    public static function isMatch($tokenStr, $prevTokens)
    {
        $prevToken = end($prevTokens);
        if (static::$pattern === $tokenStr && !($prevToken instanceof AbstractTokenOperator || $prevToken instanceof TokenLeftBracket || $prevToken instanceof TokenComma)) {
            return true;
        }
        return false;
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return 1;
    }

    /**
     * @return string
     */
    public function getAssociation()
    {
        return self::LEFT_ASSOC;
    }

    /**
     * @param InterfaceToken[] $stack
     * @return TokenScalarNumber
     */
    public function execute(&$stack)
    {
        $op2 = array_pop($stack);
        $op1 = array_pop($stack);
        $result = $op1->getValue() - $op2->getValue();

        return new TokenScalarNumber($result);
    }
}

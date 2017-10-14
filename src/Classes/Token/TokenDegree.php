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
class TokenDegree extends AbstractOperator
{
    /**
     * @return string
     */
    public static function getRegex()
    {
        return '\^';
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return 3;
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
     * @return TokenNumber
     */
    public function execute(&$stack)
    {
        $op2 = array_pop($stack);
        $op1 = array_pop($stack);
        $result = $op1->getValue() ** $op2->getValue();

        return new TokenNumber($result);
    }
}

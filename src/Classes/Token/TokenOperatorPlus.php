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
class TokenOperatorPlus extends AbstractTokenOperator
{
    protected static $pattern = '+';

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
     *
     * @return TokenScalarNumber
     */
    public function execute(&$stack)
    {
        $op2 = array_pop($stack);
        $op1 = array_pop($stack);
        $result = $op1->getValue() + $op2->getValue();

        return new TokenScalarNumber($result);
    }
}

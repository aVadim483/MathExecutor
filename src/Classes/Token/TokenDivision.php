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
class TokenDivision extends AbstractOperator
{
    protected static $pattern = '/';

    /**
     * @return int
     */
    public function getPriority()
    {
        return 2;
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
     * @return TokenNumber
     */
    public function execute(&$stack)
    {
        $op2 = array_pop($stack);
        $op1 = array_pop($stack);
        $result = (float)$op2->getValue() !== 0.0 ? $op1->getValue() / $op2->getValue() : 0;

        return new TokenNumber($result);
    }
}

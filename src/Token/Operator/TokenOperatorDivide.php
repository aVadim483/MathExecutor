<?php
/**
* This file is part of the MathExecutor package
*
* (c) Alexander Kiryukhin
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code
*/

namespace avadim\MathExecutor\Token\Operator;

use avadim\MathExecutor\Generic\AbstractTokenOperator;
use avadim\MathExecutor\Generic\AbstractToken;
use avadim\MathExecutor\Token\TokenScalarNumber;

/**
 * Class TokenOperatorDivide
 *
 * @package avadim\MathExecutor\Token
 */
class TokenOperatorDivide extends AbstractTokenOperator
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
     * @param AbstractToken[] $stack
     *
     * @return TokenScalarNumber
     */
    public function execute(&$stack)
    {
        $op2 = array_pop($stack);
        $op1 = array_pop($stack);
        $result = (float)$op2->getValue() !== 0.0 ? $op1->getValue() / $op2->getValue() : 0;

        return new TokenScalarNumber($result);
    }
}

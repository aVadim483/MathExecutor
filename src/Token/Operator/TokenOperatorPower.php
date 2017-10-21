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
 * Class TokenOperatorPower
 *
 * @package avadim\MathExecutor\Token
 */
class TokenOperatorPower extends AbstractTokenOperator
{
    protected static $pattern = '^';

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
     * @param AbstractToken[] $stack
     *
     * @return TokenScalarNumber
     */
    public function execute(&$stack)
    {
        $op2 = array_pop($stack);
        $op1 = array_pop($stack);
        $result = $op1->getValue() ** $op2->getValue();

        return new TokenScalarNumber($result);
    }
}

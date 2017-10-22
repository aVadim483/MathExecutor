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

namespace avadim\MathExecutor\Token\Operator;

use avadim\MathExecutor\Generic\AbstractTokenOperator;
use avadim\MathExecutor\Generic\AbstractToken;
use avadim\MathExecutor\Token\TokenScalarNumber;

/**
 * Class TokenOperatorDivide
 *
 * @package avadim\MathExecutor
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
     *
     * @throws \DivisionByZeroError
     */
    public function execute(&$stack)
    {
        $op2 = array_pop($stack);
        $op1 = array_pop($stack);
        if ((float)$op2->getValue() === 0.0) {
            throw new \DivisionByZeroError('Divide a number by zero');
        }
        $result = $op1->getValue() / $op2->getValue();

        return new TokenScalarNumber($result);
    }
}

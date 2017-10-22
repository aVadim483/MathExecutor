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
 * Class TokenOperatorMinus
 *
 * @package avadim\MathExecutor
 */
class TokenOperatorMinus extends AbstractTokenOperator
{
    protected static $pattern = '-';

    private $unary = false;

    /**
     * @param string $value
     * @param array  $options
     */
    public function __construct($value, $options = [])
    {
        parent::__construct($value, $options);
        if (!empty($options['begin'])) {
            $this->unary = true;
        }
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        if ($this->unary) {
            return 4;
        }
        return 1;
    }

    /**
     * @return string
     */
    public function getAssociation()
    {
        if ($this->unary) {
            return self::RIGHT_ASSOC;
        }
        return self::LEFT_ASSOC;
    }

    /**
     * @param AbstractToken[] $stack
     *
     * @return TokenScalarNumber
     */
    public function execute(&$stack)
    {
        if ($this->unary) {
            $op = array_pop($stack);
            $result = -$op->getValue();
        } else {
            $op2 = array_pop($stack);
            $op1 = array_pop($stack);
            $result = $op1->getValue() - $op2->getValue();
        }

        return new TokenScalarNumber($result);
    }
}

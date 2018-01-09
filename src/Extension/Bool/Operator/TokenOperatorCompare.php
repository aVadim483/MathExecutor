<?php
/**
 * This file is part of the MathExecutor package
 * https://github.com/aVadim483/MathExecutor
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace avadim\MathExecutor\Extension\Bool\Operator;

use avadim\MathExecutor\Generic\AbstractToken;
use avadim\MathExecutor\Generic\AbstractTokenOperator;
use avadim\MathExecutor\Token\TokenScalarNumber;

/**
 * Class TokenOperatorCompare
 *
 * @package avadim\MathExecutor
 */
class TokenOperatorCompare extends AbstractTokenOperator
{
    /**
     * @return int
     */
    public function getPriority()
    {
        return 0;
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
        $stack[] = static::$pattern;
        return $this->calculator->callFunction('compare', $stack);
    }

}
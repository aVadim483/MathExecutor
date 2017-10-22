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

namespace avadim\MathExecutor\Generic;

/**
 * Class AbstractTokenOperator
 *
 * @package avadim\MathExecutor
 */
abstract class AbstractTokenOperator extends AbstractToken
{
    const RIGHT_ASSOC   = 'RIGHT';
    const LEFT_ASSOC    = 'LEFT';

    /**
     * @return int
     */
    abstract public function getPriority();

    /**
     * @return string
     */
    abstract public function getAssociation();

    /**
     * @param  array       $stack
     *
     * @return mixed
     */
    abstract public function execute(&$stack);

    /**
     * @param $token
     *
     * @return bool
     */
    public function lowPriority($token)
    {
        if (
            ($token instanceof AbstractTokenOperator)
            &&
            (
                ($this->getAssociation() === AbstractTokenOperator::LEFT_ASSOC && $this->getPriority() <= $token->getPriority())
                ||
                ($this->getAssociation() === AbstractTokenOperator::RIGHT_ASSOC && $this->getPriority() < $token->getPriority())
            )
        ) {
            return true;
        }
        return false;
    }

}

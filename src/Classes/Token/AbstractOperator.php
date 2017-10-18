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
abstract class AbstractOperator extends AbstractToken
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
     * @return TokenNumber
     */
    abstract public function execute(&$stack);

}

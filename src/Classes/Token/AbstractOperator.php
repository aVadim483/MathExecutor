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
abstract class AbstractOperator implements InterfaceToken, InterfaceOperator
{
    const RIGHT_ASSOC   = 'RIGHT';
    const LEFT_ASSOC    = 'LEFT';

    /**
     * @param string           $tokenStr
     * @param InterfaceToken[] $prevTokens
     *
     * @return bool
     */
    public function isMatch($tokenStr, $prevTokens)
    {
        $regex = sprintf('/%s/i', static::getRegex());
        if (preg_match($regex, $tokenStr)) {
            return true;
        }
        return false;
    }
}

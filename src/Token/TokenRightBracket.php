<?php
/**
 * This file is part of the MathExecutor package
 *
 * (c) Alexander Kiryukhin
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace avadim\MathExecutor\Token;

use avadim\MathExecutor\Generic\AbstractToken;

/**
 * Class TokenRightBracket
 *
 * @package avadim\MathExecutor\Token
 */
class TokenRightBracket extends AbstractToken
{
    protected static $pattern = ')';

}

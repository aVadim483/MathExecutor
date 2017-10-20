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

use avadim\MathExecutor\Classes\Generic\AbstractToken;

/**
 * @author Alexander Kiryukhin <alexander@symdev.org>
 */
class TokenIdentifier extends AbstractToken
{
    protected static $pattern = '/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/';
    protected static $matching = self::MATCH_REGEX;

}

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

use avadim\MathExecutor\Generic\AbstractTokenScalar;

/**
 * Class TokenScalarString
 *
 * @package avadim\MathExecutor\Token
 */
class TokenScalarString extends AbstractTokenScalar
{
    protected static $pattern = '/\"[^\"]*\"/';
    protected static $matching = self::MATCH_REGEX;

}

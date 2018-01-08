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

namespace avadim\MathExecutor\Token;

use avadim\MathExecutor\Generic\AbstractToken;

/**
 * Class TokenIdentifier
 *
 * @package avadim\MathExecutor
 */
class TokenIdentifier extends AbstractToken
{
    protected static $pattern = '/^[a-zA-Z_\x7f-\xff]([a-zA-Z0-9_\x7f-\xff]*)$/';
    protected static $matching = self::MATCH_REGEX;

}

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

use avadim\MathExecutor\Generic\AbstractTokenGroup;

/**
 * Class TokenLeftBracket
 *
 * @package avadim\MathExecutor
 */
class TokenLeftBracket extends AbstractTokenGroup
{
    protected static $pattern = '(';

}

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

use avadim\MathExecutor\Generic\AbstractTokenScalar;

/**
 * Class TokenScalarString
 *
 * @package avadim\MathExecutor
 */
class TokenScalarString extends AbstractTokenScalar
{
    protected static $pattern = '/^\"[^\"]*\"$/';
    protected static $matching = self::MATCH_REGEX;

    /**
     * @param string $lexeme
     * @param array  $options
     */
    public function __construct($lexeme, $options = [])
    {
        $value = (string)substr($lexeme, 1, -1);
        parent::__construct($value, $options);
        $this->lexeme = $lexeme;
    }

}

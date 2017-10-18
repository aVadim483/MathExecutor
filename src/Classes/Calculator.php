<?php
/**
 * This file is part of the MathExecutor package
 *
 * (c) Alexander Kiryukhin
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace avadim\MathExecutor\Classes;

use avadim\MathExecutor\Classes\Token\AbstractOperator;
use avadim\MathExecutor\Classes\Token\AbstractScalarToken;
use avadim\MathExecutor\Classes\Token\TokenFunction;
use avadim\MathExecutor\Classes\Token\TokenLeftBracket;
use avadim\MathExecutor\Classes\Token\TokenNumber;
use avadim\MathExecutor\Classes\Token\TokenVariable;
use avadim\MathExecutor\Exception\CalcException;

/**
 * @author Alexander Kiryukhin <alexander@symdev.org>
 */
class Calculator
{
    /**
     * Calculate array of tokens in reverse polish notation
     *
     * @param  array $tokens    Array of tokens
     * @param  array $variables Array of variables
     *
     * @return int|float
     *
     * @throws CalcException
     */
    public function calculate($tokens, $variables)
    {
        $stack = [];
        foreach ($tokens as $token) {
            if ($token instanceof TokenLeftBracket) {
                $stack[] = $token;
            }
            if ($token instanceof AbstractScalarToken) {
                $stack[] = $token;
            }
            if ($token instanceof TokenVariable) {
                $variable = $token->getValue();
                if (!array_key_exists($variable, $variables)) {
                    throw new CalcException('Unknown variable "' . $variable . '"', CalcException::CALC_UNKNOWN_VARIABLE);
                }
                $value = $variables[$variable];
                $stack[] = new TokenNumber($value);
            }
            if ($token instanceof AbstractOperator || $token instanceof TokenFunction) {
                $stack[] = $token->execute($stack);
            }
        }
        $result = array_pop($stack);
        if (!empty($stack)) {
            throw new CalcException('Incorrect expression ', CalcException::CALC_INCORRECT_EXPRESSION);
        }

        return $result->getValue();
    }
}

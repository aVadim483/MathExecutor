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

use avadim\MathExecutor\Classes\Token\InterfaceOperator;
use avadim\MathExecutor\Classes\Token\TokenFunction;
use avadim\MathExecutor\Classes\Token\TokenLeftBracket;
use avadim\MathExecutor\Classes\Token\TokenNumber;
use avadim\MathExecutor\Classes\Token\TokenVariable;
use avadim\MathExecutor\Exception\IncorrectExpressionException;
use avadim\MathExecutor\Exception\UnknownVariableException;

/**
 * @author Alexander Kiryukhin <alexander@symdev.org>
 */
class Calculator
{
    /**
     * Calculate array of tokens in reverse polish notation
     *
     * @param  array                         $tokens    Array of tokens
     * @param  array                         $variables Array of variables
     *
     * @return number                        Result
     *
     * @throws IncorrectExpressionException
     * @throws UnknownVariableException
     */
    public function calculate($tokens, $variables)
    {
        $stack = array();
        foreach ($tokens as $token) {
            if ($token instanceof TokenLeftBracket) {
                array_push($stack, $token);
            }
            if ($token instanceof TokenNumber) {
                array_push($stack, $token);
            }
            if ($token instanceof TokenVariable) {
                $variable = $token->getValue();
                if (!array_key_exists($variable, $variables)) {
                    throw new UnknownVariableException();
                }
                $value = $variables[$variable];
                array_push($stack, new TokenNumber($value));
            }
            if ($token instanceof InterfaceOperator || $token instanceof TokenFunction) {
                array_push($stack, $token->execute($stack));
            }
        }
        $result = array_pop($stack);
        if (!empty($stack)) {
            throw new IncorrectExpressionException();
        }

        return $result->getValue();
    }
}

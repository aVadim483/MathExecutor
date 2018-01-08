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
use avadim\MathExecutor\Exception\CalcException;

/**
 * Class TokenFunction
 *
 * @package avadim\MathExecutor
 */
class TokenFunction extends TokenIdentifier
{
    /**
     * @param array $stack
     *
     * @return TokenScalarNumber
     *
     * @throws CalcException
     */
    public function execute(&$stack)
    {
        $args = [];
        $token = null;
        list($name, $numArguments, $callback, $variableArguments) = $this->options;
        for ($i = 0; $i < $numArguments; $i++) {
            $token = $stack ? array_pop($stack) : null;
            if ($token instanceof AbstractTokenScalar || $token instanceof TokenIdentifier) {
                $args[] = $token->getValue();
            } elseif (is_scalar($token)) {
                $args[] = $token;
            } else {
                throw new CalcException('Wrong arguments of function "' . $name . '"', CalcException::CALC_WRONG_FUNC_ARGS);
            }
        }
        if ($variableArguments) {
            while ($stack && ($token = array_pop($stack)) && !$token instanceof TokenLeftBracket) {
                $args[] = $token->getValue();
            }
        } elseif ($stack) {
            $token = array_pop($stack);
            if (!$token instanceof TokenLeftBracket) {
                throw new CalcException('Wrong arguments of function "' . $name . '"', CalcException::CALC_WRONG_FUNC_ARGS);
            }
        }
        $result = call_user_func_array($callback, array_reverse($args));

        return new TokenScalarNumber($result);
    }
}

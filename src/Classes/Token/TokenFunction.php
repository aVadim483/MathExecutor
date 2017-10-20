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

use avadim\MathExecutor\Classes\Generic\AbstractTokenScalar;
use avadim\MathExecutor\Exception\CalcException;

/**
 * @author Alexander Kiryukhin <alexander@symdev.org>
 */
class TokenFunction extends TokenIdentifier
{
    protected static $pattern = '/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/';
    protected static $matching = self::MATCH_REGEX;

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
            if (empty($token) || !($token instanceof AbstractTokenScalar || $token instanceof TokenIdentifier)) {
                throw new CalcException('Wrong arguments of function "' . $name . '"', CalcException::CALC_WRONG_FUNC_ARGS);
            }
            $args[] = $token->getValue();
        }
        if ($variableArguments) {
            while ($stack && ($token = array_pop($stack)) && !$token instanceof TokenLeftBracket) {
                $args[] = $token->getValue();
            }
        } else {
            $token = array_pop($stack);
        }
        if (!$token instanceof TokenLeftBracket) {
            throw new CalcException('Wrong arguments of function "' . $name . '"', CalcException::CALC_WRONG_FUNC_ARGS);
        }
        $result = call_user_func_array($callback, array_reverse($args));

        return new TokenScalarNumber($result);
    }
}

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

use avadim\MathExecutor\Exception\IncorrectExpressionException;

/**
 * @author Alexander Kiryukhin <alexander@symdev.org>
 */
class TokenFunction extends AbstractContainerToken implements InterfaceFunction
{
    /**
     * @return string
     */
    public static function getRegex()
    {
        return '[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*';
    }

    /**
     * @param array $stack
     *
     * @return TokenNumber
     *
     * @throws IncorrectExpressionException
     */
    public function execute(&$stack)
    {
        $args = [];
        $token = null;
        list($numArguments, $function, $variableArguments) = $this->value;
        for ($i = 0; $i < $numArguments; $i++) {
            $token = $stack ? array_pop($stack) : null;
            if (empty($token) || !$token instanceof TokenNumber) {
                throw new IncorrectExpressionException();
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
            throw new IncorrectExpressionException();
        }
        $result = call_user_func_array($function, $args);

        return new TokenNumber($result);
    }
}

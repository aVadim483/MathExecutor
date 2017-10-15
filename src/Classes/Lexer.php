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
use avadim\MathExecutor\Classes\Token\InterfaceOperator;
use avadim\MathExecutor\Classes\Token\TokenComma;
use avadim\MathExecutor\Classes\Token\TokenFunction;
use avadim\MathExecutor\Classes\Token\TokenLeftBracket;
use avadim\MathExecutor\Classes\Token\TokenNumber;
use avadim\MathExecutor\Classes\Token\TokenRightBracket;
use avadim\MathExecutor\Classes\Token\TokenVariable;
use avadim\MathExecutor\Exception\IncorrectBracketsException;
use avadim\MathExecutor\Exception\IncorrectExpressionException;
use avadim\MathExecutor\Exception\UnknownFunctionException;
use avadim\MathExecutor\Exception\UnknownTokenException;

/**
 * @author Alexander Kiryukhin <alexander@symdev.org>
 */
class Lexer
{
    /**
     * @var TokenFactory
     */
    private $tokenFactory;

    public function __construct($tokenFactory)
    {
        $this->tokenFactory = $tokenFactory;
    }

    /**
     * @param  string $input Source string of equation
     *
     * @return array  Tokens stream
     *
     * @throws IncorrectExpressionException
     * @throws UnknownFunctionException
     * @throws UnknownTokenException
     */
    public function stringToTokensStream($input)
    {
        $matches = [];
        // minus before number
        $input = preg_replace_callback('/([\)\w])\s*\-(\d)/', function ($matches){
            return $matches[1] . ' - ' . $matches[2];
        }, $input);
        preg_match_all($this->tokenFactory->getTokenParserRegex(), $input, $matches);
        $tokensStream = [];
        foreach($matches[0] as $tokenStr) {
            $tokensStream[] = $this->tokenFactory->createToken($tokenStr, $tokensStream);
        }

        return $tokensStream;
    }

    /**
     * @param  array $tokensStream Tokens stream
     * @return array Array of tokens in revers polish notation
     *
     * @throws IncorrectExpressionException
     * @throws IncorrectBracketsException
     */
    public function buildReversePolishNotation($tokensStream)
    {
        $output = [];
        $stack = [];
        $function = 0;

        foreach ($tokensStream as $token) {
            if ($token instanceof AbstractScalarToken) {
                $output[] = $token;
            }
            if ($token instanceof TokenVariable) {
                $output[] = $token;
            }
            if ($token instanceof TokenFunction) {
                $stack[] = $token;
                ++$function;
            }
            if ($token instanceof TokenLeftBracket) {
                $stack[] = $token;
                if ($function > 0) {
                    $output[] = $token;
                }
            }
            if ($token instanceof TokenComma) {
                while ($stack && (!$stack[count($stack)-1] instanceof TokenLeftBracket)) {
                    $output[] = array_pop($stack);
                    if (empty($stack)) {
                        throw new IncorrectExpressionException();
                    }
                }
            }
            if ($token instanceof TokenRightBracket) {
                while (($current = array_pop($stack)) && (!$current instanceof TokenLeftBracket)) {
                    $output[] = $current;
                }
                if (!empty($stack) && ($stack[count($stack)-1] instanceof TokenFunction)) {
                    $output[] = array_pop($stack);
                }
                if ($function > 0) {
                    --$function;
                }
            }

            if ($token instanceof AbstractOperator) {
                while (
                    count($stack) > 0 &&
                    ($stack[count($stack)-1] instanceof InterfaceOperator) &&
                    ((
                        $token->getAssociation() === AbstractOperator::LEFT_ASSOC &&
                        $token->getPriority() <= $stack[count($stack)-1]->getPriority()
                    ) || (
                        $token->getAssociation() === AbstractOperator::RIGHT_ASSOC &&
                        $token->getPriority() < $stack[count($stack)-1]->getPriority()
                    ))
                ) {
                    $output[] = array_pop($stack);
                }

                $stack[] = $token;
            }
        }
        while (!empty($stack)) {
            $token = array_pop($stack);
            if ($token instanceof TokenLeftBracket || $token instanceof TokenRightBracket) {
                throw new IncorrectBracketsException();
            }
            $output[] = $token;
        }

        return $output;
    }
}

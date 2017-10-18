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
use avadim\MathExecutor\Classes\Token\TokenComma;
use avadim\MathExecutor\Classes\Token\TokenFunction;
use avadim\MathExecutor\Classes\Token\TokenLeftBracket;
use avadim\MathExecutor\Classes\Token\TokenRightBracket;
use avadim\MathExecutor\Classes\Token\TokenVariable;

use avadim\MathExecutor\Exception\LexerException;

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
     * @throws LexerException
     */
    public function stringToTokensStream($input)
    {
        // parse to lexemes array
        $lexemes = token_get_all('<?php ' . $input);
        array_shift($lexemes);

        // convert lexemes to tokens
        $tokensStream = [];
        foreach($lexemes as $lexeme) {
            if (is_string($lexeme)) {
                $tokenStr = $lexeme;
            } elseif(isset($lexeme[0], $lexeme[1]) && $lexeme[0] !== T_BAD_CHARACTER && $lexeme[0] !== T_WHITESPACE) {
                $tokenStr = $lexeme[1];
            } else {
                $tokenStr = null;
            }
            if (null !== $tokenStr) {
                $tokensStream[] = $this->tokenFactory->createToken($tokenStr, $tokensStream);
            }
        }

        return $tokensStream;
    }

    /**
     * @param  array $tokensStream Tokens stream
     * @return array Array of tokens in revers polish notation
     *
     * @throws LexerException
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
                        throw new LexerException('Incorrect expression', LexerException::LEXER_ERROR);
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
                    ($stack[count($stack)-1] instanceof AbstractOperator) &&
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
                throw new LexerException('Incorrect brackets expression', LexerException::LEXER_ERROR);
            }
            $output[] = $token;
        }

        return $output;
    }
}

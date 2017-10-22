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

namespace avadim\MathExecutor;

use avadim\MathExecutor\Generic\AbstractTokenOperator;
use avadim\MathExecutor\Generic\AbstractTokenScalar;

use avadim\MathExecutor\Token\TokenComma;
use avadim\MathExecutor\Token\TokenFunction;
use avadim\MathExecutor\Token\TokenIdentifier;
use avadim\MathExecutor\Token\TokenLeftBracket;
use avadim\MathExecutor\Token\TokenRightBracket;
use avadim\MathExecutor\Token\TokenVariable;

use avadim\MathExecutor\Exception\LexerException;

/**
 * Class Lexer
 *
 * @package avadim\MathExecutor
 */
class Lexer
{
    /**
     * @var TokenFactory
     */
    private $tokenFactory;

    /**
     * Lexer constructor.
     *
     * @param TokenFactory $tokenFactory
     */
    public function __construct($tokenFactory)
    {
        $this->tokenFactory = $tokenFactory;
    }

    /**
     * Parse input string and returns tokens stream
     *
     * @param  string $input Source string of equation
     *
     * @return array
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
     * Returns tokens in revers polish notation
     *
     * @param  array $tokensStream Tokens stream
     *
     * @return array
     *
     * @throws LexerException
     */
    public function buildReversePolishNotation($tokensStream)
    {
        $output = [];
        $stack = [];
        $function = 0;

        foreach ($tokensStream as $token) {
            if ($token instanceof TokenFunction) {
                $stack[] = $token;
                ++$function;
            } elseif ($token instanceof AbstractTokenScalar || $token instanceof TokenVariable || $token instanceof TokenIdentifier) {
                $output[] = $token;
            } elseif ($token instanceof TokenLeftBracket) {
                $stack[] = $token;
                if ($function > 0) {
                    $output[] = $token;
                }
            } elseif ($token instanceof TokenComma) {
                while ($stack && (!$stack[count($stack)-1] instanceof TokenLeftBracket)) {
                    $output[] = array_pop($stack);
                    if (empty($stack)) {
                        throw new LexerException('Incorrect expression', LexerException::LEXER_ERROR);
                    }
                }
            } elseif ($token instanceof TokenRightBracket) {
                while (($current = array_pop($stack)) && (!$current instanceof TokenLeftBracket)) {
                    $output[] = $current;
                }
                if (!empty($stack) && ($stack[count($stack)-1] instanceof TokenFunction)) {
                    $output[] = array_pop($stack);
                }
                if ($function > 0) {
                    --$function;
                }
            } elseif ($token instanceof AbstractTokenOperator) {
                while (($count = count($stack)) > 0 && $token->lowPriority($stack[$count-1])) {
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

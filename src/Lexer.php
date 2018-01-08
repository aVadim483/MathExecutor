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
    private $lexemes = [];

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
     * @param string $input Source string of equation
     */
    public function init($input)
    {
        // parse to lexemes array
        $phpTokens = token_get_all('<?php ' . $input);
        array_shift($phpTokens);

        $this->lexemes = [];
        foreach($phpTokens as $phpToken) {
            if (is_string($phpToken)) {
                $lexemeStr = $phpToken;
            } elseif(isset($phpToken[0], $phpToken[1]) && $phpToken[0] !== T_WHITESPACE) {
                $lexemeStr = $phpToken[1];
            } else {
                $lexemeStr = null;
            }
            if (null !== $lexemeStr) {
                $this->lexemes[] = $lexemeStr;
            }
        }

    }

    /**
     * @return array
     *
     * @throws LexerException
     */
    public function getTokensStream()
    {
        // convert lexemes to tokens
        $tokensStream = [];
        foreach ($this->lexemes as $lexemeNum => $lexemeStr) {
            $tokensStream[] = $this->tokenFactory->createToken($lexemeStr, $tokensStream, $this->lexemes, $lexemeNum);
        }
        // convert identifiers to functions
        foreach ($tokensStream as $num => $token) {
            if ($token instanceof TokenIdentifier && isset($tokensStream[$num + 1]) && $tokensStream[$num + 1] instanceof TokenLeftBracket) {
                $tokensStream[$num] = $this->tokenFactory->createFunction($token->getLexeme());
            }
        }
        return $tokensStream;
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
        $this->init($input);

        return $this->getTokensStream();
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

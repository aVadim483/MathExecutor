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
    /** @var Container */
    private $container;

    /** @var array  */
    private $lexemes = [];

    /**
     * Lexer constructor.
     *
     * @param Container $container
     */
    public function __construct($container = null)
    {
        $this->setContainer($container);
    }

    /**
     * @param Container $container
     *
     * @return $this
     */
    public function setContainer($container)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * @return TokenFactory
     */
    public function getTokenFactory()
    {
        return $this->container->get('TokenFactory');
    }

    public function setLexemes($lexemes)
    {
        $this->lexemes = $lexemes;

        return $this;
    }

    public function getLexemes()
    {
        return $this->lexemes;
    }

    /**
     * @param string $input Source string of equation
     *
     * @return $this
     */
    public function init($input)
    {
        $lexemes = $this->parse($input);
        $this->setLexemes($lexemes);

        return $this;
    }

    /**
     * @param $input
     *
     * @return array
     */
    protected function parse($input)
    {
        // parse to lexemes array
        $phpTokens = token_get_all('<?php ' . $input);
        array_shift($phpTokens);

        $lexemesArray = [];
        foreach($phpTokens as $phpToken) {
            if (is_string($phpToken)) {
                $lexemeStr = $phpToken;
            } elseif(isset($phpToken[0], $phpToken[1]) && $phpToken[0] !== T_WHITESPACE) {
                $lexemeStr = $phpToken[1];
            } else {
                $lexemeStr = null;
            }
            if (null !== $lexemeStr) {
                if (strlen($lexemeStr) > 1 && ($lexemeStr[0] === '#' || $lexemeStr[0] === '/')) {
                    $lexemesArray[] = [$lexemeStr[0]];
                    $lexemesArray[] = $this->parse(substr($lexemeStr, 1));
                } else {
                    $lexemesArray[] = [$lexemeStr];
                }
            }
        }
        return array_merge(...$lexemesArray);
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
        $lexemes = $this->getLexemes();
        foreach ($lexemes as $lexemeNum => $lexemeStr) {
            $tokensStream[] = $this->getTokenFactory()->createToken($lexemeStr, $tokensStream, $lexemes, $lexemeNum);
        }
        // convert identifiers to functions
        foreach ($tokensStream as $num => $token) {
            if ($token instanceof TokenIdentifier && isset($tokensStream[$num + 1]) && $tokensStream[$num + 1] instanceof TokenLeftBracket) {
                $tokensStream[$num] = $this->getTokenFactory()->createFunction($token->getLexeme());
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

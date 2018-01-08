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

use avadim\MathExecutor\Generic\AbstractToken;
use avadim\MathExecutor\Generic\AbstractTokenDelimiter;
use avadim\MathExecutor\Generic\AbstractTokenGroup;
use avadim\MathExecutor\Generic\AbstractTokenOperator;

use avadim\MathExecutor\Exception\ConfigException;
use avadim\MathExecutor\Exception\LexerException;
use avadim\MathExecutor\Token\TokenScalarNumber;
use avadim\MathExecutor\Token\TokenScalarString;

/**
 * Class TokenFactory
 *
 * @package avadim\MathExecutor
 */
class TokenFactory
{
    /**
     * @var Container
     */
    private $container;

    /**
     * Available tokens (not functions)
     *
     * @var array
     */
    protected $tokens = [];

    /**
     * Available functions
     *
     * @var array
     */
    protected $functions = [];

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
     * @param string $name
     * @param string $tokenClass
     * @param string $pattern
     * @param bool   $prepend
     *
     * @throws ConfigException
     */
    protected function registerToken($name, $tokenClass, $pattern = null, $prepend = false)
    {
        $matching = $tokenClass::getMatching($pattern);
        if (!isset($matching['pattern']) && !isset($matching['matching'])) {
            throw new ConfigException('Method class "' . $tokenClass . '::getMatching()" returns bad array', ConfigException::CONFIG_OPERATOR_BAD_INTERFACE);
        }
        $matching['class'] = $tokenClass;

        if ($prepend && !isset($this->tokens[$name])) {
            $this->tokens = array_merge([$name => $matching], $this->tokens);
        } else {
            $this->tokens[$name] = $matching;
        }
    }

    /**
     * Add token class
     *
     * @param string $name
     * @param string $class
     * @param string $pattern
     *
     * @throws ConfigException
     */
    public function addToken($name, $class, $pattern = null)
    {
        $this->registerToken($name, $class, $pattern, false);
    }

    /**
     * Add operator class
     *
     * @param string $name
     * @param string $class
     *
     * @throws ConfigException
     */
    public function addOperator($name, $class)
    {
        $this->registerToken($name, $class, null, true);
    }

    /**
     * Add function
     *
     * @param string   $name
     * @param mixed    $function
     */
    public function addFunction($name, $function)
    {
        $this->functions[$name] = $function;
    }

    /**
     * Create token object
     *
     * @param string $tokenStr      Token as string
     * @param array  $tokensStream  Stream of previous tokens
     * @param array  $lexemes       Array of all lexemes
     * @param int    $lexemeNum     Number of current lexemes
     *
     * @return mixed
     *
     * @throws LexerException
     */
    public function createToken($tokenStr, $tokensStream, $lexemes, $lexemeNum)
    {
        if ($tokensStream) {
            $prevToken = end($tokensStream);
            $beginExpression = ($prevToken instanceof AbstractTokenOperator || $prevToken instanceof AbstractTokenGroup || $prevToken instanceof AbstractTokenDelimiter);
        } else {
            $prevToken = null;
            $beginExpression = true;
        }

        $options = ['begin' => $beginExpression];
        foreach ($this->tokens as $tokenName => $tokenMatching) {
            $tokenClass = $tokenMatching['class'];
            $tokenCallback = $tokenMatching['callback'];

            switch ($tokenMatching['matching']) {
                case AbstractToken::MATCH_CALLBACK:
                    if ($tokenClass::$tokenCallback($tokenStr, $tokensStream)) {
                        return new $tokenClass($tokenStr, $options);
                    }
                    break;
                case AbstractToken::MATCH_REGEX:
                    if (preg_match($tokenMatching['pattern'], $tokenStr)) {
                        return new $tokenClass($tokenStr, $options);
                    }
                    break;
                case AbstractToken::MATCH_NUMERIC:
                    if (is_numeric($tokenStr)) {
                        return new $tokenClass($tokenStr, $options);
                    }
                    break;
                case AbstractToken::MATCH_STRING:
                default:
                    if ($tokenMatching['pattern'] === $tokenStr) {
                        return new $tokenClass($tokenStr, $options);
                    }
            }
        }
        throw new LexerException('Unknown token "' . $tokenStr . '"', LexerException::LEXER_UNKNOWN_TOKEN);
    }

    /**
     * @param $value
     *
     * @return TokenScalarNumber|TokenScalarString
     */
    public function createScalarToken($value)
    {
        return is_numeric($value) ? new TokenScalarNumber($value) : new TokenScalarString($value);
    }

    /**
     * Create function object
     *
     * @param string $name
     *
     * @return mixed
     *
     * @throws LexerException
     */
    public function createFunction($name)
    {
        if (isset($this->functions[$name], $this->tokens['function']['class'])) {
            $tokenClass = $this->tokens['function']['class'];
            $tokenOptions = isset($this->functions[$name]) ? $this->functions[$name] : [];
            return new $tokenClass($name, $tokenOptions);
        }
        throw new LexerException('Unknown function "' . $name . '"', LexerException::LEXER_UNKNOWN_FUNCTION);
    }

    /**
     * Returns registered functions
     *
     * @return array
     */
    public function getFunctions()
    {
        return $this->functions;
    }
}

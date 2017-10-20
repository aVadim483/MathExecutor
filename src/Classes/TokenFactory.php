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

use avadim\MathExecutor\Classes\Generic\AbstractToken;
use avadim\MathExecutor\Classes\Generic\InterfaceToken;

use avadim\MathExecutor\Exception\ConfigException;
use avadim\MathExecutor\Exception\LexerException;

/**
 * @author Alexander Kiryukhin <alexander@symdev.org>
 */
class TokenFactory
{
    /**
     * Available tokens (not operators and not functions)
     *
     * @var array
     */
    protected $tokens = [];

    /**
     * Available operators
     *
     * @var array
     */
    protected $operators = [];

    /**
     * Available functions
     *
     * @var array
     */
    protected $functions = [];

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
        try {
            $class = new \ReflectionClass($tokenClass);
        } catch (\Exception $e) {
            throw new ConfigException('Cannot get reflection of class "' . $tokenClass . '"', ConfigException::CONFIG_OTHER_ERRORS, $e);
        }
        if (!in_array(InterfaceToken::class, $class->getInterfaceNames(), true)) {
            throw new ConfigException('Token class does not implement interface ' . InterfaceToken::class, ConfigException::CONFIG_OPERATOR_BAD_INTERFACE);
        }

        $matching = $tokenClass::getMatching($pattern);
        if (!isset($matching['pattern']) && !isset($matching['matching'])) {
            throw new ConfigException('Token class "' . $tokenClass . '" does not implement interface ' . InterfaceToken::class, ConfigException::CONFIG_OPERATOR_BAD_INTERFACE);
        }
        $matching['class'] = $tokenClass;

        if (!isset($this->tokens[$name]) && $prepend) {
            $this->tokens = array_merge([$name => $matching], $this->tokens);
        } else {
            $this->tokens[$name] = $matching;
        }
    }

    /**
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
     * Add operator
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
     * @param callable $callback
     * @param int      $minArguments
     * @param bool     $variableArguments
     */
    public function addFunction($name, $callback, $minArguments = 1, $variableArguments = false)
    {
        if (null === $minArguments) {
            $minArguments = 1;
        } elseif ($minArguments === -1) {
            $minArguments = 0;
            $variableArguments = true;
        }
        $this->functions[$name] = [$name, $minArguments, $callback, $variableArguments];
    }

    /**
     * @param string $tokenStr
     * @param array  $tokensStream
     *
     * @return mixed
     *
     * @throws LexerException
     */
    public function createToken($tokenStr, $tokensStream)
    {
        foreach ($this->tokens as $tokenName => $tokenMatching) {
            $tokenClass = $tokenMatching['class'];
            $tokenCallback = $tokenMatching['callback'];

            switch ($tokenMatching['matching']) {
                case AbstractToken::MATCH_CALLBACK:
                    if ($tokenClass::$tokenCallback($tokenStr, $tokensStream)) {
                        return new $tokenClass($tokenStr);
                    }
                    break;
                case AbstractToken::MATCH_REGEX:
                    if (preg_match($tokenMatching['pattern'], $tokenStr)) {
                        return new $tokenClass($tokenStr);
                    }
                    break;
                case AbstractToken::MATCH_NUMERIC:
                    if (is_numeric($tokenStr)) {
                        return new $tokenClass($tokenStr);
                    }
                    break;
                case AbstractToken::MATCH_STRING:
                default:
                    if ($tokenMatching['pattern'] === $tokenStr) {
                        return new $tokenClass($tokenStr);
                    }
            }
        }
        throw new LexerException('Unknown token "' . $tokenStr . '"', LexerException::LEXER_UNKNOWN_TOKEN);
    }

    /**
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

}

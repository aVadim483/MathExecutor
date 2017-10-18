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

use avadim\MathExecutor\Classes\Token\AbstractToken;
use avadim\MathExecutor\Classes\Token\InterfaceToken;
use avadim\MathExecutor\Classes\Token\TokenComma;
use avadim\MathExecutor\Classes\Token\TokenFunction;
use avadim\MathExecutor\Classes\Token\TokenLeftBracket;
use avadim\MathExecutor\Classes\Token\TokenNumber;
use avadim\MathExecutor\Classes\Token\TokenRightBracket;
use avadim\MathExecutor\Classes\Token\TokenVariable;
use avadim\MathExecutor\Classes\Token\TokenString;

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
     * @param string $sPattern
     *
     * @throws ConfigException
     */
    public function addToken($name, $tokenClass, $sPattern = null)
    {
        try {
            $class = new \ReflectionClass($tokenClass);
        } catch (\Exception $e) {
            throw new ConfigException('Cannot get reflection of class "' . $tokenClass . '"', ConfigException::CONFIG_OTHER_ERRORS, $e);
        }
        if (!in_array(InterfaceToken::class, $class->getInterfaceNames(), true)) {
            throw new ConfigException('Token class does not implement interface ' . InterfaceToken::class, ConfigException::CONFIG_OPERATOR_BAD_INTERFACE);
        }

        /** @var InterfaceToken $tokenClass */
        $matching = $tokenClass::getMatching($sPattern);
        if (!isset($matching['pattern']) && !isset($matching['matching'])) {
            throw new ConfigException('Token class "' . $tokenClass . '" does not implement interface ' . InterfaceToken::class, ConfigException::CONFIG_OPERATOR_BAD_INTERFACE);
        }
        $matching['class'] = $tokenClass;
        $this->tokens[$name] = $matching;
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
        if ($minArguments === -1) {
            $minArguments = 0;
            $variableArguments = true;
        }
        $this->functions[$name] = [$name, $minArguments, $callback, $variableArguments];
    }

    /**
     * Add operator
     *
     * @param string $name
     * @param string $operatorClass
     *
     * @throws ConfigException
     */
    public function addOperator($name, $operatorClass)
    {
        $this->addToken($name, $operatorClass);

        /*
        try {
            $class = new \ReflectionClass($operatorClass);
        } catch (\Exception $e) {
            throw new ConfigException('Cannot get reflection of class "' . $operatorClass . '"', ConfigException::CONFIG_OTHER_ERRORS, $e);
        }
        if (!in_array(InterfaceToken::class, $class->getInterfaceNames(), true)) {
            throw new ConfigException('Operator class does not implement interface ' . InterfaceToken::class, ConfigException::CONFIG_OPERATOR_BAD_INTERFACE);
        }

        /** @var InterfaceToken $operatorClass * /
        $matching = $operatorClass::getMatching();
        if (!isset($matching['pattern'], $matching['matching'])) {
            throw new ConfigException('Operator class "' . $operatorClass . '" does not implement interface ' . InterfaceToken::class, ConfigException::CONFIG_OPERATOR_BAD_INTERFACE);
        }
        $matching['class'] = $operatorClass;
        $this->operators[] = $matching;
        */
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
        foreach ($this->tokens as $tokenMatching) {
            $tokenClass = $tokenMatching['class'];
            $tokenCallback = $tokenMatching['callback'];
            $tokenOptions = isset($this->functions[$tokenStr]) ? $this->functions[$tokenStr] : [];

            switch ($tokenMatching['matching']) {
                case AbstractToken::MATCH_CALLBACK:
                    if ($tokenClass::$tokenCallback($tokenStr, $tokensStream)) {
                        return new $tokenClass($tokenStr, $tokenOptions);
                    }
                    break;
                case AbstractToken::MATCH_REGEX:
                    if (preg_match($tokenMatching['pattern'], $tokenStr)) {
                        return new $tokenClass($tokenStr, $tokenOptions);
                    }
                    break;
                case AbstractToken::MATCH_NUMERIC:
                    if (is_numeric($tokenStr)) {
                        return new $tokenClass($tokenStr, $tokenOptions);
                    }
                    break;
                case AbstractToken::MATCH_STRING:
                default:
                    if ($tokenMatching['pattern'] === $tokenStr) {
                        return new $tokenClass($tokenStr, $tokenOptions);
                    }
            }
        }
        throw new LexerException('Unknown token "' . $tokenStr . '"', LexerException::LEXER_UNKNOWN_TOKEN);
    }

    /**
     * @param  string $tokenStr
     * @param  array  $tokensStream
     *
     * @return InterfaceToken
     *
     * @throws LexerException
     */
    public function createToken0($tokenStr, $tokensStream)
    {
        if ($tokenStr === '(') {
            return new TokenLeftBracket($tokenStr);
        }

        if ($tokenStr === ')') {
            return new TokenRightBracket($tokenStr);
        }

        if ($tokenStr === ',') {
            return new TokenComma($tokenStr);
        }

        if (is_numeric($tokenStr)) {
            return new TokenNumber($tokenStr);
        }

        if (preg_match(TokenString::getRegex(), $tokenStr)) {
            return new TokenString(substr($tokenStr,1, -1));
        }

        foreach ($this->operators as $operatorMatching) {
            $operatorClass = $operatorMatching['class'];
            if ($operatorMatching['matching']) {
                if ($operatorClass::isMatch($tokenStr, $tokensStream)) {
                    return new $operatorClass;
                }
            } elseif ($operatorMatching['pattern'] === $tokenStr) {
                return new $operatorClass;
            }
        }

        if (preg_match(TokenVariable::getRegex(), $tokenStr)) {
            return new TokenVariable(substr($tokenStr,1));
        }

        if (preg_match(TokenFunction::getRegex(), $tokenStr)) {
            if (isset($this->functions[$tokenStr])) {
                return new TokenFunction($this->functions[$tokenStr]);
            } else {
                throw new LexerException('Unknown function "' . $tokenStr . '"', LexerException::LEXER_UNKNOWN_FUNCTION);
            }
        }

        throw new LexerException('Unknown token "' . $tokenStr . '"', LexerException::LEXER_UNKNOWN_TOKEN);
    }
}

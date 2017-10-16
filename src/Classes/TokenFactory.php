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
     * @param  string $operatorClass
     *
     * @throws ConfigException
     */
    public function addOperator($operatorClass)
    {
        try {
            $class = new \ReflectionClass($operatorClass);
        } catch (\Exception $e) {
            throw new ConfigException('Cannot get reflection', ConfigException::CONFIG_OTHER_ERRORS, $e);
        }
        if (!in_array(InterfaceToken::class, $class->getInterfaceNames(), true)) {
            throw new ConfigException('Operator class does not implement interface ' . InterfaceToken::class, ConfigException::CONFIG_OPERATOR_BAD_INTERFACE);
        }

        $this->operators[] = $operatorClass;
        $this->operators = array_unique($this->operators);
    }

    /**
     * @param  string $tokenStr
     * @param  array  $tokensStream
     *
     * @return InterfaceToken
     *
     * @throws LexerException
     */
    public function createToken($tokenStr, $tokensStream)
    {
        if ($tokenStr === '(') {
            return new TokenLeftBracket();
        }

        if ($tokenStr === ')') {
            return new TokenRightBracket();
        }

        if ($tokenStr === ',') {
            return new TokenComma();
        }

        if (is_numeric($tokenStr)) {
            return new TokenNumber($tokenStr);
        }

        if (preg_match(TokenString::getRegex(), $tokenStr)) {
            return new TokenString(substr($tokenStr,1, -1));
        }

        foreach ($this->operators as $operator) {
            if ($operator::isMatch($tokenStr, $tokensStream)) {
                return new $operator;
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

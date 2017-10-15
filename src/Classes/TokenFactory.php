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
use avadim\MathExecutor\Exception\UnknownFunctionException;
use avadim\MathExecutor\Exception\UnknownOperatorException;
use avadim\MathExecutor\Exception\UnknownTokenException;

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
     * @param $name
     * @param $function
     * @param $minArguments
     * @param $variableArguments
     */
    public function addFunction($name, $function, $minArguments = 1, $variableArguments = false)
    {
        $this->functions[$name] = [$minArguments, $function, $variableArguments];
    }

    /**
     * Add operator
     *
     * @param  string $operatorClass
     *
     * @throws UnknownOperatorException
     */
    public function addOperator($operatorClass)
    {
        try {
            $class = new \ReflectionClass($operatorClass);

            if (!in_array(InterfaceToken::class, $class->getInterfaceNames(), true)) {
                throw new UnknownOperatorException;
            }
        } catch (\Exception $e) {
            throw new UnknownOperatorException;
        }

        $this->operators[] = $operatorClass;
        $this->operators = array_unique($this->operators);
    }

    /**
     * @return string
     */
    public function getTokenParserRegex()
    {
        $operatorsRegex = '';
        foreach ($this->operators as $operator) {
            $operatorsRegex .= $operator::getRegex();
        }

        return sprintf(
            '/(%s)|(%s)|([%s])|(%s)|(%s)|([%s%s%s])/i',
            TokenString::getRegex(),
            TokenNumber::getRegex(),
            $operatorsRegex,
            TokenFunction::getRegex(),
            TokenVariable::getRegex(),
            TokenLeftBracket::getRegex(),
            TokenRightBracket::getRegex(),
            TokenComma::getRegex()
        );
    }

    /**
     * @param  string $tokenStr
     * @param  array  $tokensStream
     *
     * @return InterfaceToken
     *
     * @throws UnknownFunctionException
     * @throws UnknownTokenException
     */
    public function createToken($tokenStr, $tokensStream)
    {
        $regex = sprintf('/%s/i', TokenString::getRegex());
        if (preg_match($regex, $tokenStr)) {
            return new TokenString(substr($tokenStr,1, -1));
        }

        if (is_numeric($tokenStr)) {
            return new TokenNumber($tokenStr);
        }

        if ($tokenStr === '(') {
            return new TokenLeftBracket();
        }

        if ($tokenStr === ')') {
            return new TokenRightBracket();
        }

        if ($tokenStr === ',') {
            return new TokenComma();
        }

        foreach ($this->operators as $operator) {
            if ($operator::isMatch($tokenStr, $tokensStream)) {
                return new $operator;
            }
        }

        $regex = sprintf('/%s/i', TokenVariable::getRegex());
        if (preg_match($regex, $tokenStr)) {
            return new TokenVariable(substr($tokenStr,1));
        }

        $regex = sprintf('/%s/i', TokenFunction::getRegex());
        if (preg_match($regex, $tokenStr)) {
            if (isset($this->functions[$tokenStr])) {
                return new TokenFunction($this->functions[$tokenStr]);
            } else {
                throw new UnknownFunctionException();
            }
        }

        throw new UnknownTokenException();
    }
}

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
    protected $operators = array();

    /**
     * Available functions
     *
     * @var array
     */
    protected $functions = array();

    /**
     * Add function
     *
     * @param $name
     * @param $function
     * @param $places
     */
    public function addFunction($name, $function, $places = 1)
    {
        $this->functions[$name] = array($places, $function);
    }

    /**
     * Add operator
     *
     * @param  string                                  $operatorClass
     *
     * @throws UnknownOperatorException
     */
    public function addOperator($operatorClass)
    {
        $class = new \ReflectionClass($operatorClass);

        if (!in_array('avadim\MathExecutor\Classes\Token\InterfaceToken', $class->getInterfaceNames())) {
            throw new UnknownOperatorException;
        }

        $this->operators[] = $operatorClass;
        $this->operators = array_unique($this->operators);
    }

    /**
     * Add variable
     *
     * @param string $name
     * @param mixed  $value
     */
    public function addVariable($name, $value)
    {

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
            '/(%s)|([%s])|(%s)|(%s)|([%s%s%s])/i',
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
     * @param  string                $token
     *
     * @return InterfaceToken
     *
     * @throws UnknownFunctionException
     * @throws UnknownTokenException
     */
    public function createToken($token)
    {
        if (is_numeric($token)) {
            return new TokenNumber($token);
        }

        if ($token === '(') {
            return new TokenLeftBracket();
        }

        if ($token === ')') {
            return new TokenRightBracket();
        }

        if ($token === ',') {
            return new TokenComma();
        }

        foreach ($this->operators as $operator) {
            $regex = sprintf('/%s/i', $operator::getRegex());
            if (preg_match($regex, $token)) {
                return new $operator;
            }
        }

        $regex = sprintf('/%s/i', TokenVariable::getRegex());
        if (preg_match($regex, $token)) {
            return new TokenVariable(substr($token,1));
        }

        $regex = sprintf('/%s/i', TokenFunction::getRegex());
        if (preg_match($regex, $token)) {
            if (isset($this->functions[$token])) {
                return new TokenFunction($this->functions[$token]);
            } else {
                throw new UnknownFunctionException();
            }
        }

        throw new UnknownTokenException();
    }
}

<?php

/**
 * This file is part of the MathExecutor package
 *
 * (c) Alexander Kiryukhin
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace MathExecutor;

use MathExecutor\Classes\Calculator;
use MathExecutor\Classes\Lexer;
use MathExecutor\Classes\TokenFactory;
use MathExecutor\Exception\IncorrectExpressionException;
use MathExecutor\Exception\UnknownOperatorException;
use MathExecutor\Exception\UnknownVariableException;

/**
 * Class MathExecutor
 * @package MathExecutor
 */
class MathExecutor
{
    /**
     * Available variables
     *
     * @var array
     */
    private $variables = array();

    /**
     * @var TokenFactory
     */
    private $tokenFactory;

    /**
     * @var array
     */
    private $cache = array();

    /**
     * Base math operators
     */
    public function __construct()
    {
        $this->addDefaults();
    }

    public function __clone()
    {
        $this->addDefaults();
    }

    /**
     * @return TokenFactory
     */
    public function getTokenFactory()
    {
        return new TokenFactory();
    }

    /**
     * @return Lexer
     */
    public function getLexer()
    {
        if (!$this->tokenFactory) {
            $this->tokenFactory = $this->getTokenFactory();
        }
        return new Lexer($this->tokenFactory);
    }

    /**
     * @return Calculator
     */
    public function getCalculator()
    {
        return new Calculator();
    }

    /**
     * Set default operands and functions
     *
     * @throws UnknownOperatorException
     */
    protected function addDefaults()
    {
        if (!$this->tokenFactory) {
            $this->tokenFactory = $this->getTokenFactory();
        }

        $this->tokenFactory->addOperator('MathExecutor\Classes\Token\TokenPlus');
        $this->tokenFactory->addOperator('MathExecutor\Classes\Token\TokenMinus');
        $this->tokenFactory->addOperator('MathExecutor\Classes\Token\TokenMultiply');
        $this->tokenFactory->addOperator('MathExecutor\Classes\Token\TokenDivision');
        $this->tokenFactory->addOperator('MathExecutor\Classes\Token\TokenDegree');

        $this->tokenFactory->addFunction('sin', 'sin');
        $this->tokenFactory->addFunction('cos', 'cos');
        $this->tokenFactory->addFunction('tn', 'tan');
        $this->tokenFactory->addFunction('asin', 'asin');
        $this->tokenFactory->addFunction('acos', 'acos');
        $this->tokenFactory->addFunction('atn', 'atan');
        $this->tokenFactory->addFunction('min', 'min', 2);
        $this->tokenFactory->addFunction('max', 'max', 2);
        $this->tokenFactory->addFunction('avg', function($arg1, $arg2) { return ($arg1 + $arg2) / 2; }, 2);

        $this->setVars(array(
            'pi' => 3.14159265359,
            'e'  => 2.71828182846
        ));
    }

    /**
     * Add variable to executor
     *
     * @param  string        $variable
     * @param  integer|float $value
     *
     * @return MathExecutor
     */
    public function setVar($variable, $value)
    {
        $this->variables[$variable] = $value;

        return $this;
    }

    /**
     * Add variables to executor
     *
     * @param  array        $variables
     * @param  bool         $clear     Clear previous variables
     *
     * @return MathExecutor
     */
    public function setVars(array $variables, $clear = true)
    {
        if ($clear) {
            $this->removeVars();
        }

        foreach ($variables as $name => $value) {
            $this->setVar($name, $value);
        }

        return $this;
    }

    /**
     * Remove variable from executor
     *
     * @param  string       $variable
     *
     * @return MathExecutor
     */
    public function removeVar($variable)
    {
        unset ($this->variables[$variable]);

        return $this;
    }

    /**
     * Remove all variables
     */
    public function removeVars()
    {
        $this->variables = array();

        return $this;
    }

    /**
     * Add operator to executor
     *
     * @param  string       $operatorClass Class of operator token
     *
     * @return MathExecutor
     *
     * @throws UnknownOperatorException
     */
    public function addOperator($operatorClass)
    {
        $this->tokenFactory->addOperator($operatorClass);

        return $this;
    }

    /**
     * Add function to executor
     *
     * @param  string       $name     Name of function
     * @param  callable     $function Function
     * @param  int          $places   Count of arguments
     *
     * @return MathExecutor
     */
    public function addFunction($name, callable $function = null, $places = 1)
    {
        $this->tokenFactory->addFunction($name, $function, $places);

        return $this;
    }


    /**
     * Execute expression
     *
     * @param $expression
     *
     * @return number
     *
     * @throws IncorrectExpressionException
     * @throws UnknownVariableException
     */
    public function execute($expression)
    {
        if (!array_key_exists($expression, $this->cache)) {
            $lexer = $this->getLexer();
            $tokensStream = $lexer->stringToTokensStream($expression);
            $tokens = $lexer->buildReversePolishNotation($tokensStream);
            $this->cache[$expression] = $tokens;
        } else {
            $tokens = $this->cache[$expression];
        }
        $calculator = $this->getCalculator();

        return $calculator->calculate($tokens, $this->variables);
    }
}

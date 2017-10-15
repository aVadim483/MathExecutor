<?php

/**
 * This file is part of the MathExecutor package
 *
 * (c) Alexander Kiryukhin
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace avadim\MathExecutor;

use avadim\MathExecutor\Classes\Calculator;
use avadim\MathExecutor\Classes\Lexer;
use avadim\MathExecutor\Classes\TokenFactory;
use avadim\MathExecutor\Exception\IncorrectBracketsException;
use avadim\MathExecutor\Exception\IncorrectExpressionException;
use avadim\MathExecutor\Exception\UnknownOperatorException;
use avadim\MathExecutor\Exception\UnknownVariableException;

/**
 * Class MathExecutor
 * @package MathExecutor
 */
class MathExecutor
{
    const RESULT_VARIABLE = '_';

    /**
     * Available variables
     *
     * @var array
     */
    private $variables = [];

    /**
     * @var TokenFactory
     */
    private $tokenFactory;

    /**
     * @var array
     */
    private $cache = [];

    /**
     * Base math operators
     *
     * @throws UnknownOperatorException
     */
    public function __construct()
    {
        $this->addDefaults();
    }

    /**
     * @throws UnknownOperatorException
     */
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

        $this->tokenFactory->addOperator('avadim\MathExecutor\Classes\Token\TokenPlus');
        $this->tokenFactory->addOperator('avadim\MathExecutor\Classes\Token\TokenUnaryMinus');
        $this->tokenFactory->addOperator('avadim\MathExecutor\Classes\Token\TokenMinus');
        $this->tokenFactory->addOperator('avadim\MathExecutor\Classes\Token\TokenMultiply');
        $this->tokenFactory->addOperator('avadim\MathExecutor\Classes\Token\TokenDivision');
        $this->tokenFactory->addOperator('avadim\MathExecutor\Classes\Token\TokenPower');

        $this->tokenFactory->addFunction('sin', 'sin');
        $this->tokenFactory->addFunction('cos', 'cos');
        $this->tokenFactory->addFunction('tn', 'tan');
        $this->tokenFactory->addFunction('asin', 'asin');
        $this->tokenFactory->addFunction('acos', 'acos');
        $this->tokenFactory->addFunction('atn', 'atan');
        $this->tokenFactory->addFunction('min', 'min', 2, true);
        $this->tokenFactory->addFunction('max', 'max', 2, true);
        $this->tokenFactory->addFunction('avg', function() { return array_sum(func_get_args()) / func_num_args(); }, 2, true);

        $this->setVars([
            'pi' => 3.14159265359,
            'e'  => 2.71828182846
        ]);
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
        $this->variables = [];

        return $this;
    }

    /**
     * @param $variable
     *
     * @return mixed
     */
    public function getVar($variable)
    {
        if (isset($this->variables[$variable])) {
            return $this->variables[$variable];
        }
        return null;
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
     * @param string $expression
     * @param string $variable
     *
     * @return $this
     *
     * @throws IncorrectExpressionException
     * @throws IncorrectBracketsException
     * @throws UnknownVariableException
     */
    public function calc($expression, $variable = null)
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

        $result = $calculator->calculate($tokens, $this->variables);

        return $this->setVar($variable ?: self::RESULT_VARIABLE, $result);
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->getVar(self::RESULT_VARIABLE);
    }

    /**
     * Execute expression
     *
     * @param $expression
     *
     * @return number
     *
     * @throws IncorrectExpressionException
     * @throws IncorrectBracketsException
     * @throws UnknownVariableException
     */
    public function execute($expression)
    {
        $this->calc($expression);

        return $this->getResult();
    }
}

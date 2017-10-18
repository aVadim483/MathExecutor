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

use avadim\MathExecutor\Exception\CalcException;
use avadim\MathExecutor\Exception\ConfigException;
use avadim\MathExecutor\Exception\LexerException;

/**
 * Class MathExecutor
 * @package MathExecutor
 */
class MathExecutor
{
    const RESULT_VARIABLE = '_';
    const VAR_PREFIX      = '$';

    private $cacheEnable = true;

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
     * @throws ConfigException
     */
    public function __construct()
    {
        $this->addDefaults();
    }

    /**
     * @throws ConfigException
     */
    public function __clone()
    {
        $this->addDefaults();
    }

    public function cacheEnable($flag)
    {
        $this->cacheEnable = (bool)$flag;
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
     * @throws ConfigException
     */
    protected function addDefaults()
    {
        if (!$this->tokenFactory) {
            $this->tokenFactory = $this->getTokenFactory();
        }

        $this->tokenFactory->addToken('left_bracket', 'avadim\MathExecutor\Classes\Token\TokenLeftBracket');
        $this->tokenFactory->addToken('right_bracket', 'avadim\MathExecutor\Classes\Token\TokenRightBracket');
        $this->tokenFactory->addToken('comma', 'avadim\MathExecutor\Classes\Token\TokenComma');
        $this->tokenFactory->addToken('number', 'avadim\MathExecutor\Classes\Token\TokenNumber');
        $this->tokenFactory->addToken('string', 'avadim\MathExecutor\Classes\Token\TokenString');
        $this->tokenFactory->addToken('variable', 'avadim\MathExecutor\Classes\Token\TokenVariable', self::VAR_PREFIX);
        $this->tokenFactory->addToken('function', 'avadim\MathExecutor\Classes\Token\TokenFunction');

        $this->tokenFactory->addOperator('plus', 'avadim\MathExecutor\Classes\Token\TokenPlus');
        $this->tokenFactory->addOperator('u_minus', 'avadim\MathExecutor\Classes\Token\TokenUnaryMinus');
        $this->tokenFactory->addOperator('minus', 'avadim\MathExecutor\Classes\Token\TokenMinus');
        $this->tokenFactory->addOperator('multiply', 'avadim\MathExecutor\Classes\Token\TokenMultiply');
        $this->tokenFactory->addOperator('division', 'avadim\MathExecutor\Classes\Token\TokenDivision');
        $this->tokenFactory->addOperator('power', 'avadim\MathExecutor\Classes\Token\TokenPower');

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
        if ($variable[0] !== self::VAR_PREFIX) {
            $variable = self::VAR_PREFIX . $variable;
        }
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
        if ($variable[0] !== self::VAR_PREFIX) {
            $variable = self::VAR_PREFIX . $variable;
        }
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
     * @throws ConfigException
     */
    public function addOperator($name, $operatorClass)
    {
        $this->tokenFactory->addOperator($name, $operatorClass);

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
     * @param string $resultVariable
     *
     * @return $this
     *
     * @throws LexerException
     * @throws CalcException
     */
    public function calc($expression, $resultVariable = null)
    {
        if (!$this->cacheEnable || !isset($this->cache[$expression])) {
            $lexer = $this->getLexer();
            $tokensStream = $lexer->stringToTokensStream($expression);
            $tokens = $lexer->buildReversePolishNotation($tokensStream);
            if ($this->cacheEnable) {
                $this->cache[$expression] = $tokens;
            }
        } else {
            $tokens = $this->cache[$expression];
        }
        $calculator = $this->getCalculator();

        $result = $calculator->calculate($tokens, $this->variables);

        return $this->setVar($resultVariable ?: self::RESULT_VARIABLE, $result);
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
     * @throws CalcException
     */
    public function execute($expression)
    {
        $this->calc($expression);

        return $this->getResult();
    }
}

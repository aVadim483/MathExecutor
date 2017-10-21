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

use avadim\MathExecutor\Exception\CalcException;
use avadim\MathExecutor\Exception\ConfigException;
use avadim\MathExecutor\Exception\LexerException;

/**
 * Class MathExecutor
 *
 * @package MathExecutor
 */
class MathExecutor
{
    const RESULT_VARIABLE = '_';
    const VAR_PREFIX      = '$';

    /**
     * @var array
     */
    private $config = [];

    /**
     * @var bool
     */
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
     * @var Lexer
     */
    private $lexer;

    /**
     * @var Calculator
     */
    private $calculator;

    /**
     * @var array
     */
    private $cache = [];

    /**
     * Base math operators
     *
     * @param array $config
     *
     * @throws ConfigException
     */
    public function __construct($config = null)
    {
        $this->init($config);
    }

    /**
     * Clone object and renew all objects
     */
    public function __clone()
    {
        $this->init($this->getConfig());
    }

    /**
     * @param array $config
     */
    protected function init($config = null)
    {
        $this->tokenFactory = $this->createTokenFactory();
        $this->lexer = $this->createLexer($this->tokenFactory);
        $this->calculator = $this->createCalculator($this->tokenFactory);

        if (null === $config) {
            $config = $this->getDefaults();
        }
        $this->setConfig($config);
    }

    /**
     * @param bool $flag
     */
    public function cacheEnable($flag)
    {
        $this->cacheEnable = (bool)$flag;
    }

    /**
     * @return TokenFactory
     */
    public function createTokenFactory()
    {
        return new TokenFactory();
    }

    /**
     * @param TokenFactory $tokenFactory
     *
     * @return Lexer
     */
    public function createLexer($tokenFactory)
    {
        return new Lexer($tokenFactory);
    }

    /**
     * @param TokenFactory $tokenFactory
     *
     * @return Calculator
     */
    public function createCalculator($tokenFactory)
    {
        return new Calculator($tokenFactory);
    }

    /**
     * @return TokenFactory
     */
    public function getTokenFactory()
    {
        return $this->tokenFactory;
    }

    /**
     * @return Lexer
     */
    public function getLexer()
    {
        return $this->lexer;
    }

    /**
     * @return Calculator
     */
    public function getCalculator()
    {
        return $this->calculator;
    }

    /**
     * @return array
     */
    protected function getDefaults()
    {
        return [
            'options' => [
                'var_prefix' => self::VAR_PREFIX,
                'result_variable' => self::RESULT_VARIABLE,
            ],
            'tokens' => [
                'left_bracket'  => '\avadim\MathExecutor\Token\TokenLeftBracket',
                'right_bracket' => '\avadim\MathExecutor\Token\TokenRightBracket',
                'comma'         => '\avadim\MathExecutor\Token\TokenComma',
                'number'        => '\avadim\MathExecutor\Token\TokenScalarNumber',
                'string'        => '\avadim\MathExecutor\Token\TokenScalarString',
                'variable'      => ['\avadim\MathExecutor\Token\TokenVariable', self::VAR_PREFIX],
                'identifier'    => '\avadim\MathExecutor\Token\TokenIdentifier',
                'function'      => '\avadim\MathExecutor\Token\TokenFunction',
            ],
            'operators' => [
                'plus'          => '\avadim\MathExecutor\Token\Operator\TokenOperatorPlus',
                'minus'         => '\avadim\MathExecutor\Token\Operator\TokenOperatorMinus',
                'multiply'      => '\avadim\MathExecutor\Token\Operator\TokenOperatorMultiply',
                'division'      => '\avadim\MathExecutor\Token\Operator\TokenOperatorDivide',
                'power'         => '\avadim\MathExecutor\Token\Operator\TokenOperatorPower',
            ],
            'functions' => [
                'sin'   => 'sin',
                'cos'   => 'cos',
                'tn'    => 'tan',
                'asin'  => 'asin',
                'acos'  => 'acos',
                'atn'   => 'atan',
                'min'   => ['min', 2, true],
                'max'   => ['max', 2, true],
                'avg'   => [function() { return array_sum(func_get_args()) / func_num_args(); }, 2, true],
            ],
            'variables' => [
                'pi' => 3.14159265359,
                'e'  => 2.71828182846
            ],
        ];
    }

    /**
     * Set default operands and functions
     *
     * @param  array        $config
     *
     * @throws ConfigException
     */
    protected function setConfig($config)
    {
        $this->config = $config;

        if (!$this->tokenFactory) {
            $this->tokenFactory = $this->getTokenFactory();
        }

        // set default tokens
        if (isset($config['tokens'])) {
            foreach($config['tokens'] as $name => $options) {
                if (is_array($options)) {
                    list($class, $pattern) = $options;
                } else {
                    $class = $options;
                    $pattern = null;
                }
                $this->tokenFactory->addToken($name, $class, $pattern);
            }
        }

        // set default operators
        if (isset($config['operators'])) {
            foreach($config['operators'] as $name => $class) {
                $this->tokenFactory->addOperator($name, $class);
            }
        }

        // set default functions
        if (isset($config['functions'])) {
            foreach($config['functions'] as $name => $options) {
                if (is_array($options)) {
                    list($callback, $minArguments, $variableArguments) = $options;
                } else {
                    $callback = $options;
                    $minArguments = null;
                    $variableArguments = null;
                }
                $this->tokenFactory->addFunction($name, $callback, $minArguments, $variableArguments);
            }
        }

        // set default variables
        if (isset($config['variables'])) {
            $this->setVars($config['variables']);
        }
        if (isset($config['options']['result_variable'])) {
            $this->setVar($config['options']['result_variable'], null);
        }
    }

    /**
     * @return array
     */
    protected function getConfig()
    {
        return $this->config;
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    protected function getConfigOption($name)
    {
        if (isset($this->config['options'][$name])) {
            return $this->config['options'][$name];
        }
        return null;
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
        if ($sVarPrefix = $this->getConfigOption('var_prefix')) {
            if ($variable[0] !== $sVarPrefix) {
                $variable = $sVarPrefix . $variable;
            }
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
     * @param string $variable
     *
     * @return mixed
     */
    public function getVar($variable)
    {
        if ($sVarPrefix = $this->getConfigOption('var_prefix')) {
            if ($variable[0] !== $sVarPrefix) {
                $variable = $sVarPrefix . $variable;
            }
        }
        if (isset($this->variables[$variable])) {
            return $this->variables[$variable];
        }
        return null;
    }

    /**
     * Add operator to executor
     *
     * @param  string   $name
     * @param  string   $operatorClass Class of operator token
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
            $tokensStack = $lexer->buildReversePolishNotation($tokensStream);
            if ($this->cacheEnable) {
                $this->cache[$expression] = $tokensStack;
            }
        } else {
            $tokensStack = $this->cache[$expression];
        }
        $calculator = $this->getCalculator();
        $result = $calculator->calculate($tokensStack, $this->variables);

        if (!$resultVariable) {
            $resultVariable = $this->getConfigOption('result_variable');
        }
        if ($resultVariable) {
            $this->setVar($resultVariable ?: self::RESULT_VARIABLE, $result);
        }
        return $this;
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        $resultVariable = $this->getConfigOption('result_variable');
        if ($resultVariable) {
            return $this->getVar(self::RESULT_VARIABLE);
        }
        return null;
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

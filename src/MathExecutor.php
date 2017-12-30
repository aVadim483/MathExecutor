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

use avadim\MathExecutor\Exception\CalcException;
use avadim\MathExecutor\Exception\ConfigException;
use avadim\MathExecutor\Exception\LexerException;
use avadim\MathExecutor\Generic\AbstractTokenScalar;

/**
 * Class MathExecutor
 *
 * @package avadim\MathExecutor
 */
class MathExecutor
{
    const RESULT_VARIABLE           = '_';
    const VAR_PREFIX                = '$';

    const IDENTIFIER_AUTO           = 1;
    const IDENTIFIER_AS_STRING      = 1;
    const IDENTIFIER_AS_VARIABLE    = 2;
    const IDENTIFIER_AS_CALLABLE    = 3;

    /**
     * Current config array
     *
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
     * Available callable identifiers
     *
     * @var array
     */
    private $identifiers = [];

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
                'var_prefix'        => self::VAR_PREFIX,
                'result_variable'   => self::RESULT_VARIABLE,
                'identifier_as'     => self::IDENTIFIER_AUTO,
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
     * Apply operands and functions
     *
     * @param  array $config
     *
     * @throws ConfigException
     */
    protected function applyConfig($config)
    {
        if (!$this->tokenFactory) {
            $this->tokenFactory = $this->getTokenFactory();
        }

        // set default tokens
        if (isset($config['tokens'])) {
            foreach((array)$config['tokens'] as $name => $options) {
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
            foreach((array)$config['operators'] as $name => $class) {
                $this->tokenFactory->addOperator($name, $class);
            }
        }

        // set default functions
        if (isset($config['functions'])) {
            foreach((array)$config['functions'] as $name => $options) {
                if (is_array($options)) {
                    list($callback, $minArguments, $variableArguments) = $options;
                } else {
                    $callback = $options;
                    $minArguments = null;
                    $variableArguments = null;
                }
                $function = static::createFunction($name, $callback, $minArguments, $variableArguments);
                $this->tokenFactory->addFunction($name, $function);
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
     * @param $config
     *
     * @return $this
     */
    protected function setConfig($config)
    {
        $this->applyConfig($config);
        $this->config = $config;

        return $this;
    }

    /**
     * @param $config
     *
     * @return $this
     */
    protected function addConfig($config)
    {
        $this->applyConfig($config);
        $this->config = array_merge($this->config, $config);

        return $this;
    }

    /**
     * @param string $configFile
     *
     * @return $this
     *
     * @throws ConfigException
     */
    public function loadConfig($configFile)
    {
        if (is_file($configFile)) {
            $config = include($configFile);
            if (is_array($config)) {
                if (isset($config['include'])) {
                    $dir = dirname($configFile) . '/';
                    $includes = (array)$config['include'];
                    foreach($includes as $filePattern) {
                        if ($filePattern && $filePattern[0] !== '.' && false === strpos($filePattern, '/.')) {
                            $files = glob($dir . $filePattern);
                            foreach($files as $includeFile) {
                                if ($includeFile !== $configFile) {
                                    include_once $includeFile;
                                }
                            }
                        }
                    }
                }
                $this->addConfig($config);
            } else {
                throw new ConfigException('Config is not array');
            }
        } else {
            throw new ConfigException('Config file does not exist');
        }

        return $this;
    }

    /**
     * @return MathExecutor
     */
    public function loadExtra()
    {
        return $this->loadConfig(__DIR__ . '/Extra/config.php');
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
     * Add identifier to executor
     *
     * @param string $identifier
     * @param callable|AbstractTokenScalar $value
     *
     * @return MathExecutor
     */
    public function setIdentifier($identifier, $value)
    {
        $this->identifiers[$identifier] = $value;

        return $this;
    }

    /**
     * Add identifiers to executor
     *
     * @param array $identifiers
     * @param bool  $clear Clear previous identifiers
     *
     * @return MathExecutor
     */
    public function setIdentifiers(array $identifiers, $clear = true)
    {
        if ($clear) {
            $this->removeIdentifiers();
        }

        foreach ($identifiers as $name => $value) {
            $this->setIdentifier($name, $value);
        }

        return $this;
    }

    /**
     * Remove identifier from executor
     *
     * @param string $identifier
     *
     * @return MathExecutor
     */
    public function removeIdentifier($identifier)
    {
        unset ($this->identifiers[$identifier]);

        return $this;
    }

    /**
     * Remove all identifiers
     */
    public function removeIdentifiers()
    {
        $this->identifiers = [];

        return $this;
    }

    /**
     * @param string $identifier
     *
     * @return mixed
     */
    public function getIdentifier($identifier)
    {
        if (isset($this->variables[$identifier])) {
            return $this->variables[$identifier];
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
     * @param string       $name     Name of function
     * @param callable     $callback Function
     * @param int          $minArguments   Count of arguments
     * @param bool         $variableArguments
     *
     * @return MathExecutor
     */
    public function addFunction($name, callable $callback = null, $minArguments = 1, $variableArguments = false)
    {
        $function = static::createFunction($name, $callback, $minArguments, $variableArguments);
        $this->tokenFactory->addFunction($name, $function);

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
        $result = $calculator->calculate($tokensStack, $this->variables, $this->identifiers);

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
    public function result()
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
     * @throws LexerException
     */
    public function execute($expression)
    {
        $this->calc($expression);

        return $this->result();
    }

    /**
     * Add function
     *
     * @param string   $name
     * @param callable $callback
     * @param int      $minArguments
     * @param bool     $variableArguments
     *
     * @return mixed;
     */
    public static function createFunction($name, $callback, $minArguments = 1, $variableArguments = false)
    {
        if (null === $minArguments) {
            $minArguments = 1;
        } elseif ($minArguments === -1) {
            $minArguments = 0;
            $variableArguments = true;
        }
        return [$name, $minArguments, $callback, $variableArguments];
    }

}

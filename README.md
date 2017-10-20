[![License](https://poser.pugx.org/avadim/math-executor/license)](https://packagist.org/packages/avadim/math-executor)
# MathExecutor

Math expressions calculator with custom operators, functions and variables

## Install via Composer

composer require avadim/math-executor

All instructions to install here: https://packagist.org/packages/avadim/math-executor

## Sample usage:

```php
require 'vendor/autoload.php';

$calculator = new \avadim\MathExecutor\MathExecutor();

print $calculator->execute('1 + 2 * (2 - (4+10))^2 + sin(10)');

// cascade execution - variable $_ has result of previous calculation
print $calculator
        ->calc('4+10')
        ->calc('1 + 2 * (2 - $_)^2')
        ->calc('$_ + sin(10)')
        ->getResult();
```

## Functions:

Default functions:
* sin
* cos
* tn
* asin
* acos
* atn
* min
* max
* avg

Add custom function to executor:
```php
$calculator->addFunction('hypotenuse', function($a, $b) {
    return sqrt($a ** 2 + $b ** 2);
}, 2);

print $calculator->execute('hypotenuse(3,4)');
```

## Operators:

Default operators: `+ - * / ^`

Add custom operator to executor:

MyNamespace/ModulusToken.php:

```php
<?php
use avadim\MathExecutor\Classes\Generic\AbstractTokenOperator;
use \avadim\MathExecutor\Classes\Generic\InterfaceToken;

use \avadim\MathExecutor\Classes\Token\TokenScalarNumber;

class TokenOperatorModulus extends AbstractTokenOperator
{
    protected static $pattern = 'mod';

    /**
     * Priority of this operator (1 equals "+" or "-", 2 equals "*" or "/", 3 equals "^")
     * @return int
     */
    public function getPriority()
    {
        return 3;
    }

    /**
     * Association of this operator (self::LEFT_ASSOC or self::RIGHT_ASSOC)
     * @return string
     */
    public function getAssociation()
    {
        return self::LEFT_ASSOC;
    }

    /**
     * Execution of this operator
     * @param InterfaceToken[] $stack Stack of tokens
     *
     * @return TokenScalarNumber
     */
    public function execute(&$stack)
    {
        $op2 = array_pop($stack);
        $op1 = array_pop($stack);
        $result = $op1->getValue() % $op2->getValue();

        return new TokenScalarNumber($result);
    }
}
```

And adding to executor:

```php
$calculator = new avadim\MathExecutor\MathExecutor();
$calculator->addOperator('mod', '\TokenOperatorModulus');
echo $calculator->execute('286 mod 100');
```

## Variables:

Default variables:

```
$pi = 3.14159265359
$e = 2.71828182846
```

You can add own variable to executor:

```php
$calculator->setVars([
    'var1' => 0.15,
    'var2' => 0.22
]);

$calculator->execute('$var1 + $var2');

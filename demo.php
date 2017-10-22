<?php

//include __DIR__ . '/protected/vendor/autoload.php';
include __DIR__ . '/src/autoload.php';

$calculator = new avadim\MathExecutor\MathExecutor();

// calc expression
echo $calculator->execute('1 + 2 * (2 - (4+10))^2 + sin(10)+0'), '<br>';

// calc expression with variable
$calculator->setVar('x', 100);
echo $calculator->execute('min(1,-sin($x),cos($x)-0.5)'), '<br>';

// cascade calculation
print $calculator
        ->calc('4+10')
        ->calc('1 + 2 * (2 - $_)^2')
        ->calc('$_ + sin(10)')
        ->result();

// EOF
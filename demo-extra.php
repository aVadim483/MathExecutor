<?php

//include __DIR__ . '/protected/vendor/autoload.php';
include __DIR__ . '/src/autoload.php';

$calculator = new avadim\MathExecutor\MathExecutor();
$calculator->loadExtra();

print $calculator
    ->calc('100+20+3', 'y')  // calc expression and save result in $y
    ->calc('if($y > 111, 23, 34)')      // if $x > 111 then result is 23 else 34
    ->result();


// EOF
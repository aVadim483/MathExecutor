<?php
/**
 * This file is part of the MathExecutor package
 * https://github.com/aVadim483/MathExecutor
 *
 */

spl_autoload_register(function ($class) {
    if (0 === strpos($class, 'avadim\\MathExecutor\\')) {
        include __DIR__ . '/' . str_replace('avadim\\MathExecutor\\', '/', $class) . '.php';
    }
});

// EOF
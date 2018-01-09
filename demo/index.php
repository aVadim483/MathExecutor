<?php
/**
 * This file is part of the MathExecutor package
 * https://github.com/aVadim483/MathExecutor
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

function source($file)
{
    $source = file_get_contents($file);
    echo '<hr>';
    echo '<pre>', htmlspecialchars($source), '<pre>';
    echo '<hr>';
    include $file;
}

if (isset($_GET['demo']) && preg_match('/^[\w\-]+$/', $_GET['demo'])) {
    $demo = $_GET['demo'];
} else {
    $demo = 'simple';
}

$file = __DIR__ . '/demo.' . $demo . '.php';
if (!is_file($file)) {
    echo 'Demo file "demo.' . $demo . '.php" not found';
} else {
    source(__DIR__ . '/demo.' . $demo . '.php');
}

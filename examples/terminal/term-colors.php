<?php 

require_once __DIR__ . '/../../vendor/autoload.php'; // Autoload files using Composer autoload

use Lyx\System\Terminal;

$str = 'Test: <f:red,b:blue>This<f> <b:red>and<b> <f:green,a:underline>more<> <f:light-green>colors<f> <a:reverse:underline>test<a>';

// Print the text with terminal color formating
echo "   with color: ";
Terminal::println($str);

// Print the raw text without color formating tags
echo "without color: ";
Terminal::printlnRaw($str);

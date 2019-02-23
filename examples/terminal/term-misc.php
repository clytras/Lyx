<?php 

require_once __DIR__ . '/../../vendor/autoload.php'; // Autoload files using Composer autoload

use Lyx\System\Terminal;

Terminal::lineTitle("Testing Line <f:cyan>Title<>");

lyx_println();

Terminal::underlineTitle("Testing Underline <f:yellow,b:blue>Title<>");

lyx_println();

Terminal::uolineTitle("Testing <a:bold>Over/Under<> line Title");

lyx_println();

// Beep
Terminal::beep();

// Wait for a keypress
Terminal::getch();

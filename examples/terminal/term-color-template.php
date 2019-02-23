<?php 

require_once __DIR__ . '/../../vendor/autoload.php'; // Autoload files using Composer autoload

use Lyx\System\Terminal;

$html = <<<HTML
<html lang="en">
 <head>
  <title>
    A Simple HTML Document
  </title>
 </head>
 <body bgcolor="#E6E6FA">
  <p align="center">This is a very simple HTML document</p>
  <p>It only has two paragraphs</p>
 </body>
</html>
HTML;

echo "\n\n\n\n";


Terminal::printTmpl($html, [
    // Use green to color all the tags
    '/<(.|\\n)*?>/' => 'f:green',

    // Use yellow to collor tags attribute values
    '/(?:\<\!\-\-(?:(?!\-\-\>)\r\n?|\n|.)*?-\-\>)|(?:<(\S+)\s+(?=.*>)|(?<=[=\s])\G)(?:((?:(?!\s|=).)*)\s*?=\K\s*?[\"\']?((?:(?<=\")(?:(?<=\\\\)\"|[^\"])*|(?<=\')(?:(?<=\\\\)\'|[^\'])*)|(?:(?!\"|\')(?:(?!\/>|>|\s).)+))[\"\']?\s*)/m' => 'f:yellow',
]);

lyx_println();

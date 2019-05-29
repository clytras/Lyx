<?php 

require_once __DIR__ . '/../../vendor/autoload.php'; // Autoload files using Composer autoload

use Lyx\System\FS;


function rsearch($folder, $pattern) {
  $dir = new RecursiveDirectoryIterator($folder);
  $ite = new RecursiveIteratorIterator($dir);
  $files = new RegexIterator($ite, $pattern, RegexIterator::GET_MATCH);
  $dirLength = strlen($folder);
  $fileList = [];
  foreach($files as $file) {
    $filepath = $file[0];
    $fileList[] = substr($filepath, -(strlen($filepath) - $dirLength));
  }
  return $fileList;
}

//$files = rsearch('\photos\', "#.*\\.(jpg|png)$#");
$entries = [];

foreach($files as $file) {
  $filename = basename($file);
  $productsParts = explode('-', $filename);
  $sku = $productsParts[0];
  $color = $productsParts[1];

  if(!isset($entries[$sku])) {
    $entries[$sku] = [];
  }

  if(!isset($entries[$sku][$color])) {
    $entries[$sku][$color] = [];
  }

  $entries[$sku][$color][] = $file;
}

print_r($entries);

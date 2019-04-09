<?php

namespace Lyx\System;

class FS
{
  public static function slash($path, $slash_char = DIRECTORY_SEPARATOR)
  {
    if(substr($path, -1) != $slash_char)
      $path .= $slash_char;
    return $path;
  }
  
  public static function removeSlash($path, $ds = DIRECTORY_SEPARATOR)
  {
    if(substr($path, -1) == $ds)
      return substr($path, 0, -1);
    return $path;
  }
  
  public static function fileExists($path)
  {
    return file_exists($path) && is_file($path);
  }

  public static function dirExists($path)
  {
    return file_exists($path) && is_dir($path);
  }

  public static function linkExists($path)
  {
    return file_exists($path) && is_link($path);
  }
  
  public static function directoryTree($dir, $user_options = [])
  {
    $result_files = [];
    $result_dirs = [];
    $options = array_merge([
      'only_dirs' => false,
      'dot_dirs' => true,
      'hidden_dirs' => true,
      'hidden_files' => true,
      'recursive' => true,
      'flat_model' => false,
      'fs_include_path' => true,
      'flat_model_root' => '',
      'sort' => 'asc'
    ], $user_options);
    
    $dir = self::slash($dir);
    $current_dir = $options['flat_model'] ? self::slash($options['flat_model_root']) : $dir; 
    $hdir = opendir($current_dir);

    while(($name = readdir($hdir)) !== false) {
      $path = $current_dir.$name;
      $current_entry = [
        'text' => $name,
        '_fs' => []
      ];
      
      if($options['fs_include_path'])
        $current_entry['_fs']['path'] = $path;
      
      if($name == '.' || $name == '..') {
        if($options['dot_dirs']) {
          $current_entry['_fs']['type'] = 'd';
        } else continue;
      } elseif(is_dir($path)) {
        $current_entry['_fs']['type'] = 'd';

        if($options['recursive'] && substr($dir, 0, strlen($path)) == $path) {
          $current_entry['nodes'] = self::directoryTree(
            $options['flat_model'] ? $dir : $path, 
            array_merge($options, ['flat_model_root' => $path]
          ));
        } else {
          if($name[0] == '.' && !$options['hidden_dirs'])
            continue;

          try {
            $counts = self::countFiles($path, [
              'recursive' => false
            ]);
            
            if((!$options['only_dirs'] && $counts['files'] > 0) || $counts['directories'] > 0) {
              $current_entry['nodes'] = [[
                'text' => '?' // means that this dir has subdirs that will be loaded when user expands it
              ]];
            }
          } catch(Exception $e) {}
        }
        
        $result_dirs[] = $current_entry;
      } elseif(is_file($path) && !$options['only_dirs']) {
          if($name[0] == '.' && !$options['hidden_files'])
            continue;
          $current_entry['_fs']['type'] = 'f';
          $result_files[] = $current_entry;
      } else continue;

      //$result[] = $current_entry;
    }
    
    closedir($hdir);
    
    if($options['sort']) {
      foreach([&$result_dirs, &$result_files] as &$result) {
        usort($result, function($i1, $i2) use ($options) {
          return $options['sort'] == 'asc' ? $i1['text'] <=> $i2['text'] : $i2['text'] <=> $i1['text'];
        });
      }
    }
    
    //return $result;
    return array_merge($result_dirs, $result_files);
  }

  public static function countFiles($path, $user_options = [])
  {
    $options = array_merge([
      'dot_dirs' => false,
      'recursive' => true
    ], $user_options);

    $result = [
      'files' => 0,
      'directories' => 0,
      'links' => 0,
      'total' => 0,
      'errors' => 0
    ];
    
    $path = self::slash($path);
    $hdir = @opendir($path);
    
    if(!$hdir) {
      $result['errors']++;
      return $result;
    }

    while(($name = readdir($hdir)) !== false) {
      $current_path = $path.$name;
      if($name == '.' || $name == '..') {
        if($options['dot_dirs']) {
          $result['directories']++;
        }
      } elseif(is_dir($current_path)) {
        $result['directories']++;

        if($options['recursive']) {
          $subdirs = $this->countFiles($current_path, $options);
          $result['files'] += $subdirs['files'];
          $result['directories'] += $subdirs['directories'];
          $result['links'] += $subdirs['links'];
          $result['errors'] += $subdirs['errors'];
        }
      } elseif(is_file($current_path)) {
        $result['files']++;
      } elseif(is_link($current_path)) {
        $result['links']++;
      }
    }
    
    closedir($hdir);
    $result['total'] = $result['files'] + $result['directories'] + $result['links'];
    return $result;
  }

  public static function replaceLine($filename, $search_pattern, $new_line, $append = false)
  {
    $lines = @file($filename);
    $out = '';
    $found = false;
  
    if(is_array($lines)) {
      foreach($lines as $line) {
        if(stristr(trim($line), $search_pattern)) {
          $out .= $new_line."\n";
          $found = true;
        } else
          $out .= $line;
      }
    }
  
    if(!$found && $append) {
      if(substr($out, -1) != "\n" && substr($out, -1) != "\r" && filesize($filename) > 0) $out .= "\n";
      $out .= $new_line."\n";
    }
  
    file_put_contents($filename, $out);
  }
  
  public static function removeLine($filename, $search_pattern)
  {
    if($lines = @file($filename)) {
      $out = '';
      foreach($lines as $line) {
        if(!stristr(trim($line), $search_pattern))
          $out .= $line;
  
      }
      file_put_contents($filename, $out);
    }
  }

  public static function killTree($dir, $user_options = [])
  {
    if(empty($dir) || $dir == '/' || $dir == '\\') {
      return;
    }

    $options = array_merge([
      'delete_root' => true
    ], $user_options);
    
    $dir = self::slash($dir);
    $it = new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS);
    $files = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::CHILD_FIRST);

    foreach($files as $file) {
      if($file->isDir()){
        @rmdir($file->getRealPath());
      } else {
        @unlink($file->getRealPath());
      }
    }

    if($options['delete_root']) {
      @rmdir($dir);
    }
  }
}

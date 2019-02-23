<?php

namespace Lyx\Utils;

class Types
{
  public static function is_digit($digit)
  {
    if(is_int($digit))
      return true;
    elseif(is_string($digit))
      return ctype_digit($digit);
    else
      // booleans, floats and others
      return false;
  }
  
  public static function array_to_object($array, &$object = null)
  {
    if(is_null($object))
      $object = new \stdClass;
      
    if(self::isAssocArray($array))
    {
      foreach($array as $key => $value)
      {
        if(is_array($value))
        {
          $object->$key = new \stdClass;
          self::array_to_object($value, $object->$key);
        }
        else
          $object->$key = $value;
      }
    }
    else
      $object = $array;
  
    return $object;
  }
  
  public static function arrayToObject($array)
  {
    return self::array_to_object($array);
  }

  public static function array_ikeys($key, $array)
  {
    $fncheck=create_function('$k', 'return strcasecmp($k,"'.$key.'")===0;');
    return array_values(array_filter(array_keys($array), $fncheck));
  }
  
  public static function array_ikey_get($key, $array)
  {
    $fncheck=create_function('$k', 'return strcasecmp($k,"'.$key.'")===0;');
    $keys=self::array_ikeys($key, $array);
  
    if(count($keys)>0)
      return $array[$keys[0]];
    else
      return array();
  }
  
  public static function array_ikey_exists($key, $array)
  {
    return count(self::array_ikeys($key, $array))>0;
  }

  public static function isAssocArray($array)
  {
    if(!is_array($array))
      return false;
    else
      return (bool)count(array_filter(array_keys($array), 'is_string'));
  }
  
  public static function &setByPath(
    $path,
    $value,
    &$array,
    $link_enable=true
  ) {
    $array_value = self::getByPath($path, $array, null, false, false, $link_enable, true, $value);
    return $array;
  }
  
  public static function &setLinkByPath($path, $link_path, &$array)
  {
    return self::setByPath($path, $link_path, $array, false);
  }
  
  // public static function setByPath_($path, $value, &$array, $link_enable=true, $array_base=null)
  // {
  //     if($array_base === null)
  //         $array_base = $array;

  //   if(($pos=strpos($path, '.'))!==false)
  //   {
  //     $baseName=substr($path, 0, $pos);
        
  //     if(!isset($array[$baseName]))
  //     {
  //         if($link_enable && is_string($array[$baseName]) && self::isLinkValue($array[$baseName]))
  //                 $array = self::getByPath(self::getLinkValue($array[$baseName], $array_base, $link_enable, $array_base));
  //         else
  //             $array[$baseName] = array();
  //     }
      
  //     return self::setByPath(substr($path, $pos+1), $value, $array[$baseName], $link_enable, $array_base);
  //   }
  //   else
  //   {
  //     $array[$path] = $value;
  //     return true;
  //   }
  // }
  
  public static function isLinkValue($value, $link_regex = '')
  {
    if(empty($link_regex))
      $link_regex = '/^(@@|##)/';

    return preg_match($link_regex, $value);
  }
  
  public static function getLinkValue(
    $value,
    $link_extract_regex = '',
    &$link_type = null
  ) {
    if(empty($link_extract_regex))
      $link_extract_regex = '/^(@@|##)(.*)/';

    if(preg_match($link_extract_regex, $value, $matches = null)) {
      if(func_num_args() >= 3)
        $link_type = $matches[1];
      return $matches[2];
    }
    return '';
  }
  
  public static function isNamePath($name, $char_separator = '.')
  {
    return strpos($name, $char_separator) !== false;
  }
  
  public static function issetByPath($path, $array)
  {
    return self::getByPath($path, $array, null, true);
  }

  public static function unsetByPath($path, $array)
  {
    return self::getByPath($path, $array, null, false, true);
  }

  public static function getByPath(
    $path, 
    &$array, 
    $default=null, 
    $exists_mode=false, 
    $unset=false, 
    $link_enable=true, 
    $do_set = false, 
    $set_value = null
  ) {
    $array_current = &$array;
    $array_current_parent = &$array;
    $path_parts = explode('.', $path);
    $current_key = '';
    $break = false;
    $part_idx = 0;
    $parts_size = count($path_parts);
    
    //\Lyx::debug('parts', $path_parts, $parts_size);
    
    do
    {
      $current_key = $path_parts[$part_idx++];
      
      //\Lyx::debug('$part_idx', $part_idx);
      //\Lyx::debug('$current_key', $current_key);

      if(isset($array_current[$current_key]))
      {
        if(is_array($array_current[$current_key]))
        {
          $array_current_parent = &$array_current;
          $array_current = &$array_current[$current_key];
          //\Lyx::debug('$array_current_parent in', $array_current_parent);
        }
        elseif(is_string($array_current[$current_key]))
        {
          if($link_enable && self::isLinkValue($array_current[$current_key]))
          {
            $link_parts = explode('.', self::getLinkValue($array_current[$current_key], null, $link_type));
            $path_parts = array_slice($path_parts, $part_idx, $parts_size - $part_idx);
            $path_parts = array_merge($link_parts, $path_parts);
            $part_idx = 0;
            $parts_size = count($path_parts);
            //if($link_type == '@@')
              $array_current = &$array;
            //elseif($link_type == '##')
            // $array_current = $array;
            
            if($link_type == '@@')
              $array_current_parent = &$array_current;
            elseif($link_type == '##')
              $array_current_parent = $array_current;
            continue;
          }
          else
          {
            $array_current_parent = &$array_current;
            $array_current = &$array_current[$current_key];
          }
        }
        else
        {
          $array_current_parent = &$array_current;
          //\Lyx::debug('$array_current_parent in 2', $array_current_parent);
          $array_current = &$array_current[$current_key];
        }
      }
      elseif($do_set) {
        $array_current_parent = &$array_current;
        $array_current[$current_key] = [];
        $array_current = &$array_current[$current_key];
      }
      else
      {
        //\Lyx::wprintln('$break');
        $break = true;
        break;
      }
      
      //\Lyx::debug('$array_current', $array_current);
    }
    while($part_idx < $parts_size);
    
    if(!$break) 
    {
      //\Lyx::debug('$array_current_parent out', $array_current_parent);
      //\Lyx::dbg([$current_key, $array_current_parent, $set_value, $do_set]);

      if(isset($array_current_parent[$current_key])) {
        if($do_set) {
          $array_current_parent[$current_key] = $set_value;
          return true;
        } elseif($unset) {
          unset($array_current_parent[$current_key]);
          return true;
        }
        else
          return $exists_mode ? true : $array_current;
      }
      elseif($unset)
        return false;
      else
        return $exists_mode ? false : $default;
    }
    else 
      return $exists_mode ? false : $default;
  }
  
  public static function getLinkByPath(
    $path,
    &$array,
    $default = null,
    $exists_mode = false,
    $unset = false,
    $link_enable = true,
    $do_set = false,
    $set_value = null
  ) {
    return self::getByPath($path, $array, $default, $exists_mode, $unset, false, $do_set, $set_value);
  }
  
  // public static function getByPath_($path, $array, $default = null, $exists_mode = false, $unset = false, $link_enable = true, $array_base = null)
  // {
  //   if($array_base === null)
  //     $array_base = &$array;
      
  //   if(isset($array[$path])) {
  //     if($unset) {
  //       unset($array[$path]);
  //       return true;
  //     } else
  //       return $exists_mode ? true : $array[$path];
  //   }
  
  //   if(($pos=strpos($path, '.'))!==false) {
  //     $baseName=substr($path, 0, $pos);
        
  //     if(isset($array[$baseName])) {
  //         if($link_enable && is_string($array[$baseName]) && self::isLinkValue($array[$baseName]))
  //           $array = self::getByPath(self::getLinkValue($array[$baseName], $array_base, $default, $exists_mode, $unset, $link_enable, $array_base));

  //       return self::getByPath(substr($path, $pos+1), $array[$baseName], $default, $exists_mode, $unset, $link_enable, $array_base);
  //     }
  //     elseif($unset)
  //       return false;
  //     else
  //       return $exists_mode ? false : $default;
  //   }
  //   elseif($unset)
  //     return false;
  //   else
  //     return $exists_mode ? false : $default;
  // }
  
  public static function getByPathCase($path, $array, $default = null)
  {
    if(self::array_ikey_exists($path, $array))
      return self::array_ikey_get($path, $array);
  
    if(($pos=strpos($path, '.')) !== false)
    {
      $baseName = substr($path, 0, $pos);
  
      if(self::array_ikey_exists($baseName, $array))
        return self::getByPathCase(substr($path, $pos + 1), self::array_ikey_get($baseName, $array));
      else
        return $default;
    }
    else
      return $default;
  }
  
  public static function hasFlag()
  {
    call_user_func_array('lyx_has_flags', func_get_args());
  }

  public static function translateBool($val)
  {
    if(!is_string($val)) 
      return (bool)$val;

    switch(mb_strtolower(trim($val)))  {
      case '1':
      case 'true':
      case 'on':
      case 'yes':
      case 'y':
      case 'ναι':
      case 'ναί':
      case 'ν':
        return true;
      default:
        return false;
    }
  }
  
  public static function toBool($val)
  {
    return static::translateBool($val);
  }
  
  public static function toBoolStr($val, $true = 'true', $false = 'false')
  {
    return static::translateBool($val) ? $true : $false;
  }

  public static function mergesort(&$array, $cmp_function = 'strcmp')
  {
    // Arrays of size < 2 require no action.
    if (count($array) < 2) return;
    // Split the array in half
    $halfway = count($array) / 2;
    $array1 = array_slice($array, 0, $halfway);
    $array2 = array_slice($array, $halfway);
    // Recurse to sort the two halves
    mergesort($array1, $cmp_function);
    mergesort($array2, $cmp_function);
    // If all of $array1 is <= all of $array2, just append them.
    if(call_user_func($cmp_function, end($array1), $array2[0]) < 1) {
      $array = array_merge($array1, $array2);
      return;
    }
    // Merge the two sorted arrays into a single sorted array
    $array = array();
    $ptr1 = $ptr2 = 0;
    while($ptr1 < count($array1) && $ptr2 < count($array2)) {
      if(call_user_func($cmp_function, $array1[$ptr1], $array2[$ptr2]) < 1) {
        $array[] = $array1[$ptr1++];
      } else {
        $array[] = $array2[$ptr2++];
      }
    }
    // Merge the remainder
    while($ptr1 < count($array1)) $array[] = $array1[$ptr1++];
    while($ptr2 < count($array2)) $array[] = $array2[$ptr2++];
    return;
  }
  
  public static function arrayOverride(array $base, array $override, array $params = [])
  {
    $params = array_replace([
      'recursive' => true,
      'use_args' => false
    ], $params);
    
    $recursive = $params['recursive'];
    $use_args = $params['use_args'];

    $result = is_array($base) ? $base : [$base];
    $index = 0;

    foreach($override as $key => $item) {
      $args = [];

      if($use_args) {
        $expression = \Lyx\Strings\Str::parseFunctionParameters(trim($key));
        if(!empty($expression['params'])) {
          $args = $expression['params'];
          $key = $expression['post_expression_directives'];
        }
      }

      if($index === $key) {
        $result[] = $item;
        $index++;
      } elseif($recursive && isset($result[$key]) && is_array($result[$key]) && is_array($override[$key])) {
        if(in_array('=', $args))
          $result[$key] = $item;
        else
          $result[$key] = self::arrayOverride($result[$key], $item, $params);
      } else
        $result[$key] = $item;
    }
    
    return $result;
  }
}

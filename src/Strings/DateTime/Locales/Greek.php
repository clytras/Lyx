<?php

namespace Lyx\Strings\DateTime\Locales;

class Greek extends \DateTime
{
  public function format($format)
  {
    $english = [
      'days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
      'months' => ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December']
    ];
    $greek = [
      'days' => ['Δευτέρα', 'Τρίτη', 'Τετάρτη', 'Πέμπτη', 'Παρασκευή', 'Σάββατο', 'Κυριακή'],
      'months' => ['Ιανουαρίου', 'Φεβρουαρίου', 'Μαρτίου', 'Απριλίου', 'Μαΐου', 'Ιουνίου', 'Ιουλίου', 'Αυγούστου', 'Σεπτεμβρίου', 'Οκτωβρίου', 'Νοεμβρίου', 'Δεκεμβρίου']
    ];
    $ret = str_replace($english['days'], $greek['days'], parent::format($format));
    $ret = str_replace($english['months'], $greek['months'], $ret);
    return $ret;
  }

  public function setTimestamp($unixtimestamp)
  {
    if(!is_numeric($unixtimestamp) && !is_null($unixtimestamp))
      trigger_error('DateTime::setTimestamp() expects parameter 1 to be long, '.gettype($unixtimestamp).' given', E_USER_WARNING);
    else {
      $this->setDate(date('Y', $unixtimestamp), date('n', $unixtimestamp), date('d', $unixtimestamp));
      $this->setTime(date('G', $unixtimestamp), date('i', $unixtimestamp), date('s', $unixtimestamp));
    }
    return $this;
  }

  /**
   * Get the time of the datetime object as a unix timestamp
   * @return int a unix timestamp representing the time in the datetime object
   */
  public function getTimestamp()
  {
    return $this->format('U');
  }
}

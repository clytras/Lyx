<?php

namespace Lyx\System;

class Fork
{
  const FUNCTION_NOT_CALLABLE = 10;
  const COULD_NOT_FORK = 15;
  
  /**
   * possible errors
   *
   * @var array
   */
  private $errors = [
    self::FUNCTION_NOT_CALLABLE => 'You must specify a valid function name that can be called from the current scope.',
    self::COULD_NOT_FORK => 'pcntl_fork() returned a status of -1. No new process was created'
  ];
  
  private $_sigHandlers = [];
  
  /**
   * callback for the function that should
   * run as a separate thread
   *
   * @var callback
   */
  public $runnable;
  
  /**
   * holds the current process id
   *
   * @var integer
   */
  private $pid;

  /**
   * thread name
   *
   * @var string
   */
  //private $name = '';
  
  /**
   * checks if threading is supported by the current
   * PHP configuration
   *
   * @return boolean
   */
  public static function available()
  {
    $required_functions = ['pcntl_fork'];

    foreach($required_functions as $function)
      if (!function_exists($function))
        return false;

    return true;
  }
  
  /**
   * class constructor - you can pass
   * the callback function as an argument
   *
   * @param callback $runnable
   */
  public function __construct($arg = null)
  {
    if(is_int($arg))
      $this->attach($arg);
    elseif(!is_null($arg)) {
      $this->_sigHandlers[SIGKILL] = array($this, 'signalHandler');
        $this->setRunnable($arg);
    }
  }
  
  public function attach()
  {
    $result = false;
    $argv = func_get_args();
    $argn = func_num_args();
    
    $this->_sigHandlers[SIGKILL] = array($this, 'signalHandler');
    
    if($argn == 1) { // pid | Thread object
      if($argv[0] instanceof Thread) {
        $this->pid = $argv[0]->pid;
        $this->name = $argv[0]->name;
        $this->runnable = $argv[0]->runnable;
        $result = true;
      } else if(is_int($argv[0])) {
        $this->pid = $argv[0];
        $this->name = $this->autoName();
        $result = true;
      }
    } elseif($argn >= 2) { // name, pid | name, Thread object
      $this->name = $argv[0];
      if($argv[1] instanceof Thread) {
        $this->pid = $argv[1]->pid;
        $this->runnable = $argv[1]->runnable;
        $result = true;
      } else if(is_int($argv[1])) {
        $this->pid = $argv[1];
        $result = true;
      }
    }
    
    return $result;
  }
  
  public function autoName()
  {
  	return 'P:{pid}';
  }
  
  /**
   * sets the callback
   *
   * @param callback $runnable
   * @return callback
   */
  public function setRunnable($runnable)
  {
    if(self::runnableOk($runnable))
      $this->runnable = $runnable;
    else
      throw new \Exception($this->getError(Thread::FUNCTION_NOT_CALLABLE), Thread::FUNCTION_NOT_CALLABLE);
  }
  
  /**
   * gets the callback
   *
   * @return callback
   */
  public function getRunnable()
  {
    return $this->runnable;
  }
  
  /**
   * checks if the callback is ok (the function/method
   * actually exists and is runnable from the current
   * context)
   * 
   * can be called statically
   *
   * @param callback $runnable
   * @return boolean
   */
  public static function runnableOk($runnable)
  {
    //return function_exists($runnable) && is_callable($runnable);
    return is_callable($runnable);
  }
  
  /**
   * returns the process id (pid) of the simulated thread
   * 
   * @return int
   */
  public function getPid()
  {
    return $this->pid;
  }
  
  public function getName()
  {
    return $this->name;
  }

  public function setName($name)
  {
    $this->name = $name;
  }
  
  /**
   * checks if the child thread is alive
   *
   * @return boolean
   */
  public function isAlive()
  {
    $pid = pcntl_waitpid($this->pid, $status, WNOHANG);
    return $pid === 0;
  }
  
  /**
   * starts the thread, all the parameters are 
   * passed to the callback function
   * 
   * @return void
   */
  public function start()
  {
    $pid = @pcntl_fork();

    if($pid == -1)
      throw new \Exception($this->getError(Thread::COULD_NOT_FORK ), Thread::COULD_NOT_FORK);

    if($pid) // parent
      return $this->pid = $pid;
    else { // child
      foreach($this->_sigHandlers as $signo => $handler)
        pcntl_signal($signo, $handler);

      //if(empty($this->name))
      //	$this->name = $this->autoName();
      
      //$pidname = preg_replace('/\{pid}/', posix_getpid(), $this->name);
      $arguments = func_get_args();
      //array_unshift($arguments, $pidname);

      if(!empty($arguments))
        call_user_func_array($this->runnable, $arguments);
      else
        call_user_func($this->runnable);
      
      exit(0);
    }
  }
  
  public function addSignalHandler($signo, $handler)
  {
    $this->_sigHandlers[$signo] = $handler;
  }
  
  /**
   * attempts to stop the thread
   * returns true on success and false otherwise
   *
   * @param integer $signal - SIGKILL/SIGTERM
   * @param boolean $wait
   */
  public function stop($signal = SIGTERM, $wait = false)
  {
    if($this->isAlive()) {
      posix_kill($this->pid, $signal);
      if($wait)
        pcntl_waitpid($this->pid, $status = 0);
    }
  }
  
  /**
   * alias of stop();
   *
   * @return boolean
   */
  public function kill($signal = SIGKILL, $wait = false)
  {
    return $this->stop($signal, $wait);
  }
  
  /**
   * gets the error's message based on
   * its id
   *
   * @param integer $_code
   * @return string
   */
  public function getError($code)
  {
    if(isset($this->errors[$code]))
      return $this->errors[$code];
    else
      return "No such error code {$code}! Quit inventing errors!";
  }
  
  /**
   * signal handler
   *
   * @param integer $signal
   */
  protected function signalHandler($signal)
  {
    switch($signal) {
      case SIGTERM:
        exit(0);
        break;
    }
  }
}

<?php

namespace Lyx\Db;

class Database
{
  private $_dbh;
  private $_stmt;
  private $_queryCounter = 0;
  private $_affectedRows = 0;
  
  private $_query;
  private $_params;
      
  public $dbinfo;
  public $lastError;
  
  public $dsn = '';
  public $username = '';
  public $password = '';

  public function __construct()
  {
    call_user_func_array(array($this, 'connect'), func_get_args());
  }
  
  public function reset($keepdb = false)
  {
    if(!$keepdb) $this->_dbh = null;
    $this->_stmt = null;
    $this->_queryCounter = 0;
    $this->_affectedRows = 0;
    $this->_query = null;
    $this->_params = array();
  }
  
  public function isConnected()
  {
    return !is_null($this->_dbh);
  }
  
  public function connect()
  {
    $this->reset();

    if(func_num_args() > 0) {
      $this->dbinfo = new DbInfo();
      call_user_func_array(array($this->dbinfo, 'set'), func_get_args());
      
      // $this->connect($this->dbinfo->compilePdoDsn(), $this->dbinfo->username, $this->dbinfo->password);
      
      // $dsn = 'mysql:host=localhost;dbname=' . $dbname;
      // $dsn = 'sqlite:myDatabase.sq3';
      // $dsn = 'sqlite::memory:';
      
      // $this->dsn = $dsn;
      // $this->username = $user;
      // $this->password = $pass;
      $options = [
        \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
        // \PDO::ATTR_AUTOCOMMIT => FALSE,
        // \PDO::ATTR_PERSISTENT => true
      ];
      try {
        $this->_dbh = new \PDO($this->dbinfo->compilePdoDsn(), $this->dbinfo->username, $this->dbinfo->password, $options);
        return true;
      } catch (\PDOException $e) {
        $this->_dbh = null;
        $this->lastError = $e;
        return false;
      }
    }
    else
      return false;
  }
  
  public function disconnect()
  {
    $this->reset();
  }
  
  public function delete($table, $conditions = '', $params = [])
  {
    $this->_query['delete'] = $table;
    $this->_params = $params;
    
    if(!empty($conditions))
      $this->where($conditions);
      
    return $this;
  }
  
  // select() from Yii
  public function select(mixed $columns = '*', string $option = '')
  {
    if(is_string($columns) && strpos($columns, '(') !== false)
      $this->_query['select'] = $columns;
    else {
      if(!is_array($columns))
        $columns = preg_split('/\s*,\s*/', trim($columns), -1, PREG_SPLIT_NO_EMPTY);

      foreach($columns as $i => $column) {
        if(is_object($column))
          $columns[$i] = (string)$column;
        else if(strpos($column,'(') === false) {
          if(preg_match('/^(.*?)(?i:\s+as\s+|\s+)(.*)$/', $column, $matches = null))
            $columns[$i] = self::quoteColumnName($matches[1]) . ' AS ' . self::quoteColumnName($matches[2]);
          else
            $columns[$i] = self::quoteColumnName($column);
        }
      }
      $this->_query['select'] = implode(', ', $columns);
    }

    if($option != '')
      $this->_query['select'] = $option.' '.$this->_query['select'];
    return $this;
  }

  // from() from Yii
  public function from($tables)
  {
    if(is_string($tables) && strpos($tables, '(') !== false)
      $this->_query['from'] = $tables;
    else {
      if(!is_array($tables))
        $tables=preg_split('/\s*,\s*/',trim($tables), -1, PREG_SPLIT_NO_EMPTY);
      foreach($tables as $i => $table) {
        if(strpos($table, '(') === false) {
          if(preg_match('/^(.*?)(?i:\s+as\s+|\s+)(.*)$/', $table, $matches = null))  // with alias
            $tables[$i] = self::quoteTableName($matches[1]) . ' ' . self::quoteTableName($matches[2]);
          else
            $tables[$i] = self::quoteTableName($table);
        }
      }
      $this->_query['from'] = implode(', ', $tables);
    }
    return $this;
  }
  
  public function update($table, $params, $conditions = null /* array('id=:id', array(...)) */)
  {
    $this->_query['table'] = $table;
    $this->_query['update'] = array_keys($params);
    $this->_params = $params;

    if(!empty($conditions))
      $this->where($conditions);
    
    return $this;
  }
  
  public function insert($table, $columns)
  {
    $this->_query['table'] = $table;
    $this->_query['insert'] = $columns;
    $this->_params = $columns;
    return $this;
  }
  
  public function where($conditions, $params = [])
  {
    if(is_array($conditions))
      list($conditions, $params) = array($conditions[0], $conditions[1]);
    
    $this->_query['where'] = $conditions;
    $this->_query['conditions'] = $params;
    
    return $this;
  }

  public function limit($limit, $offset = null)
  {
    $this->_query['limit']=(int)$limit;
    if($offset!==null)
      $this->offset($offset);
    return $this;
  }

  public function offset($offset)
  {
    $this->_query['offset']=(int)$offset;
    return $this;
  }

  public function orderBy($fields)
  {
    $this->_query['order']=is_array($fields)?implode(', ', $fields):$fields;
    return $this;
  }
  
  public function _compileQuery()
  {
    $sql = '';
    $proc_where = false;

    if(isset($this->_query['select'])) {
      $sql = 'SELECT '.$this->_query['select'];
      $proc_where = true;
    } elseif(isset($this->_query['insert'])) {
      $sql = 'INSERT INTO '.self::quoteTableName($this->_query['table']).' (';
      $fields = array();
      $values = array();

      foreach($this->_query['insert'] as $name => $value) {
        $fields[] = self::quoteColumnName($name);
        $values[] = ':'.$name;
      }
      
      $sql .= implode(',', $fields).') VALUES('.implode(',', $values).')';
    } elseif(isset($this->_query['update'])) {
      $sql = 'UPDATE '.self::quoteTableName($this->_query['table']).' SET ';
      $fields = array();

      foreach($this->_query['update'] as $name)
        $fields[] = self::quoteColumnName($name).'=:'.$name;
      
      $sql .= implode(',', $fields);
      $proc_where = true;
    } elseif(isset($this->_query['delete'])) {
      $sql = 'DELETE FROM '.self::quoteTableName($this->_query['delete']);
      $proc_where = true;
    }
    
    if(isset($this->_query['from']))
      $sql .= ' FROM '.$this->_query['from'];
    
    if($proc_where && isset($this->_query['where'])) {
      $sql .= ' WHERE '.$this->_query['where'];
      $this->_params = array_merge($this->_params, $this->_query['conditions']);
    }
    
    if(isset($this->_query['order']))
        $sql .= ' ORDER BY '.$this->_query['order'];

    $limit = isset($this->_query['limit']) ? (int)$this->_query['limit'] : -1;
    $offset = isset($this->_query['offset']) ? (int)$this->_query['offset'] : -1;
    
    if($limit >= 0 || $offset > 0) {
      if($limit >= 0)
        $sql.=' LIMIT '.(int)$limit;
      if($offset > 0)
        $sql.=' OFFSET '.(int)$offset;
    }

    return $sql;
  }

  public function query($query, $bind = null)
  {
    if(is_object($this->_stmt))
      $this->_stmt->closeCursor();

    $this->_stmt = $this->_dbh->prepare($query);
    if(!is_null($bind))
      $this->bind($bind);

    return $this;
  }
  
  public function bindValue($pos, $value = null, $type = null)
  {
    return $this->bind($pos, $value, $type);
  }
  
  public function bindParam($pos, &$param = null, $type = null)
  {
    return $this->bind($pos, $param, $type, 'bindParam');
  }

  public function bind($pos, &$value = null, $type = null, $func = 'bindValue')
  {
    $data = $pos;
    if(!is_array($pos)) {
      if(is_int($pos))
        $pos--;

      $data = array($pos => $value);
    }

    foreach($data as $key => $val) {
      if(is_null($type)) {
        if(is_int($val))
          $type = \PDO::PARAM_INT;
        elseif(is_bool($val))
          $type = \PDO::PARAM_BOOL;
        elseif(is_null($val))
          $type = \PDO::PARAM_NULL;
        else
          $type = \PDO::PARAM_STR;
      }

      if(is_int($key)) // 1-indexed position of the parameter
        $key++;

      $this->_stmt->{$func}($key, $val, $type);
    }
    
    return $this;
  }

  public function execute($input_parameters = [])
  {
    if(!is_null($this->_query)) {
      $sql = $this->_compileQuery();
      $this->query($sql);
      $this->bind($this->_params);
      $this->_query = null;
    }
    
    $this->_queryCounter++;
    if(count($input_parameters) > 0)
      $this->_stmt->execute($input_parameters);
    else
      $this->_stmt->execute();
    
    return $this;
  }
  
  public function exec($statement)
  {
    return $this->_affectedRows = $this->_dbh->exec($statement);
  }
  
  public function resultScalar($column_number = 0)
  {
    $result = $this->_stmt->fetchColumn($column_number);
    //$this->_stmt->closeCursor();
    return $result;
  }

  public function resultAll()
  {
    $result = $this->_stmt->fetchAll(\PDO::FETCH_ASSOC);
    //$this->_stmt->closeCursor();
    return $result;
  }
  
  public function resultRow($fetch_obj = false)
  {
    if($fetch_obj)
      $style = \PDO::FETCH_OBJ;
    else
      $style = \PDO::FETCH_ASSOC;
    // $this->execute();
    return $this->_stmt->fetch($style);
  }
  
  public function resultColumn()
  {
    return $this->_stmt->fetchAll(\PDO::FETCH_COLUMN);
  }

  // returns last insert ID
  //!!!! if called inside a transaction, must call it before closing the transaction!!!!!!
  public function lastInsertId()
  {
    return $this->_dbh->lastInsertId();
  }

  // begin transaction // must be innoDatabase table
  public function beginTransaction()
  {
    return $this->_dbh->beginTransaction();
  }

  // end transaction
  public function commitTransaction()
  {
    return $this->_dbh->commit();
  }

  // cancel transaction
  public function rollbackTransaction()
  {
    return $this->_dbh->rollBack();
  }

  // returns number of rows updated, deleted, or inserted
  public function rowCount()
  {
    return $this->_affectedRows = $this->_stmt->rowCount();
  }

  // returns number of queries executed
  public function queryCounter()
  {
    return $this->_queryCounter;
  }

  public function debugDumpParams()
  {
    return $this->_stmt->debugDumpParams();
  }
  
  public function errorInfo()
  {
    return $this->_dbh->errorInfo();
  }
  
  public function truncateTable($table)
  {
    $this->exec('TRUNCATE TABLE '.self::quoteTableName($table));
  }
  
  public function getTableStatus($table, $field = null)
  {
    $ret = null;
    if($this->isConnected()) {
      $this->query("SHOW TABLE STATUS LIKE '".$table."'");
      $this->execute();
      $result = $this->resultRow();
      if(!is_null($field) && !empty($field))
        $ret = $result[$field];
      else
        $ret = $result;
    }
    return $ret;
  }
  
  public function getTableAutoIncrement($table)
  {
    return $this->getTableStatus($table, 'Auto_increment');
  }
  
  public function countRows($table)
  {
    $ret = null;
    if($this->isConnected()) {
      try {
        $this->query("SELECT COUNT(*) FROM ".self::quoteTableName($table));
        $this->execute();
        $ret = $this->resultScalar();
      } catch(\PDOException $pdoe) {
        $this->lastError = $pdoe->getMessage();
      }
    }
    return $ret;
  }
  
  public function getTables()
  {
    $ret = null;
    if($this->isConnected()) {
      try {
        $this->query("SHOW TABLES");
        $this->execute();
        $ret = $this->resultColumn();
      } catch(\PDOException $pdoe) {
        $this->lastError = $pdoe->getMessage();
      }
    }
    return $ret;
  }

  public static function quoteTableName($name)
	{
    if(strpos($name, '.') === false)
      return self::quoteSimpleTableName($name);

    $parts = explode('.', $name);

    foreach($parts as $i => $part)
      $parts[$i] = self::quoteSimpleTableName($part);

    return implode('.', $parts);
	}
	
	public static function quoteSimpleTableName($name)
	{
		return "`".$name."`";
	}

	public static function quoteSimpleColumnName($name)
	{
		return '`'.$name.'`';
	}
	
	public static function quoteColumnName($name)
	{
		if(($pos = strrpos($name, '.')) !== false)
		{
			$prefix = $this->quoteTableName(substr($name, 0, $pos)) . '.';
			$name = substr($name, $pos + 1);
		}
		else
			$prefix = '';

		return $prefix . ($name === '*' ? $name : self::quoteSimpleColumnName($name));
	}
}

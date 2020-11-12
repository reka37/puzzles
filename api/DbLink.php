<?php

class e_db_exception extends Exception { }

class DbLink extends mysqli
{
  public function query($query, $resultMode = MYSQLI_STORE_RESULT)
  {
    $result = parent::query($query, $resultMode);

    if ($this->error != "") {
      $errorText = "dbLink mysql error (" . $this->errno . "): " . $this->error;
      error_log($errorText);
      throw new e_db_exception($errorText);
    }

    return $result;
  }
}

class Db
{
    /**
    * @var dbLink
    */
    protected $link;

  public function __construct($dbServer, $dbUsername, $dbPassword, $dbName)
  {
    $this->link = new dbLink($dbServer, $dbUsername, $dbPassword, $dbName);
    if ($this->link) {
      $this->link->set_charset("utf8");
      $this->link->query("SET sql_mode = ''");
    }
    return $this->link;
  }

  function __destruct()
  {
    if ($this->link) {
      $this->link->close();
    }
  }
}
class DbPdo
{
    /**
    * @var PDO
    */
    protected $db;

	public function __construct($database, $dbuser, $dbpass)
	{
	  try {
			$this->db = new PDO('mysql:host=localhost;dbname='.$database, $dbuser, $dbpass);
			$this->db->query('set names utf8');
			return $this->db;
		} catch (PDOException $e) {
			print "Ошибка соединения!: " . $e->getMessage() . "<br/>";
			die();
		}
	}
}
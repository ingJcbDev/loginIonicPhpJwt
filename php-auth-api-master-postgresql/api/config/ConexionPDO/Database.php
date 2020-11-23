<?php

include_once ('config/ConfigDB.php');

class Database {

    /**
     * The singleton instance
     * 
     */
    static private $PDOInstance;
    private $port = 5432;
    protected $transactionCounter = 0;
    public $transactionStatus = true; //Cuando se crea la transacción y genera Error, pasa a estado false.
    public $transactionMessage = array();
    public $debug = false;

    /**
     * Creates a PDO instance representing a connection to a database and makes the instance available as a singleton
     * 
     * @param string $dsn The full DSN, eg: mysql:host=localhost;dbname=testdb
     * @param string $username The user name for the DSN string. This parameter is optional for some PDO drivers.
     * @param string $password The password for the DSN string. This parameter is optional for some PDO drivers.
     * @param array $driver_options A key=>value array of driver-specific connection options
     * 
     * @return PDO
     */
    public function __construct($ConfigDB = '') {
        if (!self::$PDOInstance) {
            try {
                if (empty($ConfigDB)) {
                    global $ConfigDB;
                }
                self::$PDOInstance = new PDO("pgsql:host=$ConfigDB[dbhost];port=$this->port;dbname=$ConfigDB[dbname];user=$ConfigDB[dbuser];password=$ConfigDB[dbpass]");
                self::$PDOInstance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die("PDO CONNECTION ERROR: " . $e->getMessage() . "<br/>");
            }
        }
        return self::$PDOInstance;
    }

    public function __destruct() {
        
    }

    /**
     * Initiates a transaction
     *
     * @return bool
     */
    public function beginTransaction() {
        if (!$this->transactionCounter++) {
            return self::$PDOInstance->beginTransaction();
        }
        $query = 'SAVEPOINT trans' . $this->transactionCounter;
        $this->exec($query);
        return $this->transactionCounter >= 0;
    }

    /**
     * Rolls back a transaction
     *
     * @return bool
     */
    public function rollBack() {
        if (--$this->transactionCounter) {
            $query = 'ROLLBACK TO trans' . ($this->transactionCounter + 1);
            $this->exec($query);
            return true;
        }
        return self::$PDOInstance->rollback();
    }

    /**
     * Commits a transaction
     *
     * @return bool
     */
    public function commit() {
        if (!--$this->transactionCounter) {
            return self::$PDOInstance->commit();
        }
        return $this->transactionCounter >= 0;
    }

    /**
     * Fetch the SQLSTATE associated with the last operation on the database handle
     * 
     * @return string 
     */
    public function errorCode() {
        return self::$PDOInstance->errorCode();
    }

    /**
     * Fetch extended error information associated with the last operation on the database handle
     *
     * @return array
     */
    public function errorInfo() {
        return self::$PDOInstance->errorInfo();
    }

    /**
     * Execute an SQL statement and return the number of affected rows
     *
     * @param string $statement
     */
    public function exec($statement) {
        return self::$PDOInstance->exec($statement);
    }

    /**
     * Retrieve a database connection attribute
     *
     * @param int $attribute
     * @return mixed
     */
    public function getAttribute($attribute) {
        return self::$PDOInstance->getAttribute($attribute);
    }

    /**
     * Return an array of available PDO drivers
     *
     * @return array
     */
    public function getAvailableDrivers() {
        return Self::$PDOInstance->getAvailableDrivers();
    }

    /**
     * Executes an SQL statement, returning a result set as a PDOStatement object
     *
     * @param string $statement
     * @return PDOStatement
     */
    public function query($statement) {
        return self::$PDOInstance->query($statement);
    }

    /**
     * Quotes a string for use in a query
     *
     * @param string $input
     * @param int $parameter_type
     * @return string
     */
    public function quote($input, $parameter_type = 0) {
        return self::$PDOInstance->quote($input, $parameter_type);
    }

    public function close_con() {
        
    }

    /**
     * Set an attribute
     *
     * @param int $attribute
     * @param mixed $value
     * @return bool
     */
    public function setAttribute($attribute, $value) {
        return self::$PDOInstance->setAttribute($attribute, $value);
    }

    /**
     * Prepares a statement for execution and returns a statement object 
     *
     * @param string $statement A valid SQL statement for the target database server
     * @param array $driver_options Array of one or more key=>value pairs to set attribute values for the PDOStatement obj 
      returned
     * @return PDOStatement
     */
    public function prepare($statement, $driver_options = false) {
        if (!$driver_options)
            $driver_options = array();
        return self::$PDOInstance->prepare($statement, $driver_options);
    }

    public function queryInsert($query, $data = null) {
        $this->printQueryEcho($query, $data, 'queryInsert');
        $status = true;
        $response = null;
        $message = '';
        try {
            $resultado = $this->prepare($query);
            $response = $resultado->execute($data);
        } catch (PDOException $exc) {
            $status = false;
            $message = utf8_encode($exc->getMessage());
            if ($this->transactionCounter > 0) {
                $this->transactionStatus = false;
            }
            $this->transactionMessage[] = $message;
            $this->printQuery($message, $query,$data);
        }
        return (object) array('status' => $status, 'response' => $response, 'errorSQL' => $message);
    }

    public function querySelectFetchRowAssoc($query, $data = null) {
        $this->printQueryEcho($query, $data, 'querySelectFetchRowAssoc');
        $status = true;
        $response = false;
        $message = '';
        try {
            $resultado = $this->prepare($query);
            $resultado->execute($data);
            $response = $resultado->fetch(PDO::FETCH_ASSOC);
            if ($response === false) {
                $response = array();
            }
        } catch (PDOException $exc) {
            $status = false;
            $message = utf8_encode($exc->getMessage());
            if ($this->transactionCounter > 0) {
                $this->transactionStatus = false;
            }
            $this->transactionMessage[] = $message;
            $this->printQuery($message, $query,$data);
        }
        $response = !empty($response) ? $response : NULL;
        return (object) array('status' => $status, 'response' => $response, 'errorSQL' => $message);
    }

    public function querySelectFetchAllAssoc($query, $data = null) {
        $this->printQueryEcho($query, $data, 'querySelectFetchAllAssoc');
        $status = true;
        $response = false;
        $message = '';
        try {
            $resultado = $this->prepare($query);
            $resultado->execute($data);
            $response = $resultado->fetchAll(PDO::FETCH_ASSOC);
            if ($response === false) {
                $response = array();
            }
        } catch (PDOException $exc) {
            $status = false;
            $message = utf8_encode($exc->getMessage());
            if ($this->transactionCounter > 0) {
                $this->transactionStatus = false;
            }
            $this->transactionMessage[] = $message;
            $this->printQuery($message, $query,$data);
        }
        $response = !empty($response) ? $response : NULL;
        return (object) array('status' => $status, 'response' => $response, 'errorSQL' => $message);
    }

    public function selectPointer($query, $data = null) {
        $this->printQueryEcho($query, $data, 'selectPointer');
        $status = true;
        $response = false;
        $message = '';
        try {
            $resultado = $this->prepare($query);
            $resultado->execute($data);
            return $resultado;
        } catch (PDOException $exc) {
            $status = false;
            $message = utf8_encode($exc->getMessage());
            if ($this->transactionCounter > 0) {
                $this->transactionStatus = false;
            }
            $this->transactionMessage[] = $message;
            $this->printQuery($message, $query,$data);
        }
        return (object) array('status' => $status, 'response' => $response, 'errorSQL' => $message);
    }

    public function querySelectFetchRowAssocObject($query, $data = null) {
        $this->printQueryEcho($query, $data, 'querySelectFetchRowAssocObject');
        $status = true;
        $response = false;
        $message = '';
        try {
            $resultado = $this->prepare($query);
            $resultado->execute($data);

            $response = $resultado->fetch(PDO::FETCH_OBJ);

            if ($response === false) {
                $response = array();
            }
        } catch (PDOException $exc) {
            $status = false;
            $message = utf8_encode($exc->getMessage());
            if ($this->transactionCounter > 0) {
                $this->transactionStatus = false;
            }
            $this->transactionMessage[] = $message;
        }
        return (object) array('status' => $status, 'response' => $response, 'errorSQL' => $message);
    }

    public function querySelectFetchAllAssocObject($query, $data = null) {
        $this->printQueryEcho($query, $data, 'querySelectFetchAllAssocObject');
        $status = true;
        $response = false;
        $message = '';
        try {
            $resultado = $this->prepare($query);
            $resultado->execute($data);
            $response = $resultado->fetchAll(PDO::FETCH_OBJ);
            if ($response === false) {
                $response = array();
            }
        } catch (PDOException $exc) {
            $status = false;
            $message = utf8_encode($exc->getMessage());
            if ($this->transactionCounter > 0) {
                $this->transactionStatus = false;
            }
            $this->transactionMessage[] = $message;
            $this->printQuery($message, $query,$data);
        }
        return (object) array('status' => $status, 'response' => $response, 'errorSQL' => $message);
    }

    public function executeQuery($query) {
        $this->printQueryEcho($query, null, 'execute');
        $status = true;
        $response = null;
        $message = '';
        try {
            $response = $this->exec($query);
        } catch (PDOException $exc) {
            $status = false;
            $message = utf8_encode($exc->getMessage());
            if ($this->transactionCounter > 0) {
                $this->transactionStatus = false;
            }
            $this->transactionMessage[] = $message;
            $this->printQuery($message, $query, null);
        }
        return (object) array('status' => $status, 'response' => $response, 'errorSQL' => $message);
    }

    public function getFields($query, $data = null) {
        $this->printQueryEcho($query, $data, 'getFields');
        $resultado = false;
        $eof = true;
        $status = true;
        $message = '';
        try {
            $resultado = $this->prepare($query);
            $resultado->execute($data);
            $eof = !($resultado->rowCount() > 0);
        } catch (PDOException $exc) {
            $status = false;
            $message = utf8_encode($exc->getMessage());
            if ($this->transactionCounter > 0) {
                $this->transactionStatus = false;
            }
            $this->transactionMessage[] = $message;
            $this->printQuery($message, $query,$data);
        }
        return (object) array('status' => $status, 'fields' => $resultado->fetch(PDO::FETCH_NUM), 'EOF' => $eof, 'errorSQL' => $message);
    }

    public function getColumFieldsAll($query, $data = null) {
        $this->printQueryEcho($query, $data, 'getColumFieldsAll');
        $resultado = false;
        $eof = true;
        $status = true;
        $message = '';
        try {
            $resultado = $this->prepare($query);
            $resultado->execute($data);
            $eof = !($resultado->rowCount() > 0);
        } catch (PDOException $exc) {
            $status = false;
            $message = utf8_encode($exc->getMessage());
            if ($this->transactionCounter > 0) {
                $this->transactionStatus = false;
            }
            $this->transactionMessage[] = $message;
            $this->printQuery($message, $query,$data);
        }
        return (object) array('status' => $status, 'fields' => $resultado->fetchAll(PDO::FETCH_COLUMN), 'EOF' => $eof, 'errorSQL' => $message);
    }

    private function printQuery($message, $query, $data) {
        $name = "cache/ERRORES_BASE_DATOS";
        if (!file_exists($name)) {
            mkdir($name);
        }

        $result = debug_backtrace();
        $errorDebug = $result['1'];

        $fecha = date("d-m-Y h:i:s");
        $error = "----------------------------------------INICIO FECHA: $fecha----------------------------------------" . PHP_EOL;
        $error .= "ARCHIVO: $errorDebug[file]" . PHP_EOL;
        $error .= "LINEA: $errorDebug[line]" . PHP_EOL;
        $error .= "FUNCION CLASE PDO: $errorDebug[function]" . PHP_EOL;
        $error .= "MENSAJE:" . PHP_EOL . "$message" . PHP_EOL;
        $error .= "CONSULTA:" . PHP_EOL . "$query" . PHP_EOL;
        $error .= "PARAMETROS:" . PHP_EOL . var_export($data, true) . PHP_EOL;
        $error .= "----------------------------------------FIN FECHA: $fecha----------------------------------------" . PHP_EOL;
        $file = fopen($name . "/ErroresConexionPDO.sql", "a+");
        fwrite($file, $error . PHP_EOL);
        fclose($file);

        $host = $_SERVER['HTTP_HOST'];
        if ($host === 'localhost' || $host === 'dusoftdev.cosmitet.net') {
            echo"<pre><hr><br>";
            echo utf8_decode($error);
            echo"</pre><br>";
            die();
        }
    }

    private function printQueryEcho($query, $param, $typeFunction) {
        if ($this->debug) {
            echo"<pre><b>Data[$typeFunction]</b><br>";
            print_r($query);
            echo "<br>";
            print_r($param);
            echo"</pre><br>";
        }
    }

}

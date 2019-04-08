<?php

final class Database
{

    public $connection; // соединение с базой данных
    private $host = 'std-mysql';
    private $user = 'std_247';
    private $password = 'qwerty123';
    private $dbname = 'std_247';
    private $charset = 'utf8';

    function __construct()
    {
        $this->connection = $this->connect();
    }

    public function connect()
    {
        try {
            $connection = new PDO("mysql:host=$this->host;dbname=$this->dbname;charset=$this->charset", $this->user, $this->password);
            return $connection;
        } catch (PDOException $exception) {
            echo 'Connection error: ' . $exception;
            return false;
        }
    }

    public function disconnect()
    {
        $this->connection = null;
    }

    public function isConnected()
    {
        return ($this->connection == null) ? false : true;
    }
}

<?php

namespace app\core;

class ConnectionFactory
{

    private $host;
    private $username;
    private $password;
    private $database;
    private $port;

    private function __construct()
    {
        $this->host = getenv('DB_HOST');
        $this->username = getenv('DB_USERNAME');
        $this->password = getenv('DB_PASSWORD');
        $this->database = getenv('DB_DATABASE');
        $this->port = getenv('DB_PORT');
    }

    public static function getInstance()
    {
        return self::createInstance();
    }

    public function getHost(): ?string
    {
        return $this->host;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getDatabase(): ?string
    {
        return $this->database;
    }

    public function getPort(): ?int
    {
        return $this->port;
    }

    public function setHost(?string $host)
    {
        $this->host = $host;
    }

    public function setUsername(?string $username)
    {
        $this->username = $username;
    }

    public function setPassword(?string $password)
    {
        $this->password = $password;
    }

    public function setDatabase(?string $database)
    {
        $this->database = $database;
    }

    public function setPort(?int $port)
    {
        $this->port = $port;
    }

    public static function build(): ?\mysqli
    {
        $instance = ConnectionFactory::getInstance();
        $connection = null;

        try {
            $connection = new \mysqli($instance->getHost(), $instance->getUsername(),
                $instance->getPassword(), $instance->getDatabase(), $instance->getPort());

            if (\mysqli_connect_errno()) {
                throw new \App\domain\exception\ConnectionException('Failed to connect, due to: ' . \mysqli_connect_error());
            }
        }
        catch (\mysqli_sql_exception $mse) {
            throw new \App\domain\exception\ConnectionException($mse->getMessage());
        }

        return $connection;
    }

    private static function createInstance()
    {
        static $INSTANCE;

        $INSTANCE = new ConnectionFactory();

        return $INSTANCE;
    }
}
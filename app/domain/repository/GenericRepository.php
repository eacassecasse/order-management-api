<?php

namespace app\domain\repository;

use App\core\ConnectionFactory;
use app\domain\exception\ConnectionException;
use App\domain\exception\MYSQLTransactionException;
use App\domain\repository\Generic;

abstract class GenericRepository implements Generic
{
    private $connection;

    public function __construct()
    {
    }

    protected function executeStatement($query = "", $params = []): ?\mysqli_stmt
    {

        try {
            $statement = $this->connect()->prepare($query);

            $types = "";

            if ($params) {
                if (is_array($params)) {
                    foreach ($params as $key => $value) {
                        if (is_string($value)) {
                            $types = $types . 's';
                        }
                        elseif (is_int($value) || is_integer($value)) {
                            $types = $types . 'i';
                        }
                        elseif (is_float($value) || is_double($value)) {
                            $types = $types . 'd';
                        }
                    }
                }

                $statement->bind_param($types, ...$params);
            }

            $statement->execute();

            return $statement;
        }
        catch (\mysqli_sql_exception $ex) {
            throw new MYSQLTransactionException($ex->getMessage());
        }
    }

    protected function select($query = "", $params = []): ?\mysqli_result
    {

        try {
            $statement = $this->executeStatement($query, $params);

            $result = $statement->get_result();
            $statement->close();

            return $result;
        }
        catch (\mysqli_sql_exception $ex) {
            throw new MYSQLTransactionException($ex->getMessage());
        }
    }

    protected function connect()
    {
        try {
            $this->connection = ConnectionFactory::build();
        }
        catch (\mysqli_sql_exception $ex) {
            throw new ConnectionException($ex->getMessage());
        }

        return $this->connection;
    }

}

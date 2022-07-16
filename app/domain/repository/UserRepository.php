<?php

namespace app\domain\repository;

use App\domain\exception\BusinessException;
use App\domain\exception\MYSQLTransactionException;
use App\domain\model\User;
use App\domain\repository\GenericRepository;

$driver = new \mysqli_driver();
$driver->report_mode = \MYSQLI_REPORT_ERROR | \MYSQLI_REPORT_STRICT;


class UserRepository extends GenericRepository
{

    public function __construct()
    {
        parent::__construct();
    }

    public function create($object)
    {

        $connection = $this->connect();
        $connection->autocommit(false);
        $connection->begin_transaction();

        if (!$object instanceof User) {
            throw new BusinessException("Unknown Entity Found");
        }

        $email = $object->getEmail();
        $password = $object->getPassword();

        try {

            $query = "INSERT INTO user
                            (email, password)
                      VALUES 
                            (?,?)";

            $statement = $this->executeStatement(
                $query, array($email, $password)
            );

            $connection->commit();

            if ($statement->insert_id > 0) {
                $user = $this->
                    select("SELECT * FROM user WHERE id = ?", array(
                    $statement->insert_id))->fetch_assoc();
            }
        }
        catch (\mysqli_sql_exception $ex) {
            $connection->rollback();
            throw new MYSQLTransactionException($ex->getMessage());
        }
        finally {
            $statement->close();
        }

        return $user;
    }

    public function findAll(int $page, int $limit, array $sorts)
    {

        try {

            $offset = ($limit * $page) - $limit;

            $query = "SELECT 
                            * 
                        FROM 
                            user 
                        ORDER BY 
                            {$this->getOrderByString($sorts)} 
                        LIMIT ?,?";

            $result = $this->select(
                $query,
                array($offset, $limit)
            );


            if ($result->num_rows === 0) {
                $users = null;
            }

            $users = array();

            while ($user = $result->fetch_assoc()) {
                array_push($users, $user);
            }
        }
        catch (\mysqli_sql_exception $ex) {
            throw new MYSQLTransactionException($ex->getMessage());
        }

        return $users;
    }

    public function findOne($id)
    {
        try {

            $query = "SELECT * FROM user WHERE id = ?";

            $result = $this->select($query, array($id));

            if ($result->num_rows === 0) {
                $user = null;
            }

            $user = $result->fetch_assoc();
        }
        catch (\mysqli_sql_exception $ex) {
            throw new MYSQLTransactionException($ex->getMessage());
        }

        return $user;
    }

    public function findByEmail($email)
    {
        try {

            $query = "SELECT * FROM user WHERE email = ?";

            $result = $this->select($query, array($email));

            if ($result->num_rows === 0) {
                $user = null;
            }

            $user = $result->fetch_assoc();
        }
        catch (\mysqli_sql_exception $ex) {
            throw new MYSQLTransactionException($ex->getMessage());
        }

        return $user;
    }

    public function update($object)
    {

        $connection = $this->connect();
        $connection->autocommit(false);
        $connection->begin_transaction();

        if (!$object instanceof User) {
            throw new BusinessException("Unknown Entity Found");
        }

        $id = $object->getId();
        $email = $object->getEmail();
        $password = $object->getPassword();

        try {

            $query = "UPDATE user 
                      SET 
                        email = ?, 
                        password = ?
                      WHERE 
                        id = ?";

            $statement = $this->executeStatement($query, array(
                $email,
                $password,
                $id)
            );


            $connection->commit();

            if ($statement->affected_rows === 0) {
                $user = null;
            }

            $user = $this->findOne($id);
        }
        catch (\mysqli_sql_exception $ex) {
            $connection->rollback();
            throw new MYSQLTransactionException($ex->getMessage());
        }

        return $user;
    }

    public function delete($id)
    {

        $connection = $this->connect();
        $connection->autocommit(false);
        $connection->begin_transaction();

        try {
            $query = "DELETE FROM user WHERE id = ?";

            $statement = $this->executeStatement($query, array($id));

            if ($statement->affected_rows === 0) {
                return false;
            }

            return true;
        }
        catch (\mysqli_sql_exception $ex) {
            $connection->rollback();
            throw new MYSQLTransactionException($ex->getMessage());
        }
    }

}

<?php

namespace app\domain\repository;

use App\domain\exception\BusinessException;
use app\domain\exception\ConnectionException;
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

    /**
     * @throws MYSQLTransactionException
     * @throws BusinessException
     * @throws ConnectionException
     */
    public function create($object): bool|array|null
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

    /**
     * @throws MYSQLTransactionException
     */
    public function existsByEmail($email): bool|array|null
    {
        try {

            $query = "SELECT * FROM user WHERE email = ?";

            $result = $this->select($query, array($email));

            if ($result->num_rows === 0) {
                return false;
            }

            $user = $result->fetch_assoc();
        }
        catch (\mysqli_sql_exception $ex) {
            throw new MYSQLTransactionException($ex->getMessage());
        }

        return $user;
    }

    /**
     * @throws BusinessException
     * @throws MYSQLTransactionException
     * @throws ConnectionException
     */
    public function update($object): bool|array|null
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

            $user = $this->existsByEmail($email);
        }
        catch (\mysqli_sql_exception | MYSQLTransactionException $ex) {
            $connection->rollback();
            throw new MYSQLTransactionException($ex->getMessage());
        }

        return $user;
    }

    /**
     * @throws MYSQLTransactionException
     * @throws ConnectionException
     */
    public function delete($id): bool
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

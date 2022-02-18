<?php
namespace app\domain\repository;

use App\domain\exception\BusinessException;
use App\domain\exception\MYSQLTransactionException;
use App\domain\model\Storage;
use App\domain\repository\GenericRepository;

$driver = new \mysqli_driver();
$driver->report_mode = \MYSQLI_REPORT_ERROR | \MYSQLI_REPORT_STRICT;

class StorageRepository extends GenericRepository
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

        if (!$object instanceof Storage) {
            throw new BusinessException('Unknown Entity Found');
        }

        $designation = $object->getDesignation();
        $code = $object->getCode();

        try {

            $query = "INSERT INTO storage
                        (designation, code)
                      VALUES (?,?)";

            $statement = $this->executeStatement($query, array(
                $designation,
                $code));

            $connection->commit();

            if ($statement->insert_id > 0) {
                $storage = $this->
                    select("SELECT * FROM storage WHERE id = ?", array(
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

        return $storage;
    }

    public function findAll()
    {

        try {
            $query = "SELECT * FROM storage";

            $result = $this->select($query);

            if ($result->num_rows === 0) {
                $storages = null;
            }

            $storages = array();

            while ($storage = $result->fetch_assoc()) {
                array_push($storages, $storage);
            }
        }
        catch (\mysqli_sql_exception $ex) {
            throw new MYSQLTransactionException($ex->getMessage());
        }

        return $storages;
    }

    public function findOne($id)
    {
        try {

            $query = "SELECT * FROM storage WHERE id = ?";

            $result = $this->select($query, array($id));

            if ($result->num_rows === 0) {
                $storage = null;
            }

            $storage = $result->fetch_assoc();
        }
        catch (\mysqli_sql_exception $ex) {
            throw new MYSQLTransactionException($ex->getMessage());
        }

        return $storage;
    }

    public function findByDesignation($designation)
    {
        try {

            $query = "SELECT * FROM storage WHERE designation = ?";

            $result = $this->select($query, array($designation));

            if ($result->num_rows === 0) {
                $storages = null;
            }

            $storages = array();

            while ($storage = $result->fetch_assoc()) {
                array_push($storages, $storage);
            }
        }
        catch (\mysqli_sql_exception $ex) {
            throw new MYSQLTransactionException($ex->getMessage());
        }

        return $storages;
    }

    public function findByCode($code)
    {
        try {

            $query = "SELECT * FROM storage WHERE code = ?";

            $result = $this->select($query, array($code));

            if ($result->num_rows === 0) {
                $storage = null;
            }

            $storage = $result->fetch_assoc();
        }
        catch (\mysqli_sql_exception $ex) {
            throw new MYSQLTransactionException($ex->getMessage());
        }

        return $storage;
    }

    public function update($object)
    {

        $connection = $this->connect();
        $connection->autocommit(false);
        $connection->begin_transaction();

        if (!$object instanceof Storage) {
            throw new BusinessException("Unknown Entity Found");
        }

        $id = $object->getId();
        $designation = $object->getDesignation();
        $code = $object->getCode();

        try {

            $query = "UPDATE storage 
                      SET 
                        designation = ?, 
                        code = ?
                      WHERE 
                        id = ?";

            $statement = $this->
                executeStatement($query, array(
                $designation,
                $code,
                $id));


            $connection->commit();

            if ($statement->affected_rows === 0) {
                $storage = null;
            }

            $storage = $this->findOne($id);
        }
        catch (\mysqli_sql_exception $ex) {
            $connection->rollback();
            throw new MYSQLTransactionException($ex->getMessage());
        }

        return $storage;
    }

    public function delete($id)
    {

        $connection = $this->connect();
        $connection->autocommit(false);
        $connection->begin_transaction();

        try {
            $query = "DELETE FROM storage WHERE id = ?";

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

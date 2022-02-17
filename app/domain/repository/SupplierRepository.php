<?php

namespace app\domain\repository;

use App\domain\exception\BusinessException;
use App\domain\exception\MYSQLTransactionException;
use App\domain\model\Supplier;
use App\domain\repository\GenericRepository;

$driver = new \mysqli_driver();
$driver->report_mode = \MYSQLI_REPORT_ERROR | \MYSQLI_REPORT_STRICT;

class SupplierRepository extends GenericRepository {

    //put your code here
    public function __construct() {
        parent::__construct();
    }

    public function create($object) {

        $connection = $this->connect();
        $connection->autocommit(false);
        $connection->begin_transaction();

        if (!$object instanceof Supplier) {
            throw new BusinessException("Unknown Entity Found");
        }

        $name = $object->getName();
        $vatNumber = $object->getVatNumber();

        try {

            $query = "INSERT INTO supplier
                   (name, vatNumber)
                    VALUES (?,?)";

            $statement = $this->executeStatement($query, array(
                $name,
                $vatNumber
                    )
            );

            $connection->commit();

            if ($statement->insert_id > 0) {
                $supplier = $this->
                                select("SELECT * FROM supplier WHERE id = ?", array(
                                    $statement->insert_id))->fetch_assoc();
            }
        } catch (\mysqli_sql_exception $ex) {
            $connection->rollback();
            throw new MYSQLTransactionException($ex->getMessage());
        } finally {
            $statement->close();
        }

        return $supplier;
    }

    public function findAll() {

        try {
            $query = "SELECT * FROM supplier";

            $result = $this->select($query);

            if ($result->num_rows === 0) {
                $suppliers = null;
            }

            $suppliers = array();

            while ($supplier = $result->fetch_assoc()) {
                array_push($suppliers, $supplier);
            }
        } catch (\mysqli_sql_exception $ex) {
            throw new MYSQLTransactionException($ex->getMessage());
        }

        return $suppliers;
    }

    public function findOne($id) {
        try {

            $query = "SELECT * FROM supplier WHERE id = ?";

            $result = $this->select($query, array($id));

            if ($result->num_rows === 0) {
                $supplier = null;
            }

            $supplier = $result->fetch_assoc();
        } catch (\mysqli_sql_exception $ex) {
            throw new MYSQLTransactionException($ex->getMessage());
        }

        return $supplier;
    }

    public function findByName($name) {
        try {

            $query = "SELECT * FROM supplier WHERE name = ?";

            $result = $this->select($query, array($name));

            if ($result->num_rows === 0) {
                $supplier = null;
            }

            $supplier = $result->fetch_assoc();
        } catch (\mysqli_sql_exception $ex) {
            throw new MYSQLTransactionException($ex->getMessage());
        }

        return $supplier;
    }

    public function findByVatNumber($vatNumber) {
        try {

            $query = "SELECT * FROM supplier WHERE vatNumber = ?";

            $result = $this->select($query, array($vatNumber));

            if ($result->num_rows === 0) {
                $supplier = null;
            }

            $supplier = $result->fetch_assoc();
        } catch (\mysqli_sql_exception $ex) {
            throw new MYSQLTransactionException($ex->getMessage());
        }

        return $supplier;
    }

    public function update($object) {

        $connection = $this->connect();
        $connection->autocommit(false);
        $connection->begin_transaction();

        if (!$object instanceof Supplier) {
            throw new BusinessException("Unknown Entity Found");
        }

        $id = $object->getId();
        $name = $object->getName();
        $vatNumber = $object->getVatNumber();

        try {

            $query = "UPDATE supplier 
                      SET 
                        name = ?, 
                        vatNumber = ?
                      WHERE 
                        id = ?";

            $statement = $this->
                    executeStatement($query, array(
                $name,
                $vatNumber,
                $id));


            $connection->commit();

            if ($statement->affected_rows === 0) {
                $supplier = null;
            }

            $supplier = $this->findOne($id);
        } catch (\mysqli_sql_exception $ex) {
            $connection->rollback();
            throw new MYSQLTransactionException($ex->getMessage());
        }

        return $supplier;
    }

    public function delete($id) {

        $connection = $this->connect();
        $connection->autocommit(false);
        $connection->begin_transaction();

        try {
            $query = "DELETE FROM supplier WHERE id = ?";

            $statement = $this->executeStatement($query, array($id));

            if ($statement->affected_rows === 0) {
                return false;
            }

            return true;
        } catch (\mysqli_sql_exception $ex) {
            $connection->rollback();
            throw new MYSQLTransactionException($ex->getMessage());
        }
    }

}

<?php

namespace app\domain\repository;

use App\domain\exception\BusinessException;
use app\domain\exception\MYSQLTransactionException;
use App\domain\model\StoredProduct;
use App\domain\repository\GenericRepository;

$driver = new \mysqli_driver();
$driver->report_mode = \MYSQLI_REPORT_ERROR | \MYSQLI_REPORT_STRICT;

class StoredProductRepository extends GenericRepository
{

    //put your code here
    public function __construct()
    {
        parent::__construct();
    }

    public function create($object)
    {

        $connection = $this->connect();
        $connection->autocommit(false);
        $connection->begin_transaction();

        if (!$object instanceof StoredProduct) {
            throw new BusinessException("Unknown Entity Found");
        }

        $productId = $object->getProduct()->getId();
        $storageId = $object->getStorage()->getId();
        $quantity = $object->getQuantity();

        try {

            $query = "INSERT INTO stored_products
                   (product_id, storage_id, quantity)
                    VALUES (?,?,?)";

            $statement = $this->executeStatement($query, array(
                $productId,
                $storageId,
                $quantity));

            $connection->commit();

            $storedProduct = $this->
                select("SELECT * FROM 
                                        stored_products 
                                        WHERE 
                                        product_id = ? 
                                        AND 
                                        storage_id = ?", array(
                $productId, $storageId))->fetch_assoc();
        }
        catch (\mysqli_sql_exception $ex) {
            $connection->rollback();
            throw new MYSQLTransactionException($ex->getMessage());
        }
        finally {
            $statement->close();
        }

        return $storedProduct;
    }

    public function findAll()
    {

        try {
            $query = "SELECT * FROM stored_products";

            $result = $this->select($query);

            if ($result->num_rows === 0) {
                $storedProducts = null;
            }

            $storedProducts = array();

            while ($storedProduct = $result->fetch_assoc()) {
                array_push($storedProducts, $storedProduct);
            }
        }
        catch (\mysqli_sql_exception $ex) {
            throw new MYSQLTransactionException($ex->getMessage());
        }

        return $storedProducts;
    }

    public function findOne($productId, $storageId)
    {
        try {

            $query = "SELECT * FROM 
                      stored_products 
                      WHERE 
                      product_id = ? 
                      AND 
                      storage_id = ?";

            $result = $this->select($query, array($productId, $storageId));

            if ($result->num_rows === 0) {
                $storedProduct = null;
            }

            $storedProduct = $result->fetch_assoc();
        }
        catch (\mysqli_sql_exception $ex) {
            throw new MYSQLTransactionException($ex->getMessage());
        }

        return $storedProduct;
    }

    public function findByProduct(StoredProduct $stored)
    {

        $productId = $stored->getProduct()->getId();

        try {

            $query = "SELECT * FROM 
                      stored_products 
                      WHERE 
                      product_id = ?";

            $result = $this->select($query, array($productId));

            if ($result->num_rows === 0) {
                $storedProducts = null;
            }

            $storedProducts = array();

            while ($storedProduct = $result->fetch_assoc()) {
                array_push($storedProducts, $storedProduct);
            }
        }
        catch (\mysqli_sql_exception $ex) {
            throw new MYSQLTransactionException($ex->getMessage());
        }

        return $storedProducts;
    }

    public function findByStorage(StoredProduct $stored)
    {

        $storageId = $stored->getStorage()->getId();

        try {

            $query = "SELECT * FROM 
                      stored_products 
                      WHERE 
                      storage_id = ?";

            $result = $this->select($query, array($storageId));

            if ($result->num_rows === 0) {
                $storedProducts = null;
            }

            $storedProducts = array();

            while ($storedProduct = $result->fetch_assoc()) {
                array_push($storedProducts, $storedProduct);
            }
        }
        catch (\mysqli_sql_exception $ex) {
            throw new MYSQLTransactionException($ex->getMessage());
        }

        return $storedProducts;
    }

    public function update($object)
    {

        $connection = $this->connect();
        $connection->autocommit(false);
        $connection->begin_transaction();

        if (!$object instanceof StoredProduct) {
            throw new BusinessException("Unknown Entity Found");
        }

        $productId = $object->getProduct()->getId();
        $storageId = $object->getStorage()->getId();
        $quantity = $object->getQuantity();

        try {

            $query = "UPDATE stored_products 
                      SET 
                        quantity = ?
                      WHERE 
                        product_id = ?
                      AND
                        storage_id = ?";

            $statement = $this->
                executeStatement($query, array(
                $quantity,
                $productId,
                $storageId));


            $connection->commit();

            if ($statement->affected_rows === 0) {
                $storedProduct = null;
            }

            $storedProduct = $this->findOne($productId, $storageId);
        }
        catch (\mysqli_sql_exception $ex) {
            $connection->rollback();
            throw new MYSQLTransactionException($ex->getMessage());
        }

        return $storedProduct;
    }

    public function deletebyProduct($productId)
    {

        $connection = $this->connect();
        $connection->autocommit(false);
        $connection->begin_transaction();

        try {
            $query = "DELETE FROM stored_products WHERE product_id = ?";

            $statement = $this->executeStatement($query, array($productId));

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

    public function deletebyStorage($storageId)
    {

        $connection = $this->connect();
        $connection->autocommit(false);
        $connection->begin_transaction();

        try {
            $query = "DELETE FROM stored_products WHERE storage_id = ?";

            $statement = $this->executeStatement($query, array($storageId));

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

    public function deleteOne($productId, $storageId)
    {

        $connection = $this->connect();
        $connection->autocommit(false);
        $connection->begin_transaction();

        try {
            $query = "DELETE FROM stored_products 
                      WHERE 
                      product_id = ? 
                      AND 
                      storage_id = ?";

            $statement = $this->executeStatement($query, array($productId, $storageId));

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

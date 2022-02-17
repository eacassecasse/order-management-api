<?php

namespace app\domain\repository;

use App\domain\exception\BusinessException;
use App\domain\exception\MYSQLTransactionException;
use App\domain\model\SupplierProduct;
use App\domain\repository\GenericRepository;

$driver = new \mysqli_driver();
$driver->report_mode = \MYSQLI_REPORT_ERROR | \MYSQLI_REPORT_STRICT;

class SupplierProductRepository extends GenericRepository
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

        if (!$object instanceof SupplierProduct) {
            throw new BusinessException("Unknown Entity Found");
        }

        $productId = $object->getProduct()->getId();
        $supplierId = $object->getSupplier()->getId();
        $price = $object->getPrice();

        try {

            $query = "INSERT INTO supplier_products
                   (product_id, supplier_id, price)
                    VALUES (?,?,?)";

            $statement = $this->executeStatement($query, array(
                $productId,
                $supplierId,
                $price));

            $connection->commit();

            $supplierProduct = $this->
                select("SELECT * FROM 
                                        supplier_products 
                                    WHERE 
                                        product_id = ? 
                                    AND 
                                        supplier_id = ?", array(
                $productId, $supplierId))->fetch_assoc();
        }
        catch (\mysqli_sql_exception $ex) {
            $connection->rollback();
            throw new MYSQLTransactionException($ex->getMessage());
        }
        finally {
            $statement->close();
        }

        return $supplierProduct;
    }

    public function findAll()
    {

        try {
            $query = "SELECT * FROM supplier_products";

            $result = $this->select($query);

            if ($result->num_rows === 0) {
                $supplierProducts = null;
            }

            $supplierProducts = array();

            while ($supplierProduct = $result->fetch_assoc()) {
                array_push($supplierProducts, $supplierProduct);
            }
        }
        catch (\mysqli_sql_exception $ex) {
            throw new MYSQLTransactionException($ex->getMessage());
        }

        return $supplierProducts;
    }

    public function findOne($productId, $supplierId)
    {
        try {

            $query = "SELECT * FROM 
                      supplier_products 
                      WHERE 
                      product_id = ? 
                      AND 
                      supplier_id = ?";

            $result = $this->select($query, array($productId, $supplierId));

            if ($result->num_rows === 0) {
                $supplierProduct = null;
            }

            $supplierProduct = $result->fetch_assoc();
        }
        catch (\mysqli_sql_exception $ex) {
            throw new MYSQLTransactionException($ex->getMessage());
        }

        return $supplierProduct;
    }

    public function findByProduct(SupplierProduct $supplierProd)
    {

        $productId = $supplierProd->getProduct()->getId();

        try {

            $query = "SELECT * FROM 
                      supplier_products 
                      WHERE 
                      product_id = ?";

            $result = $this->select($query, array($productId));

            if ($result->num_rows === 0) {
                $supplierProducts = null;
            }

            $supplierProducts = array();

            while ($supplierProduct = $result->fetch_assoc()) {
                array_push($supplierProducts, $supplierProduct);
            }
        }
        catch (\mysqli_sql_exception $ex) {
            throw new MYSQLTransactionException($ex->getMessage());
        }

        return $supplierProducts;
    }

    public function findBySupplier(SupplierProduct $supplierProd)
    {

        $supplierId = $supplierProd->getSupplier()->getId();

        try {

            $query = "SELECT * FROM 
                      supplier_products 
                      WHERE 
                      supplier_id = ?";

            $result = $this->select($query, array($supplierId));

            if ($result->num_rows === 0) {
                $supplierProducts = null;
            }

            $supplierProducts = array();

            while ($supplierProduct = $result->fetch_assoc()) {
                array_push($supplierProducts, $supplierProduct);
            }
        }
        catch (\mysqli_sql_exception $ex) {
            throw new MYSQLTransactionException($ex->getMessage());
        }

        return $supplierProducts;
    }

    public function update($object)
    {

        $connection = $this->connect();
        $connection->autocommit(false);
        $connection->begin_transaction();

        if (!$object instanceof SupplierProduct) {
            throw new BusinessException("Unknown Entity Found");
        }

        $productId = $object->getProduct()->getId();
        $supplierId = $object->getSupplier()->getId();
        $price = $object->getPrice();

        try {

            $query = "UPDATE supplier_products 
                      SET 
                        price = ?
                      WHERE 
                        product_id = ?
                      AND
                        supplier_id = ?";

            $statement = $this->
                executeStatement($query, array(
                $price,
                $productId,
                $supplierId));


            $connection->commit();

            if ($statement->affected_rows === 0) {
                $supplierProduct = null;
            }

            $supplierProduct = $this->findOne($productId, $supplierId);
        }
        catch (\mysqli_sql_exception $ex) {
            $connection->rollback();
            throw new MYSQLTransactionException($ex->getMessage());
        }

        return $supplierProduct;
    }

    public function deletebyProduct($productId)
    {

        $connection = $this->connect();
        $connection->autocommit(false);
        $connection->begin_transaction();

        try {
            $query = "DELETE FROM supplier_products WHERE product_id = ?";

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

    public function deletebySupplier($supplierId)
    {

        $connection = $this->connect();
        $connection->autocommit(false);
        $connection->begin_transaction();

        try {
            $query = "DELETE FROM supplier_products WHERE supplier_id = ?";

            $statement = $this->executeStatement($query, array($supplierId));

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

    public function deleteOne($productId, $supplierId)
    {

        $connection = $this->connect();
        $connection->autocommit(false);
        $connection->begin_transaction();

        try {
            $query = "DELETE FROM supplier_products 
                      WHERE 
                      product_id = ? 
                      AND 
                      supplier_id = ?";

            $statement = $this->executeStatement($query, array($productId, $supplierId));

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

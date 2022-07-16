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
                select("SELECT 
                            product0_.id AS product_id, product0_.description, 
                            product0_.measure_unit AS unit, product0_.total_quantity, 
                            supplier_products0_.price, supplier0_.id AS supplier_id, 
                            supplier0_.name, supplier0_.vatNumber, product0_.lowest_price
                        FROM
                            product product0_
                        INNER JOIN 
                            (supplier supplier0_ INNER JOIN supplier_products supplier_products0_)
                        ON
                            (product0_.id = supplier_products0_.product_id 
                        AND 
                            supplier0_.id = supplier_products0_.supplier_id
                        AND
                            supplier_products0_.product_id = ?
                        AND
                            supplier_products0_.supplier_id = ?)", array(
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

    public function findAll(int $supplierId, int $page, int $limit, array $sorts)
    {

        try {

            $offset = ($limit * $page) - $limit;

            $query = "SELECT 
                            product0_.id AS product_id, product0_.description, 
                            product0_.measure_unit AS unit, product0_.total_quantity, 
                            supplier_products0_.price, supplier0_.id AS supplier_id, 
                            supplier0_.name, supplier0_.vatNumber, product0_.lowest_price
                        FROM
                            product product0_
                        INNER JOIN 
                            (supplier supplier0_ INNER JOIN supplier_products supplier_products0_)
                        ON
                            (product0_.id = supplier_products0_.product_id 
                        AND 
                            supplier0_.id = supplier_products0_.supplier_id
                        AND   
                            supplier0_.id = ?)
                        ORDER BY {$this->getOrderByString($sorts)}
                        LIMIT ?, ?";

            $result = $this->select(
                $query,
                array($supplierId, $offset, $limit)
            );


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

    public function findOne($supplierId, $productId)
    {
        try {

            $query = "SELECT 
                            product0_.id AS product_id, product0_.description, 
                            product0_.measure_unit AS unit, product0_.total_quantity, 
                            supplier_products0_.price, supplier0_.id AS supplier_id, 
                            supplier0_.name, supplier0_.vatNumber, product0_.lowest_price
                        FROM
                            product product0_
                        INNER JOIN 
                            (supplier supplier0_ INNER JOIN supplier_products supplier_products0_)
                        ON
                            (product0_.id = supplier_products0_.product_id 
                        AND 
                            supplier0_.id = supplier_products0_.supplier_id
                        AND
                            supplier_products0_.product_id = ?
                        AND
                            supplier_products0_.supplier_id = ?)";

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

    public function findByProduct(int $productId, int $page, int $limit, array $sorts)
    {

        try {

            $offset = ($limit * $page) - $limit;

            $query = "SELECT 
                            product0_.id AS product_id, product0_.description, 
                            product0_.measure_unit AS unit, product0_.total_quantity, 
                            supplier_products0_.price, supplier0_.id AS supplier_id, 
                            supplier0_.name, supplier0_.vatNumber, product0_.lowest_price
                        FROM
                            product product0_
                        INNER JOIN 
                            (supplier supplier0_ INNER JOIN supplier_products supplier_products0_)
                        ON
                            (product0_.id = supplier_products0_.product_id 
                        AND 
                            supplier0_.id = supplier_products0_.supplier_id
                        AND
                            product0_.id = ?)
                        ORDER BY {$this->getOrderByString($sorts)}
                        LIMIT ?, ?";

            $result = $this->select(
                $query,
                array($productId, $offset, $limit)
            );

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


    public function findByParams(int $supplierId, array $options, int $page, int $limit, array $sorts)
    {

        try {

            $offset = ($limit * $page) - $limit;

            $query = "SELECT 
                            product0_.id AS product_id, product0_.description, 
                            product0_.measure_unit AS unit, product0_.total_quantity, 
                            supplier_products0_.price, supplier0_.id AS supplier_id, 
                            supplier0_.name, supplier0_.vatNumber, product0_.lowest_price
                        FROM
                            product product0_
                        INNER JOIN 
                            (supplier supplier0_ INNER JOIN supplier_products supplier_products0_)
                        ON
                            (product0_.id = supplier_products0_.product_id 
                        AND 
                            supplier0_.id = supplier_products0_.supplier_id
                        AND
                            supplier0_.id = ?
                        AND
                            {$this->whereClauseBuilder($options)})
                        ORDER BY {$this->getOrderByString($sorts)}
                        LIMIT ?, ?";

            $result = $this->select(
                $query,
                array($supplierId, $offset, $limit)
            );

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

    public function getTotal(int $id): int
    {
        return $this->getTotalQuantity("supplier_products", "supplier", $id);
    }

    public function getTotalByProduct(int $id): int
    {
        return $this->getTotalQuantity("supplier_products", "product", $id);
    }

}

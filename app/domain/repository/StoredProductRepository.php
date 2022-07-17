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

            $statement = $this->executeStatement(
                $query,
                array($productId, $storageId, $quantity)
            );

            $connection->commit();

            $storedProduct = $this->select(
                "SELECT 
                product0_.id AS product_id, product0_.description, product0_.measure_unit AS unit, 
                product0_.lowest_price, stored_products0_.quantity, storage0_.id AS storage_id, 
                storage0_.designation, storage0_.code, product0_.total_quantity
            FROM
                product product0_
            INNER JOIN 
                (storage storage0_ INNER JOIN stored_products stored_products0_)
            ON
                (product0_.id = stored_products0_.product_id 
            AND 
                storage0_.id = stored_products0_.storage_id
            AND
                stored_products0_.product_id = ?
            AND 
                stored_products0_.storage_id = ?)",
                array($productId, $storageId)
            )->fetch_assoc();
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

    public function findAll(int $storageId, int $page, int $limit, array $sorts)
    {

        try {

            $offset = ($limit * $page) - $limit;

            $query = "SELECT 
                            product0_.id AS product_id, product0_.description, 
                            product0_.measure_unit AS unit, product0_.lowest_price,
                            stored_products0_.quantity, storage0_.id AS storage_id, 
                            storage0_.designation, storage0_.code, product0_.total_quantity
                        FROM
                            product product0_
                        INNER JOIN 
                            (storage storage0_ INNER JOIN stored_products stored_products0_)
                        ON
                            (storage0_.id = ?
                        AND
                            product0_.id = stored_products0_.product_id 
                        AND 
                            storage0_.id = stored_products0_.storage_id)
                        ORDER BY {$this->getOrderByString($sorts)}
                        LIMIT ?, ?";


            $result = $this->select(
                $query,
                array($storageId, $offset, $limit)
            );

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

    public function findOne($storageId, $productId)
    {
        try {

            $query = "SELECT 
                            product0_.id AS product_id, product0_.description, 
                            product0_.measure_unit AS unit, product0_.lowest_price,
                            stored_products0_.quantity, storage0_.id AS storage_id, 
                            storage0_.designation, storage0_.code, product0_.total_quantity
                        FROM
                            product product0_
                        INNER JOIN 
                            (storage storage0_ INNER JOIN stored_products stored_products0_)
                        ON
                            (product0_.id = stored_products0_.product_id 
                        AND 
                            storage0_.id = stored_products0_.storage_id
                        AND
                            stored_products0_.product_id = ?
                        AND 
                            stored_products0_.storage_id = ?)";

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

    public function findByProduct(int $productId, int $page, int $limit, array $sorts)
    {

        try {

            $offset = ($limit * $page) - $limit;

            $query = "SELECT 
                            product0_.id AS product_id, product0_.description, 
                            product0_.measure_unit AS unit, product0_.lowest_price,
                            stored_products0_.quantity, storage0_.id AS storage_id, 
                            storage0_.designation, storage0_.code, product0_.total_quantity
                        FROM
                            product product0_
                        INNER JOIN 
                            (storage storage0_ INNER JOIN stored_products stored_products0_)
                        ON
                            (product0_.id = stored_products0_.product_id 
                        AND 
                            storage0_.id = stored_products0_.storage_id
                        AND
                            product0_.id = ?)
                        ORDER BY {$this->getOrderByString($sorts)}
                        LIMIT ?, ?";

            $result = $this->select(
                $query,
                array($productId, $offset, $limit)
            );


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


    public function findByParams(int $storageId, array $options, int $page, int $limit, array $sorts)
    {

        try {

            $offset = ($limit * $page) - $limit;

            $query = "SELECT 
                            product0_.id AS product_id, product0_.description, 
                            product0_.measure_unit AS unit, product0_.lowest_price,
                            stored_products0_.quantity, storage0_.id AS storage_id, 
                            storage0_.designation, storage0_.code, product0_.total_quantity
                        FROM
                            product product0_
                        INNER JOIN 
                            (storage storage0_ INNER JOIN stored_products stored_products0_)
                        ON
                            (product0_.id = stored_products0_.product_id 
                        AND 
                            storage0_.id = stored_products0_.storage_id
                        AND
                            storage0_.id = ?
                        AND
                            {$this->whereClauseBuilder($options)})
                        ORDER BY {$this->getOrderByString($sorts)}
                        LIMIT ?, ?";

            $result = $this->select(
                $query,
                array($storageId, $offset, $limit)
            );


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

            $statement = $this->executeStatement(
                $query,
                array($quantity, $productId, $storageId)
            );


            $connection->commit();

            if ($statement->affected_rows === 0) {
                $storedProduct = null;
            }

            $storedProduct = $this->findOne($storageId, $productId);
        }
        catch (\mysqli_sql_exception $ex) {
            $connection->rollback();
            throw new MYSQLTransactionException($ex->getMessage());
        }

        return $storedProduct;
    }

    public function deleteOne($storageId, $productId)
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

            $statement = $this->executeStatement(
                $query,
                array($productId, $storageId)
            );

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
        return $this->getTotalQuantity("stored_products", "storage", $id);
    }

    public function getTotalByProduct(int $id): int
    {
        return $this->getTotalQuantity("stored_products", "product", $id);
    }

}

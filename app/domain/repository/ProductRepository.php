<?php

namespace app\domain\repository;

use App\domain\exception\BusinessException;
use App\domain\exception\MYSQLTransactionException;
use App\domain\model\Product;
use App\domain\repository\GenericRepository;

$driver = new \mysqli_driver();
$driver->report_mode = \MYSQLI_REPORT_ERROR | \MYSQLI_REPORT_STRICT;

class ProductRepository extends GenericRepository
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

        if (!$object instanceof Product) {
            throw new BusinessException('Unknown Entity Found');
        }

        $description = $object->getDescription();
        $unit = $object->getUnit();
        $lowestPrice = $object->getLowestPrice();
        $totalQuantity = $object->getTotalQuantity();

        try {

            $query = "INSERT INTO product
                   (description, measure_unit, lowest_price, total_quantity)
                    VALUES (?,?,?,?)";

            $statement = $this->executeStatement(
                $query,
                array($description, $unit, $lowestPrice, $totalQuantity)
            );

            $connection->commit();

            if ($statement->insert_id > 0) {
                $product = $this->select(
                    "SELECT * FROM product WHERE id = ?",
                    array($statement->insert_id)
                )->fetch_assoc();
            }
        }
        catch (\mysqli_sql_exception $ex) {
            $connection->rollback();
            throw new MYSQLTransactionException($ex->getMessage());
        }
        finally {
            $statement->close();
        }

        return $product;
    }

    public function findAll(int $page, int $limit, array $sorts)
    {

        try {

            $offset = ($limit * $page) - $limit;

            $query = "SELECT 
                            * 
                        FROM 
                            product 
                        ORDER BY 
                            {$this->getOrderByString($sorts)} 
                        LIMIT ?,?";

            $result = $this->select(
                $query,
                array($offset, $limit)
            );

            if ($result->num_rows === 0) {
                $products = null;
            }

            $products = array();

            while ($product = $result->fetch_assoc()) {
                array_push($products, $product);
            }

        }
        catch (\mysqli_sql_exception $ex) {
            throw new MYSQLTransactionException($ex->getMessage());
        }

        return $products;
    }

    public function findOne($id)
    {
        try {

            $query = "SELECT * FROM product WHERE id = ?";

            $result = $this->select($query, array($id));

            if ($result->num_rows === 0) {
                $product = null;
            }

            $product = $result->fetch_assoc();
        }
        catch (\mysqli_sql_exception $ex) {
            throw new MYSQLTransactionException($ex->getMessage());
        }

        return $product;
    }

    public function findByParams(array $options, int $page, int $limit, array $sorts)
    {
        try {

            $offset = ($limit * $page) - $limit;

            $query = "SELECT 
                            * 
                        FROM 
                            product 
                        WHERE 
                            {$this->whereClauseBuilder($options)} 
                        ORDER BY 
                            {$this->getOrderByString($sorts)} 
                        LIMIT ?, ?";

            $result = $this->select($query, array($offset, $limit));

            if ($result->num_rows === 0) {
                $products = null;
            }

            $products = array();

            while ($product = $result->fetch_assoc()) {
                array_push($products, $product);
            }
        }
        catch (\mysqli_sql_exception $ex) {
            throw new MYSQLTransactionException($ex->getMessage());
        }

        return $products;
    }

    public function update($object)
    {

        $connection = $this->connect();
        $connection->autocommit(false);
        $connection->begin_transaction();

        if (!$object instanceof Product) {
            throw new BusinessException("Unknown Entity Found");
        }

        $id = $object->getId();
        $description = $object->getDescription();
        $unit = $object->getUnit();
        $lowestPrice = $object->getLowestPrice();
        $totalQuantity = $object->getTotalQuantity();

        try {

            $query = "UPDATE product 
                      SET 
                        description = ?, 
                        measure_unit = ?, 
                        lowest_price = ?, 
                        total_quantity = ? 
                      WHERE 
                        id = ?";

            $statement = $this->executeStatement(
                $query,
                array($description, $unit, $lowestPrice, $totalQuantity, $id)
            );


            $connection->commit();

            if ($statement->affected_rows === 0) {
                $product = null;
            }

            $product = $this->findOne($id);
        }
        catch (\mysqli_sql_exception $ex) {
            $connection->rollback();
            throw new MYSQLTransactionException($ex->getMessage());
        }

        return $product;
    }

    public function delete($id)
    {

        $connection = $this->connect();
        $connection->autocommit(false);
        $connection->begin_transaction();

        try {
            $query = "DELETE FROM product WHERE id = ?";

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

    public function getTotal(): int
    {
        return $this->getTotalQuantity("product");
    }
}

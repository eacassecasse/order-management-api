<?php

namespace App\domain\repository;

use App\core\shared\Utilities;
use App\domain\exception\BusinessException;
use App\domain\exception\MYSQLTransactionException;
use App\domain\model\Order;
use App\domain\repository\GenericRepository;

$driver = new \mysqli_driver();
$driver->report_mode = \MYSQLI_REPORT_ERROR | \MYSQLI_REPORT_STRICT;

class ValidityRepository extends GenericRepository
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

        if (!$object instanceof Order) {
            throw new BusinessException("Unknown Entity Found");
        }

        $productId = $object->getProduct()->getId();
        $expirationDate = Utilities::toDatabaseDatetime($object->getExpirationDate());
        $quantity = $object->getQuantity();

        try {

            $query = "INSERT INTO validities
                   (product_id, expiration_date, quantity)
                    VALUES (?,?,?)";

            $statement = $this->executeStatement($query, array(
                $productId,
                $expirationDate,
                $quantity
            ));

            $connection->commit();

            if ($statement->insert_id > 0) {
                $validity = $this->select("SELECT 
                    product0_.id AS product_id, product0_.description, 
                    product0_.measure_unit AS unit, product0_.total_quantity, 
                    validities0_.id AS validity_id, validities0_.expiration_date, 
                    validities0_.quantity 
                FROM 
                    product product0_ 
                INNER JOIN 
                    (validities validities0_) 
                ON 
                    (validities0_.id = ?
                AND
                    product0_.id = ?
                AND
                    product0_.id = validities0_.product_id)
                ", array(
                    $statement->insert_id, $productId
                ))->fetch_assoc();
            }
        }
        catch (\mysqli_sql_exception $ex) {
            $connection->rollback();
            throw new MYSQLTransactionException($ex->getMessage());
        }
        finally {
            $statement->close();
        }
        return $validity;
    }

    public function findAll(int $productId, int $page, int $limit, array $sorts)
    {

        try {

            $offset = ($limit * $page) - $limit;

            $query = "SELECT 
                            product0_.id AS product_id, product0_.description, 
                            product0_.measure_unit AS unit, product0_.total_quantity, 
                            validities0_.id AS validity_id, validities0_.expiration_date, 
                            validities0_.quantity 
                        FROM 
                            product product0_ 
                        INNER JOIN 
                            (validities validities0_) 
                        ON 
                            (product0_.id = ? 
                        AND
                            product0_.id = validities0_.product_id)
                        ORDER BY {$this->getOrderByString($sorts)}
                        LIMIT ?,?";

            $result = $this->select(
                $query,
                array($productId, $offset, $limit)
            );


            if ($result->num_rows === 0) {
                $validities = null;
            }

            $validities = array();

            while ($validity = $result->fetch_assoc()) {
                array_push($validities, $validity);
            }
        }
        catch (\mysqli_sql_exception $ex) {
            throw new MYSQLTransactionException($ex->getMessage());
        }

        return $validities;
    }

    public function findOne($productId, $validityId)
    {
        try {

            $query = "SELECT 
            product0_.id AS product_id, product0_.description, 
            product0_.measure_unit AS unit, product0_.total_quantity, 
            validities0_.id AS validity_id, validities0_.expiration_date, 
            validities0_.quantity 
        FROM 
            product product0_ 
        INNER JOIN 
            (validities validities0_) 
        ON 
            (product0_.id = ?
        AND
            validities0_.id = ?
        AND
            product0_.id = validities0_.product_id)";

            $result = $this->select($query, array($productId, $validityId));

            if ($result->num_rows === 0) {
                $validity = null;
            }

            $validity = $result->fetch_assoc();
        }
        catch (\mysqli_sql_exception $ex) {
            throw new MYSQLTransactionException($ex->getMessage());
        }

        return $validity;
    }

    public function findByParams(int $productId, array $options, int $page, int $limit, array $sorts)
    {

        try {

            $offset = ($limit * $page) - $limit;

            $query = "SELECT 
                            product0_.id AS product_id, product0_.description, 
                            product0_.measure_unit AS unit, product0_.total_quantity, 
                            validities0_.id AS validity_id, validities0_.expiration_date, 
                            validities0_.quantity 
                        FROM 
                            product product0_ 
                        INNER JOIN 
                            (validities validities0_) 
                        ON 
                            (product0_.id = ?
                        AND
                            {$this->whereClauseBuilder($options)}
                        AND
                            product0_.id = validities0_.product_id)
                        ORDER BY {$this->getOrderByString($sorts)}
                        LIMIT ?,?";

            $result = $this->select(
                $query,
                array($productId, $offset, $limit)
            );

            if ($result->num_rows === 0) {
                $validities = null;
            }

            $validities = array();

            while ($validity = $result->fetch_assoc()) {
                array_push($validities, $validity);
            }
        }
        catch (\mysqli_sql_exception $ex) {
            throw new MYSQLTransactionException($ex->getMessage());
        }

        return $validities;
    }
    public function update($object)
    {

        $connection = $this->connect();
        $connection->autocommit(false);
        $connection->begin_transaction();

        if (!$object instanceof Order) {
            throw new BusinessException("Unknown Entity Found");
        }

        $validity_id = $object->getId();
        $expirationDate = Utilities::toDatabaseDatetime($object->getExpirationDate());
        $productId = $object->getProduct()->getId();
        $quantity = $object->getQuantity();

        try {

            $query = "UPDATE validities 
                      SET 
                        expiration_date = ?, 
                        quantity = ?
                      WHERE 
                        id = ? and product_id = ?";

            $statement = $this->executeStatement($query, array(
                $expirationDate,
                $quantity,
                $validity_id,
                $productId
            ));


            $connection->commit();

            if ($statement->affected_rows === 0) {
                $validity = null;
            }

            $validity = $this->findOne($validity_id, $productId);
        }
        catch (\mysqli_sql_exception $ex) {
            $connection->rollback();
            throw new MYSQLTransactionException($ex->getMessage());
        }

        return $validity;
    }

    public function delete($validityId)
    {

        $connection = $this->connect();
        $connection->autocommit(false);
        $connection->begin_transaction();

        try {
            $query = "DELETE FROM validities WHERE id = ?";

            $statement = $this->executeStatement($query, array($validityId));

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
        return $this->getTotalQuantity("validities", 'product', $id);
    }

}

<?php

namespace app\domain\repository;

use App\core\shared\Utilities;
use App\domain\exception\BusinessException;
use App\domain\exception\MYSQLTransactionException;
use App\domain\model\Product;
use App\domain\model\Validity;
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

        if (!$object instanceof Validity) {
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
                $quantity));

            $connection->commit();

            if ($statement->insert_id > 0) {
                $validity = $this->
                    select("SELECT * FROM validities WHERE id = ? and product_id = ?", array(
                    $statement->insert_id, $productId))->fetch_assoc();
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

    public function findAll()
    {

        try {
            $query = "SELECT * FROM validities";

            $result = $this->select($query);

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

    public function findOne($id, $productId)
    {
        try {

            $query = "SELECT * FROM validities WHERE id = ? and product_id = ?";

            $result = $this->select($query, array($id, $productId));

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

    public function findByExpirationDate(\Datetime $expirationDate)
    {

        $datetime = Utilities::toDatabaseDatetime($expirationDate);

        try {

            $query = "SELECT * FROM validities WHERE expiration_date = ?";

            $result = $this->select($query, array($datetime));

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

    public function findByProduct(Product $product)
    {

        $productId = $product->getId();

        try {

            $query = "SELECT * FROM validities WHERE product_id = ?";

            $result = $this->select($query, array($productId));

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

        if (!$object instanceof Validity) {
            throw new BusinessException("Unknown Entity Found");
        }

        $id = $object->getId();
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

            $statement = $this->
                executeStatement($query, array(
                $expirationDate,
                $quantity,
                $id,
                $productId));


            $connection->commit();

            if ($statement->affected_rows === 0) {
                $validity = null;
            }

            $validity = $this->findOne($id, $productId);
        }
        catch (\mysqli_sql_exception $ex) {
            $connection->rollback();
            throw new MYSQLTransactionException($ex->getMessage());
        }

        return $validity;
    }

    public function delete($id, $productId)
    {

        $connection = $this->connect();
        $connection->autocommit(false);
        $connection->begin_transaction();

        try {
            $query = "DELETE FROM validities WHERE id = ? and product_id = ?";

            $statement = $this->executeStatement($query, array($id, $productId));

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

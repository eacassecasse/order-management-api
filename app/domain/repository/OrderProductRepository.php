<?php

namespace App\domain\repository;

use App\domain\exception\BusinessException;
use app\domain\exception\ConnectionException;
use App\domain\exception\EntityNotFoundException;
use App\domain\exception\MYSQLTransactionException;
use App\domain\model\ProductOrder;
use App\domain\model\SupplierProduct;
use App\domain\repository\GenericRepository;

$driver = new \mysqli_driver();
$driver->report_mode = \MYSQLI_REPORT_ERROR | \MYSQLI_REPORT_STRICT;

class OrderProductRepository extends GenericRepository
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

        if (!$object instanceof ProductOrder) {
            throw new BusinessException("Unable to process such entity");
        }

        $productId = $object->getProduct()->getId();
        $orderId = $object->getOrder()->getId();
        $quantity = $object->getQuantity();

        try {

            $query = "INSERT INTO order_products
                   (order_id, product_id, quantity)
                    VALUES (?,?,?)";

            $statement = $this->executeStatement($query, array(
                $orderId,
                $productId,
                $quantity));

            $connection->commit();

            if ($statement->affected_rows > 0) {
                $orderProduct = $this->
                select("SELECT 
                            order0_.id AS order_id, order0_.number AS order_no,
                            order0_.issue_date AS order_date, order0_.total_price AS order_price,
                            order0_.status AS order_status, product0_.id AS product_id, 
                            product0_.description, product0_.price, order_product0_.quantity 
                        FROM
                            orders order0_
                        INNER JOIN 
                            (products product0_ INNER JOIN order_products order_product0_)
                        ON
                            (order0_.id = order_product0_.order_id 
                        AND 
                            product0_.id = order_product0_.product_id
                        AND
                            order_product0_.order_id = ?
                        AND
                            order_product0_.product_id = ?)", array(
                    $orderId, $productId))->fetch_assoc();
            } else {
                throw new BusinessException("It was not possible to add this product to the given order");
            }
        }
        catch (\mysqli_sql_exception|MYSQLTransactionException $ex) {
            $connection->rollback();
            throw new MYSQLTransactionException($ex->getMessage());
        } catch (ConnectionException $e) {
            throw new ConnectionException($e->getMessage());
        } finally {
            $statement->close();
        }

        return $orderProduct;
    }

    /**
     * @throws MYSQLTransactionException
     * @throws ConnectionException
     * @throws EntityNotFoundException
     */
    public function findAll(int $orderId, int $page, int $limit, array $sorts): array
    {

        try {

            $offset = ($limit * $page) - $limit;

            $query = "SELECT 
                            order0_.id AS order_id, order0_.number AS order_no,
                            order0_.issue_date AS order_date, order0_.total_price AS order_price,
                            order0_.status AS order_status, product0_.id AS product_id, 
                            product0_.description, product0_.price, order_product0_.quantity 
                        FROM
                            orders order0_
                        INNER JOIN 
                            (products product0_ INNER JOIN order_products order_product0_)
                        ON
                            (order0_.id = order_product0_.order_id 
                        AND 
                            product0_.id = order_product0_.product_id
                        AND   
                            order0_.id = ?)
                        ORDER BY {$this->getOrderByString($sorts)}
                        LIMIT ?, ?";

            $result = $this->select(
                $query,
                array($orderId, $offset, $limit)
            );


            if ($result->num_rows === 0) {
                throw new EntityNotFoundException("Could not find any product for the given order");
            }

            $orderProducts = array();

            while ($orderProduct = $result->fetch_assoc()) {
                $orderProducts[] = $orderProduct;
            }
        }
        catch (\mysqli_sql_exception|MYSQLTransactionException $ex) {
            throw new MYSQLTransactionException($ex->getMessage());
        } catch (ConnectionException $ex) {
            throw new ConnectionException($ex->getMessage());
        }

        return $orderProducts;
    }

    /**
     * @throws MYSQLTransactionException
     * @throws ConnectionException
     * @throws EntityNotFoundException
     */
    public function findOne($orderId, $productId): bool|array|null
    {
        try {

            $query = "SELECT 
                            order0_.id AS order_id, order0_.number AS order_no,
                            order0_.issue_date AS order_date, order0_.total_price AS order_price,
                            order0_.status AS order_status, product0_.id AS product_id, 
                            product0_.description, product0_.price, order_product0_.quantity 
                        FROM
                            orders order0_
                        INNER JOIN 
                            (products product0_ INNER JOIN order_products order_product0_)
                        ON
                            (order0_.id = order_product0_.order_id 
                        AND 
                            product0_.id = order_product0_.product_id
                        AND
                            order_product0_.order_id = ?
                        AND
                            order_product0_.product_id = ?)";

            $result = $this->select($query, array($orderId, $productId));

            if ($result->num_rows === 0) {
                throw new EntityNotFoundException("Could not find the given product on the provided order");
            }

            $orderProduct = $result->fetch_assoc();
        }
        catch (\mysqli_sql_exception|MYSQLTransactionException $ex) {
            throw new MYSQLTransactionException($ex->getMessage());
        } catch (ConnectionException $ex) {
            throw new ConnectionException($ex->getMessage());
        }

        return $orderProduct;
    }

    /**
     * @throws MYSQLTransactionException
     * @throws ConnectionException
     * @throws EntityNotFoundException
     */
    public function findByParams(int $orderId, array $options, int $page, int $limit, array $sorts): array
    {

        try {

            $offset = ($limit * $page) - $limit;

            $query = "SELECT 
                            order0_.id AS order_id, order0_.number AS order_no,
                            order0_.issue_date AS order_date, order0_.total_price AS order_price,
                            order0_.status AS order_status, product0_.id AS product_id, 
                            product0_.description, product0_.price, order_product0_.quantity 
                        FROM
                            orders order0_
                        INNER JOIN 
                            (products product0_ INNER JOIN order_products order_products0_)
                        ON
                            (order0_.id = order_products0_.order_id 
                        AND 
                            product0_.id = order_products0_.product_id
                        AND
                            order0_.id = ?
                        AND
                            {$this->whereClauseBuilder($options)})
                        ORDER BY {$this->getOrderByString($sorts)}
                        LIMIT ?, ?";

            $result = $this->select(
                $query,
                array($orderId, $offset, $limit)
            );

            if ($result->num_rows === 0) {
                throw new EntityNotFoundException("Could not find any product with the given params for such order");
            }

            $orderProducts = array();

            while ($orderProduct = $result->fetch_assoc()) {
                $orderProducts[] = $orderProduct;
            }
        }
        catch (\mysqli_sql_exception|MYSQLTransactionException $ex) {
            throw new MYSQLTransactionException($ex->getMessage());
        } catch (ConnectionException $ex) {
            throw new ConnectionException($ex->getMessage());
        }

        return $orderProducts;
    }


    /**
     * @throws MYSQLTransactionException
     * @throws EntityNotFoundException
     * @throws BusinessException
     * @throws ConnectionException
     */
    public function update($object): bool|array|null
    {

        $connection = $this->connect();
        $connection->autocommit(false);
        $connection->begin_transaction();

        if (!$object instanceof ProductOrder) {
            throw new BusinessException("Could not process such entity");
        }

        $orderId = $object->getOrder()->getId();
        $productId = $object->getProduct()->getId();
        $quantity = $object->getQuantity();

        try {

            $query = "UPDATE order_products
                      SET 
                        quantity = ?
                      WHERE 
                        order_id = ?
                      AND
                        product_id = ?";

            $statement = $this->
                executeStatement($query, array(
                $quantity,
                $orderId,
                $productId));


            $connection->commit();

            if ($statement->affected_rows === 0) {
                throw new BusinessException("It was not possible to update such product for the given order");
            }

            $orderProduct = $this->findOne($orderId, $productId);
        }
        catch (\mysqli_sql_exception|MYSQLTransactionException $ex) {
            $connection->rollback();
            throw new MYSQLTransactionException($ex->getMessage());
        } catch (ConnectionException $ex) {
            throw new ConnectionException($ex->getMessage());
        } catch (EntityNotFoundException $ex) {
            throw new EntityNotFoundException($ex->getMessage());
        }

        return $orderProduct;
    }

    /**
     * @throws MYSQLTransactionException
     * @throws ConnectionException
     */
    public function delete($orderId, $productId): bool
    {

        $connection = $this->connect();
        $connection->autocommit(false);
        $connection->begin_transaction();

        try {
            $query = "DELETE FROM order_products 
                      WHERE 
                      order_id = ? 
                      AND 
                      product_id = ?";

            $statement = $this->executeStatement($query, array($orderId, $productId));

            if ($statement->affected_rows === 0) {
                return false;
            }

            return true;
        }
        catch (\mysqli_sql_exception|MYSQLTransactionException $ex) {
            $connection->rollback();
            throw new MYSQLTransactionException($ex->getMessage());
        } catch (ConnectionException $ex) {
            throw new ConnectionException($ex->getMessage());
        }
    }

    /**
     * @throws MYSQLTransactionException
     * @throws ConnectionException
     */
    public function getTotal(int $id): int
    {
        return $this->getTotalQuantity("order_products", "orders", $id);
    }

}

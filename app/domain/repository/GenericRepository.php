<?php

namespace app\domain\repository;

use App\core\ConnectionFactory;
use app\domain\exception\ConnectionException;
use App\domain\exception\MYSQLTransactionException;
use App\domain\repository\Generic;

abstract class GenericRepository implements Generic
{
    private $connection;

    public function __construct()
    {
    }

    protected function executeStatement($query = "", $params = []): ?\mysqli_stmt
    {

        try {
            $statement = $this->connect()->prepare($query);

            $types = "";

            if ($params) {
                if (is_array($params)) {
                    foreach ($params as $value) {
                        if (is_string($value)) {
                            $types = $types . 's';
                        }
                        elseif (is_int($value) || is_integer($value)) {
                            $types = $types . 'i';
                        }
                        elseif (is_float($value) || is_double($value)) {
                            $types = $types . 'd';
                        }
                    }
                }

                $statement->bind_param($types, ...$params);
            }

            $statement->execute();

            return $statement;
        }
        catch (\mysqli_sql_exception $ex) {
            throw new MYSQLTransactionException($ex->getMessage());
        }
    }

    protected function select($query = "", $params = []): ?\mysqli_result
    {

        try {
            $statement = $this->executeStatement($query, $params);

            $result = $statement->get_result();
            $statement->close();

            return $result;
        }
        catch (\mysqli_sql_exception $ex) {
            throw new MYSQLTransactionException($ex->getMessage());
        }
    }

    protected function connect()
    {
        try {
            $this->connection = ConnectionFactory::build();
        }
        catch (\mysqli_sql_exception $ex) {
            throw new ConnectionException($ex->getMessage());
        }

        return $this->connection;
    }

    protected function getTotalQuantity(string $tableName, string $referencedTable = '', int $id = 0): int
    {

        if ($referencedTable !== '') {
            $query = "SELECT
                            count(*) as num_rows
                        FROM "
                . $tableName
                . " WHERE "
                . $referencedTable . "_id = ?";

            $params = array($id);
            $result = $this->select($query, $params);
        }
        else {
            $query = "select count(*) as num_rows from " . $tableName;
            $result = $this->select($query);
        }

        $totalQuantity = 0;


        if ($result->num_rows !== 0) {
            $totalQuantity = ($result->fetch_assoc())['num_rows'];
        }

        return $totalQuantity;
    }
    protected function getOrderByString(?array $sorts): ?string
    {

        $connection = $this->connect();

        $orderString = "";

        foreach ($sorts as $sort) {


            foreach ($sort as $key => $value) {
                if ($key == 1 || ($key % 2 == 1)) {
                    $orderString .= " " . strtoupper($connection->real_escape_string($value));
                }
                else {
                    $orderString .= ", " . $connection->real_escape_string($value);
                }
            }
        }

        return ltrim($orderString, ",");
    }

    protected function whereClauseBuilder(array $array)
    {
        if (isset($array) && count($array) > 0) {

            $connection = $this->connect();

            $finalClause = "(";

            $opmodeor = isset($array['opmodeor']) ? filter_var($array['opmodeor'], FILTER_VALIDATE_BOOLEAN) : false;

            foreach ($array as $key => $ar) {

                $explodedQueryParam = explode('_', $key);

                if (count($explodedQueryParam) === 3) {
                    $fieldName = ($explodedQueryParam[2] === 'ormode') ? $explodedQueryParam[0] . '_'
                        . $explodedQueryParam[1] : $explodedQueryParam[0] . '_' . $explodedQueryParam[1]
                        . '_' . $explodedQueryParam[2];
                    $ormode = ($explodedQueryParam[2] === 'ormode') ? true : false;
                }
                elseif (count($explodedQueryParam) === 2) {
                    $fieldName = ($explodedQueryParam[1] === 'ormode') ? 
                        $fieldName = $explodedQueryParam[0] :
                        $fieldName = $explodedQueryParam[0] . '_' . $explodedQueryParam[1];

                    $ormode = ($explodedQueryParam[1] === 'ormode') ? true : false;
                }
                else {
                    $fieldName = $explodedQueryParam[0];
                    $ormode = false;
                }

                if (is_array($ar)) {
                    $finalClause .= '(';
                    if ($fieldName === 'expiration_date') {
                        $finalClause .= $fieldName . " BETWEEN";
                    }

                    foreach ($ar as $val) {

                        $explodedQueryParamValue = explode('~', $val);

                        $value = isset($explodedQueryParamValue[1]) ? ($this->evaluator($explodedQueryParamValue[2]) ? 
                            $this->evaluator($explodedQueryParamValue[2]) :
                            filter_var($explodedQueryParamValue[2], FILTER_SANITIZE_FULL_SPECIAL_CHARS)) :
                            ($this->evaluator($explodedQueryParamValue[0]) ? 
                            $this->evaluator($explodedQueryParamValue[0]) :
                            filter_var($explodedQueryParamValue[0], FILTER_SANITIZE_FULL_SPECIAL_CHARS));

                        $operator = isset($explodedQueryParamValue[1]) ? 
                            filter_var($explodedQueryParamValue[1], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : '';

                        $operationMode = $ormode ? 'OR' : 'AND';

                        if (is_float($value) || is_int($value)) {
                            switch ($operator) {
                                case 'eq':
                                    $finalClause .= $fieldName . " = " . $connection->real_escape_string($value);
                                    break;
                                case 'neq':
                                    $finalClause .= $fieldName . " != " . $connection->real_escape_string($value);
                                    break;
                                case 'lt':
                                    $finalClause .= $fieldName . " < " . $connection->real_escape_string($value);
                                    break;
                                case 'lteq':
                                    $finalClause .= $fieldName . " <= " . $connection->real_escape_string($value);
                                    break;
                                case 'gt':
                                    $finalClause .= $fieldName . " > " . $connection->real_escape_string($value);
                                    break;
                                case 'gteq':
                                    $finalClause .= $fieldName . " >= " . $connection->real_escape_string($value);
                                    break;
                            }
                        }
                        elseif ($value instanceof \DateTime) {
                            // TODO Add business logic for between operation
                            $finalClause .= " '" . $connection->real_escape_string($value->format('Y-m-d H:i:s')) . "'";
                        }
                        else {
                            switch ($operator) {
                                case '':
                                    $finalClause .= $fieldName . " LIKE '" . $connection->real_escape_string($value) . "'";
                                    break;
                                case 'eq':
                                    $finalClause .= $fieldName . " = '" . $connection->real_escape_string($value) . "'";
                                    break;
                                case 'neq':
                                    $finalClause .= $fieldName . " <> '" . $connection->real_escape_string($value) . "'";
                                    break;
                                case 'sw':
                                    $finalClause .= $fieldName . " LIKE '" . $connection->real_escape_string($value) . "%'";
                                    break;
                                case 'ew':
                                    $finalClause .= $fieldName . " LIKE '%" . $connection->real_escape_string($value) . "'";
                                    break;
                            }
                        }

                        $finalClause .= " " . $operationMode . " ";
                    }

                    $finalClause = rtrim($finalClause, " " . $operationMode) . ")";
                }
                else {
                    if ($fieldName !== 'opmodeor') {
                        $finalClause .= "(";

                        $explodedQueryParamValue = explode('~', $ar);

                        $value = isset($explodedQueryParamValue[1]) ? ($this->evaluator($explodedQueryParamValue[2]) ? 
                            $this->evaluator($explodedQueryParamValue[2]) :
                            filter_var($explodedQueryParamValue[2], FILTER_SANITIZE_FULL_SPECIAL_CHARS)) :
                            ($this->evaluator($explodedQueryParamValue[0]) ? 
                            $this->evaluator($explodedQueryParamValue[0]) :
                            filter_var($explodedQueryParamValue[0], FILTER_SANITIZE_FULL_SPECIAL_CHARS));

                        $operator = isset($explodedQueryParamValue[1]) ? 
                            filter_var($explodedQueryParamValue[1], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : '';

                        if (is_float($value) || is_int($value)) {
                            switch ($operator) {
                                case 'eq':
                                    $finalClause .= $fieldName . " = " . $connection->real_escape_string($value);
                                    break;
                                case '':
                                    $finalClause .= $fieldName . " = " . $connection->real_escape_string($value);
                                    break;
                                case 'neq':
                                    $finalClause .= $fieldName . " != " . $connection->real_escape_string($value);
                                    break;
                                case 'lt':
                                    $finalClause .= $fieldName . " < " . $connection->real_escape_string($value);
                                    break;
                                case 'lteq':
                                    $finalClause .= $fieldName . " <= " . $connection->real_escape_string($value);
                                    break;
                                case 'gt':
                                    $finalClause .= $fieldName . " > " . $connection->real_escape_string($value);
                                    break;
                                case 'gteq':
                                    $finalClause .= $fieldName . " >= " . $connection->real_escape_string($value);
                                    break;
                            }
                        }
                        elseif ($value instanceof \DateTime) {
                            if ($fieldName === 'expiration_date') {
                                switch ($operator) {
                                    case '':
                                        $finalClause .= $fieldName . " = '" . $connection->real_escape_string($value->format('Y-m-d H:i:s')) . "'";
                                        break;
                                    case 'eq':
                                        $finalClause .= $fieldName . " = '" . $connection->real_escape_string($value->format('Y-m-d H:i:s')) . "'";
                                        break;
                                    case 'neq':
                                        $finalClause .= $fieldName . " <> '" . $connection->real_escape_string($value->format('Y-m-d H:i:s')) . "'";
                                        break;
                                    case 'lt':
                                        $finalClause .= $fieldName . " < '" . $connection->real_escape_string($value->format('Y-m-d H:i:s')) . "'";
                                        break;
                                    case 'gt':
                                        $finalClause .= $fieldName . " > " . $connection->real_escape_string($value->format('Y-m-d H:i:s')) . "'";
                                        break;
                                }
                            }
                        }
                        else {
                            switch ($operator) {
                                case '':
                                    $finalClause .= $fieldName . " LIKE '" . $connection->real_escape_string($value) . "'";
                                    break;
                                case 'eq':
                                    $finalClause .= $fieldName . " = '" . $connection->real_escape_string($value) . "'";
                                    break;
                                case 'neq':
                                    $finalClause .= $fieldName . " <> '" . $connection->real_escape_string($value) . "'";
                                    break;
                                case 'sw':
                                    $finalClause .= $fieldName . " LIKE '" . $connection->real_escape_string($value) . "%'";
                                    break;
                                case 'ew':
                                    $finalClause .= $fieldName . " LIKE '%" . $connection->real_escape_string($value) . "'";
                                    break;
                            }
                        }

                        $finalClause .= ")";
                    }
                    else {
                        continue;
                    }
                }

                $generalOperationMode = $opmodeor ? 'OR' : 'AND';

                $finalClause .= " " . $generalOperationMode . " ";
            }

            $finalClause = " " . rtrim($finalClause, " " . $generalOperationMode) . ")";

            return $finalClause;
        }
    }
    private function evaluator($value): mixed
    {
        if (filter_var($value, FILTER_VALIDATE_INT)) {
            return filter_var($value, FILTER_VALIDATE_INT);
        }
        elseif (filter_var($value, FILTER_VALIDATE_FLOAT)) {
            return filter_var($value, FILTER_VALIDATE_FLOAT);
        }
        elseif (filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN);
        }
        elseif (\App\core\shared\Utilities::isDate($value)) {
            return new \DateTime($value);
        }
        else {
            return false;
        }
    }


}

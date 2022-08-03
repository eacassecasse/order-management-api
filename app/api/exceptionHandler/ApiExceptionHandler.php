<?php

namespace app\api\exceptionHandler;


use App\domain\exception\BusinessException;
use App\domain\exception\ConnectionException;
use App\domain\exception\EntityNotFoundException;
use App\domain\exception\MYSQLTransactionException;

interface Problem
{

    public function getType();

    public function getDescription();
    public function getDatetime();

    public function getCode();

    public function setType($type);

    public function setDescription($description);

    public function setDatetime($datetime);

    public function setCode($code);
}

$problem = new class implements Problem, \JsonSerializable {

    private $type;
    private $description;
    private $datetime;
    private $code;

    public function __construct()
    {

    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDatetime($datetime)
    {
        $this->datetime = $datetime;
    }

    public function getDatetime()
    {
        return $this->datetime;
    }

    public function setCode($code)
    {
        $this->code = $code;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function jsonSerialize(): mixed
    {
        return [
        'code' => $this->code,
        'type' => $this->type,
        'description' => $this->description,
        'date&time' => $this->datetime->format('d-m-Y H:i:s TZ')
        ];
    }

};

$GLOBALS['problem'] = $problem;
class ApiExceptionHandler
{
    public static function handleEntityNotFoundException(EntityNotFoundException $entityNotFoundException)
    {

        $problem = $GLOBALS['problem'];

        if ($problem instanceof Problem) {
            $problem->setType("resource_not_found");
            $problem->setDescription($entityNotFoundException->getMessage());
            $problem->setDatetime(new \DateTime());
            $problem->setCode(404);
        }

        return $problem;
    }


    public static function handleMYSQLTransactionException(MYSQLTransactionException $mysqlTransactionException)
    {

        $problem = $GLOBALS['problem'];

        if ($problem instanceof Problem) {
            $problem->setType("database_fetch_failure");
            $problem->setDescription($mysqlTransactionException->getMessage());
            $problem->setDatetime(new \DateTime());
            $problem->setCode(418);
        }

        return $problem;
    }

    public static function handleConnectionException(ConnectionException $connectionException)
    {

        $problem = $GLOBALS['problem'];

        if ($problem instanceof Problem) {
            $problem->setType("request_failed");
            $problem->setDescription($connectionException->getMessage());
            $problem->setDatetime(new \DateTime());
            $problem->setCode(500);
        }

        return $problem;
    }

    public static function handleBusinessException(BusinessException $businessException)
    {

        $problem = $GLOBALS['problem'];

        if ($problem instanceof Problem) {
            $problem->setDescription($businessException->getMessage());
            $problem->setDatetime(new \DateTime());
            $problem->setCode(400);
        }

        return $problem;
    }

    public static function handleMethodNotSupported($message, $method)
    {
        $problem = $GLOBALS['problem'];

        if ($problem instanceof Problem) {
            $problem->setType("method_not_supported");
            $problem->setDescription($message . ' {' . $method . '}');
            $problem->setDatetime(new \DateTime());
            $problem->setCode(405);
        }

        return $problem;
    }
}
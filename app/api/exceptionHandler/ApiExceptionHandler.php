<?php

namespace app\api\exceptionHandler;


use App\domain\exception\BusinessException;
use App\domain\exception\ConnectionException;
use App\domain\exception\EntityNotFoundException;
use App\domain\exception\MYSQLTransactionException;

interface Problem
{

    public function getTitle();

    public function getDatetime();

    public function getStatus();

    public function setTitle($title);

    public function setDatetime($datetime);

    public function setStatus($status);
}

$problem = new class implements Problem, \JsonSerializable {

    private $title;
    private $datetime;
    private $status;

    public function __construct()
    {

    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getDatetime()
    {
        return $this->datetime;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function setDatetime($datetime)
    {
        $this->datetime = $datetime;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function jsonSerialize()
    {
        return [
        'status' => $this->getStatus(),
        'title' => $this->getTitle(),
        'date&time' => $this->getDatetime()
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
            $problem->setTitle($entityNotFoundException->getMessage());
            $problem->setDatetime(new \DateTime());
            $problem->setStatus(404);
        }

        return $problem;
    }


    public static function handleMYSQLTransactionException(MYSQLTransactionException $mysqlTransactionException)
    {

        $problem = $GLOBALS['problem'];

        if ($problem instanceof Problem) {
            $problem->setTitle($mysqlTransactionException->getMessage());
            $problem->setDatetime(new \DateTime());
            $problem->setStatus(400);
        }

        return $problem;
    }

    public static function handleConnectionException(ConnectionException $connectionException)
    {

        $problem = $GLOBALS['problem'];

        if ($problem instanceof Problem) {
            $problem->setTitle($connectionException->getMessage());
            $problem->setDatetime(new \DateTime());
            $problem->setStatus(500);
        }

        return $problem;
    }

    public static function handleBusinessException(BusinessException $businessException)
    {

        $problem = $GLOBALS['problem'];

        if ($problem instanceof Problem) {
            $problem->setTitle($businessException->getMessage());
            $problem->setDatetime(new \DateTime());
            $problem->setStatus(400);
        }

        return $problem;
    }

    public static function handleMethodNotSupported($message, $method)
    {
        $problem = $GLOBALS['problem'];

        if ($problem instanceof Problem) {
            $problem->setTitle($message . ' {' . $method . '}');
            $problem->setDatetime(new \DateTime());
            $problem->setStatus(405);
        }

        return $problem;
    }
}
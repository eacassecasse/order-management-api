<?php

namespace app\domain\exception;

class BusinessException extends \Exception
{
    public function __construct($message)
    {
        parent::__construct($message);
    }
}

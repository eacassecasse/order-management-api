<?php

namespace app\domain\exception;

class EntityNotFoundException extends \App\domain\exception\BusinessException
{
    public function __construct($message)
    {
        parent::__construct($message);
    }
}
<?php

namespace App\api\model;

class UserIdInputModel
{

    private string $id;

    public function __construct()
    {

    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id)
    {
        $this->id = $id;
    }

}

<?php

namespace App\api\model;

class ProductIdInputModel
{

    private int $id;

    public function __construct()
    {

    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id)
    {
        $this->id = $id;
    }

}

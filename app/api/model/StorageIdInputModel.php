<?php

namespace app\api\model;

class StorageIdInputModel
{

    private $id;

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

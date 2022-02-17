<?php

namespace app\api\model;

class ProductInputModel
{

    private $description;
    private $unit;

    public function __construct()
    {

    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getUnit(): ?string
    {
        return $this->unit;
    }

    public function setDescription(?string $description)
    {
        $this->description = $description;
    }

    public function setUnit(?string $unit)
    {
        $this->unit = $unit;
    }

}

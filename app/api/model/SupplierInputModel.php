<?php

namespace app\api\model;

class SupplierInputModel
{

    private $name;
    private $vatNumber;

    public function __construct()
    {

    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getVatNumber(): ?int
    {
        return $this->vatNumber;
    }

    public function setName(?string $name)
    {
        $this->name = $name;
    }

    public function setVatNumber(?int $vatNumber)
    {
        $this->vatNumber = $vatNumber;
    }

}

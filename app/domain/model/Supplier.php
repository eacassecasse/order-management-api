<?php

namespace app\domain\model;

use App\domain\model\ObjectModel;

class Supplier extends ObjectModel
{

    private $id;
    private $name;
    private $vatNumber;
    private $products = [];

    public function __construct()
    {

    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getVatNumber(): ?int
    {
        return $this->vatNumber;
    }

    public function getProducts(): ?array
    {
        return $this->products;
    }

    public function setId(?int $id)
    {
        $this->id = $id;
    }

    public function setName(?string $name)
    {
        $this->name = $name;
    }

    public function setVatNumber(?int $vatNumber)
    {
        $this->vatNumber = $vatNumber;
    }

    public function setProducts(?array $products)
    {
        $this->products = $products;
    }

}

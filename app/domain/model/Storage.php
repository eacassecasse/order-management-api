<?php

namespace app\domain\model;

use App\domain\model\ObjectModel;

class Storage extends ObjectModel
{

    private $id;
    private $designation;
    private $code;
    private $products = [];

    public function __construct()
    {

    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDesignation(): ?string
    {
        return $this->designation;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function getProducts(): ?array
    {
        return $this->products;
    }

    public function setId(?int $id)
    {
        $this->id = $id;
    }

    public function setDesignation(?string $designation)
    {
        $this->designation = $designation;
    }

    public function setCode(?string $code)
    {
        $this->code = $code;
    }

    public function setProducts(?array $products)
    {
        $this->products = $products;
    }

}

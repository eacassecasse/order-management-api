<?php

namespace app\api\model;


use App\api\model\ProductIdInputModel;
use App\api\model\SupplierIdInputModel;

class SupplierProductInputModel
{

    private $supplier;
    private $product;
    private $price;

    public function __construct()
    {

    }

    public function getSupplier(): ?SupplierIdInputModel
    {
        return $this->supplier;
    }

    public function getProduct(): ?ProductIdInputModel
    {
        return $this->product;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setSupplier(?SupplierIdInputModel $supplier)
    {
        $this->supplier = $supplier;
    }

    public function setProduct(?ProductIdInputModel $product)
    {
        $this->product = $product;
    }

    public function setPrice(?float $price)
    {
        $this->price = $price;
    }

}

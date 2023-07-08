<?php

namespace App\api\model;

use App\api\model\ProductIdInputModel;

class ValidityInputModel
{

    private string $expirationDate;
    private float $quantity;
    private ProductIdInputModel $product;

    public function __construct()
    {

    }

    public function getExpirationDate(): ?string
    {
        return $this->expirationDate;
    }

    public function getQuantity(): ?float
    {
        return $this->quantity;
    }

    public function getProduct(): ?ProductIdInputModel
    {
        return $this->product;
    }

    public function setExpirationDate(?string $expirationDate)
    {
        $this->expirationDate = $expirationDate;
    }

    public function setQuantity(?float $quantity)
    {
        $this->quantity = $quantity;
    }

    public function setProduct(?ProductIdInputModel $product)
    {
        $this->product = $product;
    }

}

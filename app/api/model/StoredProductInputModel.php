<?php

namespace App\api\model;

use App\api\model\ProductIdInputModel;
use App\api\model\StorageIdInputModel;

class StoredProductInputModel
{

    private StorageIdInputModel $storage;
    private ProductIdInputModel $product;
    private float $quantity;

    public function __construct()
    {

    }

    public function getStorage(): ?StorageIdInputModel
    {
        return $this->storage;
    }

    public function getProduct(): ?ProductIdInputModel
    {
        return $this->product;
    }

    public function getQuantity(): ?float
    {
        return $this->quantity;
    }

    public function setStorage(?StorageIdInputModel $storage)
    {
        $this->storage = $storage;
    }

    public function setProduct(?ProductIdInputModel $product)
    {
        $this->product = $product;
    }

    public function setQuantity(?float $quantity)
    {
        $this->quantity = $quantity;
    }

}

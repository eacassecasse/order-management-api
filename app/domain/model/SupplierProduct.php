<?php

namespace app\domain\model;

use App\domain\model\ObjectModel;
use App\domain\model\Product;
use App\domain\model\Supplier;

class SupplierProduct extends ObjectModel
{

    private $price;
    private $supplier;
    private $product;

    public function __construct()
    {

    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function getSupplier(): ?Supplier
    {
        return $this->supplier;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setPrice(?float $price)
    {
        $this->price = $price;
    }

    public function setSupplier(?Supplier $supplier)
    {
        $this->supplier = $supplier;
    }

    public function setProduct(?Product $product)
    {
        $this->product = $product;
    }

    public function __equals($object): ?bool
    {

        if ($object == null) {
            return false;
        }

        if ($this == $object) {
            return true;
        }

        if (!$object instanceof SupplierProduct) {
            return false;
        }

        return $object->getProduct()->getId() == $this->getProduct()->getId() &&
            $object->getSupplier()->getId() == $this->getSupplier()->getId();
    }

}

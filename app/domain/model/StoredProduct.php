<?php

namespace app\domain\model;

use App\domain\model\ObjectModel;

class StoredProduct extends ObjectModel
{

    private $quantity;
    private $product;
    private $storage;

    public function __construct()
    {

    }

    public function getQuantity(): ?float
    {
        return $this->quantity;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function getStorage(): ?Storage
    {
        return $this->storage;
    }

    public function setQuantity(?float $quantity)
    {
        $this->quantity = $quantity;
    }

    public function setProduct(?Product $product)
    {
        $this->product = $product;
    }

    public function setStorage(?Storage $storage)
    {
        $this->storage = $storage;
    }

    public function __equals($object): ?bool
    {

        if ($object == null) {
            return false;
        }

        if ($this == $object) {
            return true;
        }

        if (!$object instanceof StoredProduct) {
            return false;
        }

        return $object->getProduct()->getId() == $this->getProduct()->getId() &&
            $object->getStorage()->getId() == $this->getStorage()->getId();
    }

}

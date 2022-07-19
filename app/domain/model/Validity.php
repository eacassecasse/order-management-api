<?php

namespace app\domain\model;

use App\domain\model\ObjectModel;
use App\domain\model\Product;

class Validity extends ObjectModel
{

    private $id;
    private $expirationDate;
    private $quantity;
    private $product;

    public function __construct()
    {

    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getExpirationDate(): ?\DateTime
    {
        return $this->expirationDate;
    }

    public function getQuantity(): ?float
    {
        return $this->quantity;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setId(?int $id)
    {
        $this->id = $id;
    }

    public function setExpirationDate(?\DateTime $expirationDate)
    {
        $this->expirationDate = $expirationDate;
    }

    public function setQuantity(?float $quantity)
    {
        $this->quantity = $quantity;
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

        if (!$object instanceof Validity) {
            return false;
        }

        return $object->getExpirationDate() == $this->getExpirationDate() &&
            $object->getProduct()->getId() == $this->getProduct()->getId();
    }

}

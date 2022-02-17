<?php

namespace app\domain\model;

use App\domain\model\ObjectModel;

class Product extends ObjectModel
{

    private $id;
    private $description;
    private $unit;
    private $lowestPrice;
    private $totalQuantity;
    private $validities = [];
    private $suppliers = [];
    private $storages = [];

    public function __construct()
    {

    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getUnit(): ?string
    {
        return $this->unit;
    }

    public function getLowestPrice(): ?float
    {
        return $this->lowestPrice;
    }

    public function getTotalQuantity(): ?float
    {
        return $this->totalQuantity;
    }

    public function getValidities(): ?array
    {
        return $this->validities;
    }

    public function getSuppliers(): ?array
    {
        return $this->suppliers;
    }

    public function getStorages(): ?array
    {
        return $this->storages;
    }

    public function setId(?int $id)
    {
        $this->id = $id;
    }

    public function setDescription(?string $description)
    {
        $this->description = $description;
    }

    public function setUnit(?string $unit)
    {
        $this->unit = $unit;
    }

    public function setLowestPrice(?float $lowestPrice)
    {
        $this->lowestPrice = $lowestPrice;
    }

    public function setTotalQuantity(?float $totalQuantity)
    {
        $this->totalQuantity = $totalQuantity;
    }

    public function setValidities(?array $validities)
    {
        $this->validities = $validities;
    }

    public function setSuppliers(?array $suppliers)
    {
        $this->suppliers = $suppliers;
    }

    public function setStorages(?array $storages)
    {
        $this->storages = $storages;
    }

    public function __equals($object): ?bool
    {

        if ($object == null) {
            return false;
        }

        if ($this == $object) {
            return true;
        }

        if (!$object instanceof Product) {
            return false;
        }

        return $object->getDescription() == $this->getDescription() &&
            $object->getUnit() == $this->getUnit();
    }

}

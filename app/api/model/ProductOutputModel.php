<?php

namespace app\api\model;

use JMS\Serializer\Annotation as Serializer;
use Hateoas\Configuration\Annotation as Hateoas;

/**
 * @Serializer\XmlRoot("product")
 * 
 * @Hateoas\Relation("self", href="expr('/v1/endpoints/products/' ~object.getId())")
 * 
 * @Hateoas\Relation("validities", href="expr('/v1/endpoints/products/' ~object.getId() ~'/validities')")
 * 
 * @Hateoas\Relation("suppliers", href="expr('/v1/endpoints/products/' ~object.getId() ~'/suppliers')")
 * 
 * @Hateoas\Relation("storages", href="expr('/v1/endpoints/products/' ~object.getId() ~'/storages')")
 */

class ProductOutputModel implements \JsonSerializable
{

    /** @Serializer\XmlAttribute */
    private $id;
    private $description;
    private $unit;
    private $lowestPrice;
    private $totalQuantity;

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

    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'description' => $this->getDescription(),
            'unit' => $this->getUnit(),
            'lowestPrice' => $this->getLowestPrice(),
            'totalQuantity' => $this->getTotalQuantity()
        ];
    }

}

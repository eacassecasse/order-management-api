<?php

namespace App\api\model;

use App\api\model\ProductOutputModel;
use App\api\model\SupplierOutputModel;

use JMS\Serializer\Annotation as Serializer;
use Hateoas\Configuration\Annotation as Hateoas;

/**
 * @Serializer\XmlRoot("supplierProducts")
 * 
 * @Hateoas\Relation("self", href = "expr('/v1/endpoints/suppliers/' ~object.getSupplier().getId() ~'/products/'~object.getProduct().getId())")
 * 
 * @Hateoas\Relation("supplier", href = "expr('/v1/endpoints/suppliers/' ~object.getSupplier().getId())",
 * embedded = "expr(object.getSupplier())", exclusion= @Hateoas\Exclusion(excludeIf = 
 * "expr(object.getSupplier() === null)"))
 * 
 * @Hateoas\Relation("product", href = "expr('/v1/endpoints/products/' ~object.getProduct().getId())",
 * embedded = "expr(object.getProduct())", exclusion= @Hateoas\Exclusion(excludeIf = 
 * "expr(object.getProduct() === null)"))
 */
class SupplierProductOutputModel implements \JsonSerializable
{

    /** @Serializer\Exclude */
    private $product;

    /** @Serializer\Exclude */
    private $supplier;
    private $price;

    public function __construct()
    {

    }

    public function getProduct(): ?ProductOutputModel
    {
        return $this->product;
    }

    public function getSupplier(): ?SupplierOutputModel
    {
        return $this->supplier;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setProduct(?ProductOutputModel $product)
    {
        $this->product = $product;
    }

    public function setSupplier(?SupplierOutputModel $supplier)
    {
        $this->supplier = $supplier;
    }

    public function setPrice(?float $price)
    {
        $this->price = $price;
    }

    public function jsonSerialize(): array
    {
        return [
            'product' => $this->getProduct(),
            'supplier' => $this->getSupplier(),
            'price' => $this->getPrice()
        ];
    }

}

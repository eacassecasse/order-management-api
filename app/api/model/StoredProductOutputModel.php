<?php

namespace App\api\model;

use App\api\model\ProductOutputModel;
use App\api\model\StorageOutputModel;

use JMS\Serializer\Annotation as Serializer;
use Hateoas\Configuration\Annotation as Hateoas;

/**
 * @Serializer\XmlRoot("storedProducts")
 * 
 * @Hateoas\Relation("self", href = "expr('/v1/endpoints/storages/' ~object.getStorage().getId() ~'/products/' ~object.getProduct().getId())")
 * 
 * @Hateoas\Relation("storage" , href = "expr('/v1/endpoints/storages/' ~object.getStorage().getId())",
 * embedded = "expr(object.getStorage())", exclusion = @Hateoas\Exclusion(excludeIf = 
 * "expr(object.getStorage() === null)"))
 * 
 *  @Hateoas\Relation("product" , href = "expr('/v1/endpoints/products/' ~object.getProduct().getId())",
 * embedded = "expr(object.getProduct())", exclusion = @Hateoas\Exclusion(excludeIf = 
 * "expr(object.getProduct() === null)"))
 */
class StoredProductOutputModel implements \JsonSerializable
{

    /** @Serializer\Exclude */
    private $storage;

    /** @Serializer\Exclude */
    private $product;
    private $quantity;

    public function __construct()
    {

    }

    public function getStorage(): ?StorageOutputModel
    {
        return $this->storage;
    }

    public function getProduct(): ?ProductOutputModel
    {
        return $this->product;
    }

    public function getQuantity(): ?float
    {
        return $this->quantity;
    }

    public function setStorage(?StorageOutputModel $storage)
    {
        $this->storage = $storage;
    }

    public function setProduct(?ProductOutputModel $product)
    {
        $this->product = $product;
    }

    public function setQuantity(?float $quantity)
    {
        $this->quantity = $quantity;
    }

    public function jsonSerialize(): array
    {
        return [
            'product' => $this->getProduct(),
            'storage' => $this->getStorage(),
            'quantity' => $this->getQuantity()
        ];
    }

}

<?php
namespace App\api\model;

use App\api\model\ProductOutputModel;

use JMS\Serializer\Annotation as Serializer;
use Hateoas\Configuration\Annotation as Hateoas;


/**
 * @Serializer\XmlRoot("validities")
 * 
 * @Hateoas\Relation("self", href="expr('/v1/endpoints/products/' ~object.getProduct().getId() ~'/validities/' ~object.getId())")
 * 
 * @Hateoas\Relation("product", href="expr('/v1/endpoints/products/'~object.getProduct().getId())",
 * embedded = "expr(object.getProduct())", exclusion = @Hateoas\Exclusion(excludeIf = 
 * "expr(object.getProduct() === null)"))
 */
class ValidityOutputModel implements \JsonSerializable
{

    /** @Serializer\XmlAttribute */
    private $id;
    private $expirationDate;
    private $quantity;
    /** @Serializer\Exclude */
    private $product;

    public function __construct()
    {

    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getExpirationDate(): ?string
    {
        return $this->expirationDate;
    }

    public function getQuantity(): ?float
    {
        return $this->quantity;
    }

    public function getProduct(): ?ProductOutputModel
    {
        return $this->product;
    }

    public function setId(?int $id)
    {
        $this->id = $id;
    }

    public function setExpirationDate(?string $expirationDate)
    {
        $this->expirationDate = $expirationDate;
    }

    public function setQuantity(?float $quantity)
    {
        $this->quantity = $quantity;
    }

    public function setProduct(?ProductOutputModel $product)
    {
        $this->product = $product;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'expirationDate' => $this->getExpirationDate(),
            'quantity' => $this->getQuantity(),
            'product' => $this->getProduct()
        ];
    }

}

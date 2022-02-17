<?php

namespace app\api\model;

use JMS\Serializer\Annotation as Serializer;
use Hateoas\Configuration\Annotation as Hateoas;

/**
 * @Serializer\XmlRoot("supplier")
 * 
 * @Hateoas\Relation("self", href="expr('/v1/endpoints/suppliers/' ~object.getId())")
 */
class SupplierOutputModel implements \JsonSerializable
{

    /** @Serializer\XmlAttribute */
    private $id;
    private $name;
    private $vatNumber;

    public function __construct()
    {

    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getVatNumber(): ?int
    {
        return $this->vatNumber;
    }

    public function setId(?int $id)
    {
        $this->id = $id;
    }

    public function setName(?string $name)
    {
        $this->name = $name;
    }

    public function setVatNumber(?int $vatNumber)
    {
        $this->vatNumber = $vatNumber;
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'vatNumber' => $this->getVatNumber()
        ];
    }

}

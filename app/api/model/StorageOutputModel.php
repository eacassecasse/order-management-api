<?php

namespace app\api\model;

use JMS\Serializer\Annotation as Serializer;
use Hateoas\Configuration\Annotation as Hateoas;

/**
 * @Serializer\XmlRoot("storage")
 * 
 * @Hateoas\Relation("self", href = "expr('/v1/endpoints/storages/' ~object.getId())")
 */
class StorageOutputModel implements \JsonSerializable
{

    /** @Serializer\XmlAttribute */
    private $id;
    private $designation;
    private $code;

    public function __construct()
    {

    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDesignation(): ?string
    {
        return $this->designation;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setId(?int $id)
    {
        $this->id = $id;
    }

    public function setDesignation(?string $designation)
    {
        $this->designation = $designation;
    }

    public function setCode(?string $code)
    {
        $this->code = $code;
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'designation' => $this->getDesignation(),
            'code' => $this->getCode()
        ];
    }

}

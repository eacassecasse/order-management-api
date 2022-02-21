<?php

namespace app\domain\model;

use App\domain\model\ObjectModel;

class User extends ObjectModel
{

    private $id;
    private $email;
    private $password;

    public function __construct()
    {

    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setId(?int $id)
    {
        $this->id = $id;
    }

    public function setEmail(?string $email)
    {
        $this->email = $email;
    }

    public function setPassword(?string $password)
    {
        $this->password = $password;
    }

    public function __equals($object): ?bool
    {

        if ($object == null) {
            return false;
        }

        if ($this == $object) {
            return true;
        }

        if (!($object instanceof User)) {
            return false;
        }

        return $object->getEmail() == $this->getEmail();
    }

}
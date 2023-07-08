<?php

namespace App\domain\model;

class Usuario extends ObjectModel
{

    private string $id;
    private string $nome;
    private string $email;
    private int $pin;

    public function __construct()
    {
    }

    /**
     * @return mixed
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getNome(): string
    {
        return $this->nome;
    }

    /**
     * @param mixed $nome
     */
    public function setNome(string $nome): void
    {
        $this->nome = $nome;
    }

    /**
     * @return mixed
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getPin(): int
    {
        return $this->pin;
    }

    /**
     * @param mixed $pin
     */
    public function setPin(int $pin): void
    {
        $this->pin = $pin;
    }

    public function __equals($object): ?bool
    {

        if ($object == null) {
            return false;
        }

        if ($this == $object) {
            return true;
        }

        if (!($object instanceof Usuario)) {
            return false;
        }

        return $object->getEmail() == $this->getEmail();
    }

}
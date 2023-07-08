<?php

namespace App\domain\model;


class Product extends ObjectModel
{

    private string $id;
    private string $descricao;
    private float $preco;

    public function __construct()
    {

    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getDescricao(): string
    {
        return $this->descricao;
    }

    public function getPreco(): ?float
    {
        return $this->preco;
    }

    public function setId(string $id)
    {
        $this->id = $id;
    }

    public function setDescricao(string $descricao)
    {
        $this->descricao = $descricao;
    }

    public function setPreco(float $preco)
    {
        $this->preco = $preco;
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

        return $object->getDescricao() == $this->getDescricao() &&
            $object->getPreco() == $this->getPreco();
    }

}

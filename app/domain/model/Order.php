<?php

namespace App\domain\model;

use DateTime;

class Order extends ObjectModel
{

    private string $id;
    private DateTime $datahora;
    private int $numero;
    private float $precoTotal;
    private $status;
    private Usuario $usuario;
    private array $produtos;

    public function __construct()
    {
        $this->produtos = array();
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return DateTime
     */
    public function getDatahora(): DateTime
    {
        return $this->datahora;
    }

    /**
     * @param DateTime $datahora
     */
    public function setDatahora(DateTime $datahora): void
    {
        $this->datahora = $datahora;
    }

    /**
     * @return int
     */
    public function getNumero(): int
    {
        return $this->numero;
    }

    /**
     * @param int $numero
     */
    public function setNumero(int $numero): void
    {
        $this->numero = $numero;
    }

    /**
     * @return float
     */
    public function getPrecoTotal(): float
    {
        return $this->precoTotal;
    }

    /**
     * @param float $precoTotal
     */
    public function setPrecoTotal(float $precoTotal): void
    {
        $this->precoTotal = $precoTotal;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status): void
    {
        $this->status = $status;
    }

    /**
     * @return Usuario
     */
    public function getUsuario(): Usuario
    {
        return $this->usuario;
    }

    /**
     * @param Usuario $usuario
     */
    public function setUsuario(Usuario $usuario): void
    {
        $this->usuario = $usuario;
    }

    /**
     * @return array
     */
    public function getProdutos(): array
    {
        return $this->produtos;
    }

    /**
     * @param array $produtos
     */
    public function setProdutos(array $produtos): void
    {
        $this->produtos = $produtos;
    }


    public function __equals($object): ?bool
    {

        if ($object == null) {
            return false;
        }

        if ($this == $object) {
            return true;
        }

        if (!$object instanceof Order) {
            return false;
        }

        return $object->getNumero() == $this->getNumero() &&
            $object->getUsuario()->getId() == $this->getUsuario()->getId();
    }

}

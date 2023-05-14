<?php

namespace App\api\model;

class UserInputModel
{

    private string $email;
    private string $password;

    public function __construct()
    {

    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setEmail(?string $email)
    {
        $this->email = $email;
    }

    public function setPassword(?string $password)
    {
        $this->password = $password;
    }

}

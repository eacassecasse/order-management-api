<?php

namespace app\domain\repository;

interface Generic
{

    public function create($object);

    public function findAll();

    public function update($object);
}

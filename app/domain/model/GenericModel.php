<?php

namespace app\domain\model;

interface GenericModel {

    public function __equals($object): ?bool;
}

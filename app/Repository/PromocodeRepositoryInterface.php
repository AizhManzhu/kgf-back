<?php

namespace App\Repository;

interface PromocodeRepositoryInterface extends CrudInterface
{
    public function getPromo(string $promo);
}

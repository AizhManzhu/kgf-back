<?php
namespace App\Repository;

interface UserRepositoryInterface extends  CrudInterface{
    public function updatePartData($id, $array);
}
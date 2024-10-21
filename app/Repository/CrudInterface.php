<?php
namespace App\Repository;

interface CrudInterface
{
    public function read();
    public function create($model);
    public function readById($id);
    public function update($id, $array);
    public function delete($id);
}

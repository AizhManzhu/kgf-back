<?php

namespace App\Repository;

use App\Http\Constants;
use App\Models\Promocode;

class PromocodeRepository implements PromocodeRepositoryInterface {

    public $promocode;

    public function __construct(Promocode $promocode) {
        $this->promocode = $promocode;
    }

    public function read()
    {
        return $this->promocode->query()->with('usedMember')->paginate(Constants::PAGINATE);
    }

    public function create($model)
    {
        return $this->promocode->create($model);
    }

    public function readById($id)
    {
        return $this->promocode->find($id);
    }

    public function update($id, $array)
    {
        return $this->promocode->query()->where('id', $id)->update($array);
    }

    public function delete($id)
    {
        return $this->promocode->query()->find($id)->delete();
    }

    public function getPromo(string $promo) {
        return $this->promocode->query()->where('code', $promo)->first();
    }
}

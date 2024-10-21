<?php
namespace App\Services;

use App\Models\Promocode;
use App\Repository\PromocodeRepository;

class PromocodeService {
    public string|null $promocode;
    public $model;
    public PromocodeRepository $promocodeRepository;

    public function __construct($promocode)
    {
        $this->promocode = $promocode;
        $this->reset();
    }

    public function reset() {
        $this->promocodeRepository = new PromocodeRepository(new Promocode());
    }

    public function execute()
    {
        $result = $this->checkPromocode();
        if ($result['is_valid']) {
            $result['promocode_id'] = $this->setPromo();
        }
        return $result;
    }

    /**
     * @return array
     */
    public function checkPromocode(): array
    {
        $isValid = false;
        $message = "Промокод не действителен";

        $this->model = $this->promocodeRepository->getPromo($this->promocode);
        if ($this->model && $this->model->has_access)
        {
            if (($this->model->type == Promocode::MULTI && $this->model->is_activated) ||
                ($this->model->type == Promocode::SINGLE && !$this->model->is_activated)) {
                $isValid = true;
                $message = "Промокод действителен";
            }

        }

        return [
            "is_valid" => $isValid,
            "promocode_id" => null,
            "message" => $message
        ];
    }


    public function setPromo()
    {
        $this->model->is_activated=1;
        if ($this->model->type == Promocode::MULTI)
           $this->model->used+=1;
        else
            $this->model->used = 1;

        $this->model->save();
        return $this->model->id;
    }
}

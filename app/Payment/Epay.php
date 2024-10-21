<?php

namespace App\Payment;

use Illuminate\Http\Request;
use Throwable;

class Epay implements Payment
{
    const ID = 800000;
    public int $id;
    public string $email;
    public int $amount;
    public string $backLink = 'https://kgforum.kz';
    public string $failureLink = 'https://kgf.cic.kz/api/v1/epay/fail';
    public string $successLink = 'https://kgf.cic.kz/api/v1/epay/success';

    public function init(Request $request) {
        foreach ($request->except('hash') as $key => $value) {
            $this->{$key} = $value;
        }
    }

    /**
     * @throws Throwable
     */
    public function pay() {
        try {
            self::beforePay();
        } catch (\Exception $e) {
            return abort(401);
        }
        return view('payment.new_epay')
            ->with('id', $this->id)
            ->with('amount', $this->amount)
            ->with('email', $this->email)
            ->with('successLink', $this->successLink)
            ->with('backLink', $this->backLink)
            ->with('failureBackLink', $this->failureLink);
    }

    /**
     * @throws Throwable
     */
    private function beforePay()
    {
        throw_if(empty($this->email), 'Email is not defined');
        throw_if(empty($this->amount), 'Amount is not defined');
        throw_if(empty($this->id), 'Order id is not defined');
        throw_if(empty($this->successLink), 'Success link is not defined');
    }

    public function isAvailable(Request $request): array
    {
        $hash = md5($request->id.env("EPAY_AUTH"));

        if ($request->get('hash') !== $hash) {
            return [false, "Forbidden"];
        }

        return [true, "Success"];
    }

    public function fail()
    {
        return abort(401);
    }
}

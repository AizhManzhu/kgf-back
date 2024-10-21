<?php

namespace App\Payment;

use App\Models\EventMember;
use App\Models\Transaction;
use App\Traits\Helpers;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Throwable;

class Kaspi implements Payment
{
    use Helpers;

    const COMMAND_PAY = 'pay';
    const COMMAND_CHECK = 'check';

    protected string $systemName = 'kaspi';

    public int $txnId;
    public string $txnDate;
    public string $account;
    public string $command;
    public string $comment;
    public float $sum;
    public int $result;

    private Builder|Collection|Model|null $eventMember;
    private int $status = 0;

    public function init(Request $request)
    {
        foreach ($request->all() as $key => $value)
            $this->{$this->camelize($key)} = $value;
    }

    /**
     * @throws Throwable
     */
    public function pay()
    {
        self::beforePay();
        $function = 'execute' . ucfirst($this->command);
        return response()->json($this->$function());
    }

    private function executeCheck(): array
    {
        $result = [
            'txn_id' => $this->txnId,
            'result' => $this->status,
            'comment' => '',
        ];

        if ($this->status == 0) {
            $this->eventMember->load('event');

            $result['sum'] = number_format((float)$this->eventMember->event->price, 2, '.', '');

            $result['fields'] = [
                'field1' => [
                    '@name' => "Название форума: ",
                    '#text' => $this->eventMember->event->title,
                ]
            ];
        }

        return $result;
    }

    private function executePay()
    {
        $this->eventMember->paid = 1;
        $this->eventMember->save();

        $transaction = Transaction::query()->create([
            'merchant_id' => env("KASPI_MERCHANT_ID"),
            'payment_system' => $this->systemName,
            'order_id' => $this->eventMember->id,
            'amount' => $this->sum,
            'status' => 'success',
            'external_transaction_id' => $this->txnId,
            'payment_date' => date('Y-m-d h:i:s', strtotime($this->txnDate)),
        ]);

        try {
            return [
                'txn_id' => $this->txnId,
                'result' => $this->status,
                'prv_txn' => $transaction->id,
                'comment' => '',
            ];
        } catch (\Exception $e) {
            Log::channel('payment')->error('------------------------------');
            Log::channel('payment')->error($e->getMessage());
            Log::channel('payment')->error($e->getTraceAsString());
            Log::channel('payment')->error('------------------------------');
            return [
                'txn_id' => $this->txnId,
                'prv_txn' => $transaction->id,
                'result' => 5,
                'comment' => 'Что-то пошло на стороне провайдера',
            ];
        }
    }

    /**
     * @throws Throwable
     */
    private function beforePay()
    {
        $this->eventMember = EventMember::query()
            ->find($this->account);

        if (!$this->eventMember) {
            $this->status = 1;
        } else {
            if ($this->eventMember->paid === 1) {
                $this->status = 3;
            }
        }
    }

    public function isAvailable(Request $request): array
    {
        if (!isset($this->command)) {
            return [false, "'command' is required"];
        }
        if (!isset($this->txnId)) {
            return [false, "'txn_id' is required"];
        }
        if (!isset($this->account)) {
            return [false, "'account' is required"];
        }

        if ($this->command === self::COMMAND_PAY) {
            if (!isset($this->txnDate)) {
                return [false, "'txn_date' is required"];
            }
            if (!isset($this->sum)) {
                return [false, "'sum' is required"];
            }
        }
//        Log::debug($request->ip());
//        Log::debug('User IP Address - ' . $this->getIPAddress());
//        if ($this->getIPAddress() !== env("KASPI_HOST")) {
//            return [false, "Forbidden"];
//        }

        return [true, ""];
    }

    public function fail(...$args): JsonResponse
    {
        return response()->json([
            'txn_id' => $this->txnId??0,
            'result' => 5,
            'comment' => $args[0],
        ]);
    }

//    private function getIPAddress()
//    {
//        //whether ip is from the share internet
//        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
//            $ip = $_SERVER['HTTP_CLIENT_IP'];
//            Log::debug("HTTP_CLIENT_IP: ".$_SERVER['HTTP_CLIENT_IP']);
//        } //whether ip is from the proxy
//        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
//            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
//            Log::debug("HTTP_X_FORWARDED_FOR: ".$_SERVER['HTTP_X_FORWARDED_FOR']);
//        } //whether ip is from the remote address
//        else {
//            $ip = $_SERVER['REMOTE_ADDR'];
//            Log::debug("REMOTE_ADDR: ".$_SERVER['REMOTE_ADDR']);
//        }
//        Log::debug($_SERVER);
//        return $ip;
//    }
}

<?php
namespace App\Http\Controllers\Api\v1;

use App\Http\Constants;
use App\Http\Controllers\Controller;
use App\Models\Transaction;

class TransactionController extends Controller
{
    public function index()
    {
        $transactions = Transaction::query()->with(['eventMember' => function($query) {
            $query->with(['member', 'event']);
        }])->paginate(Constants::PAGINATE);
        return $this->handleResponse($transactions);
    }

    public function show(Transaction $transaction)
    {
        return $this->handleResponse($transaction);
    }
}

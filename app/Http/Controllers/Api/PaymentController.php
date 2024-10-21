<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EventMember;
use App\Repository\TelegramRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

require_once(app_path('Payment') . "/kkb.utils.php");


class PaymentController extends Controller
{

    const AUTH_TOKEN = '/k19&ajk';
    public function pay($app, Request $request)
    {
        $hash = md5($request->id.self::AUTH_TOKEN);
        if ($request->hash != $hash) {
            abort(401);
        }
        if ($app === 'epay') {
            $eventMembers = EventMember::with('member')->find($request->id);
            $id = 990000 + $eventMembers->id;
            $email = $eventMembers->member->email;
            return view('payment.epay', compact('id', 'email'));
        } else {
            return abort(404);
        }
    }

    public function success($app, Request $request)
    {
        if ($app === 'epay') {
            Log::debug($request->all());
            $result = process_response(stripslashes($request->response), public_path(env('EPAY_CERT_PATH'))."/config.txt");

            if (is_array($result)) {

                if ($result["ORDER_ORDER_ID"] > 0) {
                    $eventMember = EventMember::with('member')->find($result['ORDER_ORDER_ID']-990000);
                    $eventMember->paid = 1;
                    $eventMember->is_activated=1;
                    $eventMember->save();
                    (new TelegramRepository())->sendActivatedMessage($eventMember->member, $eventMember);
                    //setResp($result["ORDER_ORDER_ID"], $export);
                }
                if (in_array("ERROR", $result) && in_array(@$result["ERROR_TYPE"], array('system', 'auth'))) {
                    $resp = "error_response " . ($result["ERROR_TYPE"] == 'system' ? 'system' : 'auth') . " " . $result["ERROR_CODE"];
                    Log::channel('payment')->error($resp);
                } elseif (!in_array("ERROR", $result) && in_array("DOCUMENT", $result)) {
                    if ($result['CHECKRESULT'] != '[SIGN_GOOD]') {
                        $resp = "sign_error " . $result['CHECKRESULT'];
                    } elseif ($result['PAYMENT_RESPONSE_CODE'] != '00') {
                        $resp = "error  PAYMENT_RESPONSE_CODE" . $result['PAYMENT_RESPONSE_CODE'];
                    } else {
                        Log::channel('payment')->notice('success payment ' . $result['ORDER_AMOUNT']);
                        Log::channel('payment')->notice($result);
                        echo 0;
/// здесь зупускаем скрипт который выполняет действия при успешной оплате
                        //  setAgr($result["ORDER_ORDER_ID"]);
                    }
                } else {
                    Log::channel('payment')->error('something went wrong ' . $result);
//                file_put_contents('logepay.log', $export . "\n \n", FILE_APPEND);
                }
            } else {
                Log::channel('payment')->error('bad response ' . $result);
            }
        } else {
            abort(404);
        }
    }

    public function fail($app, Request $request)
    {

    }
}

<?php
namespace App\Repository;

use Illuminate\Http\JsonResponse;

trait Base
{
    public function handleResponse($result, $msg = 'Success'): JsonResponse
    {
        $res = [
            'success' => true,
            'data'    => $result,
            'message' => $msg,
        ];
        return response()->json($res, 200);
    }

    public function handleError($errorMsg, $error = [], $code = 404): JsonResponse
    {
        $res = [
            'success' => false,
            'message' => $errorMsg,
        ];
        if(!empty($error)){
            $res['data'] = $error;
        }
        return response()->json($res, $code);
    }
}

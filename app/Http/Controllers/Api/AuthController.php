<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Validator;


class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $authAttempt = Auth::attempt(['email' => $request->email, 'password' => $request->password]);
        if($authAttempt){
            $auth = Auth::user();
            $success['token'] =  $auth->createToken('LaravelSanctumAuth')->plainTextToken;
            $success['name'] =  $auth->name;

            return $this->handleResponse($success, 'User logged-in!');
        }
        else{
            return $this->handleError('Введен неверный e-mail или пароль', ['error'=>'']);
        }
    }

    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'confirm_password' => 'required|same:password',
        ]);

        if($validator->fails()){
            return $this->handleError($validator->errors());
        }

        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        $success['token'] =  $user->createToken('LaravelSanctumAuth')->plainTextToken;
        $success['name'] =  $user->name;

        return $this->handleResponse($success, 'User successfully registered!');
    }

    public function logout()
    {
        Auth::guard('web')->logout();
        return $this->handleResponse(true, 'User logged-out!');
    }


}

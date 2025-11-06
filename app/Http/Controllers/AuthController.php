<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Validators\AuthValidator;
use App\Services\AuthService;
use App\Models\User;


class AuthController extends Controller
{
    public function login (Request $request) {
        return view('auth.login');
    }

    public function authenticate (Request $request) {
        $validator = AuthValidator::loginValidator($request->all());

        if ($validator !== true)
        {
            $message = ['error' => $validator->errors()->all()];
            return response()->json(["status" => false, "code" => 401, "message" => $message, "data" => ''], 401);
        }

        $loginWithEmail = [
            'email' => $request->user_name,
            'password' => $request->password,
        ];

        $loginWithUsername = [
            'user_name' => $request->user_name,
            'password' => $request->password,
        ];

        if (auth()->attempt($loginWithEmail) || auth()->attempt($loginWithUsername)) 
        {
            $user = auth()->user();

            if ($user->status == 'ACTIVE')
            {
                $token = $user->createToken('authToken')->accessToken;
                $data = ['authenticated_user' => $user, 'token' => $token];
                return response()->json(["status" => true, "code" => 200, "message" => 'Login Successfully', "data" => $data], 200);
            }
            else 
            {
                $message = ['error' => 'Username or password is invalid'];
                return response()->json(["status" => false, "code" => 401, "message" => "Login Denied", "data" => $message], 401);
            }
        }
        else 
        {
            $message = ['error' => 'Username or password is invalid'];
            return response()->json(["status" => false, "code" => 401, "message" => "Login Denied", "data" => $message], 401);
        }
    }


    public function changePassword (Request $request) {
        $data = $request->all();

        $validator = AuthValidator::changePasswordValidator($data);

        if ($validator !== true)
        {
            $error = ['error' => $validator->errors()->all()];
            return response()->json(["status" => false, "code" => 401, "message" => "Incorrect Request Data", "data" => $error], 401);
        }

        $response = AuthService::changePasswordProcess($data);
        $response = json_decode($response);

        return response()->json(["status" => $response->status, "code" => $response->status_code, "message" => $response->message, "data" => $response->data], $response->status_code);
    }


    public function logout (Request $request) {
        $token = $request->user()->token();
        $token->revoke();
        $response = ['message' => 'You have been successfully logged out!'];
        return response($response, 200);
    }
}

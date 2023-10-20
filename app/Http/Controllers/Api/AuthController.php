<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {


        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required|min:6'
        ]);
        if (Auth::guard()->attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();
            $user['image'] = url('/public/uploads/user') . '/' . $user->image;
            $user['role_name'] = $user->rolesnamedata->role_slug;
            $token = $user->createToken($user->id)->accessToken;

            $response['status'] = true;
            $response['message'] = "Success";
            $response['token'] = $token->token;
            $user->token = $token->token;
            $response['data'] = $user;
        } else {
            $response['status'] = false;
            $response['message'] = "Unsuccess";
        }
        return response()->json($response);
    }



    public function checkUpdatedUserValue($userId)
    {
        $user = User::find($userId);
        if ($user) {
            
            $user['image'] = url('/public/uploads/user') . '/' . $user->image;
            $user['role_name'] = $user->rolesnamedata->role_slug;
            $token = $user->createToken($user->id)->accessToken;

            $response['status'] = true;
            $response['message'] = "Success";
            $response['token'] = $token->token;
            $user->token = $token->token;
            $response['data'] = $user;
        } else {
            $response['status'] = false;
            $response['message'] = "Unsuccess";
        }
        return response()->json($response);
    }
}
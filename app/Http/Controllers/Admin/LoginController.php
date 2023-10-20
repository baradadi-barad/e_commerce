<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\MessageBag;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = RouteServiceProvider::HOME;

    public function __construct()
    {

        $this->middleware('guest')->except('logout');
    }

    public function showLogin()
    {
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $errors = new MessageBag;
        $this->validate($request, [
            'email'   => 'required|email',
            'password' => 'required|min:6'
        ]);

        if (Auth::guard()->attempt(['email' => $request->email, 'password' => $request->password], $request->get('remember'))) {
            return redirect()->intended('/dashboard');
        }
        $errors = new MessageBag(['emailpassword' => ['Email or Password invalid.']]); // if Auth::attempt fails (wrong credentials) create a new message bag instance.
        // pre($errors); exit;
        // return Redirect::back()->withErrors($errors)->withInput(Input::except('password')); 
        return back()->withInput($request->only('email', 'remember'))->withErrors($errors);
    }
}

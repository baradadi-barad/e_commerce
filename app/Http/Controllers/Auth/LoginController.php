<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    protected function authenticated(Request $request, $user) {
        

        if ($user->role == "admin") {
            return redirect('/admin/dashboard');
        } 
        elseif(($user->role == "customer"))
        {
            return redirect('/');
        }
         else {
            return redirect('/home');
        }
        // if(Auth::guard($guard)->check()){
        //     $role=Auth::user()->role;
        //     switch($role){
        //         case 'admin':
        //             return redirect ('/admin');
        //             break;
        //         case 'customer':
        //             return redirect ('/login');
        //             break;
        //         case 'guest':
        //            return  redirect ('/');
        //            break;
        //     }
        // }

   }
}

<?php

namespace App\Http\Controllers;

use App;
use App\Models\Catalog;
use App\Models\Categories;
use App\Models\ContactUs;
use App\Models\Forms;
use App\Models\NewsLetter;
use App\Models\Products;
use App\Models\Sectors;
//use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    public function change_lang($lang, Request $request)
    {
        app()->setLocale($lang);
        session()->put('lang', $lang);
        $lang = session()->get('lang');
        return redirect()->back();
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $forms = Forms::all();
        $sectors = Sectors::all();
        $records = Catalog::all();
        return view('catalog.catalog', compact('forms', 'sectors', 'records'));
    }

    public function home()
    {
        // $store_categories = Categories::get();
        $store_categories = Categories::orderBy('id', 'asc')->get();
        $featured_product = Products::where('feature_product', 1)->get();
        return view('products.index', compact('store_categories', 'featured_product'));
    }
    public function myAccount()
    {

        $user = Auth::user();
        $data['users'] = $user;
        return view('myaccount.myaccount', $data);

    }

    public function contactus()
    {
        return view('contactus');
    }
    public function saveContactForm(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required',
            'subject' => 'required',
            'message' => 'required',
        ]);

        $data['name'] = $request->input('name');
        $data['email'] = $request->input('email');
        $data['subject'] = $request->input('subject');
        $data['message'] = $request->input('message');
        ContactUs::create($data);
        return back()->with('success', 'Your message was sent successfully. Thanks.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        return redirect('/');
    }
    public function newsLetter(Request $request)
    {
        if ($request->ajax()) {
            $email = $request->input('email');
            $checkExist = NewsLetter::where('email', $email)->first();
            if ($checkExist) {
                return response()->json(['status' => 'false', 'message' => 'Email Id Already Subscribed For Newsletter']);
            } else {
                $data['email'] = $request->email;
                NewsLetter::create($data);

                return response()->json(['status' => 'true', 'message' => 'Email Id Registred For Newsletter']);
            }
        }
    }
}

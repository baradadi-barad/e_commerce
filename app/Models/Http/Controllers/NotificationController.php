<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\User;
use App\Notification;
use Session;
use Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;

class NotificationController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        deleteNotification();
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
   
    public function index(Request $request)
    {
        $data = array();

        $userId = Auth::user()->id;
        $data = Notification::orderBy('status', 'ASC')->where('whom_to_seen','LIKE',"%'".$userId."'%")->get();
        
        return ['data' => $data, 'status' => 'true'];
    }

    public function getNotification(Request $request)
    {
        $id = $request->id;

        $data = Notification::where('id',$id)->get();

        $update = Notification:: where('id', $id)->update(['status' => 1]);

        return ['data' => $data, 'status' => 'true'];
    }

    public function deleteNoti(Request $request)
    {
        $id = $request->id;

        $data = Notification::where('id',$id)->delete();

        return ['status' => 'true'];
    }

    public function getAllNoti(Request $request)
    {
        $data = 0;

        $userId = Auth::user()->id;
        $data = Notification::where('whom_to_seen','LIKE',"%'".$userId."'%")->where('status',0)->count();

        return ['data' => $data, 'status' => 'true'];
    }
}

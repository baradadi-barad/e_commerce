<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\ActivityRecord;
use Session;
use Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use App\User;
use DB;

class ActivityController extends Controller
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
    public function index()
    {
        $userId = Auth::user()->id;       
        $data = ActivityRecord::orderBy('id','desc')->where('user_id',$userId)->get();
        $added_by = User::where('status','enable')->get();
       
        return view('activity.view', ['data' => $data,'added_by' => $added_by]);
    }
     
    
    public function filter_data(Request $request)
    {   
        $type = $request->type;
        $month_from = $request->month_from;
        $month_to = $request->month_to;

        $postData = $request->all();

        if(isset($postData) && count($postData) > 0 ){
            $postData['month_from'] = str_replace('/','-',$postData['month_from']);
            $postData['month_to'] = str_replace('/','-',$postData['month_to']);
        }

        $userId = Auth::user()->id;   
        $data = ActivityRecord::orderBy('id','desc')
            ->where('user_id',$userId)
            ->where(function ($q) use ($type,$month_from,$month_to,$postData) {
                if($type != ''){
                    $q->where('type', $type);
                }
                if($month_from != ''){
                    $q->whereDate('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])));
                }
                if($month_to != ''){
                    $q->whereDate('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])));
                }
            })->get();

        $added_by = User::where('status','enable')->get();

        return view('activity.view', ['data' => $data,'added_by' => $added_by,'postData'=>$postData]);
    }

    public function view(Request $request)
    {   
        $id = $request->id;
        $modal = $request->modal;
        $data = array();
        if(isset($modal) && isset($id)){
            $model_name = '\\App\\'.$modal;
            $data = $model_name::where('id',$id)->first();
        }
        return ['data' => $data, 'status' => 'true'];
    }
}

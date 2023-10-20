<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Nutrition;
use Session;
use Auth;
use App\Hospitals;
use App\User;
use DB;

class NutritionController extends Controller
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
        $nutrition = Nutrition::orderBy('id','desc')->where('added_by',$userId)->with('addedBy')->get();
        $hospital_name = Hospitals::get();
        $added_by = User::where('status','enable')->get();

        return view('nutrition.view', ['nutrition' => $nutrition,'hospital_name' => $hospital_name,'added_by' => $added_by]);
    }
    public function add(Request $request)
    {
        $current_year = date('Y');
        $years = range($current_year-5, $current_year+10);
        $current_month = date('m');
        return view('nutrition.add',['years'=>$years],['currentMonth'=>$current_month]);
    }
    public function delete($id)
    {
        
        Nutrition::find($id)->delete();
        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'Record Delete In Nutrition';
        $data1['record_id'] = 0;
        $data1['route_name'] = 'nutrition';
        $data1['type'] = 'nhmis';

        activity($data1);
        Session::flash('errorMessage', 'Record deleted successfully!');

        return redirect()->route('nutrition');
    }
    public function edit($id)
    {
        $current_year = date('Y');
        $years = range($current_year-5, $current_year+10);
        $current_month = date('m');
        $nutrition = Nutrition::find($id);
        return view('nutrition.edit', ['nutrition' => $nutrition],['years'=>$years]);
    }
    public function display($id)
    {
        $current_year = date('Y');
        $years = range($current_year-5, $current_year+10);
        $current_month = date('m');
        $data = Nutrition::find($id);
        return view('nutrition.display', ['data' => $data],['years'=>$years]);
    }
    public function insert(Request $request)
    {
        $userId = Auth::user()->id;
        $request->flash();
        $postData = $request->all();

        $nutrition = new Nutrition;
        $nutrition->added_by = $userId;
        $nutrition->month = $postData['month'] ;
        $nutrition->year = $postData['year'] ;
        $data->hospital_id = Auth::user()->hospital_name;
        $nutrition->children_0to59_mwt = $postData['children_0to59_mwt'] ;
        $nutrition->children_0to59_mwbbl = $postData['children_0to59_mwbbl'] ;
        $nutrition->children_0to6_rbebf = $postData['children_0to6_rbebf'] ;
        $nutrition->children_6to11_mgva = $postData['children_6to11_mgva'] ;
        $nutrition->children_12to59_mgva = $postData['children_12to59_mgva'] ;
        $nutrition->children_12to59_mgdm = $postData['children_12to59_mgdm'] ;
        $nutrition->children_lt5y_otp_sc = $postData['children_lt5y_otp_sc'] ;
        $nutrition->children_lt5y_discharged = $postData['children_lt5y_discharged'] ;
        $nutrition->children_admitted_cmam_program = $postData['children_admitted_cmam_program'] ;
        $nutrition->children_defaulted_cmam_program = $postData['children_defaulted_cmam_program'] ;
        $nutrition->save();

        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'New Record Add In Nutrition';
        $data1['record_id'] = $data->id;
        $data1['route_name'] = 'nutrition';
        $data1['type'] = 'nhmis';

        activity($data1);

        Session::flash('successMessage', 'Record added successfully!');
        return redirect()->route('nutrition');
    }
    public function update($id, Request $request)
    {
        $request->flash();
        $postData = $request->all();
        
        $data = Nutrition::find($id);
        $data->month = $postData['month'] ;
        $data->year = $postData['year'] ;
        $data->hospital_id = Auth::user()->hospital_name;
        $data->children_0to59_mwt = $postData['children_0to59_mwt'] ;
        $data->children_0to59_mwbbl = $postData['children_0to59_mwbbl'] ;
        $data->children_0to6_rbebf = $postData['children_0to6_rbebf'] ;
        $data->children_6to11_mgva = $postData['children_6to11_mgva'] ;
        $data->children_12to59_mgva = $postData['children_12to59_mgva'] ;
        $data->children_12to59_mgdm = $postData['children_12to59_mgdm'] ;
        $data->children_lt5y_otp_sc = $postData['children_lt5y_otp_sc'] ;
        $data->children_lt5y_discharged = $postData['children_lt5y_discharged'] ;
        $data->children_admitted_cmam_program = $postData['children_admitted_cmam_program'] ;
        $data->children_defaulted_cmam_program = $postData['children_defaulted_cmam_program'] ;
        $data->save();
        
        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'Record Edit In Nutrition';
        $data1['record_id'] = $data->id;
        $data1['route_name'] = 'nutrition';
        $data1['type'] = 'nhmis';

        activity($data1);
        Session::flash('successMessage', 'Record updated successfully!');
        
        return redirect()->route('nutrition');
    }
  
    public function filter_data(Request $request)
    {
        // pre($request->all());
        $hospitalname = $request->hospitalname;
        $added_by_filter = $request->added_by;
        $month_from = $request->month_from;
        $month_to = $request->month_to;

        $postData = $request->all();

        $userId = Auth::user()->id;   
        $nutrition = Nutrition::orderBy('id','desc')
            ->where(function ($q) use ($hospitalname,$added_by_filter,$month_from,$month_to) {
                if($hospitalname != ''){
                    $q->where('hospital_id', $hospitalname);
                }
                if($added_by_filter != ''){
                    $q->where('added_by', $added_by_filter);
                }
                if($month_from != ''){
                    $q->whereDate('created_at','>=', $month_from);
                }
                if($month_to != ''){
                    $q->whereDate('created_at','<=', $month_to);
                }
            })
            ->with('addedBy')->get();

        $hospital_name = Hospitals::get();
        $added_by = User::where('status','enable')->get();
        
        return view('nutrition.view', ['nutrition' => $nutrition,'hospital_name' => $hospital_name,'added_by' => $added_by,'postData'=>$postData]);

    }
}

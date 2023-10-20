<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\SrhHivIntegration;
use Session;
use Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use App\Hospitals;
use App\User;
use DB;

class SrhHivIntegrationController extends Controller
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
   
    public function add(Request $request){
        
        $current_year = date('Y');
        $years = range($current_year-5, $current_year+10);
        $current_month = date('m');
        
       return view('srh-hiv-integration.add',['years'=>$years],['currentMonth'=>$current_month]);
    }
    
     public function view()
    {
        $userId = Auth::user()->id;       
        $data = SrhHivIntegration::orderBy('id','desc')->where('added_by',$userId)->with('addedBy')->paginate(10);
       
        return view('patient-seen.display', ['data' => $data]);
    }
    public function display($id)
    {
        $current_year = date('Y');
        $years = range($current_year-5, $current_year+10);
        $current_month = date('m');
        
        $data = SrhHivIntegration::find($id);
        return view('srh-hiv-integration.display', ['data' => $data],['years'=>$years]);
    }
     public function index()
    {
        $userId = Auth::user()->id;       
        $data = SrhHivIntegration::orderBy('id','desc')->where('added_by',$userId)->with('addedBy')->get();
        $hospital_name = Hospitals::get();
        $added_by = User::where('status','enable')->get();
       
        return view('srh-hiv-integration.view', ['data' => $data,'hospital_name' => $hospital_name,'added_by' => $added_by]);
    }
    
     public function delete($id)
    {
        
        SrhHivIntegration::find($id)->delete();

        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'Record Delete In SRH-HIV Integration';
        $data1['record_id'] = 0;
        $data1['route_name'] = 'srh-hiv-integration';
        $data1['type'] = 'art';

        activity($data1);
        Session::flash('errorMessage', 'Record deleted successfully!');

        return redirect()->route('srh-hiv-integration');
    }
    public function edit($id)
    {
        $current_year = date('Y');
        $years = range($current_year-5, $current_year+10);
        $current_month = date('m');
        
        $data = SrhHivIntegration::find($id);
        return view('srh-hiv-integration.edit', ['data' => $data],['years'=>$years]);
    }
     public function insert(Request $request){
        
        $userId = Auth::user()->id;
        $request->flash();
        $postData = $request->all();

        $data = new SrhHivIntegration;
        $data->added_by = $userId;
        $data->month = $postData['month'] ;
        $data->year = $postData['year'] ;
        $data->hospital_id = Auth::user()->hospital_name;
        $data->hct_clients_provided_male = $postData['hct_clients_provided_male'] ;
        $data->hct_clients_provided_female = $postData['hct_clients_provided_female'] ;
        $data->hct_clients_provided_total = $postData['hct_clients_provided_total'] ;
        $data->hct_clients_referred_male = $postData['hct_clients_referred_male'] ;
        $data->hct_clients_referred_female = $postData['hct_clients_referred_female'] ;
        $data->hct_clients_referred_total = $postData['hct_clients_referred_total'] ;
        $data->hct_clients_screened_male = $postData['hct_clients_screened_male'] ;
        $data->hct_clients_screened_female = $postData['hct_clients_screened_female'] ;
        $data->hct_clients_screened_total = $postData['hct_clients_screened_total'] ;
        $data->hct_clients_treated_male = $postData['hct_clients_treated_male'] ;
        $data->hct_clients_treated_female = $postData['hct_clients_treated_female'] ;
        $data->hct_clients_treated_total = $postData['hct_clients_treated_total'] ;
        $data->fp_clients_provided_male = $postData['fp_clients_provided_male'] ;
        $data->fp_clients_provided_female = $postData['fp_clients_provided_female'] ;
        $data->fp_clients_provided_tootal = $postData['fp_clients_provided_tootal'] ;
        
        $data->save();
         
        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'New Record Add In SRH-HIV Integration';
        $data1['record_id'] = $data->id;
        $data1['route_name'] = 'srh-hiv-integration';
        $data1['type'] = 'art';

        activity($data1);
        Session::flash('successMessage', 'Record added successfully!');
         return redirect()->route('srh-hiv-integration');
    }
    
    public function update($id, Request $request)
    {
        $userId = Auth::user()->id;
        $request->flash();
        $postData = $request->all();
        
        $data = SrhHivIntegration::find($id);
        $data->added_by = $userId;
        $data->month = $postData['month'] ;
        $data->year = $postData['year'] ;
        $data->hospital_id = Auth::user()->hospital_name;
        $data->hct_clients_provided_male = $postData['hct_clients_provided_male'] ;
        $data->hct_clients_provided_female = $postData['hct_clients_provided_female'] ;
        $data->hct_clients_provided_total = $postData['hct_clients_provided_total'] ;
        $data->hct_clients_referred_male = $postData['hct_clients_referred_male'] ;
        $data->hct_clients_referred_female = $postData['hct_clients_referred_female'] ;
        $data->hct_clients_referred_total = $postData['hct_clients_referred_total'] ;
        $data->hct_clients_screened_male = $postData['hct_clients_screened_male'] ;
        $data->hct_clients_screened_female = $postData['hct_clients_screened_female'] ;
        $data->hct_clients_screened_total = $postData['hct_clients_screened_total'] ;
        $data->hct_clients_treated_male = $postData['hct_clients_treated_male'] ;
        $data->hct_clients_treated_female = $postData['hct_clients_treated_female'] ;
        $data->hct_clients_treated_total = $postData['hct_clients_treated_total'] ;
        $data->fp_clients_provided_male = $postData['fp_clients_provided_male'] ;
        $data->fp_clients_provided_female = $postData['fp_clients_provided_female'] ;
        $data->fp_clients_provided_tootal = $postData['fp_clients_provided_tootal'] ;
        
        $data->save();
        
        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'Record Edit In SRH-HIV Integration';
        $data1['record_id'] = $data->id;
        $data1['route_name'] = 'srh-hiv-integration';
        $data1['type'] = 'art';

        activity($data1);
        Session::flash('successMessage', 'Record updated successfully!');
        
        return redirect()->route('srh-hiv-integration');
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
        $data = SrhHivIntegration::orderBy('id','desc')
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
        
        return view('srh-hiv-integration.view', ['data' => $data,'hospital_name' => $hospital_name,'added_by' => $added_by,'postData'=>$postData]);

    }
}

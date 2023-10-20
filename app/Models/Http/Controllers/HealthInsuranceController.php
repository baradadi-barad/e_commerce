<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\HealthInsurance;
use Session;
use Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use App\Hospitals;
use App\User;
use DB;

class HealthInsuranceController extends Controller
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
        
       return view('health-insurance.add',['years'=>$years],['currentMonth'=>$current_month]);
    }
    
     public function view()
    {
        $userId = Auth::user()->id;       
        $data = HealthInsurance::orderBy('id','desc')->where('added_by',$userId)->with('addedBy')->paginate(10);
       
        return view('health-insurance.display', ['data' => $data]);
    }
    public function display($id)
    {
        $current_year = date('Y');
        $years = range($current_year-5, $current_year+10);
        $current_month = date('m');
        
        $data = HealthInsurance::find($id);
        return view('health-insurance.display', ['data' => $data],['years'=>$years]);
    }
     public function index()
    {
        $userId = Auth::user()->id;       
        $data = HealthInsurance::orderBy('id','desc')->where('added_by',$userId)->with('addedBy')->get();
        $hospital_name = Hospitals::get();
        $added_by = User::where('status','enable')->get();
       
        return view('health-insurance.view', ['data' => $data,'hospital_name' => $hospital_name,'added_by' => $added_by]);
    }
    
     public function delete($id)
    {
        
        HealthInsurance::find($id)->delete();
        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'Record Delete In Health Insurance';
        $data1['record_id'] = 0;
        $data1['route_name'] = 'health-insurance';
        $data1['type'] = 'record_office';

        activity($data1);
        Session::flash('errorMessage', 'Record deleted successfully!');

        return redirect()->route('health-insurance');
    }
    public function edit($id)
    {
        $current_year = date('Y');
        $years = range($current_year-5, $current_year+10);
        $current_month = date('m');
        
        $data = HealthInsurance::find($id);
        return view('health-insurance.edit', ['data' => $data],['years'=>$years]);
    }
     public function insert(Request $request){
        
        $userId = Auth::user()->id;
        $request->flash();
        $postData = $request->all();

        $data = new HealthInsurance;
        $data->added_by = $userId;
        $data->month = $postData['month'] ;
        $data->year = $postData['year'] ;
        $data->hospital_id = Auth::user()->hospital_name;
        $data->nhis_male = $postData['nhis_male'] ;
        $data->nhis_female = $postData['nhis_female'] ;
        $data->nhis_total = $postData['nhis_total'] ;
        $data->fhis_male = $postData['fhis_male'] ;
        $data->fhis_female = $postData['fhis_female'] ;
        $data->fhis_total = $postData['fhis_total'] ;
        $data->nhis_enrolled_male = $postData['nhis_enrolled_male'] ;
        $data->nhis_enrolled_female = $postData['nhis_enrolled_female'] ;
        $data->nhis_enrolled_total = $postData['nhis_enrolled_total'] ;
        $data->fhis_enrolled_male = $postData['fhis_enrolled_male'] ;
        $data->fhis_enrolled_female = $postData['fhis_enrolled_female'] ;
        $data->fhis_enrolled_total = $postData['fhis_enrolled_total'] ;
        
        $data->save();
        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'New Record Add In Health Insurance';
        $data1['record_id'] = $data->id;
        $data1['route_name'] = 'health-insurance';
        $data1['type'] = 'record_office';

        activity($data1);
         
        Session::flash('successMessage', 'Record added successfully!');
         return redirect()->route('health-insurance');
    }
    
    public function update($id, Request $request)
    {
        $userId = Auth::user()->id;
        $request->flash();
        $postData = $request->all();
        
        $data = HealthInsurance::find($id);
        $data->added_by = $userId;
        $data->month = $postData['month'] ;
        $data->year = $postData['year'] ;
        $data->hospital_id = Auth::user()->hospital_name;
        $data->nhis_male = $postData['nhis_male'] ;
        $data->nhis_female = $postData['nhis_female'] ;
        $data->nhis_total = $postData['nhis_total'] ;
        $data->fhis_male = $postData['fhis_male'] ;
        $data->fhis_female = $postData['fhis_female'] ;
        $data->fhis_total = $postData['fhis_total'] ;
        $data->nhis_enrolled_male = $postData['nhis_enrolled_male'] ;
        $data->nhis_enrolled_female = $postData['nhis_enrolled_female'] ;
        $data->nhis_enrolled_total = $postData['nhis_enrolled_total'] ;
        $data->fhis_enrolled_male = $postData['fhis_enrolled_male'] ;
        $data->fhis_enrolled_female = $postData['fhis_enrolled_female'] ;
        $data->fhis_enrolled_total = $postData['fhis_enrolled_total'] ;
        
        $data->save();
        
        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'Record Edit In Health Insurance';
        $data1['record_id'] = $data->id;
        $data1['route_name'] = 'health-insurance';
        $data1['type'] = 'record_office';

        activity($data1);
        Session::flash('successMessage', 'Record updated successfully!');
        
        return redirect()->route('health-insurance');
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
        $data = HealthInsurance::orderBy('id','desc')
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

        // pre($data);

        // return json_encode(array("status" => true,'data' => $data,'hospital_name' => $hospital_name,'added_by' => $added_by));

        return view('health-insurance.view', ['data' => $data,'hospital_name' => $hospital_name,'added_by' => $added_by,'postData'=>$postData]);

    }
    
}

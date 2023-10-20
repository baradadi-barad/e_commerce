<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\GeneralOutpatient;
use Session;
use Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use App\Hospitals;
use App\User;
use DB;

class GeneralOutpatientController extends Controller
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
        
       return view('general-outpatient.add',['years'=>$years],['currentMonth'=>$current_month]);
    }
    
     public function view()
    {
        $userId = Auth::user()->id;   
        $data = GeneralOutpatient::orderBy('id','desc')->where('added_by',$userId)->with('addedBy')->paginate(10);
       
        return view('general-outpatient.display', ['data' => $data]);
    }
    public function display($id)
    {
        $current_year = date('Y');
        $years = range($current_year-5, $current_year+10);
        $current_month = date('m');
        
        $data = GeneralOutpatient::find($id);
        return view('general-outpatient.display', ['data' => $data],['years'=>$years]);
    }
     public function index()
    {
        $userId = Auth::user()->id;       
        $data = GeneralOutpatient::orderBy('id','desc')->where('added_by',$userId)->with('addedBy')->get();
        $hospital_name = Hospitals::get();
        $added_by = User::where('status','enable')->get();
       
        return view('general-outpatient.view', ['data' => $data,'hospital_name' => $hospital_name,'added_by' => $added_by]);
    }
    
     public function delete($id)
    {
        
        GeneralOutpatient::find($id)->delete();

        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'Record Delete In General Outpatient';
        $data1['record_id'] = 0;
        $data1['route_name'] = 'general-outpatient';
        $data1['type'] = 'record_office';

        activity($data1);
        Session::flash('errorMessage', 'Record deleted successfully!');

        return redirect()->route('general-outpatient');
    }
    public function edit($id)
    {
        $current_year = date('Y');
        $years = range($current_year-5, $current_year+10);
        $current_month = date('m');
        
        $data = GeneralOutpatient::find($id);
        return view('general-outpatient.edit', ['data' => $data],['years'=>$years]);
    }
     public function insert(Request $request){
        
        $userId = Auth::user()->id;
        $request->flash();
        $postData = $request->all();

        $data = new GeneralOutpatient;
        $data->added_by = $userId;
        $data->month = $postData['month'] ;
        $data->year = $postData['year'] ;
        
        $data->gopd_attendance_adult_male = $postData['gopd_attendance_adult_male'] ;
        $data->gopd_attendance_adult_female = $postData['gopd_attendance_adult_female'] ;
        $data->gopd_attendance_adult_total = $postData['gopd_attendance_adult_total'] ;
        
        $data->gopd_attendance_pediatrics_male = $postData['gopd_attendance_pediatrics_male'] ;
        $data->gopd_attendance_pediatrics_female = $postData['gopd_attendance_pediatrics_female'] ;
        $data->gopd_attendance_pediatrics_total = $postData['gopd_attendance_pediatrics_total'] ;
        
        $data->medical_corticated_fitness_male = $postData['medical_corticated_fitness_male'] ;
        $data->medical_corticated_fitness_female = $postData['medical_corticated_fitness_female'] ;
        $data->medical_corticated_fitness_total = $postData['medical_corticated_fitness_total'] ;
        
        $data->maternity_leave_male = $postData['maternity_leave_male'] ;
        $data->maternity_leave_female = $postData['maternity_leave_female'] ;
        $data->maternity_leave_total = $postData['maternity_leave_total'] ;
        
        $data->antenatal_attendance_male = $postData['antenatal_attendance_male'] ;
        $data->antenatal_attendance_female = $postData['antenatal_attendance_female'] ;
        $data->antenatal_attendance_total = $postData['antenatal_attendance_total'] ;
        
        $data->postnatal_attendance_male = $postData['postnatal_attendance_male'] ;
        $data->postnatal_attendance_female = $postData['postnatal_attendance_female'] ;
        $data->postnatal_attendance_total = $postData['postnatal_attendance_total'] ;
        
        $data->family_planning_attendance_male = $postData['family_planning_attendance_male'] ;
        $data->family_planning_attendance_female = $postData['family_planning_attendance_female'] ;
        $data->family_planning_attendance_total = $postData['family_planning_attendance_total'] ;
        
        
        $data->save();
         
        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'New Record Add In General Outpatient';
        $data1['record_id'] = $data->id;
        $data1['route_name'] = 'general-outpatient';
        $data1['type'] = 'record_office';

        activity($data1);
        Session::flash('successMessage', 'Record added successfully!');
         return redirect()->route('general-outpatient');
    }
    
    public function update($id, Request $request)
    {
        $userId = Auth::user()->id;
        $request->flash();
        $postData = $request->all();
        
        $data = GeneralOutpatient::find($id);
        $data->added_by = $userId;
        $data->month = $postData['month'] ;
        $data->year = $postData['year'] ;
        $data->gopd_attendance_adult_male = $postData['gopd_attendance_adult_male'] ;
        $data->gopd_attendance_adult_female = $postData['gopd_attendance_adult_female'] ;
        $data->gopd_attendance_adult_total = $postData['gopd_attendance_adult_total'] ;
        
        $data->gopd_attendance_pediatrics_male = $postData['gopd_attendance_pediatrics_male'] ;
        $data->gopd_attendance_pediatrics_female = $postData['gopd_attendance_pediatrics_female'] ;
        $data->gopd_attendance_pediatrics_total = $postData['gopd_attendance_pediatrics_total'] ;
        
        $data->medical_corticated_fitness_male = $postData['medical_corticated_fitness_male'] ;
        $data->medical_corticated_fitness_female = $postData['medical_corticated_fitness_female'] ;
        $data->medical_corticated_fitness_total = $postData['medical_corticated_fitness_total'] ;
        
        $data->maternity_leave_male = $postData['maternity_leave_male'] ;
        $data->maternity_leave_female = $postData['maternity_leave_female'] ;
        $data->maternity_leave_total = $postData['maternity_leave_total'] ;
        
        $data->antenatal_attendance_male = $postData['antenatal_attendance_male'] ;
        $data->antenatal_attendance_female = $postData['antenatal_attendance_female'] ;
        $data->antenatal_attendance_total = $postData['antenatal_attendance_total'] ;
        
        $data->postnatal_attendance_male = $postData['postnatal_attendance_male'] ;
        $data->postnatal_attendance_female = $postData['postnatal_attendance_female'] ;
        $data->postnatal_attendance_total = $postData['postnatal_attendance_total'] ;
        
        $data->family_planning_attendance_male = $postData['family_planning_attendance_male'] ;
        $data->family_planning_attendance_female = $postData['family_planning_attendance_female'] ;
        $data->family_planning_attendance_total = $postData['family_planning_attendance_total'] ;
        
        $data->save();
        
        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'Record Edit In General Outpatient';
        $data1['record_id'] = $data->id;
        $data1['route_name'] = 'general-outpatient';
        $data1['type'] = 'record_office';

        activity($data1);
        Session::flash('successMessage', 'Record updated successfully!');
        
        return redirect()->route('general-outpatient');
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
        $data = GeneralOutpatient::orderBy('id','desc')
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

        return view('general-outpatient.view', ['data' => $data,'hospital_name' => $hospital_name,'added_by' => $added_by,'postData'=>$postData]);

    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\PatientGeneralStatistics;
use Session;
use Auth;
use App\Hospitals;
use App\User;
use DB;

class PatientGeneralStatisticsController extends Controller
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
        $data = PatientGeneralStatistics::orderBy('id','DESC')->where('added_by',$userId)->with('addedBy')->get();
        $hospital_name = Hospitals::get();
        $added_by = User::where('status','enable')->get();

        return view('patient-general-statistics.view', ['data' => $data,'hospital_name' => $hospital_name,'added_by' => $added_by]);
    }
    public function add()
    {
        $current_year = date('Y');
        $currentMonth = date('m');
        
        $years = range($current_year-5, $current_year+10);
        return view('patient-general-statistics.add',['years'=>$years,'currentMonth' => $currentMonth]);
    }
    public function insert(Request $request)
    {
        $userId = Auth::user()->id;
        $request->flash();
        $postData = $request->all();

        $data = new PatientGeneralStatistics;
        $data->added_by = $userId;
        $data->achievements = $postData['achievements'] ;
        $data->challenges = $postData['challenges'] ;
        $data->month = $postData['month'] ;
        $data->year = $postData['year'] ;
        $data->no_of_patient_seen_male = $postData['no_of_patient_seen_male'] ;
        $data->no_of_patient_seen_female = $postData['no_of_patient_seen_female'] ;
        $data->no_of_delivery_male = $postData['no_of_delivery_male'] ;
        $data->no_of_delivery_female = $postData['no_of_delivery_female'] ;
        $data->no_of_deaths_male = $postData['no_of_deaths_male'] ;
        $data->no_of_deaths_female = $postData['no_of_deaths_female'] ;
        $data->no_of_admission_male = $postData['no_of_admission_male'] ;
        $data->no_of_admission_female = $postData['no_of_admission_female'] ;
        $data->no_of_patient_sc_male = $postData['no_of_patient_sc_male'] ;
        $data->no_of_patient_sc_female = $postData['no_of_patient_sc_female'] ;
        $data->no_of_discharges_male = $postData['no_of_discharges_male'] ;
        $data->no_of_discharges_female = $postData['no_of_discharges_female'] ;
        $data->registered_anc_attendees = $postData['registered_anc_attendees'] ;
        $data->internally_generated_revenue = $postData['internally_generated_revenue'] ;
        $data->registered_anc_attendees_under5m = $postData['registered_anc_attendees_under5m'] ;
        $data->registered_anc_attendees_under5f = $postData['registered_anc_attendees_under5f'] ;
        $data->save();


        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'New Record Add In Patient General Statistic';
        $data1['record_id'] = $data->id;
        $data1['route_name'] = 'patient-general-statistics';
        $data1['type'] = 'm_e';

        activity($data1);
        Session::flash('successMessage', 'Record added successfully!');
        return redirect()->route('patient-general-statistics');
    }
    public function display($id)
    {
        $current_year = date('Y');
        $currentMonth = date('m');
        
        $years = range($current_year-5, $current_year+10);
        $data = PatientGeneralStatistics::find($id);
        return view('patient-general-statistics.display', ['data' => $data,'years'=>$years,'currentMonth' => $currentMonth]);
    }
    public function delete($id)
    {
        PatientGeneralStatistics::find($id)->delete();
        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'Record Delete In Patient General Statistic';
        $data1['record_id'] = 0;
        $data1['route_name'] = 'patient-general-statistics';
        $data1['type'] = 'm_e';

        activity($data1);
        Session::flash('errorMessage', 'Record deleted successfully!');

        return redirect()->route('patient-general-statistics');
    }
    public function edit($id)
    {
        $current_year = date('Y');
        $currentMonth = date('m');
        
        $years = range($current_year-5, $current_year+10);
        $data = PatientGeneralStatistics::find($id);
        return view('patient-general-statistics.edit', ['data' => $data,'years'=>$years,'currentMonth' => $currentMonth]);
    }
    public function update($id, Request $request)
    {
        $request->flash();
        $postData = $request->all();
        
        $data = PatientGeneralStatistics::find($id);
        $data->achievements = $postData['achievements'] ;
        $data->challenges = $postData['challenges'] ;
        $data->month = $postData['month'] ;
        $data->year = $postData['year'] ;
        $data->no_of_patient_seen_male = $postData['no_of_patient_seen_male'] ;
        $data->no_of_patient_seen_female = $postData['no_of_patient_seen_female'] ;
        $data->no_of_delivery_male = $postData['no_of_delivery_male'] ;
        $data->no_of_delivery_female = $postData['no_of_delivery_female'] ;
        $data->no_of_deaths_male = $postData['no_of_deaths_male'] ;
        $data->no_of_deaths_female = $postData['no_of_deaths_female'] ;
        $data->no_of_admission_male = $postData['no_of_admission_male'] ;
        $data->no_of_admission_female = $postData['no_of_admission_female'] ;
        $data->no_of_patient_sc_male = $postData['no_of_patient_sc_male'] ;
        $data->no_of_patient_sc_female = $postData['no_of_patient_sc_female'] ;
        $data->no_of_discharges_male = $postData['no_of_discharges_male'] ;
        $data->no_of_discharges_female = $postData['no_of_discharges_female'] ;
        $data->registered_anc_attendees = $postData['registered_anc_attendees'] ;
        $data->internally_generated_revenue = $postData['internally_generated_revenue'] ;
        $data->registered_anc_attendees_under5m = $postData['registered_anc_attendees_under5m'] ;
        $data->registered_anc_attendees_under5f = $postData['registered_anc_attendees_under5f'] ;
        $data->save();
        
        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'Record Edit In Patient General Statistic';
        $data1['record_id'] = $data->id;
        $data1['route_name'] = 'patient-general-statistics';
        $data1['type'] = 'm_e';

        activity($data1);
        Session::flash('successMessage', 'Record updated successfully!');
        
        return redirect()->route('patient-general-statistics');
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
        $data = PatientGeneralStatistics::orderBy('id','desc')
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

        return view('patient-general-statistics.view', ['data' => $data,'hospital_name' => $hospital_name,'added_by' => $added_by,'postData'=>$postData]);

    }
}

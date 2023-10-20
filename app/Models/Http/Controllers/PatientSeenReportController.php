<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\PatientSeenReport;
use Session;
use Illuminate\Support\Facades\DB;
use Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use  Illuminate\Database\Eloquent\Builder;
use App\Hospitals;
use App\User;

class PatientSeenReportController extends Controller
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
        $current_month = date('m');
        $years = range($current_year-5, $current_year+10);
      
       return view('patient-seen-report.add',['years'=>$years],['current_month'=>$current_month]);
    }
    
     public function view()
    {
         $userId = Auth::user()->id;      
        $patientreport = PatientSeenReport::groupBy('hospita_name')->where('added_by',$userId)->with('addedBy')->paginate(10);
        
      

            $total = PatientSeenReport::select(DB::raw('SUM(popd) as popd, SUM(gopd) as gopd, SUM(a_e) as a_e,'
                                    . ' SUM(plastic_surg) as plastic_surg, SUM(urology) as urology , SUM(sopd) as sopd , SUM(o_g) as o_g , SUM(drema) as drema , SUM(mopd) as mopd'))
                    
                    ->first();            
        $patientreport[count($patientreport)] = $total;
        return view('patient-seen-report.add', ['patientreport' => $patientreport]);
    }
     public function display($id)
    {
       
        $current_year = date('Y');
        $years = range($current_year-5, $current_year+10);
      
        $patientreportEdit = PatientSeenReport::find($id);
        
        
        
        return view('patient-seen-report.display', ['patientreportEdit' => $patientreportEdit],['years'=>$years]);
    }
     public function index(Request $request)
    {
        $userId = Auth::user()->id;       
        $patientreport = PatientSeenReport::with('addedBy')->where('added_by',$userId)->get();
        $hospital_name = Hospitals::get();
        $added_by = User::where('status','enable')->get();

        foreach($patientreport as $value){
            $total = 0;
            $total = $total +  $value['popd'] +  $value['gopd']+  $value['a_e']+  $value['plastic_surg']+  $value['urology']
                    +  $value['sopd']+  $value['o_g']+  $value['drema']+  $value['mopd'];
            $value->totalPatienSeen = $total;
        }
        return view('patient-seen-report.view', ['patientreport' => $patientreport,'hospital_name' => $hospital_name,'added_by' => $added_by]);
    }
    
     public function delete($id)
    {
        
        PatientSeenReport::find($id)->delete();

        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'Record Delete In Patient Seen Report';
        $data1['record_id'] = 0;
        $data1['route_name'] = 'patient-seen-report';
        $data1['type'] = 'm_e';

        activity($data1);
        Session::flash('errorMessage', 'Record deleted successfully!');

        return redirect()->route('patient-seen-report');
    }
    public function edit($id)
    {
         $current_year = date('Y');
        $years = range($current_year-5, $current_year+10);
      
        $patientreportEdit = PatientSeenReport::find($id);
        return view('patient-seen-report.edit', ['patientreportEdit' => $patientreportEdit],['years'=>$years]);
    }
     public function insert(Request $request){
        
        $userId = Auth::user()->id;
        $request->flash();
        $postData = $request->all();
        $patientSeenReport = new PatientSeenReport;
        $patientSeenReport->added_by = $userId;
        $patientSeenReport->month = $postData['month'] ;
        $patientSeenReport->year = $postData['year'] ;
        $patientSeenReport->hospital_name = $postData['hospita_name'] ;
        $patientSeenReport->doctors_name = $postData['doctors_name'] ;
        $patientSeenReport->specialist = '';//$postData['specialist'] ;
        $patientSeenReport->number_of_patient_seen = '';//$postData['number_of_patient_seen'] ;
        $patientSeenReport->popd = $postData['popd'] ;
        $patientSeenReport->gopd = $postData['gopd'] ;
        $patientSeenReport->a_e = $postData['a_e'] ;
        $patientSeenReport->plastic_surg = $postData['plastic_surg'] ;
        $patientSeenReport->urology = $postData['urology'] ;
        $patientSeenReport->sopd = $postData['sopd'] ;
        $patientSeenReport->o_g = $postData['o_g'] ;
        $patientSeenReport->drema = $postData['drema'] ;
        $patientSeenReport->mopd = $postData['mopd'] ;
        
        $patientSeenReport->save();
         
        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'New Record Add In Patient Seen Report';
        $data1['record_id'] = $patientSeenReport->id;
        $data1['route_name'] = 'patient-seen-report';
        $data1['type'] = 'm_e';

        activity($data1);
        Session::flash('successMessage', 'Record added successfully!');
         return redirect()->route('patient-seen-report');
    }
    
    public function update($id, Request $request)
    {
        $userId = Auth::user()->id;
        $request->flash();
        $postData = $request->all();
        
        $patientSeenReportUpdate = PatientSeenReport::find($id);
        $patientSeenReportUpdate->added_by = $userId;
        $patientSeenReportUpdate->month = $postData['month'] ;
        $patientSeenReportUpdate->year = $postData['year'] ;
        $patientSeenReportUpdate->hospita_name = $postData['hospita_name'] ;
        $patientSeenReportUpdate->doctors_name = $postData['doctors_name'] ;
        $patientSeenReportUpdate->specialist = '';//$postData['specialist'] ;
        $patientSeenReportUpdate->number_of_patient_seen = '';//$postData['number_of_patient_seen'] ;
        $patientSeenReportUpdate->popd = $postData['popd'] ;
        $patientSeenReportUpdate->gopd = $postData['gopd'] ;
        $patientSeenReportUpdate->a_e = $postData['a_e'] ;
        $patientSeenReportUpdate->plastic_surg = $postData['plastic_surg'] ;
        $patientSeenReportUpdate->urology = $postData['urology'] ;
        $patientSeenReportUpdate->sopd = $postData['sopd'] ;
        $patientSeenReportUpdate->o_g = $postData['o_g'] ;
        $patientSeenReportUpdate->drema = $postData['drema'] ;
        $patientSeenReportUpdate->mopd = $postData['mopd'] ;
        
        $patientSeenReportUpdate->save();
        
        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'Record Edit In Patient Seen Report';
        $data1['record_id'] = $patientSeenReport->id;
        $data1['route_name'] = 'patient-seen-report';
        $data1['type'] = 'm_e';

        activity($data1);
        Session::flash('successMessage', 'Record updated successfully!');
        
        return redirect()->route('patient-seen-report');
    }
    
    public function filter_data(Request $request)
    {
        $hospitalname = $request->hospitalname;
        $added_by_filter = $request->added_by;
        $month_from = $request->month_from;
        $month_to = $request->month_to;

        $postData = $request->all();

        if($hospitalname != ''){
            $hospitalname_first = Hospitals::select('hospital_name')->where('id',$hospitalname)->first();

            $hospitalname = $hospitalname_first->hospital_name;
        }

        $userId = Auth::user()->id;   
        $patientreport = PatientSeenReport::orderBy('id','desc')
            ->where(function ($q) use ($hospitalname,$added_by_filter,$month_from,$month_to) {
                if($hospitalname != ''){
                    $q->where('hospital_name', $hospitalname);
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
        
        foreach($patientreport as $value){
            $total = 0;
            $total = $total +  $value['popd'] +  $value['gopd']+  $value['a_e']+  $value['plastic_surg']+  $value['urology']
                    +  $value['sopd']+  $value['o_g']+  $value['drema']+  $value['mopd'];
            $value->totalPatienSeen = $total;
        }

        return view('patient-seen-report.view', ['patientreport' => $patientreport,'hospital_name' => $hospital_name,'added_by' => $added_by,'postData' => $postData]);

    }
}
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\FamilyPlanningRecordOffice;
use Session;
use Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use App\Hospitals;
use App\User;
use DB;

class FamilyPlanningRecordOfficeController extends Controller
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
        
       return view('family-planning-record.add',['years'=>$years],['currentMonth'=>$current_month]);
    }
    
     public function view()
    {
        $userId = Auth::user()->id;   
        $data = FamilyPlanningRecordOffice::orderBy('id','desc')->with('addedBy')->where('added_by',$userId)->paginate(10);
       
        return view('family-planning-record.display', ['data' => $data]);
    }
    public function display($id)
    {
        $current_year = date('Y');
        $years = range($current_year-5, $current_year+10);
        $current_month = date('m');
        
        $data = FamilyPlanningRecordOffice::find($id);
        return view('family-planning-record.display', ['data' => $data],['years'=>$years]);
    }

    public function index()
    {
        $userId = Auth::user()->id;   
        $data = FamilyPlanningRecordOffice::orderBy('id','desc')->where('added_by',$userId)->with('addedBy')->get();
        $hospital_name = Hospitals::get();
        $added_by = User::where('status','enable')->get();
       
        return view('family-planning-record.view', ['data' => $data,'hospital_name' => $hospital_name,'added_by' => $added_by]);
    }
    
    public function delete($id)
    {
        
        FamilyPlanningRecordOffice::find($id)->delete();
        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'Record Delete In Family Planning';
        $data1['record_id'] = 0;
        $data1['route_name'] = 'family-planning-record';
        $data1['type'] = 'record_office';

        activity($data1);
        Session::flash('errorMessage', 'Record deleted successfully!');

        return redirect()->route('family-planning-record');
    }

    public function edit($id)
    {
        $current_year = date('Y');
        $years = range($current_year-5, $current_year+10);
        $current_month = date('m');
        
        $data = FamilyPlanningRecordOffice::find($id);
        return view('family-planning-record.edit', ['data' => $data],['years'=>$years]);
    }

    public function insert(Request $request){
        
        $userId = Auth::user()->id;
        $request->flash();
        $postData = $request->all();

        $data = new FamilyPlanningRecordOffice;
        $data->added_by = $userId;
        $data->hospital_id = Auth::user()->hospital_name;
        $data->month = $postData['month'] ; 
        $data->year = $postData['year'] ;
        $data->new_family_planning_acceptors_dispensed = $postData['new_family_planning_acceptors_dispensed'] ;
        $data->new_family_planning_acceptors_used = $postData['new_family_planning_acceptors_used'] ;
        $data->new_family_planning_acceptors_total = $postData['new_family_planning_acceptors_total'] ;
        $data->depo_provera_Injection_dispensed = $postData['depo_provera_Injection_dispensed'] ;
        $data->depo_provera_Injection_used = $postData['depo_provera_Injection_used'] ;
        $data->depo_provera_Injection_total = $postData['depo_provera_Injection_total'] ;
        $data->exlution_microlut_dispensed = $postData['exlution_microlut_dispensed'] ;
        $data->exlution_microlut_used = $postData['exlution_microlut_used'] ;
        $data->exlution_microlut_total = $postData['exlution_microlut_total'] ;
        $data->iucd_dispensed = $postData['iucd_dispensed'] ;
        $data->iucd_used = $postData['iucd_used'] ;
        $data->iucd_total = $postData['iucd_total'] ;
        $data->lo_feminal_dispensed = $postData['lo_feminal_dispensed'] ;
        $data->lo_feminal_used = $postData['lo_feminal_used'] ;
        $data->lo_feminal_total = $postData['lo_feminal_total'] ;
        $data->microgynon_dispensed = $postData['microgynon_dispensed'] ;
        $data->microgynon_used = $postData['microgynon_used'] ;
        $data->microgynon_total = $postData['microgynon_total'] ;
        $data->noristerat_dispensed = $postData['noristerat_dispensed'] ;
        $data->noristerat_used = $postData['noristerat_used'] ;
        $data->noristerat_total = $postData['noristerat_total'] ;
        $data->implanon_dispensed = $postData['implanon_dispensed'] ;
        $data->implanon_used = $postData['implanon_used'] ;
        $data->implanon_total = $postData['implanon_total'] ;
        $data->jardelle_dispensed = $postData['jardelle_dispensed'] ;
        $data->jardelle_used = $postData['jardelle_used'] ;
        $data->jardelle_total = $postData['jardelle_total'] ;
        $data->condom_male_and_female_dispensed = $postData['condom_male_and_female_dispensed'] ;
        $data->condom_male_and_female_used = $postData['condom_male_and_female_used'] ;
        $data->condom_male_and_female_total = $postData['condom_male_and_female_total'] ;

        $data->save();
         
        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'New Record Add In Family Planning';
        $data1['record_id'] = $data->id;
        $data1['route_name'] = 'family-planning-record';
        $data1['type'] = 'record_office';

        activity($data1);
        Session::flash('successMessage', 'Record added successfully!');
         return redirect()->route('family-planning-record');
    }
    
    public function update($id, Request $request)
    {
        $userId = Auth::user()->id;
        $request->flash();
        $postData = $request->all();
        
        $data = FamilyPlanningRecordOffice::find($id);
        $data->added_by = $userId;
        $data->month = $postData['month'] ;
        $data->year = $postData['year'] ;
        $data->hospital_id = Auth::user()->hospital_name;
        $data->new_family_planning_acceptors_dispensed = $postData['new_family_planning_acceptors_dispensed'] ;
        $data->new_family_planning_acceptors_used = $postData['new_family_planning_acceptors_used'] ;
        $data->new_family_planning_acceptors_total = $postData['new_family_planning_acceptors_total'] ;
        $data->depo_provera_Injection_dispensed = $postData['depo_provera_Injection_dispensed'] ;
        $data->depo_provera_Injection_used = $postData['depo_provera_Injection_used'] ;
        $data->depo_provera_Injection_total = $postData['depo_provera_Injection_total'] ;
        $data->exlution_microlut_dispensed = $postData['exlution_microlut_dispensed'] ;
        $data->exlution_microlut_used = $postData['exlution_microlut_used'] ;
        $data->exlution_microlut_total = $postData['exlution_microlut_total'] ;
        $data->iucd_dispensed = $postData['iucd_dispensed'] ;
        $data->iucd_used = $postData['iucd_used'] ;
        $data->iucd_total = $postData['iucd_total'] ;
        $data->iucd_dispensed = $postData['lo_feminal_dispensed'] ;
        $data->iucd_used = $postData['lo_feminal_used'] ;
        $data->iucd_total = $postData['lo_feminal_total'] ;
        $data->iucd_dispensed = $postData['microgynon_dispensed'] ;
        $data->iucd_used = $postData['microgynon_used'] ;
        $data->iucd_total = $postData['microgynon_total'] ;
        $data->iucd_dispensed = $postData['noristerat_dispensed'] ;
        $data->iucd_used = $postData['noristerat_used'] ;
        $data->iucd_total = $postData['noristerat_total'] ;
        $data->iucd_dispensed = $postData['implanon_dispensed'] ;
        $data->iucd_used = $postData['implanon_used'] ;
        $data->iucd_total = $postData['implanon_total'] ;
        $data->iucd_dispensed = $postData['jardelle_dispensed'] ;
        $data->iucd_used = $postData['jardelle_used'] ;
        $data->iucd_total = $postData['jardelle_total'] ;
        $data->iucd_dispensed = $postData['condom_male_and_female_dispensed'] ;
        $data->iucd_used = $postData['condom_male_and_female_used'] ;
        $data->iucd_total = $postData['condom_male_and_female_total'] ;
        
        $data->save();
        
        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'Record Edit In Family Planning';
        $data1['record_id'] = $data->id;
        $data1['route_name'] = 'family-planning-record';
        $data1['type'] = 'record_office';

        activity($data1);
        Session::flash('successMessage', 'Record updated successfully!');
        
        return redirect()->route('family-planning-record');
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
        $data = FamilyPlanningRecordOffice::orderBy('id','desc')
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

        return view('family-planning-record.view', ['data' => $data,'hospital_name' => $hospital_name,'added_by' => $added_by,'postData'=>$postData]);

    }
}

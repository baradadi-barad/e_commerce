<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\FamilyPlanning;
use Session;
use Auth;
use App\Hospitals;
use App\User;
use DB;

class FamilyPlanningController extends Controller
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
        $data = FamilyPlanning::orderBy('id','desc')->where('added_by',$userId)->with('addedBy')->get();
        $hospital_name = Hospitals::get();
        $added_by = User::where('status','enable')->get();
        
        return view('family-planning.view', ['data' => $data,'hospital_name' => $hospital_name,'added_by' => $added_by]);
    }
    public function add(Request $request)
    {
        $current_year = date('Y');
        $years = range($current_year-5, $current_year+10);
        $current_month = date('m');
        return view('family-planning.add',['years'=>$years],['currentMonth'=>$current_month]);
    }
    public function delete($id)
    {
        FamilyPlanning::find($id)->delete();

        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'Record Delete In Family Planning';
        $data1['record_id'] = 0;
        $data1['route_name'] = 'family-planning';
        $data1['type'] = 'nhmis';

        activity($data1);
        Session::flash('errorMessage', 'Record deleted successfully!');

        return redirect()->route('family-planning');
    }
    public function edit($id)
    {
        $current_year = date('Y');
        $years = range($current_year-5, $current_year+10);
        $current_month = date('m');
        $data = FamilyPlanning::find($id);
        return view('family-planning.edit', ['data' => $data],['years'=>$years]);
    }
    public function display($id)
    {
        $current_year = date('Y');
        $years = range($current_year-5, $current_year+10);
        $current_month = date('m');
        $data = FamilyPlanning::find($id);
        return view('family-planning.display', ['data' => $data],['years'=>$years]);
    }
    public function insert(Request $request)
    {
       $request->flash();
        $postData = $request->all();
        $userId = Auth::user()->id;
        
        $data = new FamilyPlanning;
        $data->added_by = $userId;
        $data->month = $postData['month'] ;
        $data->year = $postData['year'] ;
        $data->hospital_id = Auth::user()->hospital_name;
        $data->clients_counselled = $postData['clients_counselled'] ;
        $data->new_fp_acceptors = $postData['new_fp_acceptors'] ;
        $data->fp_ca_hct_services = $postData['fp_ca_hct_services'] ;
        $data->ir_fp_services_from_hct = $postData['ir_fp_services_from_hct'] ;
        $data->ir_fp_services_from_art = $postData['ir_fp_services_from_art'] ;
        $data->females_aged_15to49y_mc = $postData['females_aged_15to49y_mc'] ;
        $data->persons_given_oral_pills = $postData['persons_given_oral_pills'] ;
        $data->oral_pill_cycle_dispensed = $postData['oral_pill_cycle_dispensed'] ;
        $data->injectables_given = $postData['injectables_given'] ;
        $data->iucd_inserted = $postData['iucd_inserted'] ;
        $data->implants_inserted = $postData['implants_inserted'] ;
        $data->sterilization = $postData['sterilization'] ;
        $data->male_condoms_distributed = $postData['male_condoms_distributed'] ;
        $data->female_condoms_distributed = $postData['female_condoms_distributed'] ;
        $data->ir_fp_services_from_pmtct = $postData['ir_fp_services_from_pmtct'] ;
        $data->save();
        
        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'New Record Add In Family Planning';
        $data1['record_id'] = $data->id;
        $data1['route_name'] = 'family-planning';
        $data1['type'] = 'nhmis';

        activity($data1);

        Session::flash('successMessage', 'Record added successfully!');
        return redirect()->route('family-planning');
    }
    public function update($id, Request $request)
    {
        $userId = Auth::user()->id;
        $request->flash();
        $postData = $request->all();
        
        $data = FamilyPlanning::find($id);
        $data->added_by = $userId;
        $data->month = $postData['month'] ;
        $data->year = $postData['year'] ;
        $data->hospital_id = Auth::user()->hospital_name;
        $data->clients_counselled = $postData['clients_counselled'] ;
        $data->new_fp_acceptors = $postData['new_fp_acceptors'] ;
        $data->fp_ca_hct_services = $postData['fp_ca_hct_services'] ;
        $data->ir_fp_services_from_hct = $postData['ir_fp_services_from_hct'] ;
        $data->ir_fp_services_from_art = $postData['ir_fp_services_from_art'] ;
        $data->females_aged_15to49y_mc = $postData['females_aged_15to49y_mc'] ;
        $data->persons_given_oral_pills = $postData['persons_given_oral_pills'] ;
        $data->oral_pill_cycle_dispensed = $postData['oral_pill_cycle_dispensed'] ;
        $data->injectables_given = $postData['injectables_given'] ;
        $data->iucd_inserted = $postData['iucd_inserted'] ;
        $data->implants_inserted = $postData['implants_inserted'] ;
        $data->sterilization = $postData['sterilization'] ;
        $data->male_condoms_distributed = $postData['male_condoms_distributed'] ;
        $data->female_condoms_distributed = $postData['female_condoms_distributed'] ;
        $data->ir_fp_services_from_pmtct = $postData['ir_fp_services_from_pmtct'] ;
        $data->save();
        
        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'Record Edit In Family Planning';
        $data1['record_id'] = $data->id;
        $data1['route_name'] = 'family-planning';
        $data1['type'] = 'nhmis';

        activity($data1);
        Session::flash('successMessage', 'Record updated successfully!');
        
        return redirect()->route('family-planning');
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
        $data = FamilyPlanning::orderBy('id','desc')
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

        return view('family-planning.view', ['data' => $data,'hospital_name' => $hospital_name,'added_by' => $added_by,'postData'=>$postData]);

    }
}

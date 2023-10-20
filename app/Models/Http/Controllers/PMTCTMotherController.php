<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\PMTCTMother;
use Session;
use Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use App\Hospitals;
use App\User;
use DB;

class PMTCTMotherController extends Controller
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
        
       return view('pmtct-mother.add',['years'=>$years],['currentMonth'=>$current_month]);
    }
    
     public function view()
    {
        $userId = Auth::user()->id;       
        $data = PMTCTMother::orderBy('id','desc')->where('added_by',$userId)->with('addedBy')->paginate(10);
       
        return view('pmtct-mother.display', ['data' => $data]);
    }
    public function display($id)
    {
        $current_year = date('Y');
        $years = range($current_year-5, $current_year+10);
        $current_month = date('m');
        
        $data = PMTCTMother::find($id);
        return view('pmtct-mother.display', ['data' => $data],['years'=>$years]);
    }
     public function index()
    {
        $userId = Auth::user()->id;       
        $data = PMTCTMother::orderBy('id','desc')->where('added_by',$userId)->with('addedBy')->get();
        $hospital_name = Hospitals::get();
        $added_by = User::where('status','enable')->get();
       
        return view('pmtct-mother.view', ['data' => $data,'hospital_name' => $hospital_name,'added_by' => $added_by]);
    }
    
     public function delete($id)
    {
        PMTCTMother::find($id)->delete();

        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'Record Delete In PMTCT Mother';
        $data1['record_id'] = 0;
        $data1['route_name'] = 'pmtct-mother';
        $data1['type'] = 'art';

        activity($data1);
        Session::flash('errorMessage', 'Record deleted successfully!');

        return redirect()->route('pmtct-mother');
    }
    public function edit($id)
    {
        $current_year = date('Y');
        $years = range($current_year-5, $current_year+10);
        $current_month = date('m');
        
        $data = PMTCTMother::find($id);
        return view('pmtct-mother.edit', ['data' => $data],['years'=>$years]);
    }
     public function insert(Request $request){
        
        $userId = Auth::user()->id;
        $request->flash();
        $postData = $request->all();

        $data = new PMTCTMother;
        $data->added_by = $userId;
        $data->month = $postData['month'] ;
        $data->year = $postData['year'] ;
        $data->hospital_id = Auth::user()->hospital_name;
        $data->pregnant_women_tested_hiv_positive_total = $postData['pregnant_women_tested_hiv_positive_total'] ;
        $data->anc_women_with_previously_hiv_status_total = $postData['anc_women_with_previously_hiv_status_total'] ;
        $data->pregnant_women_testing_received_results_anc_total = $postData['pregnant_women_testing_received_results_anc_total'] ;
        $data->pregnant_women_testing_received_results_l_d_total = $postData['pregnant_women_testing_received_results_l_d_total'] ;
        $data->pregnant_women_testing_received_results_pnc_total = $postData['pregnant_women_testing_received_results_pnc_total'] ;
        $data->partners_hiv_positive_pregnant_women_tested_hiv_negative = $postData['partners_hiv_positive_pregnant_women_tested_hiv_negative'] ;
        $data->partners_hiv_positive_pregnant_women_tested_hiv_positive = $postData['partners_hiv_positive_pregnant_women_tested_hiv_positive'] ;
        $data->partners_hiv_negative_pregnant_women_tested_hiv_positive = $postData['partners_hiv_negative_pregnant_women_tested_hiv_positive'] ;
        $data->partners_hiv_negative_pregnant_women_tested_hiv_negative = $postData['partners_hiv_negative_pregnant_women_tested_hiv_negative'] ;
        $data->hiv_positive_pregnant_women_art_eligibility_stage_cd4 = $postData['hiv_positive_pregnant_women_art_eligibility_stage_cd4'] ;
        $data->pregnant_hiv_positive_woman_arv_prophylaxis_pmtct_triple = $postData['pregnant_hiv_positive_woman_arv_prophylaxis_pmtct_triple'] ;
        $data->pregnant_hiv_positive_woman_arv_prophylaxis_pmtct_sdnvp_labour = $postData['pregnant_hiv_positive_woman_arv_prophylaxis_pmtct_sdnvp_labour'] ;
        $data->pregnant_hiv_positive_woman_arv_prophylaxis_pmtct_azt = $postData['pregnant_hiv_positive_woman_arv_prophylaxis_pmtct_azt'] ;
        $data->pregnant_hiv_positive_woman_arv_prophylaxis_pmtct_sdnvp_labour_o = $postData['pregnant_hiv_positive_woman_arv_prophylaxis_pmtct_sdnvp_labour_o'] ;
        $data->pregnant_hiv_positive_woman_arv_prophylaxis_pmtct_total = $postData['pregnant_hiv_positive_woman_arv_prophylaxis_pmtct_total'] ;
       
        $data->save();
         
        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'New Record Add In PMTCT Mother';
        $data1['record_id'] = $data->id;
        $data1['route_name'] = 'pmtct-mother';
        $data1['type'] = 'art';

        activity($data1);
        Session::flash('successMessage', 'Record added successfully!');
         return redirect()->route('pmtct-mother');
    }
    
    public function update($id, Request $request)
    {
        $userId = Auth::user()->id;
        $request->flash();
        $postData = $request->all();
        
        $data = PMTCTMother::find($id);
        $data->added_by = $userId;
        $data->month = $postData['month'] ;
        $data->year = $postData['year'] ;
        $data->hospital_id = Auth::user()->hospital_name;
        $data->pregnant_women_tested_hiv_positive_total = $postData['pregnant_women_tested_hiv_positive_total'] ;
        $data->anc_women_with_previously_hiv_status_total = $postData['anc_women_with_previously_hiv_status_total'] ;
        $data->pregnant_women_testing_received_results_anc_total = $postData['pregnant_women_testing_received_results_anc_total'] ;
        $data->pregnant_women_testing_received_results_l_d_total = $postData['pregnant_women_testing_received_results_l_d_total'] ;
        $data->pregnant_women_testing_received_results_pnc_total = $postData['pregnant_women_testing_received_results_pnc_total'] ;
        $data->partners_hiv_positive_pregnant_women_tested_hiv_negative = $postData['partners_hiv_positive_pregnant_women_tested_hiv_negative'] ;
        $data->partners_hiv_positive_pregnant_women_tested_hiv_positive = $postData['partners_hiv_positive_pregnant_women_tested_hiv_positive'] ;
        $data->partners_hiv_negative_pregnant_women_tested_hiv_positive = $postData['partners_hiv_negative_pregnant_women_tested_hiv_positive'] ;
        $data->partners_hiv_negative_pregnant_women_tested_hiv_negative = $postData['partners_hiv_negative_pregnant_women_tested_hiv_negative'] ;
        $data->hiv_positive_pregnant_women_art_eligibility_stage_cd4 = $postData['hiv_positive_pregnant_women_art_eligibility_stage_cd4'] ;
        $data->pregnant_hiv_positive_woman_arv_prophylaxis_pmtct_triple = $postData['pregnant_hiv_positive_woman_arv_prophylaxis_pmtct_triple'] ;
        $data->pregnant_hiv_positive_woman_arv_prophylaxis_pmtct_sdnvp_labour = $postData['pregnant_hiv_positive_woman_arv_prophylaxis_pmtct_sdnvp_labour'] ;
        $data->pregnant_hiv_positive_woman_arv_prophylaxis_pmtct_azt = $postData['pregnant_hiv_positive_woman_arv_prophylaxis_pmtct_azt'] ;
        $data->pregnant_hiv_positive_woman_arv_prophylaxis_pmtct_sdnvp_labour_o = $postData['pregnant_hiv_positive_woman_arv_prophylaxis_pmtct_sdnvp_labour_o'] ;
        $data->pregnant_hiv_positive_woman_arv_prophylaxis_pmtct_total = $postData['pregnant_hiv_positive_woman_arv_prophylaxis_pmtct_total'] ;
       
        $data->save();
        
        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'Record Edit In PMTCT Mother';
        $data1['record_id'] = $data->id;
        $data1['route_name'] = 'pmtct-mother';
        $data1['type'] = 'art';

        activity($data1);
        Session::flash('successMessage', 'Record updated successfully!');
        
        return redirect()->route('pmtct-mother');
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
        $data = PMTCTMother::orderBy('id','desc')
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
        
        return view('pmtct-mother.view', ['data' => $data,'hospital_name' => $hospital_name,'added_by' => $added_by,'postData'=>$postData]);

    }
}

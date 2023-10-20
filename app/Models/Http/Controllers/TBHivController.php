<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\TBHiv;
use Session;
use Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use App\Hospitals;
use App\User;
use DB;

class TBHivController extends Controller
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
        
       return view('tb-hiv.add',['years'=>$years],['currentMonth'=>$current_month]);
    }
    
     public function view()
    {
        $userId = Auth::user()->id;       
        $data = TBHiv::orderBy('id','desc')->where('added_by',$userId)->with('addedBy')->paginate(10);
       
        return view('tb-hiv.display', ['data' => $data]);
    }
    public function display($id)
    {
        $current_year = date('Y');
        $years = range($current_year-5, $current_year+10);
        $current_month = date('m');
        
        $data = TBHiv::find($id);
        return view('tb-hiv.display', ['data' => $data],['years'=>$years]);
    }
     public function index()
    {
        $userId = Auth::user()->id;       
        $data = TBHiv::orderBy('id','desc')->where('added_by',$userId)->with('addedBy')->get();
        $hospital_name = Hospitals::get();
        $added_by = User::where('status','enable')->get();
       
        return view('tb-hiv.view', ['data' => $data,'hospital_name' => $hospital_name,'added_by' => $added_by]);
    }
    
     public function delete($id)
    {
        
        TBHiv::find($id)->delete();
        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'Record Delete In TB/HIV';
        $data1['record_id'] = 0;
        $data1['route_name'] = 'tb-hiv';
        $data1['type'] = 'art';

        activity($data1);
        Session::flash('errorMessage', 'Record deleted successfully!');

        return redirect()->route('tb-hiv');
    }
    public function edit($id)
    {
        $current_year = date('Y');
        $years = range($current_year-5, $current_year+10);
        $current_month = date('m');
        
        $data = TBHiv::find($id);
        return view('tb-hiv.edit', ['data' => $data],['years'=>$years]);
    }
     public function insert(Request $request){
        
        $userId = Auth::user()->id;
        $request->flash();
        $postData = $request->all();

        $data = new TBHiv;
        $data->added_by = $userId;
        $data->month = $postData['month'] ;
        $data->year = $postData['year'] ;
        $data->hospital_id = Auth::user()->hospital_name;
        $data->individuals_clinically_screened_tb_total = $postData['individuals_clinically_screened_tb_total'] ;
        $data->individuals_clinically_screened_score1_total = $postData['individuals_clinically_screened_score1_total'] ;
        $data->registered_tb_patients_for_hiv_total = $postData['registered_tb_patients_for_hiv_total'] ;
        $data->individuals_started_tb_treatment_hiv_negative_total = $postData['individuals_started_tb_treatment_hiv_negative_total'] ;
        $data->individuals_started_tb_treatment_hiv_unknown_total = $postData['individuals_started_tb_treatment_hiv_unknown_total'] ;
        $data->hiv_positive_clients_attending_hiv_total = $postData['hiv_positive_clients_attending_hiv_total'] ;
        $data->tb_patients_with_hiv_receiving_art_total = $postData['tb_patients_with_hiv_receiving_art_total'] ;
        $data->co_infected_persons_on_cpt_total = $postData['co_infected_persons_on_cpt_total'] ;
       
        $data->save();
         
        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'New Record Add In TB/HIV';
        $data1['record_id'] = $data->id;
        $data1['route_name'] = 'tb-hiv';
        $data1['type'] = 'art';

        activity($data1);
        Session::flash('successMessage', 'Record added successfully!');
         return redirect()->route('tb-hiv');
    }
    
    public function update($id, Request $request)
    {
        $userId = Auth::user()->id;
        $request->flash();
        $postData = $request->all();
        
        $data = TBHiv::find($id);
        $data->added_by = $userId;
        $data->month = $postData['month'] ;
        $data->year = $postData['year'] ;
        $data->hospital_id = Auth::user()->hospital_name;
        $data->individuals_clinically_screened_tb_total = $postData['individuals_clinically_screened_tb_total'] ;
        $data->individuals_clinically_screened_score1_total = $postData['individuals_clinically_screened_score1_total'] ;
        $data->registered_tb_patients_for_hiv_total = $postData['registered_tb_patients_for_hiv_total'] ;
        $data->individuals_started_tb_treatment_hiv_negative_total = $postData['individuals_started_tb_treatment_hiv_negative_total'] ;
        $data->individuals_started_tb_treatment_hiv_unknown_total = $postData['individuals_started_tb_treatment_hiv_unknown_total'] ;
        $data->hiv_positive_clients_attending_hiv_total = $postData['hiv_positive_clients_attending_hiv_total'] ;
        $data->tb_patients_with_hiv_receiving_art_total = $postData['tb_patients_with_hiv_receiving_art_total'] ;
        $data->co_infected_persons_on_cpt_total = $postData['co_infected_persons_on_cpt_total'] ;
        
        $data->save();
        
        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'Record Edit In TB/HIV';
        $data1['record_id'] = $data->id;
        $data1['route_name'] = 'tb-hiv';
        $data1['type'] = 'art';

        activity($data1);
        Session::flash('successMessage', 'Record updated successfully!');
        
        return redirect()->route('tb-hiv');
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
        $data = TBHiv::orderBy('id','desc')
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
        
        return view('tb-hiv.view', ['data' => $data,'hospital_name' => $hospital_name,'added_by' => $added_by,'postData'=>$postData]);

    }
}

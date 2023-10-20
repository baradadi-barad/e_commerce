<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\AccidentEmergency;
use Session;
use Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use App\Hospitals;
use App\User;
use DB;

class AccidentEmergencyController extends Controller
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
        
       return view('accident-emergency.add',['years'=>$years],['currentMonth'=>$current_month]);
    }
    
     public function view()
    {
        $userId = Auth::user()->id;       
        $data = AccidentEmergency::orderBy('id','desc')->where('added_by',$userId)->with('addedBy')->paginate(10);
       
        return view('accident-emergency.display', ['data' => $data]);
    }
    public function display($id)
    {
        $current_year = date('Y');
        $years = range($current_year-5, $current_year+10);
        $current_month = date('m');
        
        $data = AccidentEmergency::find($id);
        return view('accident-emergency.display', ['data' => $data],['years'=>$years]);
    }
     public function index()
    {
        $userId = Auth::user()->id;       
        $data = AccidentEmergency::orderBy('id','desc')->where('added_by',$userId)->with('addedBy')->get();
        $hospital_name = Hospitals::get();
        $added_by = User::where('status','enable')->get();
       
        return view('accident-emergency.view', ['data' => $data,'hospital_name' => $hospital_name,'added_by' => $added_by]);
    }
    
     public function delete($id)
    {
        
        AccidentEmergency::find($id)->delete();

        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'Record Delete In A&E';
        $data1['record_id'] = 0;
        $data1['route_name'] = 'accident-emergency';
        $data1['type'] = 'record_office';

        activity($data1);
        
        Session::flash('errorMessage', 'Record deleted successfully!');

        return redirect()->route('accident-emergency');
    }
    public function edit($id)
    {
        $current_year = date('Y');
        $years = range($current_year-5, $current_year+10);
        $current_month = date('m');
        
        $data = AccidentEmergency::find($id);
        return view('accident-emergency.edit', ['data' => $data],['years'=>$years]);
    }
     public function insert(Request $request){
        
        $userId = Auth::user()->id;
        $request->flash();
        $postData = $request->all();

        $data = new AccidentEmergency;
        $data->added_by = $userId;
        $data->month = $postData['month'] ;
        $data->year = $postData['year'] ;
        $data->rta_cases_male = $postData['rta_cases_male'] ;
        $data->rta_cases_female = $postData['rta_cases_female'] ;
        $data->rta_cases_total = $postData['rta_cases_total'] ;
        $data->dressing_a_e_male = $postData['dressing_a_e_male'] ;
        $data->dressing_a_e_female = $postData['dressing_a_e_female'] ;
        $data->dressing_a_e_total = $postData['dressing_a_e_total'] ;
        $data->dressing_sopd_male = $postData['dressing_sopd_male'] ;
        $data->dressing_sopd_female = $postData['dressing_sopd_female'] ;
        $data->dressing_sopd_total = $postData['dressing_sopd_total'] ;
        $data->injection_male = $postData['injection_male'] ;
        $data->injection_female = $postData['injection_female'] ;
        $data->injection_total = $postData['injection_total'] ;
        $data->bid_male = $postData['bid_male'] ;
        $data->bid_female = $postData['bid_female'] ;
        $data->bid_total = $postData['bid_total'] ;
        
        $data->save();

        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'New Record Add In A&E';
        $data1['record_id'] = $data->id;
        $data1['route_name'] = 'accident-emergency';
        $data1['type'] = 'record_office';

        activity($data1);
         
        Session::flash('successMessage', 'Record added successfully!');
         return redirect()->route('accident-emergency');
    }
    
    public function update($id, Request $request)
    {
        $userId = Auth::user()->id;
        $request->flash();
        $postData = $request->all();
        
        $data = AccidentEmergency::find($id);
        $data->added_by = $userId;
        $data->month = $postData['month'] ;
        $data->year = $postData['year'] ;
        $data->rta_cases_male = $postData['rta_cases_male'] ;
        $data->rta_cases_female = $postData['rta_cases_female'] ;
        $data->rta_cases_total = $postData['rta_cases_total'] ;
        $data->dressing_a_e_male = $postData['dressing_a_e_male'] ;
        $data->dressing_a_e_female = $postData['dressing_a_e_female'] ;
        $data->dressing_a_e_total = $postData['dressing_a_e_total'] ;
        $data->dressing_sopd_male = $postData['dressing_sopd_male'] ;
        $data->dressing_sopd_female = $postData['dressing_sopd_female'] ;
        $data->dressing_sopd_total = $postData['dressing_sopd_total'] ;
        $data->injection_male = $postData['injection_male'] ;
        $data->injection_female = $postData['injection_female'] ;
        $data->injection_total = $postData['injection_total'] ;
        $data->bid_male = $postData['bid_male'] ;
        $data->bid_female = $postData['bid_female'] ;
        $data->bid_total = $postData['bid_total'] ;
        
        $data->save();


        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'Record Edit In A&E';
        $data1['record_id'] = $data->id;
        $data1['route_name'] = 'accident-emergency';
        $data1['type'] = 'record_office';

        activity($data1);
        
        Session::flash('successMessage', 'Record updated successfully!');
        
        return redirect()->route('accident-emergency');
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
        $data = AccidentEmergency::orderBy('id','desc')
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

        return view('accident-emergency.view', ['data' => $data,'hospital_name' => $hospital_name,'added_by' => $added_by,'postData'=>$postData]);

    }
}

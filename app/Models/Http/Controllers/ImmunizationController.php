<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Immunization;
use Session;
use Auth;
use App\Hospitals;
use App\User;
use DB;

class ImmunizationController extends Controller
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
        $immunizationDisplay = Immunization::orderBy('id','desc')->where('added_by',$userId)->with('addedBy')->get();
        $hospital_name = Hospitals::get();
        $added_by = User::where('status','enable')->get();

        return view('immunization.view', ['immunizationDisplay' => $immunizationDisplay,'hospital_name' => $hospital_name,'added_by' => $added_by]);
    }
    public function add(Request $request)
    {
        $current_year = date('Y');
        $years = range($current_year-5, $current_year+10);
        $current_month = date('m');
        return view('immunization.add',['years'=>$years],['currentMonth'=>$current_month]);
    }
    public function delete($id)
    {
        Immunization::find($id)->delete();
        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'Record Delete In Immunization';
        $data1['record_id'] = 0;
        $data1['route_name'] = 'immunization';
        $data1['type'] = 'nhmis';

        activity($data1);
        Session::flash('successMessage', 'Record deleted successfully!');

        return redirect()->route('immunization');
    }
    public function edit($id)
    {
            $current_year = date('Y');
        $years = range($current_year-5, $current_year+10);
        $current_month = date('m');
        $immunizationEdit = Immunization::find($id);
        return view('immunization.edit', ['immunizationEdit' => $immunizationEdit],['years'=>$years]);
    }
    public function display($id)
    {
            $current_year = date('Y');
        $years = range($current_year-5, $current_year+10);
        $current_month = date('m');
          $data = Immunization::find($id);
        return view('immunization.display', ['data' => $data],['years'=>$years]);
    }
    public function insert(Request $request)
    {
        $userId = Auth::user()->id;
        $request->flash();
        $postData = $request->all();

        $immunization = new Immunization;
        $immunization->added_by = $userId;
        $immunization->month = $postData['month'] ;
        $immunization->year = $postData['year'] ;
        $data->hospital_id = Auth::user()->hospital_name;
        $immunization->opv_0_birth = $postData['opv_0_birth'] ;
        $immunization->hep_b_0_birth = $postData['hep_b_0_birth'] ;
        $immunization->bcg = $postData['bcg'] ;
        $immunization->opv_1 = $postData['opv_1'] ;
        $immunization->hep_b_1 = $postData['hep_b_1'] ;
        $immunization->penta_1 = $postData['penta_1'] ;
        $immunization->dpt_1 = $postData['dpt_1'] ;
        $immunization->pcv_1 = $postData['pcv_1'] ;
        $immunization->opv_2 = $postData['opv_2'] ;
        $immunization->hep_b_2 = $postData['hep_b_2'] ;
        $immunization->penta_2 = $postData['penta_2'] ;
        $immunization->dpt_2 = $postData['dpt_2'] ;
        $immunization->pcv_2 = $postData['pcv_2'] ;
        $immunization->opv_3 = $postData['opv_3'] ;
        $immunization->penta_3 = $postData['penta_3'] ;
        $immunization->dpt_3 = $postData['dpt_3'] ;
        $immunization->pcv_3 = $postData['pcv_3'] ;
        $immunization->measles_1 = $postData['measles_1'] ;
        $immunization->fully_immunized_l1_year = $postData['fully_immunized_l1_year'] ;
        $immunization->yellow_fever = $postData['yellow_fever'] ;
        $immunization->measles_2 = $postData['measles_2'] ;
        $immunization->conjugate_a_csm = $postData['conjugate_a_csm'] ;
        $immunization->save();

        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'New Record Add In Immunization';
        $data1['record_id'] = $data->id;
        $data1['route_name'] = 'immunization';
        $data1['type'] = 'nhmis';

        activity($data1);
            //store status message
        Session::flash('successMessage', 'Record added successfully!');
        return redirect()->route('immunization');
    }
    public function update($id, Request $request)
    {
       $postData = $request->all();
        $id =$id;
         $userId = Auth::user()->id;
        $immunization = Immunization::find($id);

        $immunization->added_by = $userId;
        $immunization->month = $postData['month'] ;
        $immunization->year = $postData['year'] ;
        $data->hospital_id = Auth::user()->hospital_name;
        $immunization->opv_0_birth = $postData['opv_0_birth'] ;
        $immunization->hep_b_0_birth = $postData['hep_b_0_birth'] ;
        $immunization->bcg = $postData['bcg'] ;
        $immunization->opv_1 = $postData['opv_1'] ;
        $immunization->hep_b_1 = $postData['hep_b_1'] ;
        $immunization->penta_1 = $postData['penta_1'] ;
        $immunization->dpt_1 = $postData['dpt_1'] ;
        $immunization->pcv_1 = $postData['pcv_1'] ;
        $immunization->opv_2 = $postData['opv_2'] ;
        $immunization->hep_b_2 = $postData['hep_b_2'] ;
        $immunization->penta_2 = $postData['penta_2'] ;
        $immunization->dpt_2 = $postData['dpt_2'] ;
        $immunization->pcv_2 = $postData['pcv_2'] ;
        $immunization->opv_3 = $postData['opv_3'] ;
        $immunization->penta_3 = $postData['penta_3'] ;
        $immunization->dpt_3 = $postData['dpt_3'] ;
        $immunization->pcv_3 = $postData['pcv_3'] ;
        $immunization->measles_1 = $postData['measles_1'] ;
        $immunization->fully_immunized_l1_year = $postData['fully_immunized_l1_year'] ;
        $immunization->yellow_fever = $postData['yellow_fever'] ;
        $immunization->measles_2 = $postData['measles_2'] ;
        $immunization->conjugate_a_csm = $postData['conjugate_a_csm'] ;
        $immunization->save();
        
        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'Record Edit In Immunization';
        $data1['record_id'] = $data->id;
        $data1['route_name'] = 'immunization';
        $data1['type'] = 'nhmis';

        activity($data1);
        
        Session::flash('successMessage', 'Record Update successfully!');
        return redirect()->route('immunization');
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
        $immunizationDisplay = Immunization::orderBy('id','desc')
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
        
        return view('immunization.view', ['immunizationDisplay' => $immunizationDisplay,'hospital_name' => $hospital_name,'added_by' => $added_by,'postData'=>$postData]);

    }
}

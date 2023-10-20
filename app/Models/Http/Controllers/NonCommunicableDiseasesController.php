<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\NonCommunicableDiseases;
use Session;
use Auth;
use App\Hospitals;
use App\User;
use DB;

class NonCommunicableDiseasesController extends Controller
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
        $data = NonCommunicableDiseases::orderBy('id','desc')->where('added_by',$userId)->with('addedBy')->get();
        $hospital_name = Hospitals::get();
        $added_by = User::where('status','enable')->get();

        return view('non-communicable-diseases.view', ['data' => $data,'hospital_name' => $hospital_name,'added_by' => $added_by]);
    }
    public function add(Request $request)
    {
        $current_year = date('Y');
        $years = range($current_year-5, $current_year+10);
        $current_month = date('m');
        return view('non-communicable-diseases.add',['years'=>$years],['currentMonth'=>$current_month]);
    }
    public function delete($id)
    {
        NonCommunicableDiseases::find($id)->delete();
        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'Record Delete In Non Communicable Diseases';
        $data1['record_id'] = 0;
        $data1['route_name'] = 'non-communicable-diseases';
        $data1['type'] = 'nhmis';

        activity($data1);
        Session::flash('errorMessage', 'Record deleted successfully!');

        return redirect()->route('non-communicable-diseases');
    }
    public function edit($id)
    {
        $current_year = date('Y');
        $years = range($current_year-5, $current_year+10);
        $current_month = date('m');
        $data = NonCommunicableDiseases::find($id);
        return view('non-communicable-diseases.edit', ['data' => $data],['years'=>$years]);
    }
     public function display($id)
    {
         $current_year = date('Y');
        $years = range($current_year-5, $current_year+10);
        $current_month = date('m');
         $userId = Auth::user()->id;   
        $data = NonCommunicableDiseases::find($id);
        return view('non-communicable-diseases.display', ['data' => $data],['years'=>$years]);
    }
    public function insert(Request $request)
    {
        $userId = Auth::user()->id;
        $request->flash();
        $postData = $request->all();

        $communicablediseases = new NonCommunicableDiseases;
        $communicablediseases->added_by = $userId;
         $communicablediseases->month = $postData['month'] ;
        $communicablediseases->year = $postData['year'] ;
        $data->hospital_id = Auth::user()->hospital_name;
        $communicablediseases->coronary_heart_disease_nc = $postData['coronary_heart_disease_nc'] ;
        $communicablediseases->diabetes_mellitus_nc = $postData['diabetes_mellitus_nc'] ;
        $communicablediseases->hypertension_nc = $postData['hypertension_nc'] ;
        $communicablediseases->sickle_cell_disease_nc = $postData['sickle_cell_disease_nc'] ;
        $communicablediseases->road_traffic_accident_nc = $postData['road_traffic_accident_nc'] ;
        $communicablediseases->home_accident_nc = $postData['home_accident_nc'] ;
        $communicablediseases->snake_bites_nc = $postData['snake_bites_nc'] ;
        $communicablediseases->asthma_nc = $postData['asthma_nc'] ;
        $communicablediseases->athritis_nc = $postData['athritis_nc'] ;
        $communicablediseases->save();

        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'New Record Add In Non Communicable Diseases';
        $data1['record_id'] = $data->id;
        $data1['route_name'] = 'non-communicable-diseases';
        $data1['type'] = 'nhmis';

        activity($data1);
        Session::flash('successMessage', 'Record added successfully!');
        return redirect()->route('non-communicable-diseases');
    }
    public function update($id, Request $request)
    {
        $request->flash();
        $postData = $request->all();
        
        $communicablediseases = NonCommunicableDiseases::find($id);
         $communicablediseases->month = $postData['month'] ;
        $communicablediseases->year = $postData['year'] ;
        $data->hospital_id = Auth::user()->hospital_name;
        $communicablediseases->coronary_heart_disease_nc = $postData['coronary_heart_disease_nc'] ;
        $communicablediseases->diabetes_mellitus_nc = $postData['diabetes_mellitus_nc'] ;
        $communicablediseases->hypertension_nc = $postData['hypertension_nc'] ;
        $communicablediseases->sickle_cell_disease_nc = $postData['sickle_cell_disease_nc'] ;
        $communicablediseases->road_traffic_accident_nc = $postData['road_traffic_accident_nc'] ;
        $communicablediseases->home_accident_nc = $postData['home_accident_nc'] ;
        $communicablediseases->snake_bites_nc = $postData['snake_bites_nc'] ;
        $communicablediseases->asthma_nc = $postData['asthma_nc'] ;
        $communicablediseases->athritis_nc = $postData['athritis_nc'] ;
        $communicablediseases->save();
        
        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'Record Edit In Non Communicable Diseases';
        $data1['record_id'] = $data->id;
        $data1['route_name'] = 'non-communicable-diseases';
        $data1['type'] = 'nhmis';

        activity($data1);
        Session::flash('successMessage', 'Record updated successfully!');
        
        return redirect()->route('non-communicable-diseases');
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
        $data = NonCommunicableDiseases::orderBy('id','desc')
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
        
        return view('non-communicable-diseases.view', ['data' => $data,'hospital_name' => $hospital_name,'added_by' => $added_by,'postData'=>$postData]);

    }
}

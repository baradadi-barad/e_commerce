<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\PatientGeneralStatistics;
use App\SurgeonPerformanceReport;
use Session;
use DB;
use Auth;
use App\Hospitals;
use App\User;

class SurgeonPerformanceReportController extends Controller
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
        $data = SurgeonPerformanceReport::with('addedBy')->where('added_by',$userId)->get();
        $hospital_name = Hospitals::get();
        $added_by = User::where('status','enable')->get();

        return view('surgeon-performance-report.view', ['data' => $data,'hospital_name' => $hospital_name,'added_by' => $added_by]);
    }
    public function add()
    {
        $current_year = date('Y');
        $years = range($current_year-5, $current_year+10);
        $current_month = date('m');
      
        return view('surgeon-performance-report.add',[
            'years'=>$years,
            'current_month'=>$current_month]);
    }
    public function insert(Request $request)
    {
        $userId = Auth::user()->id;
        $request->flash();
        $postData = $request->all();

        $data = new SurgeonPerformanceReport;
        $data->added_by = $userId;
        $data->doctors_name = $postData['doctors_name'] ;
        $data->month = $postData['month'] ;
        $data->year = $postData['year'] ;
        $data->major_operation_male = $postData['major_operation_male'] ;
        $data->major_operation_female = $postData['major_operation_female'] ;
        $data->intermediate_operation_male = $postData['intermediate_operation_male'] ;
        $data->intermediate_operation_female = $postData['intermediate_operation_female'] ;
        $data->minor_operation_male = $postData['minor_operation_male'] ;
        $data->minor_operation_female = $postData['minor_operation_female'] ;
        $data->save();

        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'New Record Add In Surgeon Performance Report';
        $data1['record_id'] = $data->id;
        $data1['route_name'] = 'surgeon-performance-report';
        $data1['type'] = 'm_e';

        activity($data1);
        Session::flash('successMessage', 'Record added successfully!');
        return redirect()->route('surgeon-performance-report');
    }
    public function delete($id)
    {
        SurgeonPerformanceReport::find($id)->delete();
        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'Record Delete In Surgeon Performance Report';
        $data1['record_id'] = 0;
        $data1['route_name'] = 'surgeon-performance-report';
        $data1['type'] = 'm_e';

        activity($data1);
        Session::flash('errorMessage', 'Record deleted successfully!');

        return redirect()->route('surgeon-performance-report');
    }
    public function edit($id)
    {
        
        $current_year = date('Y');
        $years = range($current_year-5, $current_year+10);
        $current_month = date('m');
                
        $data = SurgeonPerformanceReport::find($id);
        return view('surgeon-performance-report.edit', [
                'data' => $data,
                'years'=>$years]);
    }
    public function display($id)
    {
        
        $current_year = date('Y');
        $years = range($current_year-5, $current_year+10);
        $current_month = date('m');
                
        $patientreportEdit = SurgeonPerformanceReport::find($id);
        return view('surgeon-performance-report.display', [
                'patientreportEdit' => $patientreportEdit,
                'years'=>$years]);
    }
    public function update($id, Request $request)
    {
        $request->flash();
        $postData = $request->all();
         $userId = Auth::user()->id;
         
        $data = SurgeonPerformanceReport::find($id);
        $data->added_by = $userId;
        $data->doctors_name = $postData['doctors_name'] ;
        $data->month = $postData['month'] ;
        $data->year = $postData['year'] ;
        $data->major_operation_male = $postData['major_operation_male'] ;
        $data->major_operation_female = $postData['major_operation_female'] ;
        $data->intermediate_operation_male = $postData['intermediate_operation_male'] ;
        $data->intermediate_operation_female = $postData['intermediate_operation_female'] ;
        $data->minor_operation_male = $postData['minor_operation_male'] ;
        $data->minor_operation_female = $postData['minor_operation_female'] ;
        $data->save();
        
        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'Record Edit In Surgeon Performance Report';
        $data1['record_id'] = $data->id;
        $data1['route_name'] = 'surgeon-performance-report';
        $data1['type'] = 'm_e';

        activity($data1);
        Session::flash('successMessage', 'Record updated successfully!');
        
        return redirect()->route('surgeon-performance-report');
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
        $data = SurgeonPerformanceReport::orderBy('id','desc')
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

        return view('surgeon-performance-report.view', ['data' => $data,'hospital_name' => $hospital_name,'added_by' => $added_by,'postData'=>$postData]);

    }
}
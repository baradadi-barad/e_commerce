<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Hospitals;
use App\DoctorPerformance;
use Session;
use Auth;

class DoctorPerformanceController extends Controller
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
        if (Auth::user()->role == 'admin') {
            $data = DoctorPerformance::orderBy('id','desc')->with('addedBy')->with('hospitalName')->paginate(10);
        }else{
            $data = DoctorPerformance::orderBy('id','desc')->where('added_by',$userId)->with('hospitalName')->with('addedBy')->paginate(10);
        }
        
        $hospital = Hospitals::where('status','enable')->get();

        return view('doctor-performance.view', [
            'hospital' => $hospital,
            'data' => $data
       ]);
    }
    public function add(Request $request)
    {
        $current_year = date('Y');
        $currentMonth = date('m');
        
        $years = range($current_year-5, $current_year+10);
        $hospital = Hospitals::where('status','enable')->get();

        return view('doctor-performance.add', [
            'hospital' => $hospital,
            'years'=>$years,
            'currentMonth' => $currentMonth
       ]);
    }
    public function delete($id)
    {
        DoctorPerformance::find($id)->delete();

        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'Record Delete In Doctor Performance';
        $data1['record_id'] = 0;
        $data1['route_name'] = 'doctor-performance';
        $data1['type'] = 'm_e';

        activity($data1);
        Session::flash('errorMessage', 'Record deleted successfully!');

        return redirect()->route('doctor-performance');
    }
    public function edit($id)
    {
        $current_year = date('Y');
        $currentMonth = date('m');
        
        $years = range($current_year-5, $current_year+10);
        $data = DoctorPerformance::find($id);
        $hospital = Hospitals::where('status','enable')->get();

        return view('doctor-performance.edit', [
            'hospital' => $hospital,
            'data' => $data,
            'years'=>$years,
            'currentMonth' => $currentMonth
       ]);
    }
    public function insert(Request $request)
    {
        $request->flash();
        $postData = $request->all();
        $userId = Auth::user()->id;
        
        $data = new DoctorPerformance;
        $data->added_by = $userId;
        $data->doctor_id = $postData['doctor_id'] ;
        $data->no_of_patient_seen = $postData['no_of_patient_seen'] ;
        $data->month = $postData['month'] ;
        $data->year = $postData['year'] ;
        $data->no_of_operation = $postData['no_of_operation'] ;
        $data->hospital_id = $postData['hospital_id'] ;
        $data->save();

        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'New Record Add In Doctor Performance';
        $data1['record_id'] = $data->id;
        $data1['route_name'] = 'doctor-performance';
        $data1['type'] = 'm_e';

        activity($data1);
        Session::flash('successMessage', 'Record added successfully!');
        return redirect()->route('doctor-performance');
    }
    public function update($id, Request $request)
    {
        $request->flash();
        $postData = $request->all();
        
        $data = DoctorPerformance::find($id);
        $data->doctor_id = $postData['doctor_id'] ;
        $data->no_of_patient_seen = $postData['no_of_patient_seen'] ;
        $data->month = $postData['month'] ;
        $data->year = $postData['year'] ;
        $data->no_of_operation = $postData['no_of_operation'] ;
        $data->hospital_id = $postData['hospital_id'] ;
        $data->save();
        
        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'Record Edit In Doctor Performance';
        $data1['record_id'] = $data->id;
        $data1['route_name'] = 'doctor-performance';
        $data1['type'] = 'm_e';

        activity($data1);
        Session::flash('successMessage', 'Record updated successfully!');
        
        return redirect()->route('doctor-performance');
    }
}

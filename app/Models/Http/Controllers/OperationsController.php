<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Operations;
use Session;
use Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use App\Hospitals;
use App\User;
use DB;

class OperationsController extends Controller
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
        
       return view('operations.add',['years'=>$years],['currentMonth'=>$current_month]);
    }
    
     public function view()
    {
        $userId = Auth::user()->id;   
        $data = Operations::orderBy('id','desc')->where('added_by',$userId)->with('addedBy')->paginate(10);
       
        return view('operations.display', ['data' => $data]);
    }
    public function display($id)
    {
        $current_year = date('Y');
        $years = range($current_year-5, $current_year+10);
        $current_month = date('m');
        
        $data = Operations::find($id);
        return view('operations.display', ['data' => $data],['years'=>$years]);
    }
     public function index()
    {
        $userId = Auth::user()->id;       
        $data = Operations::orderBy('id','desc')->where('added_by',$userId)->with('addedBy')->get();
        $hospital_name = Hospitals::get();
        $added_by = User::where('status','enable')->get();
       
        return view('operations.view', ['data' => $data,'hospital_name' => $hospital_name,'added_by' => $added_by]);
    }
    
     public function delete($id)
    {
        
        Operations::find($id)->delete();
        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'Record Delete In Operation';
        $data1['record_id'] = 0;
        $data1['route_name'] = 'operations';
        $data1['type'] = 'record_office';

        activity($data1);
        Session::flash('errorMessage', 'Record deleted successfully!');

        return redirect()->route('operations');
    }
    public function edit($id)
    {
        $current_year = date('Y');
        $years = range($current_year-5, $current_year+10);
        $current_month = date('m');
        
        $data = Operations::find($id);
        return view('operations.edit', ['data' => $data],['years'=>$years]);
    }
     public function insert(Request $request){
        
        $userId = Auth::user()->id;
        $request->flash();
        $postData = $request->all();

        $data = new Operations;
        $data->added_by = $userId;
        $data->month = $postData['month'] ;
        $data->year = $postData['year'] ;
        
        $data->major_operation_male = $postData['major_operation_male'] ;
        $data->major_operation_female = $postData['major_operation_female'] ;
        $data->major_operation_total = $postData['major_operation_total'] ;
        
        $data->intermediate_operation_male = $postData['intermediate_operation_male'] ;
        $data->intermediate_operation_female = $postData['intermediate_operation_female'] ;
        $data->intermediate_operation_total = $postData['intermediate_operation_total'] ;
        
        $data->minor_operation_male = $postData['minor_operation_male'] ;
        $data->minor_operation_female = $postData['minor_operation_female'] ;
        $data->minor_operation_total = $postData['minor_operation_total'] ;
        
        $data->circumcision_male = $postData['circumcision_male'] ;
        $data->circumcision_female = $postData['circumcision_female'] ;
        $data->circumcision_total = $postData['circumcision_total'] ;
        
        $data->save();

        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'New Record Add In Operation';
        $data1['record_id'] = $data->id;
        $data1['route_name'] = 'operations';
        $data1['type'] = 'record_office';

        activity($data1);
         
        Session::flash('successMessage', 'Record added successfully!');
         return redirect()->route('operations');
    }
    
    public function update($id, Request $request)
    {
        $userId = Auth::user()->id;
        $request->flash();
        $postData = $request->all();
        
        $data = Operations::find($id);
        $data->added_by = $userId;
        $data->month = $postData['month'] ;
        $data->year = $postData['year'] ;
        
        $data->major_operation_male = $postData['major_operation_male'] ;
        $data->major_operation_female = $postData['major_operation_female'] ;
        $data->major_operation_total = $postData['major_operation_total'] ;
        
        $data->intermediate_operation_male = $postData['intermediate_operation_male'] ;
        $data->intermediate_operation_female = $postData['intermediate_operation_female'] ;
        $data->intermediate_operation_total = $postData['intermediate_operation_total'] ;
        
        $data->minor_operation_male = $postData['minor_operation_male'] ;
        $data->minor_operation_female = $postData['minor_operation_female'] ;
        $data->minor_operation_total = $postData['minor_operation_total'] ;
        
        $data->circumcision_male = $postData['circumcision_male'] ;
        $data->circumcision_female = $postData['circumcision_female'] ;
        $data->circumcision_total = $postData['circumcision_total'] ;
        
        $data->save();
        
        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'Record Edit In Operation';
        $data1['record_id'] = $data->id;
        $data1['route_name'] = 'operations';
        $data1['type'] = 'record_office';

        activity($data1);
        Session::flash('successMessage', 'Record updated successfully!');
        
        return redirect()->route('operations');
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
        $data = Operations::orderBy('id','desc')
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

        return view('operations.view', ['data' => $data,'hospital_name' => $hospital_name,'added_by' => $added_by,'postData'=>$postData]);

    }
}

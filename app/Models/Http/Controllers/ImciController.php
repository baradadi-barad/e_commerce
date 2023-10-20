<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Imci;
use Session;
use Auth;
use App\Hospitals;
use App\User;
use DB;

class ImciController extends Controller
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
        $data = Imci::orderBy('id','desc')->where('added_by',$userId)->with('addedBy')->get();
        $hospital_name = Hospitals::get();
        $added_by = User::where('status','enable')->get();

        return view('imci.view', ['data' => $data,'hospital_name' => $hospital_name,'added_by' => $added_by]);
    }
    public function add(Request $request)
    {
        $current_year = date('Y');
        $years = range($current_year-5, $current_year+10);
        $current_month = date('m');
        return view('imci.add',['years'=>$years],['currentMonth'=>$current_month]);
    }
    public function delete($id)
    {
        Imci::find($id)->delete();
        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'Record Delete In IMCI';
        $data1['record_id'] = 0;
        $data1['route_name'] = 'imci';
        $data1['type'] = 'nhmis';

        activity($data1);
        Session::flash('errorMessage', 'Record deleted successfully!');

        return redirect()->route('imci');
    }
    public function edit($id)
    {
        $current_year = date('Y');
        $years = range($current_year-5, $current_year+10);
        $current_month = date('m');
        $data = Imci::find($id);
        return view('imci.edit', ['data' => $data],['years'=>$years]);
    }
    public function display($id)
    {
        $current_year = date('Y');
        $years = range($current_year-5, $current_year+10);
        $current_month = date('m');
        $data = Imci::find($id);
        return view('imci.display', ['data' => $data],['years'=>$years]);
    }
    public function insert(Request $request)
    {
        $request->flash();
        $postData = $request->all();
        $userId = Auth::user()->id;
        
        $data = new Imci;
        $data->added_by = $userId;
         $data->month = $postData['month'] ;
        $data->year = $postData['year'] ;
        $data->hospital_id = Auth::user()->hospital_name;
        $data->diarrhoea_nc_lt5y = $postData['diarrhoea_nc_lt5y'] ;
        $data->diarrhoea_nc_lt5y_gorp = $postData['diarrhoea_nc_lt5y_gorp'] ;
        $data->diarrhoea_nc_lt5y_gozs = $postData['diarrhoea_nc_lt5y_gozs'] ;
        $data->pneumonia_nc_lt5y = $postData['pneumonia_nc_lt5y'] ;
        $data->pneumonia_nc_lt5y_ga = $postData['pneumonia_nc_lt5y_ga'] ;
        $data->measles_nc_lt5y = $postData['measles_nc_lt5y'] ;
        $data->save();

        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'New Record Add In IMCI';
        $data1['record_id'] = $data->id;
        $data1['route_name'] = 'imci';
        $data1['type'] = 'nhmis';

        activity($data1);
        Session::flash('successMessage', 'Record added successfully!');
        return redirect()->route('imci');
    }
    public function update($id, Request $request)
    {
        $request->flash();
        $postData = $request->all();
        
        $data = Imci::find($id);
         $data->month = $postData['month'] ;
        $data->year = $postData['year'] ;
        $data->hospital_id = Auth::user()->hospital_name;
        $data->diarrhoea_nc_lt5y = $postData['diarrhoea_nc_lt5y'] ;
        $data->diarrhoea_nc_lt5y_gorp = $postData['diarrhoea_nc_lt5y_gorp'] ;
        $data->diarrhoea_nc_lt5y_gozs = $postData['diarrhoea_nc_lt5y_gozs'] ;
        $data->pneumonia_nc_lt5y = $postData['pneumonia_nc_lt5y'] ;
        $data->pneumonia_nc_lt5y_ga = $postData['pneumonia_nc_lt5y_ga'] ;
        $data->measles_nc_lt5y = $postData['measles_nc_lt5y'] ;
        $data->save();
        
        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'Record Edit In IMCI';
        $data1['record_id'] = $data->id;
        $data1['route_name'] = 'imci';
        $data1['type'] = 'nhmis';

        activity($data1);
        Session::flash('successMessage', 'Record updated successfully!');
        
        return redirect()->route('imci');
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
        $data = Imci::orderBy('id','desc')
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
        
        return view('imci.view', ['data' => $data,'hospital_name' => $hospital_name,'added_by' => $added_by,'postData'=>$postData]);

    }
}

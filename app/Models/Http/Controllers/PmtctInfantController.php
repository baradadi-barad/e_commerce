<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\PmctcInfantForm;
use Session;
use Auth;
use App\Hospitals;
use App\User;
use DB; 

class PmtctInfantController extends Controller
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

    public function index()
    {
        $userId = Auth::user()->id;   
        $data = PmctcInfantForm::orderBy('id','desc')->where('added_by',$userId)->with('addedBy')->get();
        $hospital_name = Hospitals::get();
        $added_by = User::where('status','enable')->get();

        return view('pmtct-infant.view', ['data' => $data,'hospital_name' => $hospital_name,'added_by' => $added_by]);
    }
    public function add(Request $request)
    {
        $current_year = date('Y');
        $years = range($current_year-5, $current_year+10);
        $current_month = date('m');
        
        return view('pmtct-infant.add',['years'=>$years],['currentMonth'=>$current_month]);
    }
    public function delete($id)
    {
        PmctcInfantForm::find($id)->delete();
        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'Record Delete In PMTCT Infant';
        $data1['record_id'] = 0;
        $data1['route_name'] = 'pmtct-infant';
        $data1['type'] = 'art';

        activity($data1);
        Session::flash('errorMessage', 'Record deleted successfully!');

        return redirect()->route('pmtct-infant');
    }
    public function edit($id)
    {
         $current_year = date('Y');
        $years = range($current_year-5, $current_year+10);
        $current_month = date('m');
        
        $data = PmctcInfantForm::find($id);
        return view('pmtct-infant.edit', ['data' => $data],['years'=>$years],['currentMonth'=>$current_month]);
    }
    public function insert(Request $request)
    {
        $userId = Auth::user()->id;
        $request->flash();
        $postData = $request->all();
        
        $data = new PmctcInfantForm;
        foreach($postData as $key => $value){
            if($key != '_token'){
               $data->$key =  $value;
            }
        }
        $data->hospital_id = Auth::user()->hospital_name;
        $data->added_by = $userId;
        $data->save();
        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'New Record Add In PMTCT Infant';
        $data1['record_id'] = $data->id;
        $data1['route_name'] = 'pmtct-infant';
        $data1['type'] = 'art';

        activity($data1);
        
        Session::flash('successMessage', 'Record added successfully!');
        return redirect()->route('pmtct-infant');
    }
    public function update($id, Request $request)
    {
        $request->flash();
        $postData = $request->all();
        unset($postData['_token']);
        $data = PmctcInfantForm::find($id);
        $data->hospital_id = Auth::user()->hospital_name;
        foreach($postData as $key => $value){
            if($key != '_token'){
               $data->$key =  $value;
            }
        }
        $data->save();
        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'Record Edit In PMTCT Infant';
        $data1['record_id'] = $data->id;
        $data1['route_name'] = 'pmtct-infant';
        $data1['type'] = 'art';

        activity($data1);
        Session::flash('successMessage', 'Record updated successfully!');
        
        return redirect()->route('pmtct-infant');
    }
    public function display($id)
    {
        $data = PmctcInfantForm::find($id);
        return view('pmtct-infant.display', ['data' => $data]);
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
        $data = PmctcInfantForm::orderBy('id','desc')
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
        
        return view('pmtct-infant.view', ['data' => $data,'hospital_name' => $hospital_name,'added_by' => $added_by,'postData'=>$postData]);

    }
}

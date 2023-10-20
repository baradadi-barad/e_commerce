<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\LaboratoryInvestigations;
use Session;
use Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use App\Hospitals;
use App\User;
use DB;

class LaboratoryInvestigationsController extends Controller
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
        
       return view('laboratory-investigations.add',['years'=>$years],['currentMonth'=>$current_month]);
    }
    
     public function view()
    {
        $userId = Auth::user()->id;       
        $data = LaboratoryInvestigations::orderBy('id','desc')->where('added_by',$userId)->with('addedBy')->paginate(10);
       
        return view('laboratory-investigations.display', ['data' => $data]);
    }
    public function display($id)
    {
        $current_year = date('Y');
        $years = range($current_year-5, $current_year+10);
        $current_month = date('m');
        
        $data = LaboratoryInvestigations::find($id);
        return view('laboratory-investigations.display', ['data' => $data],['years'=>$years]);
    }
     public function index()
    {
        $userId = Auth::user()->id;       
        $data = LaboratoryInvestigations::orderBy('id','desc')->where('added_by',$userId)->with('addedBy')->get();
        $hospital_name = Hospitals::get();
        $added_by = User::where('status','enable')->get();
       
        return view('laboratory-investigations.view', ['data' => $data,'hospital_name' => $hospital_name,'added_by' => $added_by]);
    }
    
     public function delete($id)
    {
        
        LaboratoryInvestigations::find($id)->delete();

        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'Record Delete In Laboratory Investigation';
        $data1['record_id'] = 0;
        $data1['route_name'] = 'laboratory-investigations';
        $data1['type'] = 'record_office';

        activity($data1);
        Session::flash('errorMessage', 'Record deleted successfully!');

        return redirect()->route('laboratory-investigations');
    }
    public function edit($id)
    {
        $current_year = date('Y');
        $years = range($current_year-5, $current_year+10);
        $current_month = date('m');
        
        $data = LaboratoryInvestigations::find($id);
        return view('laboratory-investigations.edit', ['data' => $data],['years'=>$years]);
    }
     public function insert(Request $request){
        
        $userId = Auth::user()->id;
        $request->flash();
        $postData = $request->all();

        $data = new LaboratoryInvestigations;
        $data->added_by = $userId;
        $data->month = $postData['month'] ;
        $data->year = $postData['year'] ;
        
        $data->hematology_male = $postData['hematology_male'] ;
        $data->hematology_female = $postData['hematology_female'] ;
        $data->hematology_total = $postData['hematology_total'] ;
        
        $data->parasitology_male = $postData['parasitology_male'] ;
        $data->parasitology_female = $postData['parasitology_female'] ;
        $data->parasitology_total = $postData['parasitology_total'] ;
        
        $data->chemistry_male = $postData['chemistry_male'] ;
        $data->chemistry_female = $postData['chemistry_female'] ;
        $data->chemistry_total = $postData['chemistry_total'] ;
        
        $data->microbiology_male = $postData['microbiology_male'] ;
        $data->microbiology_female = $postData['microbiology_female'] ;
        $data->microbiology_total = $postData['microbiology_total'] ;
        
        $data->histology_male = $postData['histology_male'] ;
        $data->histology_female = $postData['histology_female'] ;
        $data->histology_total = $postData['histology_total'] ;
        
        $data->cyto_male = $postData['cyto_male'] ;
        $data->cyto_female = $postData['cyto_female'] ;
        $data->cyto_total = $postData['cyto_total'] ;
        
        $data->blood_transfusion_male = $postData['blood_transfusion_male'] ;
        $data->blood_transfusion_female = $postData['blood_transfusion_female'] ;
        $data->blood_transfusion_total = $postData['blood_transfusion_total'] ;
        
        
        $data->blood_donation_male = $postData['blood_donation_male'] ;
        $data->blood_donation_female = $postData['blood_donation_female'] ;
        $data->blood_donation_total = $postData['blood_donation_total'] ;
        
        
        $data->save();

        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'New Record Add In Laboratory Investigation';
        $data1['record_id'] = $data->id;
        $data1['route_name'] = 'laboratory-investigations';
        $data1['type'] = 'record_office';

        activity($data1);
         
        Session::flash('successMessage', 'Record added successfully!');
         return redirect()->route('laboratory-investigations');
    }
    
    public function update($id, Request $request)
    {
        $userId = Auth::user()->id;
        $request->flash();
        $postData = $request->all();
        
        $data = LaboratoryInvestigations::find($id);
        $data->added_by = $userId;
        $data->month = $postData['month'] ;
        $data->year = $postData['year'] ;
        $data->hematology_male = $postData['hematology_male'] ;
        $data->hematology_female = $postData['hematology_female'] ;
        $data->hematology_total = $postData['hematology_total'] ;
        
        $data->parasitology_male = $postData['parasitology_male'] ;
        $data->parasitology_female = $postData['parasitology_female'] ;
        $data->parasitology_total = $postData['parasitology_total'] ;
        
        $data->chemistry_male = $postData['chemistry_male'] ;
        $data->chemistry_female = $postData['chemistry_female'] ;
        $data->chemistry_total = $postData['chemistry_total'] ;
        
        $data->microbiology_male = $postData['microbiology_male'] ;
        $data->microbiology_female = $postData['microbiology_female'] ;
        $data->microbiology_total = $postData['microbiology_total'] ;
        
        $data->histology_male = $postData['histology_male'] ;
        $data->histology_female = $postData['histology_female'] ;
        $data->histology_total = $postData['histology_total'] ;
        
        $data->cyto_male = $postData['cyto_male'] ;
        $data->cyto_female = $postData['cyto_female'] ;
        $data->cyto_total = $postData['cyto_total'] ;
        
        $data->blood_transfusion_male = $postData['blood_transfusion_male'] ;
        $data->blood_transfusion_female = $postData['blood_transfusion_female'] ;
        $data->blood_transfusion_total = $postData['blood_transfusion_total'] ;
        
        
        $data->blood_donation_male = $postData['blood_donation_male'] ;
        $data->blood_donation_female = $postData['blood_donation_female'] ;
        $data->blood_donation_total = $postData['blood_donation_total'] ;
        
        
        $data->save();
        
        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'Record Edit In Laboratory Investigation';
        $data1['record_id'] = $data->id;
        $data1['route_name'] = 'laboratory-investigations';
        $data1['type'] = 'record_office';

        activity($data1);
        Session::flash('successMessage', 'Record updated successfully!');
        
        return redirect()->route('laboratory-investigations');
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
        $data = LaboratoryInvestigations::orderBy('id','desc')
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

        return view('laboratory-investigations.view', ['data' => $data,'hospital_name' => $hospital_name,'added_by' => $added_by,'postData'=>$postData]);

    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use DB;
use App\MortalityAndMorbidityReview;
use App\MortalityAndMorbidityReviewData;
use Session;
use Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use App\Hospitals;
use App\User;

class MortalityAndMorbidityReviewController extends Controller
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
        
       return view('mortality-and-morbidity-review.add',['years'=>$years],['currentMonth'=>$current_month]);
    }
    
     public function view()
    {
        $userId = Auth::user()->id;       
        $data = MortalityAndMorbidityReview::orderBy('id','desc')->where('added_by',$userId)->with('addedBy')->paginate(10);
       
        return view('mortality-and-morbidity-review.display', ['data' => $data]);
    }
    public function display($id)
    {
        $current_year = date('Y');
        $years = range($current_year-5, $current_year+10);
        $current_month = date('m');
        
        $data = MortalityAndMorbidityReview::with('mortalityandmorbidityreviewdata')->find($id);
        return view('mortality-and-morbidity-review.display', ['data' => $data],['years'=>$years]);
    }
     public function index()
    {
        $userId = Auth::user()->id;       
        $data = MortalityAndMorbidityReview::orderBy('id','desc')->where('added_by',$userId)->with('addedBy')->get();
        $hospital_name = Hospitals::get();
        $added_by = User::where('status','enable')->get();
       
        return view('mortality-and-morbidity-review.view', ['data' => $data,'hospital_name' => $hospital_name,'added_by' => $added_by]);
    }
    
     public function delete($id)
    {
        $pid = $id;
        MortalityAndMorbidityReview::find($id)->delete();
        MortalityAndMorbidityReviewData::where('parent_id',$pid)->delete();
        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'Record Delete In Mortality And Morbidity Review';
        $data1['record_id'] = 0;
        $data1['route_name'] = 'mortality-and-morbidity-review';
        $data1['type'] = 'record_office';

        activity($data1);
        Session::flash('errorMessage', 'Record deleted successfully!');

        return redirect()->route('mortality-and-morbidity-review');
    }
    public function edit($id)
    {
        $current_year = date('Y');
        $years = range($current_year-5, $current_year+10);
        $current_month = date('m');
        
        $data = MortalityAndMorbidityReview::with('mortalityandmorbidityreviewdata')->find($id);
        return view('mortality-and-morbidity-review.edit', ['data' => $data],['years'=>$years]);
    }
     public function insert(Request $request){
        
        $userId = Auth::user()->id;
        $request->flash();
        $postData = $request->all();
        

        $data = new MortalityAndMorbidityReview;
        $data->added_by = $userId;
        $data->month = $postData['month'] ;
        $data->year = $postData['year'] ;
        $data->save();

        $parent_id =  DB::getPdo()->lastInsertId();
        foreach ($postData['data'] as $value) {
            $data = new MortalityAndMorbidityReviewData;
            $data->parent_id = $parent_id;
            $data->hospital_no = $value['hos_no'] ;
            $data->sex =$value['sex'] ;
            $data->age = $value['age'] ;
            $data->causes_of_death = $value['causes_of_death'] ;
            $data->save();
        }
      
        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'New Record Add In Mortality And Morbidity Review';
        $data1['record_id'] = $data->id;
        $data1['route_name'] = 'mortality-and-morbidity-review';
        $data1['type'] = 'record_office';

        activity($data1);
        Session::flash('successMessage', 'Record added successfully!');
         return redirect()->route('mortality-and-morbidity-review');
    }
    
    public function update($id, Request $request)
    {
        $userId = Auth::user()->id;
        $request->flash();
        $postData = $request->all();
        MortalityAndMorbidityReviewData::where('parent_id',$id)->delete();
        $data = MortalityAndMorbidityReview::find($id);
        $data->added_by = $userId;
        $data->month = $postData['month'] ;
        $data->year = $postData['year'] ;
        $data->save();
        
        $parent_id =  $id;
        foreach ($postData['data'] as $value) {
            $data = new MortalityAndMorbidityReviewData;
            $data->parent_id = $parent_id;
            $data->hospital_no = $value['hos_no'] ;
            $data->sex =$value['sex'] ;
            $data->age = $value['age'] ;
            $data->causes_of_death = $value['causes_of_death'] ;
            $data->save();
        }
        
        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'Record Edit In Mortality And Morbidity Review';
        $data1['record_id'] = $data->id;
        $data1['route_name'] = 'mortality-and-morbidity-review';
        $data1['type'] = 'record_office';

        activity($data1);
        Session::flash('successMessage', 'Record updated successfully!');
        
        return redirect()->route('mortality-and-morbidity-review');
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
        $data = MortalityAndMorbidityReviewData::orderBy('id','desc')
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

        return view('mortality-and-morbidity-review.view', ['data' => $data,'hospital_name' => $hospital_name,'added_by' => $added_by,'postData'=>$postData]);

    }
}

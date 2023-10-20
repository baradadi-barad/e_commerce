<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Hospitals;
use App\Countries;
use App\States;
use App\HospitalCategories;
use App\HospitalTypes;
use App\User;
use Auth;
use Session;
use Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;

class HospitalController extends Controller
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
         //fetch all users data
        $hospitals = Hospitals::with('CategoryName')->with('TypeName')->orderBy('id','desc')->paginate(10);

        return view('hospital.hospital', ['hospitals' => $hospitals]);
    }
    public function add(){
        $h_type = HospitalTypes::where('status','enable')->get();
        $h_category = HospitalCategories::where('status','enable')->get();
        $countries = Countries::orderBy('id','asc')->get();
        $states = States::orderBy('id','asc')->get();
        return view('hospital.add', ['countries'=> $countries,'states'=>$states,'hospital_type'=>$h_type,'hospital_category'=>$h_category]);
    }
    public function insert(Request $request) {
        
        $userId = Auth::user()->id;
        $request->flash();
        $postData = $request->all();

        $data = new Hospitals;
        foreach($postData as $key => $value){
            if($key != '_token'){
               $data->$key =  $value;
            }
        }
        $data->added_by = $userId;
        $data->save();

        $data2['user_id'] = Auth::user()->id;
        $data2['activity_name'] = 'New Hospital Added In Hospital Management';
        $data2['record_id'] = $data->id;
        $data2['route_name'] = 'hospital';
        $data2['type'] = 'hospitalmanagement';

        activity($data2);

        $user_ids1 = array();
        $user_ids2 = array();
        $user_ids1 = User::where("hospital_name",$data->id)->where('role','Admin')->get();
        $user_ids2 = User::where('id',Auth::user()->id)->get();

        $array_value = array();
        $array_value2 = array();
        foreach($user_ids1 as $us_id1){
            $array_value[] = $us_id1->id;
        }
        foreach($user_ids2 as $us_id2){
            $array_value2[] = $us_id2->id;
        }

        $user_ids =  array_unique(array_merge($array_value,$array_value2));
        $v = array();
        $whom_to_seen = '';
        foreach($user_ids as $value){
            $v[] = "'".$value."'";
        }
        $whom_to_seen = implode(',',$v);
        $data1 = array();

        $data1['user_id'] = Auth::user()->id;
        $data1['hospital_id'] = Auth::user()->hospital_name;
        $data1['subject'] = 'Add New Hospital';
        $data1['content'] = 'New '.$data->hospital_name.'Hospital Added By '. Auth::user()->first_name.' '. Auth::user()->last_name;
        $data1['whom_to_seen'] = $whom_to_seen;

        saveNotification($data1);

        Session::flash('successMessage', 'Hospitals added successfully!');
            return redirect()->route('hospital');
   
        
    }
     

    public function edit($id){
        $h_type = HospitalTypes::where('status','enable')->get();
        $h_category = HospitalCategories::where('status','enable')->get();
        $hospital = Hospitals::find($id);
        $countries = Countries::orderBy('id','asc')->get();
        $states = States::orderBy('id','asc')->get();
        return view('hospital.edit', ['hospitalData' => $hospital,'countries'=> $countries,'states'=>$states,'hospital_type'=>$h_type,'hospital_category'=>$h_category]);
    }
    public function update($id, Request $request){
      
        $userId = Auth::user()->id;
        $request->flash();
        $postData = $request->all();
        
        unset($postData['_token']);
        $data = Hospitals::find($id);
        foreach($postData as $key => $value){
            if($key != '_token'){
               $data->$key =  $value;
            }
        }
        $data->save();

        $data2['user_id'] = Auth::user()->id;
        $data2['activity_name'] = 'Hospital Edit In Hospital Management';
        $data2['record_id'] = $data->id;
        $data2['route_name'] = 'hospital';
        $data2['type'] = 'hospitalmanagement';

        activity($data2);

        $user_ids1 = array();
        $user_ids2 = array();
        $user_ids1 = User::where("hospital_name",$data->id)->where('role','Admin')->get();
        $user_ids2 = User::where('id',Auth::user()->id)->get();

        $array_value = array();
        $array_value2 = array();
        foreach($user_ids1 as $us_id1){
            $array_value[] = $us_id1->id;
        }
        foreach($user_ids2 as $us_id2){
            $array_value2[] = $us_id2->id;
        }

        $user_ids =  array_unique(array_merge($array_value,$array_value2));
        $v = array();
        $whom_to_seen = '';
        foreach($user_ids as $value){
            $v[] = "'".$value."'";
        }
        $whom_to_seen = implode(',',$v);
        $data1 = array();

        $data1['user_id'] = Auth::user()->id;
        $data1['hospital_id'] = Auth::user()->hospital_name;
        $data1['subject'] = 'Update Hospital Detail';
        $data1['content'] = $data->hospital_name.'Hospital Detail Updated By '. Auth::user()->first_name.' '. Auth::user()->last_name;
        $data1['whom_to_seen'] = $whom_to_seen;

        saveNotification($data1);

            Session::flash('successMessage', 'Hospitals Detail updated successfully!');
            return redirect()->route('hospital');
        
    }

    public function status($id){
        $data = Hospitals::find($id);

        if($data->status == "enable"){
            $status = "disable";
            $message = 'Hospitals disable successfully!';
        }else{
            $status = "enable";
            $message = 'Hospitals enable successfully!';
        }

        $data->status = $status;
        $data->save();

        $data2['user_id'] = Auth::user()->id;
        $data2['activity_name'] = 'Hospital Status Change In Hospital Management';
        $data2['record_id'] = $data->id;
        $data2['route_name'] = 'hospital';
        $data2['type'] = 'hospitalmanagement';

        activity($data2);

        $user_ids1 = array();
        $user_ids2 = array();
        $user_ids1 = User::where("hospital_name",$data->id)->where('role','Admin')->get();
        $user_ids2 = User::where('id',Auth::user()->id)->get();

        $array_value = array();
        $array_value2 = array();
        foreach($user_ids1 as $us_id1){
            $array_value[] = $us_id1->id;
        }
        foreach($user_ids2 as $us_id2){
            $array_value2[] = $us_id2->id;
        }

        $user_ids =  array_unique(array_merge($array_value,$array_value2));
        $v = array();
        $whom_to_seen = '';
        foreach($user_ids as $value){
            $v[] = "'".$value."'";
        }
        $whom_to_seen = implode(',',$v);
        $data1 = array();

        $data1['user_id'] = Auth::user()->id;
        $data1['hospital_id'] = Auth::user()->hospital_name;
        $data1['subject'] = 'Update Hospital Status';
        $data1['content'] = $data->hospital_name.'Hospital Status Change By '. Auth::user()->first_name.' '. Auth::user()->last_name;
        $data1['whom_to_seen'] = $whom_to_seen;

        saveNotification($data1);

        Session::flash('successMessage', $message);

        return json_encode(array("status" => true));
    }
    
    public function delete($id){
        $hospital = Hospitals::find($id);
        $hospital_name = $hospital->hospital_name;
        $h_id = $hospital->id;
        $hospital->delete();

        $data2['user_id'] = Auth::user()->id;
        $data2['activity_name'] = 'Hospital Delete In Hospital Management';
        $data2['record_id'] = 0;
        $data2['route_name'] = 'hospital';
        $data2['type'] = 'hospitalmanagement';

        activity($data2);

        $user_ids1 = array();
        $user_ids2 = array();
        $user_ids1 = User::where("hospital_name",$data->id)->where('role','Admin')->get();
        $user_ids2 = User::where('id',Auth::user()->id)->get();

        $array_value = array();
        $array_value2 = array();
        foreach($user_ids1 as $us_id1){
            $array_value[] = $us_id1->id;
        }
        foreach($user_ids2 as $us_id2){
            $array_value2[] = $us_id2->id;
        }

        $user_ids =  array_unique(array_merge($array_value,$array_value2));
        $v = array();
        $whom_to_seen = '';
        foreach($user_ids as $value){
            $v[] = "'".$value."'";
        }
        $whom_to_seen = implode(',',$v);
        $data1 = array();

        $data1['user_id'] = Auth::user()->id;
        $data1['hospital_id'] = Auth::user()->hospital_name;
        $data1['subject'] = 'Hospital Delete';
        $data1['content'] = $hospital_name.' Hospital is delete by '. Auth::user()->first_name.' '. Auth::user()->last_name;
        $data1['whom_to_seen'] = $whom_to_seen;

        saveNotification($data1);

        Session::flash('successMessage', 'Hospitals deleted successfully!');

        return redirect()->route('hospital');
    }
    
}

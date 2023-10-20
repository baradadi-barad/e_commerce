<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Hospitals;
use App\Countries;
use App\States;
use App\Contexts;
use App\Roles;
use App\RoleRights;
use App\User;
use Auth;
use DB;
use Session;
use Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;

class RoleManagementController extends Controller
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
        $rolesData = Roles::orderBy('id','desc')->paginate(10);
        $rolesLists = Contexts::where('parent_id',0)->orderBy('name','asc')->get();
        
       
        
        return view('role-management.view', ['rolesData' => $rolesData,'rolesLists'=>$rolesLists]);
    }
    public function Context(Request $request)
    {
        $request->flash();
        $postData = $request->all();
        $rolesLists = [];
        $rolesLists = Contexts::where('parent_id',$postData['form_data'])
                ->where('parent_id','!=',0)
                ->orderBy('name','asc')->get();
        
          
        return ['rolesLists'=>$rolesLists];
    }
    public function ContextData(Request $request)
    {
        $request->flash();
        $postData = $request->all();
        $rolesLists = [];
        if($postData['contextcode'] != ''){
            $contextData = Contexts::with('rolesnamedata14')->where('parent_id',$postData['rightid'])
                ->where('code',$postData['contextcode'])
                ->orderBy('name','asc')->get();
        }else{
            $contextData = Contexts::with('rolesnamedata14')->where('id',$postData['rightid'])
                
                ->orderBy('name','asc')->get();
        }
                foreach ($contextData as $value) {
                    $data = array();
                    $data = explode(',', $value['available_rights']);
                    $value['available_rights']= $data ;
                     
                }
        return ['contextData'=>$contextData];
    }
    public function add(){
         $rolesLists = Contexts::where('parent_id',0)->orderBy('name','asc')->get();
        return view('role-management.add', ['rolesLists'=>$rolesLists]);
    }
    public function insert(Request $request) {
        
        $userId = Auth::user()->id;
        $request->flash();
        $postData = $request->all();
        $data = new Roles;
        $data->added_by = $userId;
        $data->name = $postData['name'] ;
        $data->code = $postData['code'] ;
        $data->able_to_login = $postData['able_to_login'] ;
        $data->save();

        $data2['user_id'] = Auth::user()->id;
        $data2['activity_name'] = 'New Role Create In Role Management';
        $data2['record_id'] = $data->id;
        $data2['route_name'] = 'role-management';
        $data2['type'] = 'rolemanagement';

        activity($data2);

        $role_id =  DB::getPdo()->lastInsertId();
       if(isset($postData['rolecheckbox'])){
        // echo '<pre>'; print_r($postData['rolecheckbox']); exit;
        foreach ($postData['rolecheckbox'] as $key => $right) {
                // echo '<pre>'; print_r($right); exit;
                $data = new RoleRights;
                $data->role_id = $role_id;
                $data->context_id = $key ;
                if(isset($right[0]) ){
                    $data->view_right = 1 ;
                }
                if(isset($right[1]) ){
                    $data->add_right = 1;
                }
                if(isset($right[2]) ){
                    $data->update_right = 1 ;
                }
                if(isset($right[3]) ){
                    $data->delete_right = 1 ;
                }
                $data->save();
            }
        }

        Session::flash('successMessage', 'Role added successfully!');
        return redirect()->route('role-management');
    }
     

    public function edit($id){
       
        $rolesLists = Contexts::where('parent_id',0)->orderBy('name','asc')->get();
        $rolesdata = Roles::with('rolescont')->where('id',$id)->first();
        
//        echo '<pre>';        print_r($rolesdata->toArray()); exit;
        return view('role-management.edit', ['rolesLists' => $rolesLists,'rolesdata'=> $rolesdata]);
    }
    public function update($id, Request $request){
      $role_id =  $id;
        $userId = Auth::user()->id;
        $request->flash();
        $postData = $request->all();
        $data = Roles::find($id);
        $data->added_by = $userId;
        $data->name = $postData['name'] ;
        $data->code = $postData['code'] ;
        $data->able_to_login = $postData['able_to_login'] ;
        $data->save();
        
        $data2['user_id'] = Auth::user()->id;
        $data2['activity_name'] = 'Role Edit In Role Management';
        $data2['record_id'] = $data->id;
        $data2['route_name'] = 'role-management';
        $data2['type'] = 'rolemanagement';

        activity($data2);
        RoleRights::where('role_id',$role_id)->delete();
       if(isset($postData['rolecheckbox'])){
        foreach ($postData['rolecheckbox'] as $key => $right) {
                $data = new RoleRights;
                $data->role_id = $role_id;
                $data->context_id = $key ;
                if(isset($right[0]) ){
                    $data->view_right = 1 ;
                }
                if(isset($right[1]) ){
                    $data->add_right = 1;
                }
                if(isset($right[2]) ){
                    $data->update_right = 1 ;
                }
                if(isset($right[3]) ){
                    $data->delete_right = 1 ;
                }
                $data->save();
            }
        }
        
        
        Session::flash('successMessage', 'Roles Detail updated successfully!');
        return redirect()->route('role-management');
        
    }

    public function status($id){
        $s = 1;
        $data = Roles::find($id);
        if($data->status == "enable"){
            $status = "disable";
            $s = 0;
            $message = 'Role disable successfully!';
        }else{
            $status = "enable";
            $s = 1;
            $message = 'Role enable successfully!';
        }

        $data->status = $status;
        $data->save();

        $role_right = RoleRights::where('role_id',$id)->update(['status'=>$s]);

        $data2['user_id'] = Auth::user()->id;
        $data2['activity_name'] = 'Role Status Change In Role Management';
        $data2['record_id'] = $data->id;
        $data2['route_name'] = 'role-management';
        $data2['type'] = 'rolemanagement';

        activity($data2);

        $user_ids1 = array();
        $user_ids2 = array();
        $user_ids1 = User::where('role','Admin')->get();
        $user_ids2 = User::where('user_role',$id)->get();

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
        $data1['subject'] = 'Update User Detail';
        $data1['content'] = $data->name.'`s Status Changed By '. Auth::user()->first_name.' '. Auth::user()->last_name.' Contect Super Admin';
        $data1['whom_to_seen'] = $whom_to_seen;

        saveNotification($data1);
        Session::flash('successMessage', $message);

        return json_encode(array("status" => true));
    }
    
    public function delete($id){
        $role_id = $id;
        Roles::find($id)->delete();
        RoleRights::where('role_id',$role_id)->delete();

        $data2['user_id'] = Auth::user()->id;
        $data2['activity_name'] = 'Role Delete In Role Management';
        $data2['record_id'] = 0;
        $data2['route_name'] = 'role-management';
        $data2['type'] = 'rolemanagement';

        activity($data2);
        Session::flash('successMessage', 'Roles deleted successfully!');

        return redirect()->route('role-management');
    }
    
    
   
    
    
}
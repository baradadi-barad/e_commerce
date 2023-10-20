<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Roles;
use App\Hospitals;
use Auth;
use Session;
use Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;

class UserController extends Controller
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
        $users = User::orderBy('id','desc')
                ->with('rolesnamedata')
//                ->where('role','user')
                ->paginate(10);
//        echo '<pre>';            print_r($users->toArray()); exit;
        return view('user.user', ['users' => $users]);
    }
    public function add(){
        $hospital = Hospitals::where('status','enable')->get(); 
        $roles = Roles::get();
        return view('user.add', ['hospital' => $hospital,'roles' => $roles]);
        
    }
    public function insert(Request $request) {
 
        $request->flash();
        $rules = array('first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'hospital_name' => 'required|max:255',
            'password' => 'required|min:4'
        );
        $validator = Validator::make(Input::all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        } else {
            $postData = $request->all();
//            echo '<pre>';            print_r($postData); exit;
            
            $data = new User;
            $data->first_name = $postData['first_name'];
            $data->last_name = $postData['last_name'] ;
            $data->email = $postData['email'] ;
            $data->password = bcrypt($postData['password']) ;
            $data->hospital_name = $postData['hospital_name'] ;
            $data->user_role = $postData['user_role'] ;
            if ($request->hasFile('profile_image')) {
                $image = $request->file('profile_image');
                $name = time().'.'.$image->getClientOriginalExtension();
                $destinationPath = public_path('/uploads/profile');
                $image->move($destinationPath, $name);
                $data->profile_picture = $name; 
            }

            if ($request->image){
                $img = $request->image;
                $folderPath  = public_path('/uploads/profile/');
                
                $image_parts = explode(";base64,", $img);
                $image_type_aux = explode("image/", $image_parts[0]);
                $image_type = $image_type_aux[1];
                
                $image_base64 = base64_decode($image_parts[1]);
                $fileName = uniqid() . '.jpeg';
                $file = $folderPath . $fileName; 
                if(file_put_contents($file, $image_base64)){ 
                    $data->profile_picture = $fileName; 
                }
            }
            $data->save() ;

            $data2['user_id'] = Auth::user()->id;
            $data2['activity_name'] = 'New User Add In User Management';
            $data2['record_id'] = $data->id;
            $data2['route_name'] = 'user';
            $data2['type'] = 'usermanagement';

            activity($data2);

            $user_ids1 = array();
            $user_ids2 = array();
            $user_ids1 = User::where('role','Admin')->get();
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
            $data1['subject'] = 'Add New User';
            $data1['content'] = 'New '.$data->first_name.' '.$data->last_name.' Added By '. Auth::user()->first_name.' '. Auth::user()->last_name;
            $data1['whom_to_seen'] = $whom_to_seen;

            saveNotification($data1);
 
            Session::flash('successMessage', 'User added successfully!');
            return redirect()->route('user');
        }
    }

    public function edit($id){
         $hospital = Hospitals::where('status','enable')->get();
          $roles = Roles::get();
        $user = User::find($id);
        
        return view('user.edit', ['users' => $user,'hospital' => $hospital,'roles' => $roles]);
    }
    public function update($id, Request $request){

        $postData = $request->all();
        if(isset($postData['password'])){
            $this->validate($request, [
                'password' => 'required|same:conform_password',
                'conform_password' => 'required'
            ]);
            if($postData['password'] != ''){
                $postData['password'] = bcrypt($postData['password']);
            }else{
                unset($postData['password']);
                User::find($id)->update($postData);
            }
            $data = User::find($id);
            if(isset($postData['password']) && $postData['password'] != ''){
                $data->password = $postData['password'] ;
            }
            if($data->save()){

                $data2['user_id'] = Auth::user()->id;
                $data2['activity_name'] = 'User Password Change In User Management';
                $data2['record_id'] = $data->id;
                $data2['route_name'] = 'user';
                $data2['type'] = 'usermanagement';

                activity($data2);
                Session::flash('successMessage', 'User Password Change Successfully!');
                return redirect()->route('user');
            }

            return redirect()->route('user');
        }else{
            //validate post data
            $this->validate($request, [
                'first_name' => 'required',
                'last_name' => 'required',
                'email' => 'required|email',
                'hospital_name' => 'required',
            ]);

            //get post data
            if($postData['email'] != $postData['oemail']){
                $data = User::where('email',$postData['email'])->first();
                if(count($data) > 0){
                    Session::flash('errorMessage', 'Email Already Registered!');
                    return redirect()->route('user.edit',$id);
                }
            }else{
                $data = User::find($id);
                $data->first_name = $postData['first_name'];
                $data->last_name = $postData['last_name'] ;
                $data->email = $postData['email'] ;
                $data->hospital_name = $postData['hospital_name'] ;
                $data->user_role = $postData['user_role'] ;
                
                if ($request->hasFile('profile_image')) {
                    $image = $request->file('profile_image');
                    $name = time().'.'.$image->getClientOriginalExtension();
                    $destinationPath = public_path('/uploads/profile');
                    $image->move($destinationPath, $name);
                    $data->profile_picture = $name; 
                }
                if ($request->image){
                    $img = $request->image;
                    $folderPath  = public_path('/uploads/profile/');
                    
                    $image_parts = explode(";base64,", $img);
                    $image_type_aux = explode("image/", $image_parts[0]);
                    $image_type = $image_type_aux[1];
                    
                    $image_base64 = base64_decode($image_parts[1]);
                    $fileName = uniqid() . '.jpeg';
                    $file = $folderPath . $fileName; 
                    if(file_put_contents($file, $image_base64)){ 
                        $data->profile_picture = $fileName; 
                    }
                }
                $data->save() ;


                $data2['user_id'] = Auth::user()->id;
                $data2['activity_name'] = 'User Detail Edit In User Management';
                $data2['record_id'] = $data->id;
                $data2['route_name'] = 'user';
                $data2['type'] = 'usermanagement';

                activity($data2);

                $user_ids1 = array();
                $user_ids2 = array();
                $user_ids1 = User::where('role','Admin')->get();
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
                $data1['subject'] = 'Update User Detail';
                $data1['content'] = $data->first_name.' '.$data->last_name.' Updated By '. Auth::user()->first_name.' '. Auth::user()->last_name;
                $data1['whom_to_seen'] = $whom_to_seen;

                saveNotification($data1);
                Session::flash('successMessage', 'User Detail updated successfully!');
                return redirect()->route('user');
            }
        }
    }
    public function editPass($id){
        $hospital = Hospitals::where('status','enable')->get();
         $roles = Roles::get();
       $user = User::find($id);
       
       return view('user.edit-pass', ['users' => $user,'hospital' => $hospital,'roles' => $roles]);
    }
    public function status($id){
        $data = User::find($id);

        if($data->status == "enable"){
            $status = "disable";
            $message = 'User disable successfully!';
        }else{
            $status = "enable";
            $message = 'User enable successfully!';
        }

        $data->status = $status;
        $data->save();


        $data2['user_id'] = Auth::user()->id;
        $data2['activity_name'] = 'User Status Change In User Management';
        $data2['record_id'] = $data->id;
        $data2['route_name'] = 'user';
        $data2['type'] = 'usermanagement';

        activity($data2);

        $user_ids1 = array();
        $user_ids2 = array();
        $user_ids1 = User::where('role','Admin')->get();
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
        $data1['subject'] = 'User Status Changed';
        $data1['content'] = $data->first_name.' '.$data->last_name.'Status Changed By '. Auth::user()->first_name.' '. Auth::user()->last_name;
        $data1['whom_to_seen'] = $whom_to_seen;

        saveNotification($data1);
        Session::flash('successMessage', $message);

        return json_encode(array("status" => true));
    }
    
    public function delete($id){
        User::find($id)->delete();

        $data2['user_id'] = Auth::user()->id;
        $data2['activity_name'] = 'User Delete In User Management';
        $data2['record_id'] = 0;
        $data2['route_name'] = 'user';
        $data2['type'] = 'usermanagement';

        activity($data2);

        Session::flash('successMessage', 'User deleted successfully!');

        return redirect()->route('user');
    }
    public function myProfile(Request $request){
        $id = Auth::user()->id;
        
        $user = User::find($id);
        $hospital = Hospitals::where('status','enable')->get();
        return view('user.my-profile',['user' => $user, 'hospital' => $hospital]);
    }
    public function editProfile($id){
        $user = User::find($id);
        $hospital = Hospitals::where('status','enable')->get();
        
        return view('user.edit-profile', ['users' => $user, 'hospital' => $hospital]);
    }
    public function updateProfile($id, Request $request){
        //validate post data
        $this->validate($request, [
            'email' => 'required',
            'hospital_name' => 'required'
        ]);
        
        //get post data
        $postData = $request->all();
        if($postData['email'] != $postData['oemail']){
            $data = User::where('email',$postData['email'])->first();
            if(count($data) > 0){
                Session::flash('errorMessage', 'Email Already Registered!');
                return redirect()->route('edit-profile',$id);
            }
        }else{
            // User::find($id)->update($postData);
            $data = User::find($id);
            $data->first_name = $postData['first_name'];
            $data->last_name = $postData['last_name'] ;
            $data->email = $postData['email'] ;
            $data->hospital_name = $postData['hospital_name'] ;
            if ($request->hasFile('profile_image')) {
                $image = $request->file('profile_image');
                $name = time().'.'.$image->getClientOriginalExtension();
                $destinationPath = public_path('/uploads/profile');
                $image->move($destinationPath, $name);
                $data->profile_picture = $name; 
            }

            if ($request->image){
                $img = $request->image;
                $folderPath  = public_path('/uploads/profile/');
                
                $image_parts = explode(";base64,", $img);
                $image_type_aux = explode("image/", $image_parts[0]);
                $image_type = $image_type_aux[1];
                
                $image_base64 = base64_decode($image_parts[1]);
                $fileName = uniqid() . '.jpeg';
                $file = $folderPath . $fileName; 
                if(file_put_contents($file, $image_base64)){ 
                    $data->profile_picture = $fileName; 
                }
            }
            $data->save() ;
            Session::flash('successMessage', 'Profile updated successfully!');
            return redirect()->route('user.my-profile');
        }
    }
    public function changePasswordView(Request $request){
        return view('user.change-password');
    }
    public function updatePassword(Request $request){
        $request->flash();
        $rules = array(
            'password' => 'required|min:4|confirmed',
            'password_confirmation' => 'required|min:4'
        );
        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails()) {
            return view('user.change-password')->withErrors($validator);
        } else {
            $postData = $request->all();
            $id = Auth::user()->id;
            
            $temp = array('password'=>bcrypt($postData['password']));
            User::find($id)->update($temp);
            Session::flash('successMessage', 'Password changed successfully!');
            return redirect()->route('user.my-profile');
        }
    }
}

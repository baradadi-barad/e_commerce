<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Roles;
use App\Models\Categories;
use App\Models\User;
use App\Models\WarehouseManagements;
use File;
use Illuminate\Support\Facades\Hash;



class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index()
    {
        $records = Roles::all();
        $warehouserecords = WarehouseManagements::all();
        return view('admin.user-management.index', compact('records','warehouserecords'));
    }

    public function create()
    {
        $rolesinfo = Roles::where('code','!=','admin')->get();
        $warehouserecords = WarehouseManagements::all();
        return view('admin.user-management.create', compact('rolesinfo','warehouserecords'));
    }


    public function getUserList(Request $request)
    {

        ## Read value
        $draw = $request->get('draw');
        $start = $request->get("start");
        $rowperpage = $request->get("length");

        $columnIndex_arr = $request->get('order');
        $columnName_arr = $request->get('columns');
        $order_arr = $request->get('order');
        $search_arr = $request->get('search');

        $columnIndex = $columnIndex_arr[0]['column'];
        $columnName = $columnName_arr[$columnIndex]['data'];
        $columnSortOrder = $order_arr[0]['dir'];
        $searchValue = $search_arr['value'];

        // Total records
        $totalRecords = User::select('count(*) as allcount')->where('role', '!=', 'admin')->count();
        $totalRecordswithFilter = User::select('count(*) as allcount')->where('role', '!=', 'admin')->where('name', 'like', '%' . $searchValue . '%')->count();

        // Fetch records
        $records = User::with('rolesnamedata')->where(function ($query) use ($searchValue) {
            if (!empty($searchValue)) {
                $query->where('first_name', 'like', "%" . $searchValue . "%")->orWhere('last_name', 'like', "%" . $searchValue . "%");
            }
        })
            ->where('role', '!=', 'admin')
            ->orderBy($columnName, $columnSortOrder)
            ->skip($start)
            ->take($rowperpage)
            ->get();



        $data_arr = array();

        foreach ($records as $record) {
            $id = '';
            $name = '';
            $image = '';
            $role = '';

            $id = $record->id;
            $name = $record->name;
            $image = $record->image;
            $is_admin = $record->is_admin;
            if (isset($record['rolesnamedata']['name'])) {

                // echo '<pre>';
                // print_r($record->toarray());
                // exit;
                $role =  $record->rolesnamedata['name'];
            } else {
                $role = $record->role;
            }



            $data_arr[] = array(
                "id" => $id,
                "name" => $name,
                "role" => $role,
                "is_admin" => $is_admin,
                "image" => user_image_url($image),
                "edit_route" => route("user-management.edit", $id),
                "change_password_route" => route("user-management.change-password", $id),
            );
        }

        $response = array(
            "draw" => intval($draw),
            "iTotalRecords" => $totalRecords,
            "iTotalDisplayRecords" => $totalRecordswithFilter,
            "aaData" => $data_arr
        );

        echo json_encode($response);
        exit;
    }


    // public function store(Request $request)
    // {

    //     echo '<pre>';
    //     print_r($request->all());
    //     exit;

    public function store(Request $request)
    {
        $request->validate(
            [
                'first_name'       => 'required',
                'role_id'       => 'required',
                'last_name'        => 'required',
                'email'            => 'required|email|unique:users',
                'password'         => 'required|min:4',
                'confirm_password' => 'required|min:4|same:password',
            ],
            [
                'role_id.required'         => 'The Role is required',
                'first_name.required'         => 'The First Name is required',
                'last_name.required'   => 'The Last Name is required',
                'email.required' => 'The Email field is required',
                'email.unique' => 'The Entered Email Must Be Unique',
                'password.required' => 'The Password field is required',
                'password.min:4' => 'The Password field min length 4 required',
                'confirm_password.required' => 'The Conform Password field is required',
                'confirm_password.same' => 'The Conform Password and Password is must same',
            ]
        );
        
        $rolesinfo = Roles::where('code',$request->role_id)->first();
        
        if($rolesinfo != ''){
            
            if(!isset($request->warehouse_id) || empty($request->warehouse_id) || $request->warehouse_id == '' || $request->warehouse_id== 'null'){
               $request->warehouse_id = 0; 
            }
            
            if($request->role_id != 'warehouse'){
                $request->warehouse_id = 0; 
            }else{
                if($request->warehouse_id == 0 || $request->warehouse_id == ''){
                    return redirect()->back()->with('error', 'Please Select Any One Warehouse');
                }
            }

            $user = new User();
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->email = $request->email;
            $user->gender = $request->gender;
            $user->role = $rolesinfo->id;
            $user->warehouse_id = $request->warehouse_id;
            $user->password = Hash::make($request->password);
            $user->name = $request->first_name .' '. $request->last_name;
            $user->added_by = auth()->user()->id;
            if ($user->save()) {
    
                if ($request->file('image')) {
                    $file = $request->file('image');
                    $filename = date('YmdHi') . "." . $file->getClientOriginalExtension();
                    $file->move(public_path('uploads/user'), $filename);
                    $user->image =  $filename;
                    $user->save();
                }
    
                return redirect()->route('user-management')->with('success', 'User Created Successfully');
            } else {
    
                return redirect()->back()->with('error', 'Something Went Wrong');
            }
            
        } else {

            return redirect()->back()->with('error', 'Something Went Wrong');
        }
    }

    public function edit($id)
    {
        
        $rolesinfo = Roles::where('code','!=','admin')->get();
        $warehouserecords = WarehouseManagements::all();
        
        $record = User::find($id);
        // $rolesinfo = Roles::where('is_admin','!=',1)->get();

        $data['record'] = $record;
        $data['rolesinfo'] = $rolesinfo;
        $data['warehouserecords'] = $warehouserecords;

        return view('admin.user-management.edit', $data);
    }


    public function changePassword($id)
    {
        $record = User::find($id);
        $rolesinfo = Roles::all();

        $data['record'] = $record;
        $data['rolesinfo'] = $rolesinfo;
        return view('admin.user-management.change-password', $data);
    }


    public function update(Request $request, $id)
    {
        $id = base64_decode($id);

        $request->validate(
            [
                'first_name'       => 'required',
                'last_name'        => 'required',
                'email'            => 'required|email',
            ],
            [
                'first_name.required'         => 'The First Name is required',
                'last_name.required'   => 'The Last Name is required',
                'email.required' => 'The Email field is required',
            ]
        );
        
        $rolesinfo = Roles::where('code',$request->role_id)->first();
        
        if($rolesinfo != ''){
            
            if(!isset($request->warehouse_id) || empty($request->warehouse_id) || $request->warehouse_id == '' || $request->warehouse_id== 'null'){
               $request->warehouse_id = 0; 
            }
            
            if($request->role_id != 'warehouse'){
                $request->warehouse_id = 0; 
            }else{
                if($request->warehouse_id == 0 || $request->warehouse_id == ''){
                    return redirect()->back()->with('error', 'Please Select Any One Warehouse');
                }
            }

            $user = User::find($id);
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->email = $request->email;
            $user->gender = $request->gender;
            $user->role = $rolesinfo->id;
            $user->warehouse_id = $request->warehouse_id;
            // $user->password = Hash::make($request->password);
            $user->name = $request->first_name .' '. $request->last_name;
            if ($user->save()) {
                if ($request->file('image')) {
                    $file = $request->file('image');
                    $filename = date('YmdHi') . "." . $file->getClientOriginalExtension();
                    $file->move(public_path('uploads/user'), $filename);
                    $user->image =  $filename;
                    $user->save();
                }
    
    
                return redirect()->route('user-management')->with('success', 'User edited Successfully');
            } else {
    
                return redirect()->back()->with('error', 'Something Went Wrong');
            }
            
        }else {
    
            return redirect()->back()->with('error', 'Something Went Wrong');
        }
    }

    public function destroy($id)
    {
        $usdata = User::find($id);
        $imagePath = $usdata->image;
        if ($usdata->delete()) {

            // check if image exist then delete
            if (!empty($imagePath)) {
                $destinationPath = public_path($imagePath);
                if (File::exists($destinationPath)) {
                    File::delete($destinationPath);
                }
            }

            return response()->json(['status' => 'true', 'message' => 'Category  Deleted successfully'], 200);
        } else {
            return response()->json(['status' => 'false', 'message' => 'Something went wrong'], 400);
        }
    }


    public function updatePassword(Request $request, $id)
    {
        $id = base64_decode($id);

        $request->validate(
            [
                'password'         => 'required|min:4',
                'confirm_password' => 'required|min:4|same:password',
            ],
            [
                'password.required' => 'The Password field is required',
                'password.min:4' => 'The Password field min length 4 required',
                'confirm_password.required' => 'The Conform Password field is required',
                'confirm_password.same' => 'The Conform Password and Password is must same',
            ]
        );

        $user = User::find($id);
        $user->password = Hash::make($request->password);
        if ($user->save()) {
            return redirect()->route('user-management')->with('success', 'Password Update Successfully');
        } else {

            return redirect()->back()->with('error', 'Something Went Wrong');
        }
    }
    
    public function userprofile()
    {
        $record = User::find(Auth()->user()->id);

        $data['record'] = $record;
        return view('admin.user-management.profile', $data);
    }

    public function userupdate(Request $request, $id)
    {
        $id = base64_decode($id);

        $request->validate(
            [
                'first_name'       => 'required',
                'last_name'        => 'required',
            ],
            [
                'first_name.required'         => 'The First Name is required',
                'last_name.required'   => 'The Last Name is required',
            ]
        );

        $user = User::find($id);
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->gender = $request->gender;
        $user->name = $request->first_name .' '. $request->last_name;
        if ($user->save()) {
            if ($request->file('image')) {
                $file = $request->file('image');
                $filename = date('YmdHi') . "." . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/user'), $filename);
                $user->image =  $filename;
                $user->save();
            }

            return redirect()->route('user.profile')->with('success', 'Details Updated Successfully');
        } else {

            return redirect()->back()->with('error', 'Something Went Wrong');
        }
    }
}

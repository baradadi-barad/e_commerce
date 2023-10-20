<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Categories;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\CompanyManagements;
use DB;
use File;
use Illuminate\Validation\Rule;

class CompanyManagementController extends Controller
{
    public function __construct()
    {
    }

    public function index()
    {
        $records = Categories::all();
        return view('admin.company-management.index');
    }

    public function getdata(Request $request){
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
         $totalRecords = CompanyManagements::select('count(*) as allcount')->count();
         $totalRecordswithFilter = CompanyManagements::select('count(*) as allcount')->count();
 
         // Fetch records
         $records = CompanyManagements::where(function ($query) use ($searchValue) {
                 if (!empty($searchValue)) {
                     $query->where('owner_name', 'like', "%" . $searchValue . "%");
                 }
             })
             ->orderBy($columnName, $columnSortOrder)
             ->skip($start)
             ->take($rowperpage)
             ->get();
 
 
         $data_arr = array();
 
         $key = 0;
         foreach ($records as $record) {
             $id = $record->id;
             $name = $record->company_name;
             $owner_name= $record->owner_name;
             $mobile = $record->mobile_number;
            $gst_no = $record->gst_no;
            $is_warehouse = $record->is_warehouse;
             $data_arr[] = array(
                 "id" => $id,
                 "company_name" => $name,
                 "owner_name" => $owner_name,
                 "mobile_number" => $mobile,
                 "gst_no" => $gst_no,
                 "is_warehouse" => $is_warehouse,
                 "edit_route" => route("company-management.edit", $record->id),
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
    
    public function create(){
        return view('admin.company-management.create');
    }
    
    public function store(Request $request){
        $this->validate($request, [
            'comapny_name' => 'required',
            // 'owner_name' => 'required',
            'email' => 'required|email|unique:company_managements,email_id',
            // 'mobile_no' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10|unique:company_managements,mobile_number',
            // 'gst_number' => 'required|min:15|min:15|unique:company_managements,gst_no',
            // 'pin_code' => 'required',
            // 'address'=>'required',
         ],[
            'comapny_name.required' => 'company name is required.',
            // 'owner_name.required' => 'owner name is required.',
            'email.required' => 'email required.',
            // 'mobile_no.required' => 'invalid mobile number.',
            // 'gst_number.required' => 'invalid GST number.',
            // 'pin_code.required' => 'pin code is required.',
            // 'address.required' => 'address is required.',


         ]);
         
        $obj = new CompanyManagements;
        $obj->company_name      = $request->comapny_name;
        $obj->owner_name        = $request->owner_name;
        $obj->email_id          = $request->email;
        $obj->mobile_number     = $request->mobile_no;
        $obj->gst_no            = $request->gst_number;
        $obj->address           = $request->address;
        $obj->pincode           = $request->pin_code;
        $obj->user_id           = auth()->user()->id;
         if ($obj->save()) {
            return redirect()->route('company-management')->with('success', 'Company Added Successfully');
        } else {
            return redirect()->back()->with('error', 'Something Went Wrong');
        }

    }
    
    public function edit($id){
        $edit = CompanyManagements::find($id);
        return view('admin.company-management.edit',compact('edit'));
    }
    
    public function update(Request $request,$id){
        $this->validate($request, [
            'comapny_name' => 'required',
            // 'owner_name' => 'required',
            'email' => 'required|email|unique:company_managements,email_id,'.$id,
            // 'mobile_no' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10|unique:company_managements,mobile_number,'.$id,
            // 'gst_number' => 'required|min:15|min:15|unique:company_managements,gst_no,'.$id,
            // 'pin_code' => 'required',
            // 'address'=>'required',
         ],[
            'comapny_name.required' => 'company name is required.',
            // 'owner_name.required' => 'owner name is required.',
            'email.required' => 'email required.',
            // 'mobile_no.required' => 'invalid mobile number.',
            // 'gst_number.required' => 'invalid GST number.',
            // 'pin_code.required' => 'pin Code is required.',
            // 'address.required' => 'address is required.',
         ]);
        $obj = CompanyManagements::find($id);
        $obj->company_name      = $request->comapny_name;
        $obj->owner_name        = $request->owner_name;
        $obj->email_id          = $request->email;
        $obj->mobile_number     = $request->mobile_no;
        $obj->gst_no            = $request->gst_number;
        $obj->address           = $request->address;
        $obj->pincode           = $request->pin_code;

        $obj->save();

         if ($obj->save()) {
            return redirect()->route('company-management')->with('success', 'Company Informatation Updated Successfully');
        } else {
            return redirect()->back()->with('error', 'Something Went Wrong');
        }
    }
    
    public function destroy($id){
        $delete = CompanyManagements::find($id); 
         if($delete->delete())
        {
            return response()->json(['status' => 'true','message' => 'Form Deleted successfully'], 200);
        }
        else
        {
            return response()->json(['status' => 'false','message' => 'Something went wrong'], 200);
        }
    }
}
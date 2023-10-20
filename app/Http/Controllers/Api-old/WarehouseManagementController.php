<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Categories;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\CompanyManagements;
use App\Models\WarehouseManagements;
use DB;
use File;
use Illuminate\Validation\Rule;

class WarehouseManagementController extends Controller
{
    public function __construct()
    {
    }

    public function index()
    {
        $records = WarehouseManagements::all();       
        return response()->json($records);
    }

    public function getdata(Request $request)
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
        $totalRecords = WarehouseManagements::select('count(*) as allcount')->count();
        $totalRecordswithFilter = WarehouseManagements::select('count(*) as allcount')->count();

        // Fetch records
        $records = WarehouseManagements::where(function ($query) use ($searchValue) {
            if (!empty($searchValue)) {
                $query->where('warehouse_name', 'like', "%" . $searchValue . "%");
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
            $name = $record->warehouse_name;
            $mobile = $record->mobile_number;
            $is_auto = $record->is_auto;
            $data_arr[] = array(
                "id" => $id,
                "warehouse_name" => $name,
                "mobile_number" => $mobile,
                "is_auto" => $is_auto,
                "edit_route" => route("warehouse-management.edit", $record->id),
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

    public function create()
    {
        return view('admin.warehouse-management.create');
    }

    public function store(Request $request)
    {
        // $this->validate($request, [
        //     'warehouse_name' => 'required',
        //     'email' => 'required|email|unique:warehouse_managements,email_id',
        //     'mobile_no' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10|unique:warehouse_managements,mobile_number',
        //     'pin_code' => 'required',
        //     'address' => 'required',
        // ], [
        //     'warehouse_name.required' => 'warehouse name is required.',
        //     'email.required' => 'email required.',
        //     'mobile_no.required' => 'invalid mobile number.',
        //     'pin_code.required' => 'pin code is required.',
        //     'address.required' => 'address is required.',


        // ]);

        $obj = new WarehouseManagements();
        $obj->warehouse_name    = $request->warehouse_name;
        $obj->email_id          = $request->email_id;
        $obj->mobile_number     = $request->mobile_number;
        $obj->address           = $request->address;
        $obj->pincode           = $request->pincode;
        $obj->user_id           = $request->user_id;
        if ($obj->save()) {
            return response()->json(["status" => 'true', "message" => 'Warehouse Created Successfully', "id" => $obj->id]);
        } else {
            return response()->json(["status" => 'false', "message" => 'Something Went Wrong']);
        }

        // return view('admin.warehouse-management.index');
    }

    public function edit($id)
    {
        $edit = WarehouseManagements::find($id);
        return view('admin.warehouse-management.edit', compact('edit'));
    }

    public function update(Request $request, $id)
    {

        // echo "<pre>"; print_r($request->all()); print_r($id); exit;
        // $this->validate($request, [
        //     'warehouse_name' => 'required',
        //     'email' => 'required|email|unique:warehouse_managements,email_id,' . $id,
        //     'mobile_no' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10|unique:warehouse_managements,mobile_number,' . $id,
        //     'pin_code' => 'required',
        //     'address' => 'required',
        // ], [
        //     'warehouse_name.required' => 'warehouse name is required.',
        //     'email.required' => 'email required.',
        //     'mobile_no.required' => 'invalid mobile number.',
        //     'pin_code.required' => 'pin Code is required.',
        //     'address.required' => 'address is required.',
        // ]);
        $obj = WarehouseManagements::find($id);
            if(!$obj) {
                return response()->json(["status" => 'false', "message" => 'Warehouse Not Found']);
            }
        $obj->warehouse_name    = $request->warehouse_name;
        $obj->email_id          = $request->email_id;
        $obj->mobile_number     = $request->mobile_number;
        $obj->address           = $request->address;
        $obj->pincode           = $request->pincode;
        $obj->user_id           = $request->user_id;

        // echo "<pre>"; print_r($obj->save()); print_r($id); exit;

        if ($obj->update()) {
            return response()->json(["status" => 'true', "message" => 'Warehouse Info Updated Successfully', "id" => $obj->id]);
        } else {
            return response()->json(["status" => 'false', "message" => 'Something Went Wrong']);
        }
    }

    public function destroy($id)
    {
        $delete = WarehouseManagements::find($id);
        if (!$delete) {
            return response()->json(['status' => 'false', 'message' => 'Warehouse Not Found'],);
        }
        if ($delete->delete()) {

            return response()->json(['status' => 'true', 'message' => 'Form Deleted successfully', "id" => $delete->id], 200);
        } else {

            return response()->json(['status' => 'false', 'message' => 'Something went wrong'], 200);
        }
    }

    public function changeAutoStatus(Request $request)
    {
        $winfo = WarehouseManagements::where('id',$request->warehouse_id)->first();
        $obj = WarehouseManagements::find($request->warehouse_id);
        if($winfo != '' && $winfo != null){
            if($winfo->auto_purchase == 1){
                $obj->auto_purchase = 0;
            }else if ($winfo->auto_purchase == 0) {
                $obj->auto_purchase = 1;
            }
            
            if ($obj->save()) {
                return response()->json(['status' => 'true', 'message' => 'Status Change successfully'], 200);
            } else {
                return response()->json(['status' => 'false', 'message' => 'Something went wrong'], 200);
            }
        }else {
            return response()->json(['status' => 'false', 'message' => 'Warehouse has not finded'], 200);
        }

    }
}

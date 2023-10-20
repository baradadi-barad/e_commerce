<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Categories;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\CompanyManagements;
use App\Models\WarehouseManagements;
use App\Models\StockDetails;
use App\Models\OrdersStock;
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
        $records = array();// WarehouseManagements::all();
        $companylist = CompanyManagements::all();
        
       // pre($companylist->toArray()); exit;
        return view('admin.warehouse-management.index',compact('records','companylist'));

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
            $is_auto = $record->auto_purchase;
            $auto_sell = $record->auto_sell;
            $data_arr[] = array(
                "id" => $id,
                "warehouse_name" => $name,
                "mobile_number" => $mobile,
                "is_auto" => $is_auto,
                "auto_sell" => $auto_sell,
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
        $this->validate($request, [
            'warehouse_name' => 'required',
            'email' => 'required|email|unique:warehouse_managements,email_id',
            'mobile_no' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10|unique:warehouse_managements,mobile_number',
            'pin_code' => 'required',
            'address' => 'required',
        ], [
            'warehouse_name.required' => 'warehouse name is required.',
            'email.required' => 'email required.',
            'mobile_no.required' => 'invalid mobile number.',
            'pin_code.required' => 'pin code is required.',
            'address.required' => 'address is required.',


        ]);

        $obj = new WarehouseManagements;
        $obj->warehouse_name      = $request->warehouse_name;
        $obj->email_id          = $request->email;
        $obj->mobile_number     = $request->mobile_no;
        $obj->address           = $request->address;
        $obj->pincode           = $request->pin_code;
        $obj->user_id           = auth()->user()->id;
        if ($obj->save()) {
            return redirect()->route('warehouse-management')->with('success', 'Warehouse Created Successfully');
        } else {
            return redirect()->back()->with('error', 'Something Went Wrong');
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
        $this->validate($request, [
            'warehouse_name' => 'required',
            'email' => 'required|email|unique:warehouse_managements,email_id,' . $id,
            'mobile_no' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10|unique:warehouse_managements,mobile_number,' . $id,
            'pin_code' => 'required',
            'address' => 'required',
        ], [
            'warehouse_name.required' => 'warehouse name is required.',
            'email.required' => 'email required.',
            'mobile_no.required' => 'invalid mobile number.',
            'pin_code.required' => 'pin Code is required.',
            'address.required' => 'address is required.',
        ]);
        $obj = WarehouseManagements::find($id);
        $obj->warehouse_name      = $request->warehouse_name;
        $obj->email_id          = $request->email;
        $obj->mobile_number     = $request->mobile_no;
        $obj->address           = $request->address;
        $obj->pincode           = $request->pin_code;

        if ($obj->save()) {
            return redirect()->route('warehouse-management')->with('success', 'Warehouse Info Updated Successfully');
        } else {
            return redirect()->back()->with('error', 'Something Went Wrong');
        }
    }

    public function destroy1($id)
    {
        $delete = WarehouseManagements::find($id);
        if ($delete->delete()) {

            return response()->json(['status' => 'true', 'message' => 'Form Deleted successfully'], 200);
        } else {

            return response()->json(['status' => 'false', 'message' => 'Something went wrong'], 200);
        }
    }
    
    public function destroy($id)
    {
        $totalRecords = StockDetails::where('warehouse_id', $id)->get();
        $totalstockqty = 0;
        foreach($totalRecords as $valqty){
            $totalstockqty = $totalstockqty + $valqty->qty;
        }

        $sellstock = OrdersStock::where('warehouse_id', $id)->count();

        if(($totalstockqty - $sellstock) == 0 ){
            $delete = WarehouseManagements::find($id);
            if ($delete->delete()) {

                return response()->json(['status' => 'true', 'message' => 'Warehouse Deleted successfully'], 200);
            } else {
                return response()->json(['status' => 'false', 'message' => 'Something went wrong'], 200);
            }
        }else{
            return response()->json(['status' => 'false', 'message' => 'This Warehouse Related Stocks is available So You Can`t Delete This Warehouse!!'], 200);
        }
    }
    
    public function changeAutoStatus(Request $request)
    {
        $winfo = WarehouseManagements::where('id',$request->warehouse_id)->first();
        $obj = WarehouseManagements::find($request->warehouse_id);
        if($winfo != ''){
            if($winfo->auto_purchase == 1){
                $obj->auto_purchase      = 0;
                $obj->auto_sell      = 0;
            }else{
                $obj->auto_purchase      = 1;
            }
            
            if ($obj->save()) {
                return response()->json(['status' => 'true', 'message' => 'Status Change successfully'], 200);
            } else {
                return response()->json(['status' => 'false', 'message' => 'Something went wrong'], 200);
            }
        }else {
            return response()->json(['status' => 'false', 'message' => 'Something went wrong'], 200);
        }

    }
    public function changeAutoSellStatus(Request $request)
    {
        $winfo = WarehouseManagements::where('id',$request->warehouse_id)->first();

        if($winfo->auto_purchase == 1){
            $obj = WarehouseManagements::find($request->warehouse_id);
            if($winfo != ''){
                if($winfo->auto_sell == 0){
                    $obj->auto_sell      = 1;
                    $obj->company_id      = $request->company_id;
                }else{
                    if($winfo->auto_sell == 1){
                        $obj->auto_sell      = 0;
                    }else{
                        $obj->auto_sell      = 1;
                    }
                }
                
                if ($obj->save()) {
                    return response()->json(['status' => 'true', 'message' => 'Status Change successfully'], 200);
                } else {
                    return response()->json(['status' => 'false', 'message' => 'Something went wrong'], 200);
                }
            }else {
                return response()->json(['status' => 'false', 'message' => 'Something went wrong'], 200);
            }
        }else {
            return response()->json(['status' => 'false', 'message' => 'Please First Active Auto Purchase Option'], 200);
        }

    }
}
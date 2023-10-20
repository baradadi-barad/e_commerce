<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Categories;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\CompanyManagements;
use App\Models\WarehouseManagements;
use App\Models\ManufactureManagement;
use App\Models\Products;
use DB;
use File;
use Illuminate\Validation\Rule;

class ManufacturerManagementController extends Controller
{
    public function __construct()
    {
    }

    public function index()
    {
        $records = ManufactureManagement::with("productInfo", "WarehouseDetail")->get();
        // foreach ($records as $key => $record) {
        //     $record['warehouse_name'] = $record->WarehouseDetail->warehouse_name;
        //     $record['product_name'] = $record->productInfo->title;
        // }
        // echo"<pre>";print_r($records); exit;
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
        $totalRecords = ManufactureManagement::select('count(*) as allcount')->count();
        $totalRecordswithFilter = ManufactureManagement::select('count(*) as allcount')->whereRelation('WarehouseDetail', 'warehouse_name', 'like', '%'.$searchValue.'%')->count();

        // Fetch records
        $records = ManufactureManagement::with('productInfo')->with('WarehouseDetail')
            ->where(function ($query) use ($searchValue) {
                if (!empty($searchValue)) {
                    $query->whereRelation('WarehouseDetail', 'warehouse_name', 'like', '%'.$searchValue.'%');
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
            $warehouse_id = $record->WarehouseDetail->warehouse_name;
            $product_id = $record->productInfo->title;
            $total_barcodes = $record->total_barcodes;
            $created_at = $record->created_at;
            $data_arr[] = array(
                "id" => $id,
                "warehouse_id" => $warehouse_id,
                "product_id" => $product_id,
                "total_barcodes" => $total_barcodes,
                "created_at" => date('Y-m-d H:i:s', strtotime($created_at)),
                // "edit_route" => route("manufacturer.edit", $record->id),
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
        // return response()->json($records);
    }

    public function create()
    {
        $warehouses = WarehouseManagements::all();
        $products = Products::all();
        return view('admin.manufacturer-management.create', compact('warehouses','products'));
    }

    public function store(Request $request)
    {
    //     $this->validate($request, [
    //         'product' => 'required',
    //         'warehouse' => 'required',
    //         'product_code' => 'required',
    //     ], [
    //         'product.required' => 'Product name is required.',
    //         'warehouse.required' => 'Warehouse is required.',
    //         'product_code.required' => 'Product code is required.',
    //     ]);
        $obj = new ManufactureManagement;
        $obj->product_id      = $request->product_id;
        $obj->warehouse_id          = $request->warehouse_id;
        $obj->barcodes     = $request->barcode_list;
        $obj->scaned_by           = $request->user_id;
        $obj->total_barcodes           = $request->total_scanned_barcode;
        if ($obj->save()) {
            return response()->json(["status" => 'true', "message" => 'Manufacturer Created Successfully', "id" => $obj->id]);
        } else {
            return response()->json(["status" => 'false', "message" => 'Something Went Wrong']);
        }
    }

    public function edit($id)
    {
        $edit = WarehouseManagements::find($id);
        return view('admin.manufacturer-management.edit', compact('edit'));
    }

    public function update(Request $request, $id)
    {
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
        $obj->warehouse_name      = $request->warehouse_name;
        $obj->email_id          = $request->email;
        $obj->mobile_number     = $request->mobile_no;
        $obj->address           = $request->address;
        $obj->pincode           = $request->pin_code;

        if ($obj->save()) {
            return redirect()->route('manufacturer')->with('success', 'Warehouse Info Updated Successfully');
        } else {
            return redirect()->back()->with('error', 'Something Went Wrong');
        }
    }

    public function destroy($id)
    {
        $delete = WarehouseManagements::find($id);
        if ($delete->delete()) {

            return response()->json(['status' => 'true', 'message' => 'Form Deleted successfully'], 200);
        } else {

            return response()->json(['status' => 'false', 'message' => 'Something went wrong'], 200);
        }
    }
}

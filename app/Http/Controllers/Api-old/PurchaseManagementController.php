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
use App\Models\PurchaseManagement;
use App\Models\Products;
use App\Models\StockDetails;
use DB;
use File;
use Illuminate\Validation\Rule;

class PurchaseManagementController extends Controller
{
    public function __construct()
    {
    }

    public function index()
    {
        $records = PurchaseManagement::with("WarehouseDetail", "productInfo")->get();
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
        $totalRecords = PurchaseManagement::select('count(*) as allcount')->count();
        $totalRecordswithFilter = PurchaseManagement::select('count(*) as allcount')->whereRelation('WarehouseDetail', 'warehouse_name', 'like', '%' . $searchValue . '%')->count();

        // Fetch records
        $records = PurchaseManagement::with('productInfo')->with('WarehouseDetail')
            ->where(function ($query) use ($searchValue) {
                if (!empty($searchValue)) {
                    $query->whereRelation('WarehouseDetail', 'warehouse_name', 'like', '%' . $searchValue . '%');
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
    }

    public function create()
    {
        $warehouses = WarehouseManagements::all();
        $products = Products::all();
        return view('admin.purchase-management.create', compact('warehouses', 'products'));
    }

    public function store(Request $request)
    {
        // $this->validate($request, [
        //     'product' => 'required',
        //     'warehouse' => 'required',
        //     'product_code' => 'required',
        // ], [
        //     'product.required' => 'Product name is required.',
        //     'warehouse.required' => 'Warehouse is required.',
        //     'product_code.required' => 'Product code is required.',
        // ]);
        $purchase = PurchaseManagement::where('manufacture_id', $request->manufacture_id)->where('product_id', $request->product)->where('warehouse_id', $request->warehouse)->first();
        if (empty($purchase) || $purchase == '' || empty((array)$purchase)) {
            $obj = new PurchaseManagement;
            $obj->product_id = $request->product;
            $obj->warehouse_id = $request->warehouse;
            $obj->barcodes = str_replace("'", '"', $request->product_code);
            $obj->scaned_by = $request->user_id;
            $obj->total_barcodes = $request->product_code_count;
            $obj->manufacture_id = $request->manufacture_id;
            if ($obj->save()) {
                $stock = new StockDetails();
                $stock->qty = $request->product_code_count;
                $stock->product_id = $request->product;
                $stock->warehouse_id = $request->warehouse;
                $stock->purchase_id = $obj->id;
                $stock->save();
                return response()->json(["status" => 'true', "message" =>   'Product Purchase Successfully']);
            } else {
                return response()->json(["status" => 'false', "message" =>   'Something Went Wrong']);
            }
        } else {
            $obj = PurchaseManagement::find($purchase->id);
            $obj->barcodes = str_replace("'", '"', $request->product_code);
            $obj->total_barcodes = $request->product_code_count;
            if ($obj->save()) {
                $stock = StockDetails::where('purchase_id', $purchase->id)->update(['qty' => $request->product_code_count]);
                return response()->json(["status" => 'true', "message" =>  'Product Purchase Successfully']);
            } else {
                return response()->json(["status" => 'false', "message" =>   'Something Went Wrong']);
            }
            // return redirect()->back()->with('error', 'This Products are already scan');
        }


        // return view('admin.warehouse-management.index');
    }

    // public function store(Request $request)
    // {

    //     $purchase = PurchaseManagement::where('manufacture_id',$request->manufacture_id)->get();
    //     $warehousesinfo = WarehouseManagements::where('id',$request->warehouse_id)->first();
    //     $priceinfo = PriceHistory::where('product_id',$request->product_id)->orderBy('id','desc')->first();


    //     // $priceiall = PriceHistory::all();
    //     // echo "<pre>"; print_r($priceiall); exit;
    //     if(empty($purchase) || $purchase == '' || count($purchase) == 0 || empty((array)$purchase)){
    //         $obj = new PurchaseManagement;
    //         $obj->product_id = $request->product_id;
    //         $obj->warehouse_id = $request->warehouse_id;
    //         $obj->barcodes = $request->barcode_list;
    //         $obj->scaned_by = $request->user_id;
    //         $obj->total_barcodes = $request->total_scanned_barcode;
    //         $obj->manufacture_id = $request->manufacture_id;
    //         $obj->buy_price = $priceinfo->buy_price;
    //         $obj->sell_price = $priceinfo->sell_price;
    //         if ($obj->save()) {
    //             return response()->json(["status" => 'success', "message" => 'Product Purchase Successfully', "data" => $obj->id]);
    //         } else {
    //             return response()->json(["status" =>'error', "message" => 'Something Went Wrong']);
    //         }
    //     }else{
    //         return response()->json(["status" => 'error', "message" => 'This Products are already scan']);
    //     }


    //     // return view('admin.warehouse-management.index');
    // }

    public function edit($id)
    {
        $edit = PurchaseManagement::find($id);
        return view('admin.purchase-management.edit', compact('edit'));
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
        $obj = PurchaseManagement::find($id);
        $obj->warehouse_name    = $request->warehouse_name;
        $obj->email_id          = $request->email;
        $obj->mobile_number     = $request->mobile_no;
        $obj->address           = $request->address;
        $obj->pincode           = $request->pin_code;

        if ($obj->save()) {
            return redirect()->route('purchase')->with('success', 'Warehouse Info Updated Successfully');
        } else {
            return redirect()->back()->with('error', 'Something Went Wrong');
        }
    }

    public function destroy($id)
    {
        $delete = PurchaseManagement::find($id);
        if ($delete->delete()) {

            return response()->json(['status' => 'true', 'message' => 'Form Deleted successfully'], 200);
        } else {

            return response()->json(['status' => 'false', 'message' => 'Something went wrong'], 200);
        }
    }

    // public function manufactureData(Request $request)
    // {
    //     $product_id = $request->product;
    //     $warehouse_id = $request->warehouse;

    //     $manufacture = ManufactureManagement::where('product_id', $product_id)->where('warehouse_id', $warehouse_id)->orderBy('id','desc')->first();

    //     if (!empty($manufacture) && $manufacture != '') {

    //         return response()->json(['status' => 'true', 'data' => ["manufacture" => $manufacture] ], 200);
    //     } else {

    //         return response()->json(['status' => 'false', 'message' => 'Data Not Available'], 200);
    //     }
    // }


    public function manufactureData(Request $request)
    {
        $product_id = $request->product;
        $warehouse_id = $request->warehouse;

        $manufacture = ManufactureManagement::where('product_id', $product_id)->where('warehouse_id', $warehouse_id)->orderBy('id', 'desc')->first();

        if (!empty($manufacture) && $manufacture != '') {
            $remaining_stock = $manufacture->total_barcodes;
            $purchase = PurchaseManagement::where('product_id', $product_id)->where('warehouse_id', $warehouse_id)->where('manufacture_id', $manufacture->id)->first();
            if (!empty($purchase) && $purchase != '') {
                $remaining_stock = $remaining_stock - $purchase->total_barcodes;
                if ($remaining_stock > 0) {
                    return response()->json(['status' => 'true', 'data' => ['manufacture' => $manufacture, 'remaining_stock' => $remaining_stock, 'purchase' => $purchase]], 200);
                } else {
                    return response()->json(['status' => 'false', 'message' => 'This Product Already Purchased'], 200);
                }
            } else {
                return response()->json(['status' => 'true', 'data' => ['manufacture' => $manufacture, 'remaining_stock' => $remaining_stock, 'purchase' => array()]], 200);
            }
        } else {

            return response()->json(['status' => 'false', 'message' => 'Data Not Available'], 200);
        }
    }
}

<?php

namespace App\Http\Controllers\Admin;

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
use App\Models\OrdersStock;
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
        $records = WarehouseManagements::all();
        return view('admin.manufacturer-management.index');
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
            if(isset($record->WarehouseDetail->warehouse_name) && !empty($record->WarehouseDetail->warehouse_name)){
                $warehouse_id = $record->WarehouseDetail->warehouse_name;
            }else{
                $warehouse_id = '';
            }
            $product_id = isset($record->productInfo->title) ? $record->productInfo->title : '';
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
        return view('admin.manufacturer-management.create', compact('warehouses','products'));
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'warehouse' => 'required',
            'product_code' => 'required',
        ], [
            'warehouse.required' => 'Warehouse is required.',
            'product_code.required' => 'Product code is required.',
        ]);
        $notValid = '';
        $warehousesinfo = WarehouseManagements::where('id',$request->warehouse)->first();
        $product_barcode = array();
        $product_codes = explode(',', $request->product_code);
        foreach($product_codes as $key => $product_code){
            $returninfo = multiexplode(array("P", "U"), $product_code);
            if (count($returninfo) == 3) {
                $product_barcode[$returninfo[1]][] = $product_code;
            }
        }
        if(empty($product_barcode) || $product_barcode == '' || count($product_barcode) < 0){
            return redirect()->route('manufacturer.create')->with('error', 'Barcodes are not valid');
        }
        foreach($product_barcode as $product_id => $barcodes){
            $barstr = '';
            $barcount = 0;
            foreach($barcodes as $key => $barcode){
                if($barstr != ''){
                    $barstr = $barstr.','.$barcode;
                    $barcount = $barcount+1;
                }else{
                    $barstr = $barcode;
                    $barcount = $barcount+1;
                }
            }
            $product_barcode[$product_id]['barcode'] = $barstr;
            $product_barcode[$product_id]['count'] = $barcount;
        }

        
        foreach($product_barcode as $pro_id => $brStr){
            $productDetail = Products::where('id', $pro_id)->first();
            if(!empty($productDetail) && $productDetail != ''){
                $obj = new ManufactureManagement;
                $obj->product_id = $pro_id;
                $obj->warehouse_id = $request->warehouse;
                $obj->barcodes = $brStr['barcode'];
                $obj->scaned_by = auth()->user()->id;
                $obj->total_barcodes = $brStr['count'];
                if($obj->save()) {
                    if($warehousesinfo != '' && !empty($warehousesinfo) && isset($warehousesinfo)){
                        if($warehousesinfo->auto_purchase == 1){
                            $puchaseinfo = new PurchaseManagement;
                            $puchaseinfo->product_id = $pro_id;
                            $puchaseinfo->warehouse_id = $request->warehouse;
                            $puchaseinfo->barcodes = $brStr['barcode'];
                            $puchaseinfo->scaned_by = auth()->user()->id;
                            $puchaseinfo->total_barcodes = $brStr['count'];
                            $puchaseinfo->manufacture_id = $obj->id;
                            if($puchaseinfo->save()){
                                $stock = new StockDetails();
                                $stock->qty = $brStr['count'];
                                $stock->barcodes = $brStr['barcode'];
                                $stock->product_id = $pro_id;
                                $stock->warehouse_id = $request->warehouse;
                                $stock->purchase_id = $puchaseinfo->id;
                                $stock->save();
                                
                                if($warehousesinfo->auto_sell == 1){
                                    $barcodelists = explode(',',$brStr['barcode']);
                                    $winfo = WarehouseManagements::where('id',$request->warehouse)->first();
                                    foreach ($barcodelists as  $value) {
                                        $returninfo = multiexplode(array("P", "U"), $value);
                                        $productinfo = Products::where('id',$pro_id)->first();
                                        $stockinfo = StockDetails::where('barcodes','LIKE', '%'.$value.'%')->first();
                        
                                            $sell = new OrdersStock();
                                            $sell->product_id = $pro_id;
                                            $sell->stock_id = $stockinfo->id;
                                            $sell->unique_id = $returninfo[2];
                                            $sell->barcode_no = $value;
                                            $sell->company_id = $winfo->company_id;
                                            $sell->warehouse_id = $request->warehouse;
                                            $sell->awb = '';
                                            $sell->user_id = auth()->user()->id;
                                            $sell->buy_price = $productinfo->buy_price;
                                            $sell->sell_price = $productinfo->sell_price;
                                            $sell->save();
                                    }
                                }
                            }
                        }
                    }
                }
            }else{
                $notValid = $product_barcode[$pro_id]['barcode'];
            }
        }
        
        // if($notValid != ''){
        //     return redirect()->route('manufacturer')->with('success', 'Created Successfully and '."$notValid".' are not Valid');
        // }else{
            return redirect()->route('manufacturer')->with('success', 'Created Successfully');
        // }


    }

    public function edit($id)
    {
        $edit = WarehouseManagements::find($id);
        return view('admin.manufacturer-management.edit', compact('edit'));
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
    
    public function manufactureData(Request $request)
    {
        $warehouse_id = $request->warehouse;
        $barstr = '';
        $manufactureItems = ManufactureManagement::get();

        foreach($manufactureItems as $key => $manufactureItems){
            if($barstr != ''){
                $barstr = $barstr.','.$manufactureItems->barcodes;
            }else{
                $barstr = $manufactureItems->barcodes;
            }
        }
        
        if($barstr != ''){
            return response()->json(['status' => 'true', 'barstr' => $barstr], 200);  
        }else{
            return response()->json(['status' => 'false', 'barstr' => $barstr], 200);
        }

    } 

    public function barcodeList(Request $request, $id)
    {
        $barstr = '';
        $manufactureItems = ManufactureManagement::where('id', $id)->get();

        foreach($manufactureItems as $key => $manufactureItem){
            if($barstr != ''){
                $barstr = $barstr.','.$manufactureItem->barcodes;
            }else{
                $barstr = $manufactureItem->barcodes;
            }
        }
        
        if($barstr != ''){
            return response()->json(['status' => 'true', 'barstr' => $barstr], 200);  
        }else{
            return response()->json(['status' => 'false', 'barstr' => $barstr], 200);
        }

    }
}

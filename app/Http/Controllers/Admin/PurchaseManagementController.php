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
        $records = PurchaseManagement::all();
        return view('admin.purchase-management.index');
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
        $warehouse_id = auth()->user()->warehouse_id;
        
        //echo $warehouse_id; exit;

        // Total records
        $totalRecords = PurchaseManagement::select('count(*) as allcount')
        ->where(function ($s) use ($warehouse_id) {
                        if(isset($warehouse_id) && $warehouse_id != '' && $warehouse_id != null && $warehouse_id != 0){
                            $s->where('warehouse_id', $warehouse_id);
                        }
                    })
        ->count();
        $totalRecordswithFilter = PurchaseManagement::select('count(*) as allcount')->whereRelation('WarehouseDetail', 'warehouse_name', 'like', '%'.$searchValue.'%')->where(function ($s) use ($warehouse_id) {
                        if(isset($warehouse_id) && $warehouse_id != '' && $warehouse_id != null && $warehouse_id != 0){
                            $s->where('warehouse_id', $warehouse_id);
                        }
                    })->count();

        // Fetch records
        $records = PurchaseManagement::with('productInfo')->with('WarehouseDetail')
            ->where(function ($query) use ($searchValue) {
                if (!empty($searchValue)) {
                    $query->whereRelation('WarehouseDetail', 'warehouse_name', 'like', '%'.$searchValue.'%');
                }
            })
            
            ->where(function ($s) use ($warehouse_id) {
                        if(isset($warehouse_id) && $warehouse_id != '' && $warehouse_id != null && $warehouse_id != 0){
                            $s->where('warehouse_id', $warehouse_id);
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
            $warehouse_id = isset($record->WarehouseDetail->warehouse_name) ? $record->WarehouseDetail->warehouse_name : '';
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
        
        $warehouse_id = auth()->user()->warehouse_id;
        $warehouses = WarehouseManagements::all();
        $products = Products::all();
        return view('admin.purchase-management.create', compact('warehouses','products','warehouse_id'));
    }

    public function store(Request $request)
    {
        
        //echo '<pre>'; print_r($request->all()); exit;
        $this->validate($request, [
            'product_code' => 'required',
        ], [
            'product_code.required' => 'Product code is required.',
        ]);

        $product_barcode = array();
        $product_codes = explode(',', $request->product_code);
        foreach($product_codes as $key => $product_code){
            $returninfo = multiexplode(array("P", "U"), $product_code);
            if (count($returninfo) == 3) {
                $product_barcode[$returninfo[1]][] = $product_code;
            }
        }
        if(empty($product_barcode) || $product_barcode == '' || count($product_barcode) < 0){
            return redirect()->route('purchase.create')->with('error', 'Barcodes are not valid');
        }
        
        $warehouse_id = auth()->user()->warehouse_id;
        
        if($warehouse_id == 0 || $warehouse_id == '' || empty($warehouse_id) || $warehouse_id == 'null'){
            $warehouse_id = $request->warehouse;
        }
        
        
        if($warehouse_id == 0 || $warehouse_id == '' || empty($warehouse_id) || $warehouse_id == 'null'){
            return redirect()->back()->with('error', 'Please Select An Appropriate Warehouse');
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


            $productinfo = Products::where('id',$pro_id)->first(); 
            $gst_rate = $productinfo->gst_rate;
            if(isset($gst_rate) && !empty($gst_rate) && $gst_rate != 0 && $gst_rate > 0){
                $buy_price = $productinfo->buy_price + (( $productinfo->buy_price * $gst_rate ) / 100) ;// getGstincluded($productinfo->buy_price,$gst_rate,false,false);
                $sell_price = $productinfo->sell_price +(( $productinfo->sell_price * $gst_rate) / 100 );//getGstincluded($productinfo->sell_price,$gst_rate,false,false);
            }else{
                $buy_price = $productinfo->buy_price;
                $sell_price = $productinfo->sell_price;
            }

            $obj = new PurchaseManagement;
            $obj->product_id = $pro_id;
            $obj->warehouse_id = $warehouse_id;
            $obj->buy_price = $buy_price;
            $obj->sell_price = $sell_price;
            $obj->barcodes = $brStr['barcode'];
            $obj->scaned_by = auth()->user()->id;
            $obj->total_barcodes = $brStr['count'];
            $obj->manufacture_id = $request->manufacture_id;
            if ($obj->save()) {
                $stock = new StockDetails();
                $stock->qty = $brStr['count'];
                $stock->barcodes = $brStr['barcode'];
                $stock->product_id = $pro_id;
                $stock->warehouse_id = $warehouse_id;
                $obj->buy_price = $buy_price;
                $obj->sell_price = $sell_price;
                $stock->purchase_id = $obj->id;
                $stock->save();
            }
        }
        return redirect()->route('purchase')->with('success', 'Product Purchase Successfully');
    }

    public function edit($id)
    {
        $edit = PurchaseManagement::find($id);
        return view('admin.purchase-management.edit', compact('edit'));
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
        $obj = PurchaseManagement::find($id);
        $obj->warehouse_name      = $request->warehouse_name;
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
    
    public function manufactureData(Request $request)
    {
        $manufacture_ids = [];
        $product_id = $request->product;
        $warehouse_id = $request->warehouse;
        $remaining_stock = 0;
        $total_stock = 0;
        $manufacture_barcodes = '';
        $manufacture_id_str = '';
        $purchase_barcodes = '';

        $manufacture = ManufactureManagement::where('warehouse_id', $warehouse_id)->orderBy('id','desc')->get();

        if (!empty($manufacture) && $manufacture != '' && count($manufacture) > 0) {
            $remaining_stock = 0;
            $total_stock = 0;
            foreach($manufacture as $key => $value){
                 $remaining_stock = $remaining_stock + $value->total_barcodes;
                $total_stock = $total_stock + $value->total_barcodes;
                
                if($manufacture_barcodes != ''){
                    $manufacture_barcodes = $manufacture_barcodes.','.$value->barcodes;
                }else{
                    $manufacture_barcodes = $value->barcodes;
                }

                if($manufacture_id_str != ''){
                    $manufacture_id_str = $manufacture_id_str.',"'.$value->id.'"';
                }else{
                    $manufacture_id_str = '"'.$value->id.'"';
                }
                
                $manufacture_ids[] = $value->id;
            }
            $purchase = PurchaseManagement::where('warehouse_id', $warehouse_id)
            ->get();
            if(!empty($purchase) && $purchase != ''){
                foreach($purchase as $ke => $val){
                    $remaining_stock = $remaining_stock - $val->total_barcodes;
                    
                   if($purchase_barcodes != ''){
                        $purchase_barcodes = $purchase_barcodes.','.$val->barcodes;
                    }else{
                        $purchase_barcodes = $val->barcodes;
                    }
                }
                if($remaining_stock > 0){
                    return response()->json(['status' => 'true','manufacture_barcodes'=>$manufacture_barcodes,'purchase_barcodes'=>$purchase_barcodes , 'manufacture' => $manufacture,'total_stock' =>$total_stock, 'remaining_stock' => $remaining_stock, 'purchase' => $purchase,'manufacture_id_str' => $manufacture_id_str], 200);
                }else{
                    return response()->json(['status' => 'false', 'message' => 'Products have already been purchased'], 200);
                }
            }else{
                return response()->json(['status' => 'true', 'manufacture' => $manufacture, 'total_stock' =>$total_stock,'remaining_stock' => $remaining_stock, 'purchase' => array(), 'manufacture_id_str' => $manufacture_id_str], 200);
            }
        } else {

            return response()->json(['status' => 'false', 'message' => 'Data Not Available'], 200);
        }
    }

    public function barcodeList(Request $request, $id)
    {
        $barstr = '';
        $purchaseItems = PurchaseManagement::where('id', $id)->get();

        foreach($purchaseItems as $key => $purchaseItem){
            if($barstr != ''){
                $barstr = $barstr.','.$purchaseItem->barcodes;
            }else{
                $barstr = $purchaseItem->barcodes;
            }
        }
        
        if($barstr != ''){
            return response()->json(['status' => 'true', 'barstr' => $barstr], 200);  
        }else{
            return response()->json(['status' => 'false', 'barstr' => $barstr], 200);
        }

    }
    
    public function remainingView(Request $request)
    {
        if(auth()->user()->warehouse_id != 0){
            $manufacture = ManufactureManagement::where('warehouse_id', auth()->user()->warehouse_id)->orderBy('id','desc')->get();
        }else{
            $manufacture = ManufactureManagement::orderBy('id','desc')->get();
        }
        $manufacture_barcodes = '';
        $purchase_barcodes = '';
        if (!empty($manufacture) && $manufacture != '' && count($manufacture) > 0) {
            foreach($manufacture as $key => $value){
                if($manufacture_barcodes != ''){
                    $manufacture_barcodes = $manufacture_barcodes.','.$value->barcodes;
                }else{
                    $manufacture_barcodes = $value->barcodes;
                }
            }
            
            if(auth()->user()->warehouse_id != 0){
                $purchase = PurchaseManagement::where('warehouse_id', auth()->user()->warehouse_id)->get();
            }else{
                $purchase = PurchaseManagement::get();
            }
            if(!empty($purchase) && $purchase != ''){
                foreach($purchase as $ke => $val){
                    if($purchase_barcodes != ''){
                        $purchase_barcodes = $purchase_barcodes.','.$val->barcodes;
                    }else{
                        $purchase_barcodes = $val->barcodes;
                    }
                }
            }
            if($manufacture_barcodes != '' && $purchase_barcodes == ''){
                $manufacture_barcodes = str_replace('"','',$manufacture_barcodes);
                $m_barcodes = explode(',',$manufacture_barcodes);
                foreach($m_barcodes as $key => $mb){
                    $returninfo = multiexplode(array("P", "U"), $mb);
                    if (count($returninfo) == 3) {
                        $product = Products::where('id', $returninfo[1])->first();
                        $warehouseInfo = ManufactureManagement::where('barcodes','LIKE', '%"'.$mb.'"%')->with('WarehouseDetail')->get();
                        foreach($warehouseInfo as $warehouse){
                            $remaining_barcodes[$warehouse->WarehouseDetail->warehouse_name][$product->title][] = $mb;
                        }
                    }
                }
                if(!empty($remaining_barcodes)){
                    return view('admin.purchase-management.show', compact('remaining_barcodes'));
                }else{
                    return redirect()->back()->with('error', 'Something Went Wrong');
                }
            }
            if($manufacture_barcodes != '' && $purchase_barcodes != ''){
                $remaining_barcodes = array();
                $manufacture_barcodes = str_replace('"','',$manufacture_barcodes);
                $purchase_barcodes = str_replace('"','',$purchase_barcodes);
                $m_barcodes = explode(',',$manufacture_barcodes);
                $p_barcodes = explode(',',$purchase_barcodes);
                if(count($m_barcodes) == count($p_barcodes)){
                    return redirect()->back()->with('error', 'No Data Available');
                }
                foreach($m_barcodes as $key => $mb){
                    if(!in_array($mb, $p_barcodes)){
                        // echo $mb.'<br>';
                        $returninfo = multiexplode(array("P", "U"), $mb);
                        if (count($returninfo) == 3) {
                            $product = Products::where('id', $returninfo[1])->first();
                            $warehouseInfo = ManufactureManagement::where('barcodes','LIKE', '%"'.$mb.'"%')->with('WarehouseDetail')->get();
                            foreach($warehouseInfo as $warehouse){
                                $remaining_barcodes[$warehouse->WarehouseDetail->warehouse_name][$product->title][] = $mb;
                            }
                        }
                    }
                }
                if(!empty($remaining_barcodes)){
                    return view('admin.purchase-management.show', compact('remaining_barcodes'));
                }else{
                    return redirect()->back()->with('error', 'Something Went Wrong');
                }
                
            }else{
                return redirect()->back()->with('error', 'Something Went Wrong');
            }
        } else {
            return redirect()->back()->with('error', 'Data Not Available');
        }
    }
}

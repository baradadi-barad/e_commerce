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
        $records = ManufactureManagement::with("productInfo", "WarehouseDetail")->get();
        return response()->json($records);
    }

    public function store(Request $request)
    {
        $warehousesinfo = WarehouseManagements::where('id', $request->warehouse_id)->first();
        $product_barcode = array();
        $product_codes = explode(',', $request->barcode_list);
        foreach ($product_codes as $key => $product_code) {
            $returninfo = multiexplode(array("P", "U"), $product_code);
            if (count($returninfo) == 3) {
                // $product_barcode[$returninfo[1]][] = $product_code;
                $product_barcode[$returninfo[1]][] = trim($product_code);
            }
        }
        if(empty($product_barcode) || $product_barcode == '' || count($product_barcode) < 0){
            return response()->json(['status' => 'true', 'message' => 'Barcodes are not valid']);
        }
        foreach ($product_barcode as $product_id => $barcodes) {
            $barstr = '';
            $barcount = 0;
            foreach ($barcodes as $key => $barcode) {
                if ($barstr != '') {
                    $barstr = $barstr . ',"'.$barcode.'"';
                    $barcount = $barcount + 1;
                } else {
                    $barstr = '"'.$barcode.'"';
                    $barcount = $barcount + 1;
                }
            }
            $product_barcode[$product_id]['barcode'] = $barstr;
            $product_barcode[$product_id]['count'] = $barcount;
        }
        
         $notValid = '';


        foreach ($product_barcode as $pro_id => $brStr) {
            
             $productDetail = Products::where('id', $pro_id)->first();
            if(!empty($productDetail) && $productDetail != ''){
                $obj = new ManufactureManagement;
                $obj->product_id = $pro_id;
                $obj->warehouse_id = $request->warehouse_id;
                $obj->barcodes = $brStr['barcode'];
                $obj->scaned_by = $request->user_id;
                $obj->total_barcodes = $brStr['count'];
                if ($obj->save()) {
                    if ($warehousesinfo != '' && !empty($warehousesinfo) && isset($warehousesinfo)) {
                        if ($warehousesinfo->auto_purchase == 1) {
                            $puchaseinfo = new PurchaseManagement;
                            $puchaseinfo->product_id = $pro_id;
                            $puchaseinfo->warehouse_id = $request->warehouse_id;
                            $puchaseinfo->barcodes = $brStr['barcode'];
                            $puchaseinfo->scaned_by = $request->user_id;
                            $puchaseinfo->total_barcodes = $brStr['count'];
                            $puchaseinfo->manufacture_id = $obj->id;
                            if($puchaseinfo->save()){
                                
                                $stock = new StockDetails();
                                $stock->qty = $brStr['count'];
                                $stock->barcodes = $brStr['barcode'];
                                $stock->product_id = $pro_id;
                                $stock->warehouse_id = $request->warehouse_id;
                                $stock->purchase_id = $puchaseinfo->id;
                                $stock->save();
                                
                                if($warehousesinfo->auto_sell == 1){
                                    $barcodelists = explode(',',$brStr['barcode']);
                                    $winfo = WarehouseManagements::where('id',$request->warehouse_id)->first();
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
                                            $sell->warehouse_id = $request->warehouse_id;
                                            $sell->awb = '';
                                            $sell->user_id = $request->user_id;
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
        
        if($notValid != ''){
            return response()->json(['status' => 'true', 'message' => 'Created Successfully and '."$notValid".' are not Valid']);
        }else{
            return response()->json(['status' => 'true', 'message' => 'Created Successfully']);
        }

    }

    public function manufactureData(Request $request)
    {
        $warehouse_id = $request->warehouse_id;
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

}
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

    public function index(Request $request)
    {
        $warehouse_id = $request->warehouse_id;
        
        $records = PurchaseManagement::with("WarehouseDetail", "productInfo")
                    ->where(function ($s) use ($warehouse_id) {
                        if(isset($warehouse_id) && $warehouse_id != '' && $warehouse_id != null && $warehouse_id != 0){
                            $s->where('warehouse_id', $warehouse_id);
                        }
                    })
                    ->get();
                    
        return response()->json($records);
    }

    

    public function store(Request $request)
    {

        $product_barcode = array();
        $product_codes = explode(',', $request->product_code);
        foreach($product_codes as $key => $product_code){
            $returninfo = multiexplode(array("P", "U"), $product_code);
            if (count($returninfo) == 3) {
                // $product_barcode[$returninfo[1]][] = $product_code;
                $product_barcode[$returninfo[1]][] = trim($product_code);
            }
        }
        foreach($product_barcode as $product_id => $barcodes){
            $barstr = '';
            $barcount = 0;
            foreach($barcodes as $key => $barcode){
                if($barstr != ''){
                    $barstr = $barstr.',"'.$barcode.'"';
                    $barcount = $barcount+1;
                }else{
                    $barstr = '"'.$barcode.'"';
                    $barcount = $barcount+1;
                }
            }
            $product_barcode[$product_id]['barcode'] = $barstr;
            $product_barcode[$product_id]['count'] = $barcount;
        }
        if(empty($product_barcode) || $product_barcode == '' || count($product_barcode) < 0){
            return response()->json(['status' => 'true', 'message' => 'Barcodes are not valid']);
        }
        foreach($product_barcode as $pro_id => $brStr){
            $obj = new PurchaseManagement;
            $obj->product_id = $pro_id;
            $obj->warehouse_id = $request->warehouse;
            $obj->barcodes = $brStr['barcode'];
            $obj->scaned_by = $request->user_id;
            $obj->total_barcodes = $brStr['count'];
            $obj->manufacture_id = $request->manufacture_id;
            if ($obj->save()) {
                $stock = new StockDetails();
                $stock->qty = $brStr['count'];
                $stock->barcodes = $brStr['barcode'];
                $stock->product_id = $pro_id;
                $stock->warehouse_id = $request->warehouse;
                $stock->purchase_id = $obj->id;
                $stock->save();
            }
        }
        return response()->json(["status" => 'true', "message" => 'Product Purchase Successfully']);
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
                    $manufacture_barcodes = $manufacture_barcodes.','.str_replace('"','', $value->barcodes);
                }else{
                    $manufacture_barcodes = str_replace('"','', $value->barcodes);
                }

                if($manufacture_id_str != ''){
                    $manufacture_id_str = $manufacture_id_str.',"'.$value->id.'"';
                }else{
                    $manufacture_id_str = '"'.$value->id.'"';
                }
                
                $manufacture_ids[] = $value->id;
            }
            $purchase = PurchaseManagement::where('warehouse_id', $warehouse_id)
            // ->whereIn('manufacture_id', $manufacture_ids)
            ->get();
            // pre($manufacture_ids); exit;
            if(!empty($purchase) && $purchase != ''){
                foreach($purchase as $ke => $val){
                    $remaining_stock = $remaining_stock - $val->total_barcodes;
                    
                   if($purchase_barcodes != ''){
                        $purchase_barcodes = $purchase_barcodes.','.str_replace('"','', $val->barcodes);
                    }else{
                        $purchase_barcodes = str_replace('"','', $val->barcodes);
                    }
                }
                if($remaining_stock > 0){
                    return response()->json(['status' => 'true','manufacture_barcodes'=>$manufacture_barcodes,'purchase_barcodes'=>$purchase_barcodes , 'manufacture' => $manufacture,'total_stock' =>$total_stock, 'remaining_stock' => $remaining_stock, 'purchase' => $purchase,'manufacture_id_str' => $manufacture_id_str], 200);
                }else{
                    return response()->json(['status' => 'false', 'message' => 'This Product Already Purchased'], 200);
                }
            }else{
                return response()->json(['status' => 'true', 'manufacture' => $manufacture, 'total_stock' =>$total_stock,'remaining_stock' => $remaining_stock, 'purchase' => array(), 'manufacture_id_str' => $manufacture_id_str], 200);
            }
        } else {

            return response()->json(['status' => 'false', 'message' => 'Data Not Available'], 200);
        }
    }


    // public function remainingView(Request $request)
    // {
    //     $warehouse_id = $request->warehouse_id;

    //     // print_r($userData); exit;
    //     if($warehouse_id != 0){
    //         $manufacture = ManufactureManagement::where('warehouse_id', $warehouse_id)->orderBy('id','desc')->get();
    //     }else{
    //         $manufacture = ManufactureManagement::orderBy('id','desc')->get();
    //     }
    //     $manufacture_barcodes = '';
    //     $purchase_barcodes = '';
    //     if (!empty($manufacture) && $manufacture != '' && count($manufacture) > 0) {
    //         foreach($manufacture as $key => $value){
    //             if($manufacture_barcodes != ''){
    //                 $manufacture_barcodes = $manufacture_barcodes.','.$value->barcodes;
    //             }else{
    //                 $manufacture_barcodes = $value->barcodes;
    //             }
    //         }
            
    //         if($warehouse_id != 0){
    //             $purchase = PurchaseManagement::where('warehouse_id', $warehouse_id)->get();
    //         }else{
    //             $purchase = PurchaseManagement::get();
    //         }
    //         if(!empty($purchase) && $purchase != ''){
    //             foreach($purchase as $ke => $val){
    //                 if($purchase_barcodes != ''){
    //                     $purchase_barcodes = $purchase_barcodes.','.$val->barcodes;
    //                 }else{
    //                     $purchase_barcodes = $val->barcodes;
    //                 }
    //             }
    //         }
    //         if($purchase_barcodes == ''){
    //             return response()->json(['status' => 'true', 'message' => 'All Products are Purchased']);
    //         }
    //         if($manufacture_barcodes != '' && $purchase_barcodes != ''){
    //             $remaining_barcodes = array();
    //             $manufacture_barcodes = str_replace('"','',$manufacture_barcodes);
    //             $purchase_barcodes = str_replace('"','',$purchase_barcodes);
    //             $m_barcodes = explode(',',$manufacture_barcodes);
    //             foreach($m_barcodes as $key => $mb){
    //                 if(!str_contains($purchase_barcodes, $mb)){
    //                     $returninfo = multiexplode(array("P", "U"), $mb);
    //                     if (count($returninfo) == 3) {
    //                         $product = Products::where('id', $returninfo[1])->first();
    //                         if($product != ''){
    //                             $remaining_barcodes[$product->title][] = $mb;
    //                         }
    //                     }
    //                 }
    //             }
    //             if(!empty($remaining_barcodes)){
    //                 return response()->json(['status' => 'true', 'message' => 'success', 'data' => compact('remaining_barcodes')]);
    //             }else{
    //                 return response()->json(['status' => 'false', 'message' => 'Something Went Wrong']);
    //             }
                
    //         }else{
    //             return response()->json(['status' => 'false', 'message' => 'Something Went Wrong']);
    //         }
    //     } else {

    //         return response()->json(['status' => 'false', 'message' => 'Data Not Available'], 200);
    //     }
    // }
    
    
    public function remainingView(Request $request)
    {
        
        $warehouse_id = $request->warehouse_id;
        
        if($warehouse_id != 0){
            $manufacture = ManufactureManagement::where('warehouse_id', $warehouse_id)->orderBy('id','desc')->get();
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
            
            if($warehouse_id != 0){
                $purchase = PurchaseManagement::where('warehouse_id', $warehouse_id)->get();
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
                    return response()->json(['status' => 'true', 'message' => 'success', 'data' => compact('remaining_barcodes')]);
                }else{
                   return response()->json(['status' => 'false', 'message' => 'Something Went Wrong']);
                }
            }
            if($manufacture_barcodes != '' && $purchase_barcodes != ''){
                $remaining_barcodes = array();
                $manufacture_barcodes = str_replace('"','',$manufacture_barcodes);
                $purchase_barcodes = str_replace('"','',$purchase_barcodes);
                $m_barcodes = explode(',',$manufacture_barcodes);
                $p_barcodes = explode(',',$purchase_barcodes);
                if(count($m_barcodes) == count($p_barcodes)){
                    return response()->json(['status' => 'false', 'message' => 'No Data Available']);
                }
                foreach($m_barcodes as $key => $mb){
                    if(!in_array($mb, $p_barcodes)){
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
                    return response()->json(['status' => 'true', 'message' => 'success', 'data' => compact('remaining_barcodes')]);
                }else{
                     return response()->json(['status' => 'false', 'message' => 'Something Went Wrong']);
                }
                
            }else{
                return response()->json(['status' => 'false', 'message' => 'Something Went Wrong']);
            }
        } else {

            return response()->json(['status' => 'false', 'message' => 'Data Not Available'], 200);
        }
    }
}

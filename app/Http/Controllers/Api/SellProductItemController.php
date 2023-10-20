<?php

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Products;
use App\Models\ReturnProducts;
use App\Models\ProductOptions;
use App\Models\ProductOptionValues;
use App\Models\Categories;
use App\Models\ReturnOrderStock;
use App\Models\OrdersStock;
use App\Models\CompanyManagements;
use App\Models\WarehouseManagements;
use App\Models\PriceHistory;
use App\Models\PurchaseManagement;

use App\Models\StockDetails;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Auth;
use File;


class SellProductItemController extends Controller
{
    
    
    // public function sellItem(Request $request)
    // {
    //     $barcodeinfo = $request->product_code;
    //     $awb = $request->awb_code;
    //     $user_id = $request->user_id;
    //     $company_id = $request->company_id;
    //     $warehouse_id = $request->warehouse_id;
        
    //     $ReturnOrderStock = ReturnOrderStock::where('barcode_no', 'LIKE', $barcodeinfo)->first();
    //     $OrdersStock = OrdersStock::where('barcode_no', 'LIKE', $barcodeinfo)->first();
    //     $OrdersStockAwb = OrdersStock::where('awb', 'LIKE', $awb)->first();
    //     if ($ReturnOrderStock != '') {
    //         return response()->json(['status' => 'false', 'message' => 'This Barcode is already Sanned'], 200);
    //     }else if ($OrdersStock != '') {
    //         return response()->json(['status' => 'false', 'message' => 'This Barcode is already Sanned'], 200);
    //     }else if ($OrdersStockAwb != '' ) {
    //         return response()->json(['status' => 'false', 'message' => 'This Barcode is already Sanned'], 200);
    //     }else {
    //         // $returninfo = explode('P,', $barcodeinfo);
    //         $returninfo = array();
    //         $returninfo = $this->multiexplode(array("P","U"), $barcodeinfo);
    //         if (count($returninfo) == 3) {
    //             $purchaseData = PurchaseManagement::where('barcodes','LIKE','%'.$barcodeinfo.'%')->where('product_id', $returninfo[1])->where('warehouse_id', $warehouse_id)->get();
    //           //  pre($purchaseData); exit;
    //             if(!empty($purchaseData) && $purchaseData != '' && count($purchaseData) > 0){
    //                 $productpriceinfo = PriceHistory::where('product_id', $returninfo[1])->first();
    //                 $stockinfo = StockDetails::where('barcodes','LIKE', '%'.$barcodeinfo.'%')->first();

    //                 $stockret = new OrdersStock();
    //                 $stockret->product_id = $returninfo[1];
    //                 $stockret->stock_id = $stockinfo->id;
    //                 $stockret->unique_id = $returninfo[2];
    //                 $stockret->barcode_no = $barcodeinfo;
    //                 $stockret->company_id = $company_id;
    //                 $stockret->warehouse_id = $warehouse_id;
    //                 $stockret->awb = $awb;
    //                 $stockret->user_id = $user_id;
    //                 $stockret->buy_price = $productpriceinfo->buy_price;
    //                 $stockret->sell_price = $productpriceinfo->sell_price;
    //                 if ($stockret->save()) {
    //                     return response()->json(['status' => 'true', 'message' => 'Sell Item successfully'], 200);
    //                 } else {
    //                     return response()->json(['status' => 'false', 'message' => 'Something went wrong, Please scan again !!'], 200);
    //                 }
    //             }else{
    //                 return response()->json(['status' => 'false', 'message' => 'This Product has not been Purchased'], 200);
    //             }
    //         } else {
    //             return response()->json(['status' => 'false', 'message' => 'This Barcode is Not Valid'], 200);
    //         }
    //     }
    // }

    
    public function sellItem(Request $request)
    {
        $barcodeinfo = $request->product_code;
        $awb = $request->awb_code;
        $user_id = $request->user_id;
        $company_id = $request->company_id;
        $warehouse_id = $request->warehouse_id;
        
        $ReturnOrderStock = ReturnOrderStock::where('barcode_no', 'LIKE', $barcodeinfo)->first();
        $OrdersStock = OrdersStock::where('barcode_no', 'LIKE', $barcodeinfo)->first();
        $OrdersStockAwb = OrdersStock::where('awb', 'LIKE', $awb)->first();
        if ($ReturnOrderStock != '') {
            return response()->json(['status' => 'false', 'message' => 'This Barcode is already Sanned'], 200);
        }else if ($OrdersStock != '') {
            return response()->json(['status' => 'false', 'message' => 'This Barcode is already Sanned'], 200);
        }else if ($OrdersStockAwb != '' ) {
            return response()->json(['status' => 'false', 'message' => 'This Barcode is already Sanned'], 200);
        }else {
            // $returninfo = explode('P,', $barcodeinfo);
            $returninfo = array();
            $returninfo = $this->multiexplode(array("P","U"), $barcodeinfo);
            if (count($returninfo) == 3) {
                $purchaseData = PurchaseManagement::where('barcodes','LIKE','%'.$barcodeinfo.'%')->where('product_id', $returninfo[1])->where('warehouse_id', $warehouse_id)->get();
              //  pre($purchaseData); exit;
                if(!empty($purchaseData) && $purchaseData != '' && count($purchaseData) > 0){
                    $productpriceinfo = PriceHistory::where('product_id', $returninfo[1])->first();
                    $stockinfo = StockDetails::where('barcodes','LIKE', '%'.$barcodeinfo.'%')->first();
                
                    $stockret = new OrdersStock();
                    $stockret->product_id = $returninfo[1];
                    $stockret->stock_id = $stockinfo->id;
                    $stockret->unique_id = $returninfo[2];
                    $stockret->barcode_no = $barcodeinfo;
                    $stockret->company_id = $company_id;
                    $stockret->warehouse_id = $warehouse_id;
                    $stockret->awb = $awb;
                    $stockret->user_id = $user_id;
                    $stockret->buy_price = $productpriceinfo->buy_price;
                    $stockret->sell_price = $productpriceinfo->sell_price;
                    if ($stockret->save()) {
                        return response()->json(['status' => 'true', 'message' => 'Sell Item successfully'], 200);
                    } else {
                        return response()->json(['status' => 'false', 'message' => 'Something went wrong, Please scan again !!'], 200);
                    }
                }else{
                    return response()->json(['status' => 'false', 'message' => 'This Product has not been Purchased'], 200);
                }
            } else {
                return response()->json(['status' => 'false', 'message' => 'This Barcode is Not Valid'], 200);
            }
        }
    }


    
    function multiexplode($delimiters, $string)
    {

        $ready = str_replace($delimiters, $delimiters[0], $string);
        $launch = explode($delimiters[0], $ready);
        return  $launch;
    }
    
    
    
    public function requiredDataForAddingProduct(Request $request)
    {
        $company = Companymanagements::where('is_warehouse','!=',1)->get();
        $warehouselist = Warehousemanagements::where('auto_sell','!=',1)->get();
        $records = OrdersStock::all();

        return response()->json(compact('records','company','warehouselist'));
    }
    
    public function sellItemList(Request $request)
    {
        $warehouse_id = $request->warehouse_id;
        $records = OrdersStock::with('productInfo')
                ->where(function ($s) use ($warehouse_id) {
                        if(isset($warehouse_id) && $warehouse_id != '' && $warehouse_id != null && $warehouse_id != 0){
                            $s->where('warehouse_id', $warehouse_id);
                        }
                    })
                ->get();
        return response()->json($records);
    }
}
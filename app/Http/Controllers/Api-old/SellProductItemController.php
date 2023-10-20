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
use App\Models\PriceHistory;
use App\Models\CompanyManagements;
use App\Models\WarehouseProductSell;
use App\Models\WarehouseManagements;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Auth;
use File;



class SellProductItemController extends Controller
{
    
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

        if ($ReturnOrderStock != '' || $OrdersStock != '' || $OrdersStockAwb != '') {
            return response()->json(['status' => 'false', 'message' => 'This Barcode is already Sanned'], 200);
        } else {
            // $returninfo = explode('P,', $barcodeinfo);
            $returninfo = array();
            $returninfo = $this->multiexplode(array("P","U"), $barcodeinfo);

            if (count($returninfo) == 3) {
                
                $productpriceinfo = PriceHistory::where('product_id', $returninfo[1])->first();

                $stockret = new OrdersStock();
                $stockret->product_id = $returninfo[1];
                $stockret->stock_id = $returninfo[2];
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
    
     public function returnItemLists(Request $request)
    {
        
        $product_name = $request->product_name;
        $company_id = $request->company_id;
        $user_id = $request->user_id;

        $records = ReturnOrderStock::with('productInfo')->with('companyInfo')->orderBy('id', 'desc')->get();
        
        $data_arr = array();

        $key = 0;
        foreach ($records as $record) {
            $id = $key  = $key + 1;
            if (isset($record->productInfo->title)) {
                $product_name = $record->productInfo->title;
            } else {
                $product_name = '';
            }

            $d = strtotime($record->created_at);
            // echo date("Y-m-d h:i:sa", $d) . "<br>";

            $stock_added_date = date("Y-m-d h:i", $d);

            $data_arr[] = array(
                "id" => $id,
                "product_name" => $product_name,
                "stock_added_date" => $stock_added_date,

            );
        }
        return response()->json($data_arr);
    }
    
    public function fetchListData(Request $request)
    {
        $company = CompanyManagements::where('is_warehouse','!=',1)->get();
        $warehouselist = WarehouseManagements::where('auto_purchase','!=',1)->get();
        $records = OrdersStock::all();

        return response()->json(compact('records','company','warehouselist'));
    }
    
    public function sellItemList(Request $request)
    {
        $records = OrdersStock::with('productInfo')->get();
        return response()->json($records);
    }
}
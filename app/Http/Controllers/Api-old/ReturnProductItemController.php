<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ReturnProducts;
use App\Models\ReturnOrderStock;
use App\Models\OrdersStock;
use App\Models\PriceHistory;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Auth;
use File;




class ReturnProductItemController extends Controller
{

    public function __construct(Request $request)
    {

        ini_set('memory_limit', '-1');

        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
        header('Access-Control-Allow-Origin: *');

        ini_set('max_execution_time', -1);
    }

    public function returnItemLists(Request $request)
    {

        $product_name = $request->product_name;
        $company_id = $request->company_id;
        $user_id = $request->user_id;

        // $records = ReturnProducts::all();
        $records = ReturnOrderStock::with('productInfo')->with('companyInfo')->orderBy('id', 'desc')->get();

        $data_arr = array();

        $key = 0;

        return response()->json($records);
    }

    function multiexplode($delimiters, $string)
    {

        $ready = str_replace($delimiters, $delimiters[0], $string);
        $launch = explode($delimiters[0], $ready);
        return $launch;
    }

    public function returnItem(Request $request)
    {
        $barcodeinfo = $request->product_code;
        $awb = $request->awb_code;
        $user_id = $request->user_id;

        $ReturnOrderStock = '';
        $ReturnOrderawbStock = '';
        $ordersStock = '';
        if ($barcodeinfo != '' || $awb != '') {
            if ($barcodeinfo != '') {
                $ordersStock = OrdersStock::where('barcode_no', 'LIKE', $barcodeinfo)->first();
                $ReturnOrderStock = ReturnOrderStock::where('barcode_no', 'LIKE', $barcodeinfo)->first();
            }
            if ($awb != '') {
                $ordersStock = OrdersStock::where('awb', 'LIKE', $awb)->first();
                $ReturnOrderawbStock = ReturnOrderStock::where('awb', 'LIKE', $awb)->first();
            }
            if ($ordersStock == '') {
                return response()->json(['status' => 'false', 'message' => 'This Item is Not Sell, Please Sell First!!!'], 200);
            } else {

                if ($ReturnOrderStock != '' || $ReturnOrderawbStock != '') {
                    return response()->json(['status' => 'false', 'message' => 'This item is already Returned'], 200);
                } else {
                    $returninfo = array();
                    $returninfo = $this->multiexplode(array("P", /* "S",*/"U", /*"p","s","u"*/), $ordersStock->barcode_no);

                    if (count($returninfo) == 3) {
                        $stockret = new ReturnOrderStock();
                        $stockret->product_id = $returninfo[1];
                        $stockret->stock_id = $returninfo[2];
                        $stockret->unique_id = $returninfo[2];
                        $stockret->barcode_no = $barcodeinfo;
                        $stockret->awb = $ordersStock->awb;
                        $stockret->company_id = $ordersStock->company_id;

                        // edited on Fri 29-09-23
                        $stockret->warehouse_id = $ordersStock->warehouse_id;
                        $stockret->buy_price = $ordersStock->buy_price;
                        $stockret->sell_price = $ordersStock->sell_price;
                        if ($stockret->save()) {
                            return response()->json(['status' => 'true', 'message' => 'Stock Return successfully'], 200);
                        } else {
                            return response()->json(['status' => 'false', 'message' => 'Something went wrong'], 200);
                        }
                    } else {
                        return response()->json(['status' => 'false', 'message' => 'This Item is Not Valid'], 200);
                    }
                }
            }
        }

    }
}
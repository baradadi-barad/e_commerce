<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ReturnProducts;
use App\Models\ReturnOrderStock;
use App\Models\OrdersStock;
use App\Models\PriceHistory;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Auth;
use File;

class ReturnProductController extends Controller
{

    public function index()
    {
        // $records = ReturnProducts::all();
        return view('admin.return_products.index');
    }

    public function returnStock(Request $request)
    {
        $barcodeinfo = $request->barcodeinfo;
        $awb = $request->awb;
        $ReturnOrderStock = '';
        $ReturnOrderawbStock = '';
        $ordersStock = '';
        if ($barcodeinfo != '' || $awb != '') {
            if($barcodeinfo != ''){
                $ordersStock = OrdersStock::where('barcode_no', 'LIKE','%'.$barcodeinfo.'%' )->first();
                $ReturnOrderStock = ReturnOrderStock::where('barcode_no', 'LIKE', '%'.$barcodeinfo.'%' )->first();
            }
            if($awb != ''){
                $ordersStock = OrdersStock::where('awb', 'LIKE',$awb )->first();
                $ReturnOrderawbStock = ReturnOrderStock::where('awb', 'LIKE', $awb )->first();
            }
            if ($ordersStock == '') {
                return response()->json(['status' => 'false', 'message' => 'This Item is Not Sell, Please Sell First!!!'], 200);
            } else {

                if ($ReturnOrderStock != '' || $ReturnOrderawbStock != '') {
                    return response()->json(['status' => 'false', 'message' => 'This item is already Returned'], 200);
                } else {
                    $returninfo = array();
                    $returninfo = $this->multiexplode(array("P","U"), $ordersStock->barcode_no);

                    if (count($returninfo) == 3) {
                        $stockret = new ReturnOrderStock();
                        $stockret->product_id = $returninfo[1];
                        $stockret->stock_id = $returninfo[2];
                        $stockret->unique_id = $returninfo[2];
                        $stockret->barcode_no = $ordersStock->barcode_no;
                        $stockret->awb = $ordersStock->awb;
                        $stockret->company_id = $ordersStock->company_id;
                        $stockret->sell_id = $ordersStock->id;
                        $stockret->warehouse_id = $ordersStock->warehouse_id;
                        $stockret->buy_price = $ordersStock->buy_price;
                        $stockret->sell_price = $ordersStock->sell_price;
                        $stockret->gst_rate = $ordersStock->gst_rate;
                        $stockret->user_id = auth()->user()->id;
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

    public function getReturnProductStockList(Request $request)
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
        // Total records
        $totalRecords = ReturnOrderStock::select('count(*) as allcount')->where(function ($s) use ($warehouse_id) {
                        if(isset($warehouse_id) && $warehouse_id != '' && $warehouse_id != null && $warehouse_id != 0){
                            $s->where('warehouse_id', $warehouse_id);
                        }
                    })->count();
        $totalRecordswithFilter = ReturnOrderStock::select('count(*) as allcount')->where(function ($s) use ($warehouse_id) {
                        if(isset($warehouse_id) && $warehouse_id != '' && $warehouse_id != null && $warehouse_id != 0){
                            $s->where('warehouse_id', $warehouse_id);
                        }
                    })->count();

        // Fetch records
        $records = ReturnOrderStock::with('productInfo')->orderBy('id', 'desc')
            ->whereRelation('productInfo', 'title', 'like', '%'.$searchValue.'%')
            ->where(function ($s) use ($warehouse_id) {
                        if(isset($warehouse_id) && $warehouse_id != '' && $warehouse_id != null && $warehouse_id != 0){
                            $s->where('warehouse_id', $warehouse_id);
                        }
                    })
            ->skip($start)
            ->take($rowperpage)
            ->get();

            //pre($records->toArray()); exit;
        $data_arr = array();

        $key = $start;
        foreach ($records as $record) {
            $key  = $key + 1;
            $return_id = $record->id;
            $product_name = isset($record->productInfo->title) ? $record->productInfo->title : ''; 

            $d = strtotime($record->created_at);
            // echo date("Y-m-d h:i:sa", $d) . "<br>";

            $stock_added_date = date("Y-m-d h:i", $d);

            $data_arr[] = array(
                "id" => $key,
                "return_id" => $return_id,
                "product_name" => $product_name,
                "stock_added_date" => $stock_added_date,

            );
        }

        // echo '<pre>';
        // print_r($data_arr);
        // exit;

        $response = array(
            "draw" => intval($draw),
            "iTotalRecords" => $totalRecords,
            "iTotalDisplayRecords" => $totalRecordswithFilter,
            "aaData" => $data_arr
        );

        echo json_encode($response);
        exit;
    }

    function multiexplode($delimiters, $string)
    {

        $ready = str_replace($delimiters, $delimiters[0], $string);
        $launch = explode($delimiters[0], $ready);
        return  $launch;
    }

    public function productData(Request $request, $id)
    {
        $returnData = ReturnOrderStock::where('id', $id)->with('productInfo')->with('companyInfo')->with('warehouseInfo')->with('sellInfo')->first();
        date_default_timezone_set('Asia/Kolkata');
        $returnData->sellInfo->new_sell_date = strftime("%Y-%m-%d %X", strtotime($returnData->sellInfo->created_at));
        if(!empty($returnData) && $returnData != ''){
            return response()->json(['status' => 'true', 'returnData' => $returnData], 200);
        }else{
            return response()->json(['status' => 'false', 'returnData' => array()], 200);
        }
    }
}
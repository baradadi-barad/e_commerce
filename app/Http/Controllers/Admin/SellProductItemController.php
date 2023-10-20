<?php

namespace App\Http\Controllers\Admin;

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

    public function index()
    {
        $company = CompanyManagements::where('is_warehouse','!=',1)->get();
        $warehouselist = WarehouseManagements::where('auto_sell','!=',1)->get();

        $records = OrdersStock::all();
        $warehouse_id = auth()->user()->warehouse_id;
        return view('admin.sell_product_item.index', compact('records','company','warehouselist','warehouse_id'));
    }

    public function sellProductItem(Request $request)
    {
        
        // echo 1; exit;
        $barcodeinfo = $request->barcodeinfo;
        $awb = $request->awb;
        $company_id = $request->company_id;
        
        $warehouse_id = auth()->user()->warehouse_id;
        
        if($warehouse_id == 0 || $warehouse_id == '' || empty($warehouse_id) || $warehouse_id == 'null'){
            $warehouse_id = $request->warehouse_id;
        }
       // echo $warehouse_id; exit;
        
        if($warehouse_id == 0 || $warehouse_id == '' || empty($warehouse_id) || $warehouse_id == 'null'){
            // return redirect()->back()->with('error', 'Please Select An Appropriate Warehouse');
            return response()->json(['status' => 'false', 'Please Select An Appropriate Warehouse'], 200);
        }
        
        // $warehouse_id = $request->warehouse_id;
        
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

                    $productinfo = Products::where('id',$returninfo[1])->first(); 
                    $gst_rate = $productinfo->gst_rate;
                    if(isset($gst_rate) && !empty($gst_rate) && $gst_rate != 0 && $gst_rate > 0){
                        $buy_price = $productinfo->buy_price + (( $productinfo->buy_price * $gst_rate ) / 100) ;// getGstincluded($productinfo->buy_price,$gst_rate,false,false);
                        $sell_price = $productinfo->sell_price +(( $productinfo->sell_price * $gst_rate) / 100 );//getGstincluded($productinfo->sell_price,$gst_rate,false,false);
                    }else{
                        $buy_price = $productinfo->buy_price;
                        $sell_price = $productinfo->sell_price;
                    }
                
                    $stockret = new OrdersStock();
                    $stockret->product_id = $returninfo[1];
                    $stockret->stock_id = $stockinfo->id;
                    $stockret->unique_id = $returninfo[2];
                    $stockret->barcode_no = $barcodeinfo;
                    $stockret->company_id = $company_id;
                    $stockret->warehouse_id = $warehouse_id;
                    $stockret->awb = $awb;
                    $stockret->gst_rate = $gst_rate;
                    $stockret->user_id = auth()->user()->id;
                    $stockret->buy_price = $buy_price;
                    $stockret->sell_price = $sell_price;
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

    public function getSellProductStockItemList(Request $request)
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
        $totalRecords = OrdersStock::select('count(*) as allcount')->where(function ($s) use ($warehouse_id) {
                        if(isset($warehouse_id) && $warehouse_id != '' && $warehouse_id != null && $warehouse_id != 0){
                            $s->where('warehouse_id', $warehouse_id);
                        }
                    })->count();
        $totalRecordswithFilter = OrdersStock::select('count(*) as allcount')->where(function ($s) use ($warehouse_id) {
                        if(isset($warehouse_id) && $warehouse_id != '' && $warehouse_id != null && $warehouse_id != 0){
                            $s->where('warehouse_id', $warehouse_id);
                        }
                    })->count();
        // Fetch records
        $records = OrdersStock::with('productInfo')
            // ->where(function ($query) use ($searchValue) {
            //     if (!empty($searchValue)) {
            //         $query->where('title', 'like', "%" . $searchValue . "%");
            //     }
            // })
            ->whereRelation('productInfo', 'title', 'like', '%'.$searchValue.'%')
            ->where(function ($s) use ($warehouse_id) {
                        if(isset($warehouse_id) && $warehouse_id != '' && $warehouse_id != null && $warehouse_id != 0){
                            $s->where('warehouse_id', $warehouse_id);
                        }
                    })
            ->orderBy($columnName, $columnSortOrder)
            ->orderBy('id', 'desc')
            ->skip($start)
            ->take($rowperpage)
            ->get();

        $data_arr = array();
        $key = $start;
        foreach ($records as $record) {
            $key  = $key + 1;
            $sell_id = $record->id;
            if (isset($record->productInfo->title)) {
                $product_name = $record->productInfo->title;
            } else {
                $product_name = '';
            }
             $sell_price = $record->sell_price;


            $d = strtotime($record->created_at);

            $stock_added_date = date("Y-m-d h:i", $d);
            $data_arr[] = array(
                "id" => $key,
                "sell_id" => $sell_id,
                "product_id" => $product_name,
                "sell_price" => $sell_price,
                "created_at" => $stock_added_date,
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
}

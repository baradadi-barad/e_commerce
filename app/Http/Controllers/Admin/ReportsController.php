<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Roles;
use App\Models\Categories;
use App\Models\User;
use App\Models\OrdersStock;
use App\Models\ReturnOrderStock;
use App\Models\Products;
use App\Models\StockDetails;
use App\Models\CompanyManagements;
use App\Models\WarehouseManagements;
use App\Models\WarehouseProductSell;
use File;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;

class ReportsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $records = array();
        $postData = array();
        $postData['report_type'] = '';
        $postData['start_date'] = '';
        $postData['end_date'] = '';
        $return_value = new \stdClass();
        $return_value->header = array();
        $return_value->data = array();
        $records = $return_value;
        return view('admin.reports.index', compact('records', 'postData'));
    }

    public function getReportData(Request $request)
    { 
        $postData = $request->all();
        // pre($postData);exit; 
        $return_value = new \stdClass();
        $records = array();
        // $rowperpage = 50;
        $from = $postData['start_date'];
        $to = $postData['end_date'];
        $to = $to . ' 23:59:59';
        $from = $from . ' 00:00:01';
        $company_id_list = array();
        $product_id_list = array();
        $warehouse_id_list = array();

        // $to = date("Y-m-d",$to);  
        // $from = date("Y-m-d",$from); 
        // echo $to; exit;
        if (isset($postData['report_type']) && $postData['report_type'] == 'sell_item') {
            $return_value->header = array('Index', 'Product Name', 'Company Name', 'Warehouse Name','Returned Date');
            $recordsinfo = OrdersStock::where('created_at', '>=', $from)
                ->where('created_at', '<=', $to)
                ->orderBy('id', 'desc')->get();

            foreach($recordsinfo as $info){
                if(!in_array($info->company_id,$company_id_list)){
                    $company_id_list[] = $info->company_id;
                }
                if(!in_array($info->product_id,$product_id_list)){
                    $product_id_list[] = $info->product_id;
                }
                if(!in_array($info->warehouse_id,$warehouse_id_list)){
                    $warehouse_id_list[] = $info->warehouse_id;
                }
            } 


            $company = CompanyManagements::whereIn('id', $company_id_list)->get();
            $product = Products::whereIn('id', $product_id_list)->get();
            $warehouse = WarehouseManagements::whereIn('id', $warehouse_id_list)->get();
           
            $records = OrdersStock::with('productInfo')->with('warehouseInfo')
                ->where('created_at', '>=', $from)
                ->where('created_at', '<=', $to)
                ->orderBy('id', 'desc');
            if (isset($postData['product'])) {
                $records->where('product_id', $postData['product']);
            }
            if (isset($postData['company'])) {
                $records->where('company_id', $postData['company']);
            }
            if (isset($postData['warehouse'])) {
                $records->where('warehouse_id', $postData['warehouse']);
            }
            $records = $records->get();
           
        } elseif (isset($postData['report_type']) && $postData['report_type'] == 'return_item') {
            $return_value->header = array('Index', 'Product Name', 'Company Name', 'Warehouse Name','Returned Date');
            
            $recordsinfo = ReturnOrderStock::where('created_at', '>=', $from)
                ->where('created_at', '<=', $to)
                ->orderBy('id', 'desc')->get();

            foreach($recordsinfo as $info){
                if(!in_array($info->company_id,$company_id_list)){
                    $company_id_list[] = $info->company_id;
                }
                if(!in_array($info->product_id,$product_id_list)){
                    $product_id_list[] = $info->product_id;
                }
                if(!in_array($info->warehouse_id,$warehouse_id_list)){
                    $warehouse_id_list[] = $info->warehouse_id;
                }
            } 


            $company = CompanyManagements::whereIn('id', $company_id_list)->get();
            $product = Products::whereIn('id', $product_id_list)->get();
            $warehouse = WarehouseManagements::whereIn('id', $warehouse_id_list)->get();
           
            $records = ReturnOrderStock::with('productInfo')->with('warehouseInfo')
                ->where('created_at', '>=', $from)
                ->where('created_at', '<=', $to)
                ->orderBy('id', 'desc');
                // ->take($rowperpage);
            if (isset($postData['product'])) {
                $records->where('product_id', $postData['product']);
            }
            if (isset($postData['company'])) {
                $records->where('company_id', $postData['company']);
            }
            if (isset($postData['warehouse'])) {
                $records->where('warehouse_id', $postData['warehouse']);
            }
            $records = $records->get();
        } elseif (isset($postData['report_type']) && $postData['report_type'] == 'warehouse') {
            $return_value->header = array('Index', 'Product Name', 'Selling Date');
            
            $recordsinfo = WarehouseProductSell::where('created_at', '>=', $from)
                ->where('created_at', '<=', $to)
                ->orderBy('id', 'desc')->get();

            foreach($recordsinfo as $info){
                if(!in_array($info->company_id,$company_id_list)){
                    $company_id_list[] = $info->company_id;
                }
                if(!in_array($info->product_id,$product_id_list)){
                    $product_id_list[] = $info->product_id;
                }
            } 


            $company = CompanyManagements::whereIn('id', $company_id_list)->get();
            $product = Products::whereIn('id', $product_id_list)->get();
            $warehouse = array();

            $records = WarehouseProductSell::with('productInfo')
                ->where('created_at', '>=', $from)
                ->where('created_at', '<=', $to)
                ->orderBy('id', 'desc');
                // ->take($rowperpage);
            if (isset($postData['product'])) {
                $records->where('product_id', $postData['product']);
            }
            if (isset($postData['company'])) {
                $records->where('company_id', $postData['company']);
            }
            $records = $records->get();
        } else {
            $records = array();
        }
        $company_array = array();
        $product_array = array();
        $return_value->data = new Collection();
        $count = 1;
        foreach ($records as $recvalue) {
            $company_data =  CompanyManagements::where('id', $recvalue->company_id)->first();
            $product_data =  OrdersStock::where('product_id', $recvalue->product_id)->get();

            $new_obj = new \stdClass();
            $new_obj->index = $count++;
            if(isset($recvalue->productInfo->title)){
                $new_obj->product_name = $recvalue->productInfo->title;

            }else{
                $new_obj->product_name = '';

            }
            
            if(isset($postData['report_type']) && $postData['report_type'] != 'warehouse'){
                if(isset($recvalue->company_id) && !empty($recvalue->company_id)){
                    $new_obj->company_name = $company_data->company_name;
    
                }else{
                    $new_obj->company_name = '';
    
                }
                if(isset($recvalue->warehouseInfo->warehouse_name)){
                    $new_obj->warehouse_name = $recvalue->warehouseInfo->warehouse_name;
    
                }else{
                    $new_obj->warehouse_name = '';
    
                }
            }

            $new_obj->created_at = date_format($recvalue['created_at'], "Y-m-d H:i:s");
            $return_value->data->push($new_obj);
        }
       // pre($return_value); exit;
        return view('admin.reports.index', [
            'postData' => $postData,
            'records' => $return_value,
            'company' => $company,
            'product' => $product,
            'warehouse' => $warehouse,
        ]);
    }
    
    public function profitIndex(Request $request)
    {   
        return view('admin.reports.profit-report');
    }
    
    public function profitReport(Request $request)
    {
        // $draw = $request->get('draw');
        // $start = $request->get("start");
        // $rowperpage = $request->get("length");

        // $columnIndex_arr = $request->get('order');
        // $columnName_arr = $request->get('columns');
        // $order_arr = $request->get('order');
        // $search_arr = $request->get('search');

        // $columnIndex = $columnIndex_arr[0]['column'];
        // $columnName = $columnName_arr[$columnIndex]['data'];
        // $columnSortOrder = $order_arr[0]['dir'];
        // $searchValue = $search_arr['value'];
        
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        
        if($start_date == '' || empty($start_date) || !isset($start_date)){
            $start_date =  date("Y-m-d");
        }
        
        if($end_date == '' || empty($end_date) || !isset($end_date)){
            $end_date =date("Y-m-d");
        }
        
        $start_date = $start_date . ' 00:00:00';
        $end_date = $end_date . ' 23:59:59';

        
        $productids = array();
        $OrdersStockinfo = OrdersStock::
            where('created_at', '>=', $start_date)
            ->where('created_at', '<=', $end_date)
            ->get();
        
        
        foreach($OrdersStockinfo as $osi){
            if(!in_array($osi->product_id,$productids)){
                $productids[] = $osi->product_id;
            }
        }

        // $totalRecords = Products::select('count(*) as allcount')
        //     ->whereIn('id',$productids)
        //     ->where('created_at', '>=', $start_date)
        //     ->where('created_at', '<=', $end_date)
        //     ->count();

        // $totalRecordsWithFilter = Products::select('count(*) as allcount')
        //     ->where('title', 'like', '%' . $searchValue . '%')
        //     ->whereIn('id',$productids)
        //     ->where('created_at', '>=', $start_date)
        //     ->where('created_at', '<=', $end_date)
        //     ->count();
        
        $records = Products::whereIn('id',$productids)->get();

        $data_arr = array();

        foreach ($records as $record) {
            $product_name = $record->title;
            $profit = 0;
            $loss = 0;
            $net_profit = 0;
            
            $order_stock_details = OrdersStock::
                where('product_id',$record->id)
                ->where('created_at', '>=', $start_date)
                ->where('created_at', '<=', $end_date)
                ->get();
            foreach($order_stock_details as $detail){
                 $profit = $profit + ($detail->sell_price - $detail->buy_price);
                 $gst_rate = $detail->gst_rate;
            }
            $return_order_stock_details = ReturnOrderStock::where('product_id',$record->id)
                ->where('created_at', '>=', $start_date)
                ->where('created_at', '<=', $end_date)
                ->get();
            foreach($return_order_stock_details as $return){
                 $loss = $loss + ($return->sell_price - $return->buy_price);
            }
            $total_profit_gst = $profit- $loss;
           // echo $profit; exit;
            $data_arr[] = array(
                "title" => $product_name,
                "profit" => $profit,
                "loss" => '₹'.$loss,
                "net_profit" => '₹'.($profit- $loss),
                "id" => $record->id,  
                "without_gst_profit" => '₹'.($total_profit_gst-($total_profit_gst*$gst_rate/100))
            );
            
        }

        $response = array(
            "status"=>'true',
            "data"=>$data_arr
        );  
        echo json_encode($response);
    }
    
    public function stockProfit(Request $request,$id)
    {
        $stock_details = StockDetails::where('product_id',$id)->get();
        $product = Products::where('id',$id)->first();
        $total_profit = 0;
        if(!empty($stock_details) && $stock_details != '' && count($stock_details) > 0){
            $profit = 0;
            foreach($stock_details as $stock_detail){
                $profit = 0;
                $sellstock = OrdersStock::where('product_id',$id)->where('stock_id',$stock_detail->id)->count();
                $profit = $profit + ($sellstock * ($stock_detail->sell_price - $stock_detail->buy_price));
                $rqty = $stock_detail->qty - $sellstock;
                $stock_detail->profit = $profit;
                $stock_detail->rqty = $rqty;
                $stock_detail->created_at_new = date('Y-m-d H:i:s', strtotime($stock_detail->created_at));
                
                
                $total_profit = $total_profit + ($sellstock * ($stock_detail->sell_price - $stock_detail->buy_price));
                echo $total_profit;
                exit;
            }
            return response()->json(['status' => 'true', 'stock_detail' => $stock_details, 'product' => $product, 'total_profit' => $total_profit], 200);
        }else{
            return response()->json(['status' => 'false', 'stock_detail' => $stock_details, 'product' => $product, 'total_profit' => $total_profit, 'message' => 'No Stock Found'], 200);
        }
    }
}

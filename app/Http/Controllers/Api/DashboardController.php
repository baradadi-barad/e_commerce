<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\OrdersStock;
use App\Models\Products; 
use App\Models\ReturnOrderStock;
use App\Models\StockDetails;

use App\Models\Categories;
use App\Models\WarehouseManagements;
use App\Models\CompanyManagements;

use App\Models\PurchaseManagement;
use App\Models\ManufactureManagement;

use DateTime;
use DatePeriod;
use DateInterval;



class DashboardController extends Controller
{
 
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }

    // public function index()
    // { 
    //     $start_date_month =array();
    //     $end_date_month =array();
    //     $sell_return_categories = array();
    //     $sell_chart = array();
    //     $return_chart = array();
    //     $profit_chart = array();
    //     $loss_chart = array();
    //     $month_start = date('Y-m-01 H:i:s');
    //     $sellcount = OrdersStock::where('created_at','>=', date('Y-m-01'))->where('created_at','<=', date('Y-m-31'))->count();
    //    // pre($sellcount); exit;
    //     $productcount = Products::count();
    //     $returncount = ReturnOrderStock::where('created_at','>=', date('Y-m-01'))->where('created_at','<=', date('Y-m-31'))->count();
        
    //     $records = Products::get();
    //     $monthly_profit = 0;
    //     $monthly_loss = 0;
    //     $total_profit = 0;
    //     $total_loss = 0;
    //     $icon = '';
    //     foreach ($records as $record) {
    //         $stock_details = StockDetails::where('product_id',$record->id)->get();
    //         foreach($stock_details as $stock_detail){
    //             $sell_details = OrdersStock::where('product_id',$record->id)->where('stock_id',$stock_detail->id)->where('created_at','>=', $month_start)->where('created_at','<=', date('Y-m-d H:i:s'))->count();
    //             $return_details = ReturnOrderStock::where('product_id',$record->id)->where('stock_id',$stock_detail->id)->where('created_at','>=', $month_start)->where('created_at','<=', date('Y-m-d H:i:s'))->count();
                
    //             $monthly_profit = $monthly_profit + ($sell_details * ($stock_detail->sell_price - $stock_detail->buy_price));
    //              $monthly_loss = $monthly_loss + ($return_details * ($stock_detail->sell_price - $stock_detail->buy_price));
                
    //         }
    //     }
    //     // echo $monthly_profit; exit;
    //     $total_profit = $monthly_profit;//-$monthly_loss;
    //     if($total_profit < 0){
    //         $icon = 'down';
    //     }else if($total_profit > 0){
    //         $icon = 'up';
    //     }
        
    //     for ($i = 0; $i < 3; $i++) 
    //     {
    //         $start_date_month[] = date('Y-m-01', strtotime( date( $month_start )." -$i months"));
    //         $end_date_month[] = date('Y-m-31', strtotime( date( $month_start )." -$i months"));
    //     }
    //     for($i = 0; $i < 3; $i++){
    //         $month_name = DateTime::createFromFormat('Y-m-d', $start_date_month[$i])->format('F');
    //         $year = DateTime::createFromFormat('Y-m-d', $start_date_month[$i])->format('Y');
            
    //         // sell-return data
    //         $chart_data_sell = OrdersStock::where('created_at','>=', $start_date_month[$i])->where('created_at','<=', $end_date_month[$i])->count();
    //         $chart_data_return = ReturnOrderStock::where('created_at','>=', $start_date_month[$i])->where('created_at','<=', $end_date_month[$i])->count();
    //         $sell_return_categories[] = substr(strtoupper($month_name),0,3).'-'.$year;
    //         $sell_chart[] = isset($chart_data_sell) ? $chart_data_sell : 0;  
    //         $return_chart[] = isset($chart_data_return) ? $chart_data_return : 0; 
            
    //         // profit-loss data
    //         $records = Products::get();
    //         $profit = 0;
    //         $loss = 0;
    //         foreach ($records as $record) {
    //             $stock_details = StockDetails::where('product_id',$record->id)->get();
    //             foreach($stock_details as $stock_detail){
    //                 $sell_details = OrdersStock::where('product_id',$record->id)->where('stock_id',$stock_detail->id)->where('created_at','>=', $start_date_month[$i])->where('created_at','<=', $end_date_month[$i])->count();
    //                 $profit = $profit + ($sell_details * ($stock_detail->sell_price - $stock_detail->buy_price));
                    
                    
    //                 $return_details = ReturnOrderStock::where('product_id',$record->id)->where('stock_id',$stock_detail->id)->where('created_at','>=', $start_date_month[$i])->where('created_at','<=', $end_date_month[$i])->count();
    //                 $loss = $loss + ($return_details * ($stock_detail->sell_price - $stock_detail->buy_price));
    //             }
    //         }
    //         $profit_chart[] = isset($profit) ? $profit : 0;
    //         $loss_chart[] = isset($loss) ? $loss : 0;
    //     }  
        
    //         $currentmonthname = date('M');
    //       //  pre($currentmonthname); exit;

    //     // echo "<PRE>"; print_r(compact('currentmonthname','sellcount','productcount','returncount','total_profit','monthly_loss','sell_return_categories','sell_chart','return_chart','profit_chart','loss_chart','icon')); exit;
    //     return response()->json(compact('currentmonthname','sellcount','productcount','returncount','total_profit','monthly_loss','sell_return_categories','sell_chart','return_chart','profit_chart','loss_chart','icon'));
    //     // return view('admin.dashboard', compact('currentmonthname','sellcount','productcount','returncount','total_profit','monthly_loss','sell_return_categories','sell_chart','return_chart','profit_chart','loss_chart','icon'));
    // }

    public function index(Request $request)
    {   
        $warehouse_id = $request->warehouse_id;
        
        $total_profit = 0;
        $start_date_month =array();
        $end_date_month =array();
        $sell_return_categories = array();
        $sell_chart = array();
        $return_chart = array();
        $profit_chart = array();
        $loss_chart = array();
        $month_start = date('Y-m-01 H:i:s');
        $sellcount = OrdersStock::where('created_at','>=', date('Y-m-01'))->where('created_at','<=', date('Y-m-31'))->count();
       // pre($sellcount); exit;
        $productcount = Products::count();
        $categories = Categories::count();
        $warehousemanagments = WarehouseManagements::count();
        $companymanagments = CompanyManagements::count();
        
        if(isset($warehouse_id) && $warehouse_id != '' && $warehouse_id != 0){
            $manufacture = ManufactureManagement::where('warehouse_id', $warehouse_id)->orderBy('id','desc')->get();
        }else{
            $manufacture = ManufactureManagement::orderBy('id','desc')->get();
        }
        $remaining_stock = 0;
        if (!empty($manufacture) && $manufacture != '' && count($manufacture) > 0) {
            foreach($manufacture as $key => $value){
                $remaining_stock = $remaining_stock + $value->total_barcodes;
            }
            if(isset($warehouse_id) && $warehouse_id != '' && $warehouse_id != 0){
                $purchase = PurchaseManagement::where('warehouse_id', $warehouse_id)->get();
            }else{
                $purchase = PurchaseManagement::orderBy('id','desc')->get();
            }
            if(!empty($purchase) && $purchase != ''){
                foreach($purchase as $ke => $val){
                    $remaining_stock = $remaining_stock - $val->total_barcodes;
                    
                }
            }
        }
        
        $returncount = ReturnOrderStock::where('created_at','>=', date('Y-m-01'))->where('created_at','<=', date('Y-m-31'))->count();
        
        $records = Products::get();
        $monthly_profit = 0;
        $monthly_loss = 0;
        $end_datetal_profit = 0;
        $end_datetal_loss = 0;
        $icon = '';
        foreach ($records as $record) {
            $stock_details = StockDetails::where('product_id',$record->id)->get();
            foreach($stock_details as $stock_detail){
                $sell_details = OrdersStock::where('product_id',$record->id)->where('stock_id',$stock_detail->id)->where('created_at','>=', $month_start)->where('created_at','<=', date('Y-m-d H:i:s'))->count();
                $return_details = ReturnOrderStock::where('product_id',$record->id)->where('stock_id',$stock_detail->id)->where('created_at','>=', $month_start)->where('created_at','<=', date('Y-m-d H:i:s'))->count();
                
                $monthly_profit = $monthly_profit + ($sell_details * ($stock_detail->sell_price - $stock_detail->buy_price));
                 $monthly_loss = $monthly_loss + ($return_details * ($stock_detail->sell_price - $stock_detail->buy_price));
                
            }
        }
        // echo $monthly_profit; exit;
        $end_datetal_profit = $monthly_profit;//-$monthly_loss;
        if($end_datetal_profit < 0){
            $icon = 'down';
        }else if($end_datetal_profit > 0){
            $icon = 'up';
        }
        $total_profit = $monthly_profit;
        
        for ($i = 0; $i < 3; $i++) 
        {
            $start_date_month[] = date('Y-m-01', strtotime( date( $month_start )." -$i months"));
            $end_date_month[] = date('Y-m-31', strtotime( date( $month_start )." -$i months"));
        }
        for($i = 0; $i < 3; $i++){
            $month_name = DateTime::createFromFormat('Y-m-d', $start_date_month[$i])->format('F');
            $year = DateTime::createFromFormat('Y-m-d', $start_date_month[$i])->format('Y');
            
            // sell-return data
            $chart_data_sell = OrdersStock::where('created_at','>=', $start_date_month[$i])->where('created_at','<=', $end_date_month[$i])->count();
            $chart_data_return = ReturnOrderStock::where('created_at','>=', $start_date_month[$i])->where('created_at','<=', $end_date_month[$i])->count();
            $sell_return_categories[] = substr(strtoupper($month_name),0,3).'-'.$year;
            $sell_chart[] = isset($chart_data_sell) ? $chart_data_sell : 0;  
            $return_chart[] = isset($chart_data_return) ? $chart_data_return : 0; 
            
            // profit-loss data
            $records = Products::get();
            $profit = 0;
            $loss = 0;
            foreach ($records as $record) {
                $stock_details = StockDetails::where('product_id',$record->id)->get();
                foreach($stock_details as $stock_detail){
                    $sell_details = OrdersStock::where('product_id',$record->id)->where('stock_id',$stock_detail->id)->where('created_at','>=', $start_date_month[$i])->where('created_at','<=', $end_date_month[$i])->count();
                    $profit = $profit + ($sell_details * ($stock_detail->sell_price - $stock_detail->buy_price));
                    
                    
                    $return_details = ReturnOrderStock::where('product_id',$record->id)->where('stock_id',$stock_detail->id)->where('created_at','>=', $start_date_month[$i])->where('created_at','<=', $end_date_month[$i])->count();
                    $loss = $loss + ($return_details * ($stock_detail->sell_price - $stock_detail->buy_price));
                }
            }
            $profit_chart[] = isset($profit) ? $profit : 0;
            $loss_chart[] = isset($loss) ? $loss : 0;
        }  
        
            $currentmonthname = date('M');
          //  pre($currentmonthname); exit;

        
          return response()->json(compact('currentmonthname','remaining_stock','sellcount','productcount','companymanagments','categories','warehousemanagments','returncount','total_profit','monthly_loss','sell_return_categories','sell_chart','return_chart','profit_chart','loss_chart','icon'));
    }


    // public function index()
    // { 
    //     $total_profit = 0;
    //     $start_date_month =array();
    //     $end_date_month =array();
    //     $sell_return_categories = array();
    //     $sell_chart = array();
    //     $return_chart = array();
    //     $profit_chart = array();
    //     $loss_chart = array();
    //     $month_start = date('Y-m-01 H:i:s');
    //     $sellcount = OrdersStock::where('created_at','>=', date('Y-m-01'))->where('created_at','<=', date('Y-m-31'))->count();
    //    // pre($sellcount); exit;
    //     $productcount = Products::count();
    //     $categories = Categories::count();
    //     $warehousemanagements = Warehousemanagements::count();
    //     $companymanagements = Companymanagements::count();
        
    //     $returncount = ReturnOrderStock::where('created_at','>=', date('Y-m-01'))->where('created_at','<=', date('Y-m-31'))->count();
        
    //     $records = Products::get();
    //     $monthly_profit = 0;
    //     $monthly_loss = 0;
    //     $end_datetal_profit = 0;
    //     $end_datetal_loss = 0;
    //     $icon = '';
    //     foreach ($records as $record) {
    //         $stock_details = StockDetails::where('product_id',$record->id)->get();
    //         foreach($stock_details as $stock_detail){
    //             $sell_details = OrdersStock::where('product_id',$record->id)->where('stock_id',$stock_detail->id)->where('created_at','>=', $month_start)->where('created_at','<=', date('Y-m-d H:i:s'))->count();
    //             $return_details = ReturnOrderStock::where('product_id',$record->id)->where('stock_id',$stock_detail->id)->where('created_at','>=', $month_start)->where('created_at','<=', date('Y-m-d H:i:s'))->count();
                
    //             $monthly_profit = $monthly_profit + ($sell_details * ($stock_detail->sell_price - $stock_detail->buy_price));
    //              $monthly_loss = $monthly_loss + ($return_details * ($stock_detail->sell_price - $stock_detail->buy_price));
                
    //         }
    //     }
    //     // echo $monthly_profit; exit;
    //     $end_datetal_profit = $monthly_profit;//-$monthly_loss;
    //     if($end_datetal_profit < 0){
    //         $icon = 'down';
    //     }else if($end_datetal_profit > 0){
    //         $icon = 'up';
    //     }
    //     $total_profit = $monthly_profit;
        
    //     for ($i = 0; $i < 3; $i++) 
    //     {
    //         $start_date_month[] = date('Y-m-01', strtotime( date( $month_start )." -$i months"));
    //         $end_date_month[] = date('Y-m-31', strtotime( date( $month_start )." -$i months"));
    //     }
    //     for($i = 0; $i < 3; $i++){
    //         $month_name = DateTime::createFromFormat('Y-m-d', $start_date_month[$i])->format('F');
    //         $year = DateTime::createFromFormat('Y-m-d', $start_date_month[$i])->format('Y');
            
    //         // sell-return data
    //         $chart_data_sell = OrdersStock::where('created_at','>=', $start_date_month[$i])->where('created_at','<=', $end_date_month[$i])->count();
    //         $chart_data_return = ReturnOrderStock::where('created_at','>=', $start_date_month[$i])->where('created_at','<=', $end_date_month[$i])->count();
    //         $sell_return_categories[] = substr(strtoupper($month_name),0,3).'-'.$year;
    //         $sell_chart[] = isset($chart_data_sell) ? $chart_data_sell : 0;  
    //         $return_chart[] = isset($chart_data_return) ? $chart_data_return : 0; 
            
    //         // profit-loss data
    //         $records = Products::get();
    //         $profit = 0;
    //         $loss = 0;
    //         foreach ($records as $record) {
    //             $stock_details = StockDetails::where('product_id',$record->id)->get();
    //             foreach($stock_details as $stock_detail){
    //                 $sell_details = OrdersStock::where('product_id',$record->id)->where('stock_id',$stock_detail->id)->where('created_at','>=', $start_date_month[$i])->where('created_at','<=', $end_date_month[$i])->count();
    //                 $profit = $profit + ($sell_details * ($stock_detail->sell_price - $stock_detail->buy_price));
                    
                    
    //                 $return_details = ReturnOrderStock::where('product_id',$record->id)->where('stock_id',$stock_detail->id)->where('created_at','>=', $start_date_month[$i])->where('created_at','<=', $end_date_month[$i])->count();
    //                 $loss = $loss + ($return_details * ($stock_detail->sell_price - $stock_detail->buy_price));
    //             }
    //         }
    //         $profit_chart[] = isset($profit) ? $profit : 0;
    //         $loss_chart[] = isset($loss) ? $loss : 0;
    //     }  
        
    //         $currentmonthname = date('M');
    //       //  pre($currentmonthname); exit;

        
    //     return response()->json(compact('currentmonthname','sellcount','productcount','companymanagements','categories','warehousemanagements','returncount','total_profit','monthly_loss','sell_return_categories','sell_chart','return_chart','profit_chart','loss_chart','icon'));
    // }

    public function indexAjax(Request $request)
    { 
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        
        if($start_date == '' || empty($start_date) || !isset($start_date)){
            $start_date =  date("Y-m-01");
            $start_date = $start_date . ' 00:00:00';
        }
        
        if($end_date == '' || empty($end_date) || !isset($end_date)){
            $end_date =date("Y-m-31");
            $end_date = $end_date . ' 23:59:59';
        }
        
        $date1 = new DateTime($start_date);
        $date2 = new DateTime($end_date);
        $interval = $date1->diff($date2);
        $days_difference = $interval->days;

        $start_date_month =array();
        $end_date_month =array();
        $sell_return_categories = array();
        $sell_chart = array();
        $return_chart = array();
        $profit_chart = array();
        $loss_chart = array();
        $month_start = date('Y-m-01 H:i:s');
        
        $sellcount = OrdersStock::where('created_at', '>=', $start_date)->where('created_at', '<=', $end_date)->count();
        $productcount = Products::count();
        $categories = Categories::count();
        $warehousemanagements = Warehousemanagements::count();
        $companymanagements = Companymanagements::count();
        $returncount = ReturnOrderStock::where('created_at', '>=', $start_date)->where('created_at', '<=', $end_date)->count();
        $records = Products::get();
        
        $monthly_profit = 0;
        $monthly_loss = 0;
        $end_datetal_profit = 0;
        $end_datetal_loss = 0;
        $icon = '';
        
        $profit = 0;
        $loss = 0;
        $net_profit = 0;
            
        $order_stock_details = OrdersStock::
            where('created_at', '>=', $start_date)
            ->where('created_at', '<=', $end_date)
            ->get();
        foreach($order_stock_details as $detail){
             $profit = $profit + ($detail->sell_price - $detail->buy_price);
        }
        
        $return_order_stock_details = ReturnOrderStock::where('created_at', '>=', $start_date)
            ->where('created_at', '<=', $end_date)
            ->get();
            
        foreach($return_order_stock_details as $return){
             $loss = $loss + ($return->sell_price - $return->buy_price);
        }
        
       // echo $profit; exit;
       $net_profit = $profit- $loss;
       if($net_profit < 0){
            $icon = 'down';
        }else if($net_profit > 0){
            $icon = 'up';
        }
            
        $sell_return_categories = $this->getBetweenDates($start_date,$end_date);
        
        foreach($sell_return_categories as $date){
            $separate_start_date = $date . ' 00:00:00';
            $separate_end_date = $date . ' 23:59:59';
            $profit1 = 0;
            $loss1 = 0;

            $chart_data_sell = OrdersStock::where('created_at','>=', $separate_start_date)->where('created_at','<=', $separate_end_date)->count();
            $chart_data_return = ReturnOrderStock::where('created_at','>=', $separate_start_date)->where('created_at','<=', $separate_end_date)->count();
            $sell_chart[] = isset($chart_data_sell) ? $chart_data_sell : 0;  
            $return_chart[] = isset($chart_data_return) ? $chart_data_return : 0; 
            
            
            $sell_details = OrdersStock::where('created_at','>=', $separate_start_date)->where('created_at','<=', $separate_end_date)->get();
            $return_details = ReturnOrderStock::where('created_at','>=', $separate_start_date)->where('created_at','<=', $separate_end_date)->get();
            
            foreach($sell_details as $sell){
                $profit1 = $profit1 + ($sell->sell_price - $sell->buy_price);
            }
            foreach($return_details as $return){
                $loss1 = $loss1 + ($return->sell_price - $return->buy_price);
            }

            $profit_chart[] = isset($profit1) ? $profit1 : 0;
            $loss_chart[] = isset($loss1) ? $loss1 : 0;
        }
        
        $data_arr[] = array(
                "profit" => '₹'.$profit,
                "loss" => '₹'.$loss,
                "net_profit" => '₹'.($profit- $loss),
                "sellcount" =>$sellcount,
                "returncount" => $returncount,
                "icon" =>$icon,
                "sell_return_categories" =>$sell_return_categories,
                "sell_chart" =>$sell_chart,
                "return_chart" =>$return_chart,
                "profit_chart" =>$profit_chart,
                "loss_chart" =>$loss_chart,

                
            );
            
             $response = array(
                "status"=>'true',
                "data"=>$data_arr[0]
            );
        // echo json_encode($response);
        // exit;
        return response()->json($response);
        
    }



    

    function getBetweenDates($startDate, $endDate) {
        $array = array();
        $interval = new DateInterval('P1D');
     
        $realEnd = new DateTime($endDate);
        $realEnd->add($interval);
     
        $period = new DatePeriod(new DateTime($startDate), $interval, $realEnd);
     
        foreach($period as $date) {
            $array[] = $date->format('Y-m-d');
        }
     
        return $array;
    }


    public function logout(){
        auth()->logout();
        return redirect()->route('login');
    }
 
}

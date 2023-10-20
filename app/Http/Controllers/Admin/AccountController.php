<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Categories;
use App\Models\CompanyManagements;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\OrdersStock;
use App\Models\Accounts;
use File;

class AccountController extends Controller
{
    public function __construct()
    {

    }
    public function index(){
        $company = CompanyManagements::all();
        return view('admin.account.index',compact('company'));
    }
    public function getdataAccount(Request $request)
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
        
        $totalRecords = Accounts::select('count(*) as allcount')->count();
        $totalRecordswithFilter = Accounts::select('count(*) as allcount')->where('company_id', 'like', '%' . $searchValue . '%')->count();

        $records = Accounts::with('companyDetail')
            ->whereRelation('companyDetail', 'company_name', 'like', '%'.$searchValue.'%')
            ->orderBy($columnName, $columnSortOrder)
            // ->skip($start)
            // ->take($rowperpage)
            ->get();

        // pre($records->toArray()); exit;
        $data_arr = array();
        $new_arr = array();
        $data_offset = 0;
        foreach ($records as $record) {
            if($record->open_account == 1){
                if(isset($new_arr[$record->company_id]['remaining'])){
                    $new_arr[$record->company_id]['remaining'] = $new_arr[$record->company_id]['remaining'] + $record->remaining;
                }else{
                    $new_arr[$record->company_id]['remaining'] = $record->remaining;
                }
            }
            if(isset($new_arr[$record->company_id]['done_ammount'])){
                $new_arr[$record->company_id]['done_ammount'] = $new_arr[$record->company_id]['done_ammount'] + $record->done_ammount;
            }else{
                $new_arr[$record->company_id]['done_ammount'] = $record->done_ammount;
            }
        }
        foreach($new_arr as $k => $d){
            $OrdersStock = OrdersStock::where('company_id',$k)->get();
            if(!empty($OrdersStock) && $OrdersStock != ''){
                foreach ($OrdersStock as $value) {
                    if(isset($new_arr[$k]['remaining'])){
                        $new_arr[$k]['remaining'] = $new_arr[$k]['remaining'] + $value->sell_price;
                    }else{
                        $new_arr[$k]['remaining'] = $value->sell_price;
                    }
                }
            } 
        }
        foreach($new_arr as $company_id => $detail){
            $company_detail = CompanyManagements::where('id', $company_id)->first();
            $data_arr[] = array(
                "c_id" => $company_id,
                "company_id" => $company_detail->company_name,
                "remaining" => $new_arr[$company_id]['remaining']-$new_arr[$company_id]['done_ammount'],
                "done_ammount"=> $new_arr[$company_id]['done_ammount'],
            );
        }
        $response = array(
            "draw" => intval($draw),
            "iTotalRecords" => count($new_arr),//$totalRecords,
            "iTotalDisplayRecords" =>count($new_arr),
            "aaData" => $data_arr
        );
        
        echo json_encode($response);
        exit;
    }
    public function getdata(Request $request){
        $remaining = 0;
        $account_remaining = 0;
        $id =  $request->company_id;
        $OrdersStock = OrdersStock::where('company_id',$id)->get();
        if(!empty($OrdersStock) && $OrdersStock != ''){
            foreach ($OrdersStock as $value) {
                $remaining = $remaining + $value->sell_price;
            }
        } 
        $completed = 0;
        $accounts = Accounts::where('company_id',$id)->get();
        foreach($accounts as $key => $account){
            $completed = $completed + $account->done_ammount;
            $remaining = $remaining + $account->remaining;
        }
        $total_ammount = $remaining - $completed;

        if ($total_ammount > 0) {
            echo json_encode($total_ammount);
        }
    }

    public function create(Request $request){
        $account = new Accounts;
        $account->company_id =     $request->c_id;
        $account->done_ammount =   $request->done_ammount;
        if ($account->save()) {
            return response()->json(['status' => 'true', 'message' => 'Data Is Save'], 200); 
        }else{
            return response()->json(['status' => 'false','message' => 'Somthing Want Wronge'], 200);
        }
    }
    public function reoprnaccount(Request $request){
        $account = new Accounts;
        $account->company_id =     $request->c_id;
        $account->remaining =      $request->r_ammount;
        $account->open_account = 1;
        if ($account->save()) {
            return response()->json(['status' => 'true', 'message' => 'Account is Open'], 200); 
        }else{
            return response()->json(['status' => 'false','message' => 'Somthing Want Wronge'], 200);
        }
    }
}
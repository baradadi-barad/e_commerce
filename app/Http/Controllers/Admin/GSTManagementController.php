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
use App\Models\Roles;
use App\Models\StockDetails;
use App\Models\Contexts;
use App\Models\GSTRates;
use App\Models\RoleRights;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Auth;
use File;

class GSTManagementController extends Controller
{

    public function index()
    {
        $records = GSTRates::all();
        return view('admin.gst-management.index', compact('records'));
    }

    public function getGSTRateList(Request $request)
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

        // Total records
        $totalRecords = GSTRates::select('count(*) as allcount')->count();
        $totalRecordswithFilter = GSTRates::select('count(*) as allcount')->count();

        // Fetch records
        $records = GSTRates::where(function ($query) use ($searchValue) {
                if (!empty($searchValue)) {
                    $query->where('rate', 'like', "%" . $searchValue . "%");
                }
            })
            ->orderBy('id', 'desc')
            ->skip($start)
            ->take($rowperpage)
            ->get();


        $data_arr = array();

        $key = 0;
        foreach ($records as $record) {
            $id = $record->id;
            $rate = $record->rate;

            $data_arr[] = array(
                "id" => $id,
                "rate" => $rate,
                "edit_route" => route("gst-management.edit", $record->id),
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


    public function create()
    {
        $contexts =array();

        $record['contexts'] = $contexts;
        return view('admin.gst-management.create', $record);
    }

    public function store(Request $request)
    {
        $request->validate(
            [
                'rate' => 'required',
            ],
            [
                'rate.required'         => 'The Rate field is required',
            ]
        );

        $user = Auth::user();
        $gstinfo = new GSTRates();
        $gstinfo->rate = $request->rate;
        $gstinfo->added_by = $user->id;
        if ($gstinfo->save()) {
            
            return redirect()->route('gst-management')->with('success', 'GST Rate Added Successfully');
        } else {
            return redirect()->back()->with('error', 'Something Went Wrong');
        }
    }

    public function edit($id)
    {
        $gstinfo = GSTRates::where('id',$id)->first();

        
        $record['gstinfo'] = $gstinfo;
        return view('admin.gst-management.edit', $record);
    }

    public function update(Request $request, $id)
    {
        $postData = $request->all();
        $role_id = $id;
        $user = Auth::user();
        $gstinfo = GSTRates::find($id);
        $gstinfo->added_by = $user->id;
        $gstinfo->rate = $postData['rate'];
        $gstinfo->save();

        return redirect()->route('gst-management')->with('success', 'GST Rate Updated Successfully');
    }

    public function destroy($id)
    {        
        $gstinfo = GSTRates::find($id);
        if ($gstinfo->delete()) {
            return response()->json(['status' => 'true', 'message' => 'GST Rate Deleted successfully'], 200);
        } else {
            return response()->json(['status' => 'false', 'message' => 'Something went wrong'], 200);
        }
    }
        
    
}

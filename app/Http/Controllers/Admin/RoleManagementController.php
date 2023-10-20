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
use App\Models\RoleRights;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Auth;
use File;

class RoleManagementController extends Controller
{

    public function index()
    {
        $records = Roles::all();
        return view('admin.role-management.index', compact('records'));
    }

    public function returnStock(Request $request)
    {
        $barcodeinfo = $request->barcodeinfo;
        $ReturnOrderStock = ReturnOrderStock::where('barcode_no', 'LIKE', '%' . $barcodeinfo . '%')->first();

        if ($ReturnOrderStock != '') {
            return response()->json(['status' => 'false', 'message' => 'This Barcode is already Sanned'], 200);
        } else {
            // $returninfo = explode('P,', $barcodeinfo);
            $returninfo = array();
            $returninfo = $this->multiexplode(array("P", "S", "U"), $barcodeinfo);

            if (count($returninfo) == 4) {
                $stockret = new ReturnOrderStock();
                $stockret->product_id = $returninfo[1];
                $stockret->stock_id = $returninfo[2];
                $stockret->unique_id = $returninfo[3];
                $stockret->barcode_no = $barcodeinfo;
                if ($stockret->save()) {
                    return response()->json(['status' => 'true', 'message' => 'Stock Return successfully'], 200);
                } else {
                    return response()->json(['status' => 'false', 'message' => 'Something went wrong'], 200);
                }
            } else {
                return response()->json(['status' => 'false', 'message' => 'This Barcode is Not Valid'], 200);
            }
        }
    }

    public function getRolesList(Request $request)
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
        $totalRecords = Roles::select('count(*) as allcount')->count();
        $totalRecordswithFilter = Roles::select('count(*) as allcount')->count();

        // Fetch records
        $records = Roles::with('rolescont')
            ->where(function ($query) use ($searchValue) {
                if (!empty($searchValue)) {
                    $query->where('name', 'like', "%" . $searchValue . "%");
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
            $name = $record->name;
            $is_admin = $record->is_admin;

            $data_arr[] = array(
                "id" => $id,
                "name" => $name,
                "is_admin" => $is_admin,
                "edit_route" => route("role-management.edit", $record->id),
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
        $contexts = Contexts::get();

        foreach ($contexts as $const) {
            $const->available_rights = explode(',', $const->available_rights);
        }

        $record['contexts'] = $contexts;
        return view('admin.role-management.create', $record);
    }

    public function store(Request $request)
    {
        $request->validate(
            [
                'name' => 'required',
                'description'   => 'required',
            ],
            [
                'name.required'         => 'The name field is required',
                'description.required'   => 'The description field is required',
            ]
        );


        $user = Auth::user();
        $roledata = new Roles();
        $roledata->name = $request->name;
        $roledata->description = $request->description;
        $roledata->role_slug = str_slug($request->name);
        $roledata->code = str_slug($request->name);
        $roledata->added_by = $user->id;
        if ($roledata->save()) {
            $role_id = $roledata->id;

            $rolerightdatainfo = array();
            $rolerightdatainfo = $request->rolecheckbox;
            foreach ($rolerightdatainfo as $key => $right) {

                $rolerightdata = new RoleRights();
                $rolerightdata->role_id = $role_id;
                $rolerightdata->context_id = $key;
                if (isset($right) && is_array($right) && in_array('view', $right)) {
                    $rolerightdata->view_right = 1;
                }
                if (isset($right) && is_array($right) && in_array('add', $right)) {
                    $rolerightdata->add_right = 1;
                }
                if (isset($right) && is_array($right) && in_array('update', $right)) {
                    $rolerightdata->update_right = 1;
                }
                if (isset($right) && is_array($right) && in_array('delete', $right)) {
                    $rolerightdata->delete_right = 1;
                }
                $rolerightdata->added_by = auth()->user()->id;
                $rolerightdata->save();
            }
            return redirect()->route('role-management')->with('success', 'Roles Created Successfully');
        } else {
            return redirect()->back()->with('error', 'Something Went Wrong');
        }
    }

    public function edit($id)
    {
        $contexts = Contexts::where('parent_id', 0)->get();
        $rolesdata = Roles::with('rolescont')->where('id', $id)->first();

        foreach ($contexts as $const) {
            if (isset($rolesdata->rolescont)) {
                foreach ($rolesdata->rolescont as $key => $value) {
                    if ($value->context_id == $const->id) {
                        $const->add = $value->add_right;
                        $const->update = $value->update_right;
                        $const->delete = $value->delete_right;
                        $const->view = $value->view_right;
                    }
                }
            }
            $const->available_rights = explode(',', $const->available_rights);
        }
        $record['contexts'] = $contexts;
        $record['rolesdata'] = $rolesdata;
        return view('admin.role-management.edit', $record);
    }

    public function update(Request $request, $id)
    {
        $postData = $request->all();
        $role_id = $id;
        $user = Auth::user();
        $data = Roles::find($id);
        $data->added_by = $user->id;
        $data->name = $postData['name'];
        $data->description = $postData['description'];
        $data->role_slug = str_slug($postData['name']);
        $data->code = str_slug($postData['name']);
        $data->save();

        RoleRights::where('role_id', $role_id)->delete();
        if (isset($postData['rolecheckbox'])) {
            $rolerightdatainfo = array();
            $rolerightdatainfo = $request->rolecheckbox;
            foreach ($rolerightdatainfo as $key => $right) {
                $rolerightdata = new RoleRights();
                $rolerightdata->role_id = $role_id;
                $rolerightdata->context_id = $key;
                if (isset($right) && is_array($right) && in_array('view', $right)) {
                    $rolerightdata->view_right = 1;
                }
                if (isset($right) && is_array($right) && in_array('add', $right)) {
                    $rolerightdata->add_right = 1;
                }
                if (isset($right) && is_array($right) && in_array('update', $right)) {
                    $rolerightdata->update_right = 1;
                }
                if (isset($right) && is_array($right) && in_array('delete', $right)) {
                    $rolerightdata->delete_right = 1;
                }
                $rolerightdata->save();
            }
        }

        return redirect()->route('role-management')->with('success', 'Roles Update Successfully');
    }

    public function destroy($id)
    {
        // echo '<pre>';
        // print_r($id);
        // exit;
        
        
        $totalRecords = User::select('count(*) as allcount')->where('role',$id)->count();
        
        if($totalRecords > 0){
            $rolesinfo = Roles::find($id);
            if ($rolesinfo->delete()) {
                return response()->json(['status' => 'true', 'message' => 'Role Deleted successfully'], 200);
            } else {
                return response()->json(['status' => 'false', 'message' => 'Something went wrong'], 200);
            }
        }else {
            return response()->json(['status' => 'false', 'message' => 'You Can`t Delete This Role because This Role Related Users Are Available,Please Remove User First !!!'], 200);
        }
        
    }
}

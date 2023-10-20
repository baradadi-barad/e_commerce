<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Categories;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Auth;
use File;

class CategoriesController extends Controller
{
    public function __construct()
    {
    }

    public function index()
    {
        $records = Categories::all();
        return view('admin.category.index', compact('records'));
    }

    public function create()
    {
        $categories = array();
        return view('admin.category.create', compact('categories'));
    }


    public function getCategories(  )
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
        $totalRecords = Categories::select('count(*) as allcount')->count();
        $totalRecordswithFilter = Categories::select('count(*) as allcount')->where('name', 'like', '%' . $searchValue . '%')->count();

        // Fetch records
        $records = Categories::where(function ($query) use ($searchValue) {
            if (!empty($searchValue)) {
                $query->where('name', 'like', "%" . $searchValue . "%");
            }
        })->orderBy($columnName, $columnSortOrder)
            ->skip($start)
            ->take($rowperpage)
            ->get();

        $data_arr = array();

        foreach ($records as $record) {
            $id = $record->id;
            $name = $record->name;
            $image = $record->image;


            $data_arr[] = array(
                "id" => $id,
                "name" => $name,
                "image" => category_image_url($image),
                "edit_route" => route("category.edit", $id),
            );
        }

        $response = array(
            "draw" => intval($draw),
            "iTotalRecords" => $totalRecords,
            "iTotalDisplayRecords" => $totalRecordswithFilter,
            "aaData" => $data_arr
        );

        echo json_encode($response);
        exit;
    }


    public function store(Request $request)
    {
        // echo 1;
        // exit;
        // $request->validate(
        //     [
        //         'name'         => 'required|unique:catagories,name',
        //         'description'   => 'required',
        //     ],
        //     [
        //         'name.required'         => 'The Name field is required',
        //         'name.unique'           => 'The Name is required unique',
        //     ]
        // );


        $category = new Categories();
        $category->name = $request->name;
        $category->category_slug = str_slug($request->name);
        $category->description = $request->description;
        $category->user_id = auth()->user()->id;
        if ($category->save()) {
            if ($request->file('image')) {
                $file = $request->file('image');
                $filename = date('YmdHi') . "." . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/category'), $filename);
                $category->image =  $filename;
                $category->save();
            }

            return redirect()->route('category')->with('success', 'Category Created Successfully');
        } else {
            return redirect()->back()->with('error', 'Something Went Wrong');
        }
    }

    public function edit($id)
    {
        $record = Categories::find($id);
        $categories = array();
        $data['record'] = $record;
        $data['categories'] = $categories;
        return view('admin.category.edit', $data);
    }


    public function update(Request $request, $id)
    {
        $id = base64_decode($id);

        $request->validate(
            [
                'name'         => 'required',
                'description'   => 'required',
            ],
            [
                'name.required'         => 'The Name field is required',
            ]
        );

        $category = Categories::find($id);
        $category->name = $request->name;
        $category->category_slug = str_slug($request->name);
        $category->description = $request->description;
        if ($category->save()) {
            $catalog_id = $category->id;
            if ($request->file('image')) {
                $destinationPath = public_path($category->image);
                if (File::exists($destinationPath)) {
                    File::delete($destinationPath);
                }
                $file = $request->file('image');
                $filename = time() . "." . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/category'), $filename);
                $category->image =  $filename;
                $category->save();
            }

            return redirect()->route('category')->with('success', 'Category Updated Successfully');
        } else {
            return redirect()->back()->with('error', 'Something Went Wrong');
        }
    }


    public function destroy($id)
    {
        $category = Categories::find($id);
        $imagePath = $category->image;
        if ($category->delete()) {

            // check if image exist then delete
            if (!empty($imagePath)) {
                $destinationPath = public_path($imagePath);
                if (File::exists($destinationPath)) {
                    File::delete($destinationPath);
                }
            }

            return response()->json(['status' => 'true', 'message' => 'Category  Deleted successfully'], 200);
        } else {
            return response()->json(['status' => 'false', 'message' => 'Something went wrong'], 400);
        }
    }
}

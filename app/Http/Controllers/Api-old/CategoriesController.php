<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Categories;

use File;

class CategoriesController extends Controller
{

    public function __construct()
    {

    }

    public function index()
    {

        $records = Categories::all();
        foreach ($records as $key => $value) {

            $value['image'] = url('/public/uploads/category/'). "/" . $value->image;
        }
        return response()->json($records);
    }

    public function store(Request $request)
    {

        $category = new Categories();
        $category->name = $request->name;
        $category->category_slug = str_slug($request->name);
        $category->description = $request->description;
        if ($category->save()) {
            // if ($request->file('image')) {
            //     $file = $request->file('image');
            //     $filename = date('YmdHi') . "." . $file->getClientOriginalExtension();
            //     $file->move(public_path('uploads/category'), $filename);
            //     $category->image = $filename;
            //     $category->save();
            // }

            if ($request->has('image')) {

                $base64Image = $request->input('image');
                $imageData = base64_decode($base64Image);
                $imageName = time() . '.jpg';

                $destinationPath = public_path('uploads/category');

                if (!File::isDirectory($destinationPath)) {
                    File::makeDirectory($destinationPath, 0777, true, true);
                }

                $imagePath = $destinationPath . '/' . $imageName;
                file_put_contents($imagePath, $imageData);
                if (File::exists($category->image)) {
                    File::delete($category->image);
                }
    
                $category->image = $imageName;
                $category->save();

            }

            $response['status'] = true;
            $response['message'] = "Category Created Successfully.";
            $response['data'] = ['id'=>$category->id,'image'=>url('/public/uploads/category/')."/".$category->image];
        } else {
            $request['status'] = false;
            $response['message'] = "Something Went Wrong.";
        }
        return response()->json($response);
    }

    public function update(Request $request, $id)
    {


        $category = Categories::find($id);
        $category->name = $request->name;
        $category->category_slug = str_slug($request->name);
        $category->description = $request->description;
        if ($category->save()) {
            if ($request->has('image')) {

                // $destination_path = public_path($category->image);
                // if(File::exists($destination_path)) {
                //     File::delete($destination_path);
                // }
                $base64Image = $request->input('image');
                $imageData = base64_decode($base64Image);
                $imageName = time() . '.jpg';

                $destinationPath = public_path('uploads/category');

                if (!File::isDirectory($destinationPath)) {
                    File::makeDirectory($destinationPath, 0777, true, true);
                }

                $imagePath = $destinationPath . '/' . $imageName;
                file_put_contents($imagePath, $imageData);
                if (File::exists($category->image)) {
                    File::delete($category->image);
                }
    
                $category->image = $imageName;
                $category->save();
                // $filename = time().".".$file->getClientOriginalExtension();
                // $file->move(public_path('uploads/category'), $filename);
                // $category->image = $filename;
                // $category->save();
            }

            $response['status'] = true;
            $response['message'] = "Category Updated Successfully";
            $response['data'] = ['id'=>$category->id,'image'=>url('/public/uploads/category/')."/".$category->image];
        } else {
            $response['status'] = false;
            $response['message'] = "Something Went Wrong";
        }

        return response()->json($response);
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

            $response['status'] = true;
            $response['message'] = "Category  Deleted successfully";
            $response['data'] = ['id'=>$category->id];
        } else {
            $response['status'] = false;
            $response['message'] = "Something went wrong";
        }

        return response()->json($response);
    }
}

?>
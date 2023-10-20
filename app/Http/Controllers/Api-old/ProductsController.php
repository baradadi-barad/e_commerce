<?php
// Api Controller
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Products;
use App\Models\ProductOptions;
use App\Models\ProductOptionValues;
use App\Models\Categories;
use App\Models\PriceHistory;
use App\Models\StockDetails;
use App\Models\ReturnOrderStock;
use App\Models\OrdersStock;
use PDF;


use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Auth;
use File;

class ProductsController extends Controller
{

    public function index()
    {

        $records = Products::with('categoryData')->get();

        foreach ($records as $key => $product) {
            $images = json_decode($product->image); // Assuming images are stored as JSON in the database

            if ($product->image == null) {
                continue;
            }
            foreach ($images as &$image) {
                $image = url('/public/uploads/products/') . "/" . $image;
            }

            $product->image = $images;
        }

        return response()->json($records);
    }

    public function fetchCategory()
    {
        $categories = Categories::where('parent_category', '==', '0')->get();
        // $record['categories'] = $categories;
        // echo "<PRE>"; print_r($record); exit;
        return response()->json($categories);
    }


    public function store(Request $request)
    {
        $product = new Products();
        $product->title = $request->input('title');
        $product->product_slug = str_slug($request->input('title'));
        $product->sku = $request->input('sku');
        // $product->buy_price = $request->input('buy_price');
        // $product->sell_price = $request->input('sell_price');
        $product->batch_no = $request->input('batch_no');
        $product->category_id = $request->input('category_id');
        $product->description = $request->input('description');
        $product->user_id = $request->input('user_id');

        // echo "<pre>"; print_r($categorie); exit;
        if ($product->save()) {
            $product_id = $product->id;
            $images = [];

            if ($request->has('images') && is_array($request->input('images'))) {

                // echo "<pre>"; print_r(count($request->input('images'))); exit;

                $i = 0;
                foreach ($request->input('images') as $base64Image) {

                    $imageData = base64_decode($base64Image);
                    $filename = time() . "_" . uniqid() . $i . '.jpg'; // Generate a unique filename with the extension .jpg
                    $path = public_path('uploads/products/' . $filename);
                    file_put_contents($path, $imageData);
                    $images[] = $filename;
                    $i++;
                }

                $product->image = json_encode($images);
                $product->save();

                $response['status'] = true;
                $response['message'] = "Product Created Successfully.";

                $response['data']['id'] = $product->id;

                $product->image = json_decode($product->image);
                foreach ($product->image as $image) {
                    $response['data']['image'][] = url("/public/uploads/products/") . "/" . $image;
                }

                $category_data = Categories::where('id', $request->input('category_id'))->first();
                $response['data']["category_data"] = $category_data;

            } else {
                $response['status'] = false;
                $response['message'] = "Image not Created.";
            }
        } else {
            $response['status'] = false;
            $response['message'] = "Failed to create the product.";
        }

        return response()->json($response);
    }


    // public function update(Request $request, $id)
    // {
    //     $product = Products::find($id);

    //     if (!$product) {
    //         return response()->json(['status' => false, 'message' => 'Product not found'], 404);
    //     }

    //     $product->title = $request->input('title');
    //     $product->product_slug = str_slug($request->input('title'));
    //     $product->sku = $request->input('sku');
    //     $product->batch_no = $request->input('batch_no');
    //     $product->category_id = $request->input('category_id');
    //     $product->description = $request->input('description');
    //     $product->user_id = $request->input('user_id');

    //     if ($request->has('images')) {
    //         $images = [];
    //         $removedImages = [];

    //         foreach ($request->input('images') as $base64Image) {
    //             $imageData = base64_decode($base64Image);
    //             $filename = time() . "_" . uniqid() . '.jpg';
    //             $path = public_path('uploads/products/' . $filename);
    //             file_put_contents($path, $imageData);
    //             $images[] = $filename;
    //         }

    //         // Handle removed images
    //         if ($request->has('removed_images')) {
    //             $removedImages = json_decode($request->input('removed_images'));
    //             foreach ($removedImages as $removedImage) {
    //                 $imagePath = public_path('uploads/products/' . $removedImage);
    //                 if (file_exists($imagePath)) {
    //                     unlink($imagePath); // Remove the file from storage
    //                 }
    //             }
    //         }

    //         // Merge the new and existing images
    //         $product->image = json_encode(array_merge(json_decode($product->image), $images));
    //     }

    //     if ($product->save()) {
    //         return response()->json(['status' => true, 'message' => 'Product updated successfully']);
    //     } else {
    //         return response()->json(['status' => false, 'message' => 'Failed to update the product']);
    //     }
    // }




    public function update(Request $request, $id)
    {
        // echo "<pre>"; print_r("..............hello world............"); exit;

        // $request->validate(
        //     [
        //         'title' => 'required',
        //         'sku' => 'required',
        //         'price' => 'required',
        //         'batch_no' => 'required',
        //     ],
        //     [
        //         'title.required' => 'The Title field is required',
        //         'sku.required' => 'The Sku field is required',
        //         'price.required' => 'The Price field is required',
        //         'batch_no.required' => 'The Batch No field is required',
        //     ]
        // );


        $product = Products::find($id);
        // $product = new Products();
        $product->title = $request->input('title');
        $product->product_slug = str_slug($request->input('title'));
        $product->sku = $request->input('sku');
        // $product->buy_price = $request->input('buy_price');
        // $product->sell_price = $request->input('sell_price');
        $product->batch_no = $request->input('batch_no');
        $product->category_id = $request->input('category_id');
        $product->description = $request->input('description');
        $product->user_id = $request->input('user_id');


        // echo "<pre>"; print_r("..............hello world..0..........".$request->image_removed); exit;

        if ($product->save()) {
            $product_id = $product->id;
            $current_images = array();
            $save_image = array();

            if ($request->has('images') && is_array($request->input('images'))) {

                $i = 0;
                foreach ($request->input('images') as $base64Image) {

                    $imageData = base64_decode($base64Image);
                    $filename = time() . "_" . uniqid() . $i . '.jpg'; // Generate a unique filename with the extension .jpg
                    $path = public_path('uploads/products/' . $filename);
                    file_put_contents($path, $imageData);
                    $save_image[] = $filename;
                    $i++;
                }
            }

            if (!empty($save_image)) {
                $product->image = json_encode($save_image);
            } else {
                $product->image = '';
            }
            $product->save();

            $response['status'] = true;
            $response['message'] = "Product Updated Successfully";
            // $response['data'] = ['id' => $product->id, 'image' => "https://mydemowork.in/stock_managment_new/public/uploads/products/" . $product->image];
            $response['data']['id'] = $product->id;

            $product->image = json_decode($product->image);
            foreach ($product->image as $image) {
                $response['data']['image'][] = url("/public/uploads/products/") . "/" . $image;
            }


            $category_data = Categories::where('id', $request->input('category_id'))->first();
            $response['data']["category_data"] = $category_data;

        } else {
            $response['status'] = false;
            $response['message'] = "Something Went Wrong";
        }

        return response()->json($response);
    }
    public function destroy($id)
    {
        $product = Products::find($id);

        if ($product != null) {
            if ($product->image != '') {
                foreach (json_decode($product->image, true) as $image) {
                    $destinationPath = public_path('uploads/products/' . $image);
                    File::delete($destinationPath);
                }
            }
                $product->delete();
    
                $response['status'] = true;
                $response['message'] = "Product  Deleted successfully";
                $response['data'] = ['id' => $product->id];
           
        }

        // if ($product->image != '') {
        //     foreach (json_decode($product->image, true) as $image) {
        //         $destinationPath = public_path('uploads/products/' . $image);
        //         File::delete($destinationPath);
        //     }

        //     $product->delete();

        //     $response['status'] = true;
        //     $response['message'] = "Product  Deleted successfully";
        //     $response['data'] = ['id' => $product->id];
        // } 
        else {
            $response['status'] = false;
            $response['message'] = "Something went wrong";
        }

        return response()->json($response);
    }

    public function getProductStocks(Request $request, $id)
    {
        // ## Read value


        // Total records
        $totalRecords = StockDetails::with('Product')->where('product_id', $id)->count();

        // echo "<pre>"; print_r("..............hello world..0..........".$totalRecords); exit;

        $totalRecordswithFilter =
            StockDetails::with('Product')->where('product_id', $id)->count();

        // Fetch records

        $records = StockDetails::with('Product', 'warehouse')
            ->where('product_id', $id)
            ->get();

        $warehouse_name = $records[0]["warehouse"]["warehouse_name"];

        $data_arr = array();

        foreach ($records as $record) {
            $sellstock = OrdersStock::where('stock_id', $record->id)->count();
            $id = $record->id;
            $qty = $record->qty;
            $rqty = $record->qty - $sellstock;
            $sell_price = $record->sell_price;
            $buy_price = $record->buy_price;
            $created_at = $record->created_at;

            $data_arr[] = array(
                "id" => $id,
                "qty" => $qty,
                "rqty" => $rqty,
                "buy_price" => $buy_price,
                "sell_price" => $sell_price,
                "warehouse_name" => $warehouse_name,
                "created_at" => date('Y-m-d H:i:s', strtotime($created_at)),
                "edit_route" => route("products.edit", $id),
                "barcode_route" => route("products.edit", $id),
                
            );
        }

        // $response = array(

        //     "status" => "true",
        //     // "iTotalDisplayRecords" => $totalRecordswithFilter,
        //     "data" => $data_arr
        // );
        ;
        
        return response()->json($data_arr);
        
        
        // $product_id = $id;

        // $x = StockDetails::all();
        // echo "<pre>"; print_r($x); exit;

        // $records = StockDetails::with('product','warehouse')->where('product_id', $id)->get();

        // return response()->json(compact('records', 'product_id'));
    }

    public function createstock(Request $request)
    {
        $stock = new StockDetails();
        $stock->qty = $request->qty;
        $stock->buy_price = $request->buy_price;
        $stock->sell_price = $request->sell_price;
        $stock->product_id = $request->product_id;
        if ($stock->save()) {
            return response()->json(['status' => 'true', 'message' => 'Stock Add successfully'], 200);
        } else {
            return response()->json(['status' => 'false', 'message' => 'Something went wrong'], 400);
        }
    }

    public function editstock(Request $request, $id)
    {
        $stock = StockDetails::find($id);

        if (isset($stock) && !empty($stock)) {
            return response()->json(['status' => 'true', 'stock' => $stock], 200);
        } else {
            return response()->json(['status' => 'false', 'message' => 'Something went wrong'], 400);
        }
    }

    public function updatestock(Request $request, $id)
    {
        $stock = StockDetails::find($id);
        $stock->qty = $request->qty;
        $stock->buy_price = $request->buy_price;
        $stock->sell_price = $request->sell_price;
        if ($stock->save()) {
            return response()->json(['status' => 'true', 'message' => 'Stock Edit successfully'], 200);
        } else {
            return response()->json(['status' => 'false', 'message' => 'Something went wrong'], 400);
        }
    }

    public function destroystock(Request $request, $id)
    {
        $stock = StockDetails::find($id);
        if ($stock->delete()) {
            return response()->json(['status' => 'true', 'message' => 'Stock Deleted successfully'], 200);
        } else {
            return response()->json(['status' => 'false', 'message' => 'Something went wrong'], 400);
        }
    }

    public function generateBarcode(Request $request, $id)
    {
        $stock = StockDetails::with('product')->find($id);

        // echo '<pre>';
        // print_r($stock);
        // exit;

        $data = ['title' => 'Generated PDF', 'stock' => $stock];
        $view = app()->make('view')->make('pdf.barcodepdf', $data)->render();
        // $pdf = PDF::loadHTML($view);
        // $path = base_path() . '/public/barcodes/barcodes.pdf';
        // $pdf->save($path);
        $file_archive = ''; //url('/') . '/public/barcodes/barcodes.pdf';
        // return $pdf->download('invoice.pdf');

        if (isset($stock) && !empty($stock)) {
            return response()->json(['status' => 'true', 'data' => $file_archive, 'view' => $view], 200);
        } else {
            return response()->json(['status' => 'false', 'message' => 'Something went wrong'], 400);
        }
    }

    public function updatePrice(Request $request)
    {

        // echo '<pre>';
        // print_r($request->all());
        // exit;
        // $request->validate(
        //     [
        //         'sell_price' => 'required',
        //         'buy_price'   => 'required',
        //     ],
        //     [
        //         'buy_price.required' => 'The Buy Price field is required',
        //         'sell_price.required' => 'The Sell Price field is required',
        //     ]
        // );

        $user_id = $request->user_id;
        $pricehistoryinfo = new PriceHistory();
        $pricehistoryinfo->product_id = $request->product_id;
        $pricehistoryinfo->old_sell_price = $request->old_sell_price ?? 0;
        $pricehistoryinfo->old_buy_price = $request->old_buy_price ?? 0;
        $pricehistoryinfo->buy_price = $request->buy_price;
        $pricehistoryinfo->sell_price = $request->sell_price;
        $pricehistoryinfo->user_id = $user_id;
        if ($pricehistoryinfo->save()) {

            $product = Products::find($request->product_id);
            $product->buy_price = $request->buy_price;
            $product->sell_price = $request->sell_price;
            $product->price_history_id = $pricehistoryinfo->id;
            $product->price_update_user_id = $user_id;
            if ($product->save()) {
                // echo 2;
                // exit;
                return response()->json(['status' => 'true', 'message' => 'Price Updated Successfully', 'data' => ["id" => $product->id]], 200);
            } else {
                return response()->json(['status' => 'false', 'message' => 'Price Updated  Not Successfully'], 200);
            }
        } else {
            return redirect()->back()->with('error', 'Something Went Wrong');
        }
    }

}

?>
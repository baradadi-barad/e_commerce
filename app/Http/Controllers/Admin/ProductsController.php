<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Products;
use App\Models\ProductOptions;
use App\Models\ProductOptionValues;
use App\Models\Categories;
use App\Models\StockDetails;
use App\Models\PriceHistory;
use App\Models\ReturnOrderStock;
use App\Models\OrdersStock;
use App\Models\GSTRates;
use PDF;


use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Auth;
use File;

class ProductsController extends Controller
{

    public function index()
    {
        $records = Products::all();
        return view('admin.products.index', compact('records'));
    }

    public function create()
    {
        $categories = Categories::where('parent_category', '==', '0')->get();
        $gstinfo = GSTRates::get();
        $record['categories'] = $categories;
        $record['gstinfo'] = $gstinfo;
        return view('admin.products.create', $record);
    }

    public function fetchSubCategory(Request $request)
    {
        $data['category'] = Categories::where("parent_category", $request->category_id)
            ->get(["name", "id"]);
        return response()->json($data);
    }

    public function getProducts(Request $request)
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
        $totalRecords = Products::whereHas('categoryData')->select('count(*) as allcount')->count();
        $totalRecordswithFilter = Products::whereHas('categoryData')->select('count(*) as allcount')->where('title', 'like', '%' . $searchValue . '%')->count();

        // Fetch records
        $records = Products::where(function ($query) use ($searchValue) {
            if (!empty($searchValue)) {
                $query->where('products.title', 'like', "%" . $searchValue . "%");
            }
        })->orderBy($columnName, $columnSortOrder)
            ->skip($start)
            ->take($rowperpage)
            ->whereHas('categoryData')
            ->with('categoryData')
            ->get();


        $data_arr = array();

        foreach ($records as $record) {
            $id = $record->id;
            $title = $record->title;
            $sku = $record->sku;
            $gst_rate = $record->gst_rate;
            $category = isset($record->categoryData->name) ? $record->categoryData->name : '';
            $buy_price = $record->buy_price;
            $sell_price = $record->sell_price;

            $data_arr[] = array(
                "id" => $id,
                "title" => $title,
                "sku" => $sku,
                "category" => "$category",
                "buy_price" => $buy_price,
                "sell_price" => $sell_price,
                "gst_rate" => $gst_rate,
                "edit_route" => route("products.edit", $id),
                "show_route" => route("products.show", $id),
                "stock_route" => route("products.stock", $id),
            );
        }
        // pre($data_arr); exit;
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
        $request->validate(
            [
                'title' => 'required',
                'sku'   => 'required',
                'buy_price' => 'required',
                'sell_price' => 'required',
                'batch_no' => 'required',
                'category_id' => 'required',
                'gst_rate' => 'required',
            ],
            [
                'title.required'         => 'The Title field is required',
                'sku.required'   => 'The Sku field is required',
                'buy_price.required' => 'The Buy Price field is required',
                'sell_price.required' => 'The Sell Price field is required',
                'batch_no.required' => 'The Batch No field is required',
                'category_id.required' => 'The Category  field is required',
                'gst_rate.required' => 'The GST Rate  field is required',
            ]
        );

        $user = Auth::user();
        $product = new Products();
        $product->title = $request->title;
        $product->product_slug = str_slug($request->title);
        $product->sku = $request->sku;
        $product->gst_rate = $request->gst_rate;
        $product->buy_price = $request->buy_price;
        $product->sell_price = $request->sell_price;
        $product->batch_no = $request->batch_no;
        $product->category_id = $request->category_id;
        $product->description = $request->description;
        $product->user_id = $user->id;
        if ($product->save()) {
            $product_id = $product->id;

            if ($request->hasFile('image')) {

                foreach ($request->file('image') as $image) {
                    $filename = time() . "." . $image->getClientOriginalName();
                    $image->move(public_path('uploads/products'), $filename);
                    $images[] = $filename;
                }
                $product->image = json_encode($images);
                $product->save();
            }
            if(empty($request->old_sell_price) || $request->old_sell_price == '' || $request->old_sell_price == 'null' || !isset($request->old_sell_price)){
                $request->old_sell_price = 0;
            }
            if(empty($request->old_buy_price) || $request->old_buy_price == '' || $request->old_buy_price == 'null' || !isset($request->old_buy_price)){
                $request->old_buy_price = 0;
            }
    
            $user = Auth::user();
            $pricehistoryinfo = new PriceHistory();
            $pricehistoryinfo->product_id = $product->id;
            $pricehistoryinfo->old_sell_price = $request->old_sell_price;
            $pricehistoryinfo->old_buy_price = $request->old_buy_price;
            $pricehistoryinfo->buy_price = $request->buy_price;
            $pricehistoryinfo->sell_price = $request->sell_price;
            $pricehistoryinfo->gst_rate = $request->gst_rate;
            $pricehistoryinfo->user_id = $user->id;
            $pricehistoryinfo->save();

            return redirect()->route('products')->with('success', 'Product Created Successfully');
        } else {
            return redirect()->back()->with('error', 'Something Went Wrong');
        }
    }
    public function updatePrice(Request $request)
    {

        // echo '<pre>';
        // print_r($request->all());
        // exit;
        $request->validate(
            [
                'sell_price' => 'required',
                'buy_price'   => 'required',
            ],
            [
                'buy_price.required' => 'The Buy Price field is required',
                'sell_price.required' => 'The Sell Price field is required',
            ]
        );
        
        if(empty($request->old_sell_price) || $request->old_sell_price == '' || $request->old_sell_price == 'null' || !isset($request->old_sell_price)){
            $request->old_sell_price = 0;
        }
        if(empty($request->old_buy_price) || $request->old_buy_price == '' || $request->old_buy_price == 'null' || !isset($request->old_buy_price)){
            $request->old_buy_price = 0;
        }

        $productinfo = Products::where('id',$request->product_id)->first();
        // echo 1; exit;
        $user = Auth::user();
        $pricehistoryinfo = new PriceHistory();
        $pricehistoryinfo->product_id = $request->product_id;
        $pricehistoryinfo->old_sell_price = $request->old_sell_price;
        $pricehistoryinfo->old_buy_price = $request->old_buy_price;
        $pricehistoryinfo->buy_price = $request->buy_price;
        $pricehistoryinfo->sell_price = $request->sell_price;
        $pricehistoryinfo->gst_rate = $productinfo->gst_rate;
        $pricehistoryinfo->user_id = $user->id;
        if ($pricehistoryinfo->save()) {

            $product = Products::find($request->product_id);
            $product->buy_price = $request->buy_price;
            $product->sell_price = $request->sell_price;
            $product->price_history_id = $pricehistoryinfo->id;
            $product->price_update_user_id = $user->id;
            if ($product->save()) {
                // echo 2;
                // exit;
                return response()->json(['status' => 'true', 'message' => 'Price Updated Successfully'], 200);
            } else {
                return response()->json(['status' => 'false', 'message' => 'Price Updated  Not Successfully'], 200);
            }
        } else {
            return redirect()->back()->with('error', 'Something Went Wrong');
        }
    }

    public function edit($id)
    {
        $record = Products::find($id);
        $categories = Categories::where('parent_category', '0')->get();
        $gstinfo = GSTRates::get();

        $data['record'] = $record;
        $data['categories'] = $categories;
        $data['gstinfo'] = $gstinfo;
        return view('admin.products.edit', $data);
    }

    public function show($id)
    {
        $record = Products::find($id);
        $categories = Categories::where('parent_category', '==', '0')->get();
        $data['record'] = $record;
        $data['categories'] = $categories;
        return view('admin.products.show', $data);
    }

    public function update(Request $request, $id)
    {
        $id = base64_decode($id);

        $request->validate(
            [
                'title' => 'required',
                'sku'   => 'required',
                // 'buy_price' => 'required',
                // 'sell_price' => 'required',
                'batch_no' => 'required',
                'gst_rate' => 'required',
            ],
            [
                'title.required'         => 'The Title field is required',
                'sku.required'   => 'The Sku field is required',
                // 'buy_price.required' => 'The Buy Price field is required',
                // 'sell_price.required' => 'The Sell Price field is required',
                'batch_no.required' => 'The Batch No field is required',
                'gst_rate.required' => 'The GST Rate field is required',
            ]
        );

        $user = Auth::user();
        $product = Products::find($id);
        $product->title = $request->title;
        $product->product_slug = str_slug($request->title);
        $product->sku = $request->sku;
        // $product->buy_price = $request->buy_price;
        // $product->sell_price = $request->sell_price;
        $product->batch_no = $request->batch_no;
        $product->gst_rate = $request->gst_rate;
        $product->category_id = $request->category_id;
        $product->description = $request->description;
        if ($product->save()) {
            $product_id = $product->id;

            if ($request->image_removed != '') {
                $removed_image = explode(',', $request->image_removed);
                foreach ($removed_image as $imageRemoved) {
                    $destinationPath = public_path('uploads/products/' . $imageRemoved);
                    File::delete($destinationPath);
                    $removedImage = array();
                    if ($imageRemoved != '') {
                        $removedImage[] = $imageRemoved;
                    } else {
                        $removedImage = array();
                    }
                }
            }

            $current_images = array();
            if ($request->old_image) {
                $current_images = $request->old_image;
            }

            $save_image = array();
            if (!empty($removedImage)) {
                foreach ($removedImage as $r) {
                    foreach ($current_images as $c) {
                        if ($r != $c) {
                            $save_image[] = $c;
                        }
                    }
                }
            } else {
                $save_image = $current_images;
            }

            if ($request->hasFile('image')) {

                foreach ($request->file('image') as $image) {
                    $filename = time() . "." . $image->getClientOriginalName();
                    $image->move(public_path('uploads/products'), $filename);
                    $save_image[] = $filename;
                }
            }

            if (!empty($save_image)) {
                $product->image = json_encode($save_image);
            } else {
                $product->image = '';
            }
            $product->save();

            return redirect()->route('products')->with('success', 'Product edited Successfully');
        } else {
            return redirect()->back()->with('error', 'Something Went Wrong');
        }
    }


    public function destroy($id)
    {

        $totalRecords = StockDetails::where('product_id', $id)->get();
        $totalstockqty = 0;
        foreach($totalRecords as $valqty){
            $totalstockqty= $totalstockqty + $valqty->qty;
        }

        $sellstock = OrdersStock::where('product_id', $id)->count();

        if(($totalstockqty - $sellstock) == 0 ){
            $product = Products::find($id);
            if (!empty($product)) {
                if ($product->image != '') {
                    foreach (json_decode($product->image, true) as $image) {
                        $destinationPath = public_path('uploads/products/' . $image);
                        File::delete($destinationPath);
                    }
                }

                $product->delete();

                return response()->json(['status' => 'true', 'message' => 'Form Deleted successfully'], 200);
            } else {
                return response()->json(['status' => 'false', 'message' => 'Something went wrong'], 200);
            }
        }else{
            return response()->json(['status' => 'false', 'message' => 'This Product Related Stocks is available So You Can`t Delete This Product!!'], 200);
        }
       
    }


    public function stock($id)
    {
        $product_id = $id;

        $records = StockDetails::with('Product')->where('product_id', $id)->get();

        return view('admin.products.stock.index', compact('records', 'product_id'));
    }

    public function getProductStocks(Request $request, $id)
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
        $totalRecords = StockDetails::with('Product')->where('product_id', $id)->count();


        $totalRecordswithFilter =
            StockDetails::with('Product')->where('product_id', $id)->count();

        // Fetch records

        $records = StockDetails::with('Product')->with('warehouse')
            ->where('product_id', $id)
            ->where(function ($query) use ($searchValue) {
                if (!empty($searchValue)) {
                    $query->whereRelation('warehouse', 'warehouse_name', 'like', '%'.$searchValue.'%');
                }
            })
            ->orderBy($columnName, $columnSortOrder)
            ->skip($start)
            ->take($rowperpage)
            ->get();


        $data_arr = array();

        foreach ($records as $record) {
            //pre($record); exit;
            $sellstock = OrdersStock::where('product_id', $record->product_id)->where('stock_id', $record->id)->count();
            $id = $record->id;
            $warehouse_id = isset($record->warehouse->warehouse_name) ? $record->warehouse->warehouse_name : '';
            $qty = $record->qty;
            $rqty = $record->qty - $sellstock;
            $sell_price = $record->sell_price;
            $buy_price = $record->buy_price;
            $created_at = $record->created_at;

            $data_arr[] = array(
                "id" => $id,
                "warehouse_id" => $warehouse_id,
                "qty" => $qty,
                "rqty" => $rqty,
                "buy_price" => $buy_price,
                "sell_price" =>  $sell_price,
                "created_at" => date('Y-m-d H:i:s', strtotime($created_at)),
                "edit_route" => route("products.edit", $id),
                "barcode_route" => route("products.edit", $id),
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
            return response()->json(['status' => 'false', 'message' => 'Something went wrong'], 200);
        }
    }

    public function editstock(Request $request, $id)
    {
        $stock = StockDetails::find($id);

        if (isset($stock) && !empty($stock)) {
            return response()->json(['status' => 'true', 'stock' => $stock], 200);
        } else {
            return response()->json(['status' => 'false', 'message' => 'Something went wrong'], 200);
        }
    }

    public function updatestock(Request $request, $id)
    {
        $stock = StockDetails::find($id);
        $sellstock = OrdersStock::where('stock_id', $id)->count();
        if (($stock->qty - $sellstock) > $request->qty) {
            return response()->json(['status' => 'true', 'type' => 'stock error', 'message' => 'Stock Qty should not be less than Remaining Qty'], 200);
        }
        $stock->qty = $request->qty;
        if ($request->buy_price < 0 || $request->sell_price < 0) {
            return response()->json(['status' => 'true', 'type' => 'stock error', 'message' => 'Price should not be less than Zero'], 200);
        }
        $stock->buy_price = $request->buy_price;
        $stock->sell_price = $request->sell_price;
        if ($stock->save()) {
            return response()->json(['status' => 'true', 'message' => 'Stock Edit successfully'], 200);
        } else {
            return response()->json(['status' => 'false', 'message' => 'Something went wrong'], 200);
        }
    }

    public function destroystock(Request $request, $id)
    {
        $stock = StockDetails::find($id);
        $sellstock = OrdersStock::where('stock_id', $id)->count();
        if ($sellstock > 0) {
            return response()->json(['status' => 'false', 'message' => 'This Stock is Used, so can`t delete it'], 200);
        }
        if ($stock->delete()) {
            return response()->json(['status' => 'true', 'message' => 'Stock Deleted successfully'], 200);
        } else {
            return response()->json(['status' => 'false', 'message' => 'Something went wrong'], 200);
        }
    }






    function multiexplode($delimiters, $string)
    {

        $ready = str_replace($delimiters, $delimiters[0], $string);
        $launch = explode($delimiters[0], $ready);
        return  $launch;
    }
    public function generateBarcode(Request $request, $id)
    {
        $stock = StockDetails::with('product')->find($id);

        // echo '<pre>';
        // print_r($stock);
        // exit;

        $data = ['title' => 'Generated PDF', 'stock' => $stock];
        $view =  app()->make('view')->make('pdf.barcodepdf', $data)->render();
        // $pdf = PDF::loadHTML($view);
        // $path = base_path() . '/public/barcodes/barcodes.pdf';
        // $pdf->save($path);
        $file_archive =  ''; //url('/') . '/public/barcodes/barcodes.pdf';
        // return $pdf->download('invoice.pdf');

        if (isset($stock) && !empty($stock)) {
            return response()->json(['status' => 'true', 'data' => $file_archive, 'view' => $view], 200);
        } else {
            return response()->json(['status' => 'false', 'message' => 'Something went wrong'], 200);
        }
    }

    public function makeBarcodeProductWise(Request $request)
    {

        $request->validate(
            [
                'product_id' => 'required',
                'no_of_item'   => 'required',
            ],
            [
                'product_id.required' => 'The Product Id is required',
                'no_of_item.required' => 'The Barcodes Qty is required',
            ]
        );


        $product_id = $request->product_id;
        $no_of_item = $request->no_of_item;




        $barcodearraystringlist = array();
        if (isset($product_id) && !empty($product_id) && isset($no_of_item) && $no_of_item > 0) {
            for ($i = 1; $i <= $no_of_item; $i++) {
                $t = time();
                $barcodeuniquestring = 'P' . $product_id . 'U' . $t . '' . $i;
                $barcodearraystringlist[] = $barcodeuniquestring;
            }

        } else {
            return response()->json(['status' => 'false', 'message' => 'Something went wrong'], 200);
        }



        $stock = $barcodearraystringlist; // StockDetails::with('product')->find($id);

        $productinfo = Products::find($product_id);

        $data = ['title' => 'Generated PDF', 'stock' => $stock, 'productinfo' => $productinfo];
        $view =  app()->make('view')->make('pdf.barcodepdf', $data)->render();
        // $pdf = PDF::loadHTML($view);
        // $path = base_path() . '/public/barcodes/barcodes.pdf';
        // $pdf->save($path);
        $file_archive =  ''; //url('/') . '/public/barcodes/barcodes.pdf';
        // return $pdf->download('invoice.pdf');

        if (isset($stock) && !empty($stock)) {
            return response()->json(['status' => 'true', 'data' => $file_archive, 'view' => $view], 200);
        } else {
            return response()->json(['status' => 'false', 'message' => 'Something went wrong'], 200);
        }
    }
}

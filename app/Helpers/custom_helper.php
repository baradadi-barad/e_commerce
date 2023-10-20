<?php

use Illuminate\Support\Str;
use App\Models\User;
use App\Models\RoleRights;
use App\Models\Contexts;
//use File;
use Illuminate\Support\Facades\Auth;

function changeDateFormate($date, $date_format)
{
    return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $date)->format($date_format);
}

function image_path($path)
{
    $destinationPath = public_path($path);

    if (!empty($path) && File::exists($destinationPath)) {
        return asset("public/" . $path);
    } else {
        return asset("assets/common/no-image.png");
    }
}
function br(){
    echo  "<br>";
}
function category_image_url($path)
{
    $destinationPath = public_path("uploads/category/" . $path);

    if (!empty($path) && File::exists($destinationPath)) {
        return asset("public/uploads/category/" . $path);
    } else {
        return asset("assets/common/no-image.png");
    }
}

function product_image_url($path)
{
    $destinationPath = public_path("uploads/products/" . $path);

    if (!empty($path) && File::exists($destinationPath)) {
        return asset("public/uploads/products/" . $path);
    } else {
        return asset("assets/common/no-image.png");
    }
}
function user_image_url($path)
{
    $destinationPath = public_path("uploads/user/" . $path);

    if (!empty($path) && File::exists($destinationPath)) {
        return asset("public/uploads/user/" . $path);
    } else {
        return asset("assets/common/no-image.png");
    }
}

function str_slug($string)
{
    $string = \Str::slug($string);
    return $string;
}

function pre($arr)
{
    echo "<pre>";
    print_r($arr);
    echo "</pre>";
}
function cart_Count()
{
    $cartCount = \Cart::getContent();
    $wordCount = count($cartCount);
    return $wordCount;
}

function checkRoles($context, $right)
{

    $userId = Auth::user()->id;
    $userRole = User::where('id', $userId)->first();
    $rolesLists = [];
    $contextsid = Contexts::where('code', $context)->first();
    $rolesLists = '';
    $temp = '';
    if ($contextsid != '') {
        $rolesLists = RoleRights::with('rolesname')
            ->with('contname')
            ->where('role_id', $userRole->role)
            ->where('context_id', $contextsid->id)
            ->where('status', 1)
            ->first();

        // pre($rolesLists);
        // exit;

        if ($rolesLists != '') {
            if ($right == 'view_right') {
                $temp = $rolesLists->view_right;
            } elseif ($right == 'add_right') {
                $temp = $rolesLists->add_right;
            } elseif ($right == 'update_right') {
                $temp = $rolesLists->update_right;
            } elseif ($right == 'delete_right') {
                $temp = $rolesLists->delete_right;
            } else {
                $temp = '';
            }
        }
    } else {
        $rolesLists = '';
        $temp = '';
    }
    return $temp;
}

function is_warehouse($id)
{
    
    return $wordCount;
}

function multiexplode($delimiters, $string)
{

    $ready = str_replace($delimiters, $delimiters[0], $string);
    $launch = explode($delimiters[0], $ready);
    return  $launch;
}

function getGstincluded($amount,$percent,$cgst,$sgst){
   $gst_amount = $amount-($amount*(100/(100+$percent)));
//    echo $gst_amount; exit;
   $percentcgst = number_format($gst_amount/2, 2);
   $percentsgst =  number_format($gst_amount/2, 2);
   $display="<p>";
   if($cgst&&$sgst){
      $gst = $percentcgst + $percentsgst;
      $display .= " CGST = ".$percentcgst." SGST = " . $percentsgst;
   }elseif($cgst){
      $gst = $percentcgst;
      $display .= " CGST = ".$percentcgst;
   }else{
      $gst = $percentsgst;
      $display .= " SGST = ".$percentsgst;
   }
   $withoutgst = number_format($amount,2);
   $withgst = number_format($withoutgst + $gst_amount,2);
   $display .="</p>";
   $display .="<p>".$withoutgst . " + " . $gst . " = " . $withgst."</p>";
   return $display;
}
function getGstexcluded($amount,$percent,$cgst,$sgst){
   $gst_amount = ($amount*$percent)/100;
   $amountwithgst = $amount + $gst_amount;   
   $percentcgst = number_format($gst_amount/2, 2);
   $percentsgst =  number_format($gst_amount/2, 2);
   $display="<p>";
   if($cgst&&$cgst){
      $gst = $percentcgst + $percentsgst;
      $display .= " CGST = ".$percentcgst." SGST = " . $percentsgst;
   }elseif($cgst){
      $gst = $percentcgst;
      $display .= " CGST = ".$percentcgst;
   }else{
      $gst = $percentsgst;
      $display .= " SGST = ".$percentsgst;
   }
   $display .="</p>";
   $display .="<p>".$amount . " + " . $gst . " = " . $amountwithgst."</p>";
   return $display;
}

function simplegstincluded($amount,$percent){
   $gst_amount = $amount-($amount*(100/(100+$percent)));
   $percentcgst = number_format($gst_amount/2, 3);
   $percentsgst = number_format($gst_amount/2, 3);
   $withoutgst = number_format($amount - $gst_amount,2);
   $total = number_format($withoutgst+$gst_amount, 2);
   
   $display="<p>".$withoutgst." + ".number_format($gst_amount,2)." ( CGST = ".$percentcgst." SGST = " . $percentsgst." ) = " . $total."</p>";
   return $display;
}

function simplegstexcluded($amount,$percent){
   $gst_amount = ($amount*$percent)/100;
   $total = number_format($amount+$gst_amount, 2);
   $percentcgst = number_format($gst_amount/2, 3);
   $percentsgst = number_format($gst_amount/2, 3);
   
   $display="<p>".$amount." + ".number_format($gst_amount,2)." ( CGST = ".$percentcgst." SGST = " . $percentsgst." ) = " . $total."</p>";
   return $display;
}

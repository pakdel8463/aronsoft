<?php
namespace App\Http\Controllers;
use App\Models\SoftwareProduct;
class SoftwareProductController extends Controller {
 public function index(){return view('front.products.index',['products'=>SoftwareProduct::where('published',true)->orderBy('sort_order')->get()]);}
 public function show(string $slug){$product=SoftwareProduct::where('slug',$slug)->where('published',true)->firstOrFail();return view('front.products.show',compact('product'));}
}

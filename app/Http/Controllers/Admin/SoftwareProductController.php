<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\SoftwareProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
class SoftwareProductController extends Controller {
 private function guard():void {abort_unless(auth()->user()?->isSuperAdmin(),403);}
 public function index(){ $this->guard();return view('admin.dash.software-products.index',['products'=>SoftwareProduct::orderBy('sort_order')->get()]); }
 public function edit(SoftwareProduct $softwareProduct){$this->guard();return view('admin.dash.software-products.edit',['product'=>$softwareProduct]);}
 public function update(Request $request,SoftwareProduct $softwareProduct){
 $this->guard();
 $data=$request->validate(['title'=>'required|string|max:180','brand'=>'required|string|max:120','eyebrow'=>'nullable|string|max:180','subtitle'=>'nullable|string|max:220','description'=>'nullable|string|max:10000','features_text'=>'nullable|string|max:10000','website_url'=>'nullable|url|max:255','button_text'=>'required|string|max:100','accent'=>['required','regex:/^#[0-9a-fA-F]{6}$/'],'seo_title'=>'nullable|string|max:65','seo_description'=>'nullable|string|max:160','sort_order'=>'nullable|integer|min:0|max:9999','cover_image'=>'nullable|image|mimes:jpg,jpeg,png,webp|max:4096','gallery_images'=>'nullable|array|max:15','gallery_images.*'=>'image|mimes:jpg,jpeg,png,webp|max:4096','delete_cover'=>'nullable|boolean','remove_gallery'=>'nullable|array','remove_gallery.*'=>'string']);
 $gallery=$softwareProduct->gallery??[];
 foreach($data['remove_gallery']??[] as $path){if(in_array($path,$gallery,true)){$gallery=array_values(array_diff($gallery,[$path]));Storage::disk('public')->delete($path);}}
 if($request->boolean('delete_cover') && $softwareProduct->cover_image){Storage::disk('public')->delete($softwareProduct->cover_image);$softwareProduct->cover_image=null;}
 if($request->hasFile('cover_image')){if($softwareProduct->cover_image)Storage::disk('public')->delete($softwareProduct->cover_image);$softwareProduct->cover_image=$request->file('cover_image')->store('software-products/'.$softwareProduct->slug,'public');}
 foreach($request->file('gallery_images',[]) as $file)$gallery[]=$file->store('software-products/'.$softwareProduct->slug,'public');
 $fields=['title','brand','eyebrow','subtitle','description','website_url','button_text','accent','seo_title','seo_description','sort_order'];foreach($fields as $field)if(array_key_exists($field,$data))$softwareProduct->$field=$data[$field];
 $softwareProduct->features=array_values(array_filter(array_map('trim',preg_split('/\r\n|\n|\r/',(string)($data['features_text']??'')))));
 $softwareProduct->gallery=$gallery;$softwareProduct->published=$request->boolean('published');$softwareProduct->save();
 return redirect()->route('panel.software-products.edit',$softwareProduct)->with('success','اطلاعات محصول ذخیره شد.');
 }
}

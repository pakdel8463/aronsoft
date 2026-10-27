<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class SoftwareProduct extends Model {
 protected $fillable=['slug','title','brand','eyebrow','subtitle','description','features','website_url','button_text','accent','cover_image','gallery','seo_title','seo_description','published','sort_order'];
 protected $casts=['features'=>'array','gallery'=>'array','published'=>'boolean'];
}

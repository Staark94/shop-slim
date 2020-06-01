<?php

namespace Cart\Models;

use Illuminate\Database\Eloquent\Model;
use Cart\Models\Product;
use Cart\Models\SubCat;
use Slim\Views\Twig;
use Illuminate\Pagination;
use Illuminate\Pagination\Paginator;
use Illuminate\Database\Capsule\Manager as DB;

class Category extends Model
{
    protected $table = 'category';

    public function getProducts($parent, $limit) {
        return Product::where('category', $parent)->orderBy('id', 'desc')->limit($limit)->get();
    }

    public function getProductsFrom($parent, $limit) {
        print_r('');
        return DB::table('products')->where('sub_category', $parent)->orderBy('id', 'desc')->limit($limit)->get();
    }

    public function subCat($id)
    {
        return DB::table('sub_category')->where('parent', $id)->get();
    }

    public function getProductsAll($parent, $items, $next) {
        if($next == 1) {
            return Product::where('category', $parent)->orderBy('id', 'asc')->skip(0)->take($items)->get();
        } else {
            return Product::where('category', $parent)->orderBy('id', 'asc')->skip($items)->take($items)->get();
        }
    }

    public function getProductsAllFrom($parent, $items, $next) {
        if($next == 1) {
            return Product::where('sub_category', $parent)->orderBy('id', 'asc')->skip(0)->take($items)->get();
        } else {
            return Product::where('sub_category', $parent)->orderBy('id', 'asc')->skip($items)->take($items)->get();
        }
    }

    public function slugCategory()
    {
        return $this->name;
    }

    public function setSlug($title = "")
    {
        if(empty($title)) return;

        $slug = str_replace(array("#", " ", "%20%", "+"), "-", $title);
        return strtolower($slug);
    }
}
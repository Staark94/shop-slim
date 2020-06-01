<?php

namespace Cart\Models;

use Illuminate\Database\Eloquent\Model;
use Cart\Models\Category;

class Product extends Model
{
    protected $table = 'products';

    public function hasLowStock()
    {
        if($this->outOfStock())
        {
            return false;
        }

        return (bool) ($this->stock <= 1);
    }

    public function outOfStock()
    {
        return $this->stock === 0;
    }

    public function inStock()
    {
        return $this->stock >= 1;
    }

    public function hasStock($quantity)
    {
        return $this->stock >= $quantity;
    }

    public function getImage()
    {
        if(!empty($this->image)) {
            return $this->image;
        } else {
            return 'https://shop-piesetv.ro/resources/views/images/product.png';
        }
    }

    public function getSlug()
    {
        $slug = strtolower($this->title);
        $url = str_replace("#", "", $slug);
        $url = str_replace("+", "", $slug);
        $urlFinal = str_replace(array(" "), "-", $url);
        return $urlFinal;
    }

    public function setSlug($title)
    {
        $slug = strtolower($title);
        $url = str_replace("#", "", $slug);
        $url = str_replace("+", "", $slug);
        $urlFinal = str_replace(array(" "), "-", $url);
        return $urlFinal;
    }

    public function productCategory()
    {
        $cat = Category::where('id', $this->category)->first();
        return "<a href='/category/$cat->slug'>$cat->name</a>";
    }

    public function catName()
    {
        $cat = Category::where('id', $this->category)->first();
        return $cat->name;
    }

    public function catLink()
    {
        $cat = Category::where('id', $this->category)->first();
        return $cat->slug;
    }

    public function showGallery() {
        if(!empty($this->galery)) {
            $data = explode(",", $this->galery);
            $directory = basename($_SERVER['DOCUMENT_ROOT'] . "/uploads");
            $dir = "https://shop-piesetv.ro/" . $directory . DIRECTORY_SEPARATOR;
            $datas = [];
            foreach ($data as $files) {
                $datas[] = '<img data-toggle="modal" data-target="#galleryModal" data-whatever="'.$dir.''. $files .'" src="'.$dir.''. $files .'" />';
            }

            return implode(" ", $datas);
        }
    }

    public function getGallery()
    {
        $data = explode(",", $this->galery);
        $directory = basename($_SERVER['DOCUMENT_ROOT'] . "/uploads");
        $dir = "https://shop-piesetv.ro/" . $directory . DIRECTORY_SEPARATOR;
        $datas = [];

        foreach ($data as $files) {
            $datas[] = $dir . $files;
        }

        return $datas;
    }

    public function date() {
        return $this->update_at;
    }
}
<?php

namespace Cart\Models;

use Illuminate\Database\Eloquent\Model;
use Cart\Models\Category;

class SubCat extends Model
{
    protected $table = 'products';

    public function catName($id)
    {
        $cat = Category::where('id', $id)->first();
        return $cat->name;
    }
}
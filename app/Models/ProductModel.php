<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductModel extends Model
{
    protected $table = "shopbe_product";

    protected $guarded = ['id'];
}

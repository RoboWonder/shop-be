<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class OrderModel extends Model
{
    protected $table = "shopbe_order";

    protected $fillable = ['id', 'product_id', 'user_id', 'amount', 'ordered_date', 'description'];

    protected static function booted()
    {
        parent::booted();

        static::creating(function ($model) {
            if(!$model->user_id){
                $model->user_id = Auth::id();
            }
            if(!$model->ordered_date){
                $model->ordered_date = Carbon::now();
            }
        });
    }
}

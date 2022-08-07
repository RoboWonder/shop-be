<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class TransactionModel extends Model
{
    protected $table = "shopbe_transaction";

    protected $guarded = ['id'];

    public const UPDATED_AT = NULL;

    protected static function booted()
    {
        parent::booted();

        static::creating(function ($model) {
            if(!$model->user_id){
                $model->user_id = Auth::id();
            }
        });
    }
}

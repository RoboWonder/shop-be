<?php

namespace App\Models;

use App\Constants\DateFormat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class TransactionModel extends Model
{
    protected $table = "shopbe_transaction";

    protected $guarded = ['id'];

    public const UPDATED_AT = NULL;

    /***
     * only get array data to work with elasticsearch.
     * can modify or model relationship to get related data.
     *
     * @return array
     * @since: 2022/08/16 22:41
     */
    public function toEsData(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'user_id' => $this->user_id,
            'order_id' => $this->order_id,
            'amount' => doubleval($this->amount),
            'description' => $this->description,
            'created_at' => $this->created_at->format(DateFormat::GENERAL_DATETIME)
        ];
    }

    protected static function booted()
    {
        parent::booted();

        static::creating(function ($model) {
            if (!$model->user_id) {
                $model->user_id = Auth::id();
            }
        });
    }
}

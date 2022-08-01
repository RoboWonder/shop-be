<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPasswordResetModel extends Model
{
    protected $table = "shopbe_user";

    protected $fillable = [
        'email',
        'token'
    ];
}

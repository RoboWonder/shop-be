<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPasswordResetModel extends Model
{
    protected $table = "shopbe_password_reset";

    protected $primaryKey = 'email';

    public $incrementing = FALSE;

    public const UPDATED_AT = NULL;

    protected $fillable = ['email', 'token'];
}

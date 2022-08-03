<?php
/**
 * Created by PhpStorm.
 * User: Hung <hunglt@hanbiro.vn>
 * Date: 2022-08-01
 * Time: 22:11
 */

namespace App\Repositories;

class UserRepository extends BaseRepository
{
    public function model()
    {
        return "App\\Models\\UserModel";
    }
}

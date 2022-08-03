<?php
/**
 * Created by PhpStorm.
 * Date: 2022-08-01
 * Time: 22:11
 */

namespace App\Repositories;

class ProductRepository extends BaseRepository
{
    public function model()
    {
        return "App\\Models\\ProductModel";
    }
}

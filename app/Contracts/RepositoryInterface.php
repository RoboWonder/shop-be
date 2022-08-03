<?php
/**
 * Created by PhpStorm.
 * Date: 2022-08-01
 * Time: 21:55
 */

namespace App\Contracts;

interface RepositoryInterface
{
    public static function __callStatic($method, $arguments);

    public function __call($method, $arguments);
}

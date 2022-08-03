<?php
/**
 * Created by PhpStorm.
 * Date: 2022-08-03
 * Time: 22:17
 */

namespace App\Services;

use App\Constants\Message;
use App\Repositories\ProductRepository;

class ProductService
{
    private $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function doCreate(array $data)
    {
        try {
            $ok = $this->productRepository->create($data);
            if(!$ok){
                throw new \Exception(Message::ERR_SHOPBE_CREATE_FAIL);
            }
        }
        catch (\Exception $e){
            return $e;
        }

        return NULL;
    }

    public function getList(array $data): array
    {
        return [];
    }

    public function getView(string $id): array
    {
        return [];
    }

    public function doUpdate(string $data): bool
    {
        return TRUE;
    }
}

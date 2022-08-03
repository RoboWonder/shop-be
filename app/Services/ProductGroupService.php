<?php
/**
 * Created by PhpStorm.
 * Date: 2022-08-03
 * Time: 22:17
 */

namespace App\Services;

use App\Constants\Message;
use App\Repositories\ProductGroupRepository;

class ProductGroupService
{
    private $productGroupRepository;

    public function __construct(ProductGroupRepository $productGroupRepository)
    {
        $this->productGroupRepository = $productGroupRepository;
    }

    public function doCreate(array $data)
    {
        try {
            $ok = $this->productGroupRepository->create($data);
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

    public function doUpdate(array $data): bool
    {
        return TRUE;
    }
}

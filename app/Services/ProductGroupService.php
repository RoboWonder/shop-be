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

    public function getList(array $args): array
    {
        $rows = $paging = [];

        try {
            if (!!$args['page']){
                $page = (int)$args['page'];
                $size = (int)$args['size'];

                $paging = [
                    'page' => $page,
                    'size' => $size,
                    'total' => 0,
                    'last_page' => $page
                ];

                $data = $this->productGroupRepository->getModel()->where(['deleted' => '0'])->paginate($size, ['*'], 'page', $page);
                if(!!$data){
                    $paging = array_merge($paging, [
                        'total' => $data->total(),
                        'last_page' => $data->lastPage(),
                    ]);

                    $rows = $data->items();
                }
            } else {
                $rows = $this->productGroupRepository->all();
            }
        }
        catch (\Exception $e){
            return [NULL, $paging, $e];
        }

        return [$rows, $paging, NULL];
    }

    public function doUpdate(int $id, array $data)
    {
        try {
            $ok = $this->productGroupRepository->update($data, $id);
            if(!$ok){
                throw new \Exception(Message::ERR_SHOPBE_UPDATE_FAIL);
            }
        }
        catch (\Exception $e){
            return $e;
        }

        return NULL;
    }

    public function doDelete(string $id, bool $softDelete = TRUE)
    {
        try {
            if($softDelete){
                $ok = $this->productGroupRepository->update(['deleted' => '1'], $id);
            }
            else{
                $ok = $this->productGroupRepository->deleteWhere(['id' => $id]);
            }
            if(!$ok){
                throw new \Exception(Message::ERR_SHOPBE_DELETE_FAIL);
            }
        }
        catch (\Exception $e){
            return $e;
        }

        return NULL;
    }
}

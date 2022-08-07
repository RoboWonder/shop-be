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

    public function getList(array $args): array
    {
        $page = (int)$args['page'];
        $size = (int)$args['size'];

        $paging = [
            'page' => $page,
            'size' => $size,
            'total' => 0,
            'last_page' => $page
        ];
        $rows = [];

        try {
            $model = $this->productRepository->getModel()->where(['deleted' => '0', 'status' => '1']);

            if (isset($args['filters']) && is_array($args['filters'])){
                $model = $model->where($args['filters']);
            }

            $data = $model->paginate($size, ['*'], 'page', $page);
            if(!!$data){
                $paging = array_merge($paging, [
                    'total' => $data->total(),
                    'last_page' => $data->lastPage(),
                ]);

                $rows = $data->items();
            }
        }
        catch (\Exception $e){
            return [NULL, $paging, $e];
        }

        return [$rows, $paging, NULL];
    }

    public function getView(string $id): array
    {
        try {
            $data = $this->productRepository->findWhere(['id' => $id, 'deleted' => '0', 'status' => '1']);
            if($data->isEmpty()){
                throw new \Exception(Message::ERR_SHOPBE_NO_DATA_FOUND);
            }
        }
        catch (\Exception $e){
            return [NULL, $e];
        }

        return [$data, NULL];
    }

    public function doUpdate(int $id, array $data)
    {
        try {
            $ok = $this->productRepository->update($data, $id);
            if(!$ok){
                throw new \Exception(Message::ERR_SHOPBE_UPDATE_FAIL);
            }
        }
        catch (\Exception $e){
            return $e;
        }

        return NULL;
    }

    public function doDelete(string $id)
    {
        try {
            $ok = $this->productRepository->update(['deleted' => '1', 'status' => '0'], $id);
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

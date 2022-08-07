<?php
/**
 * Created by PhpStorm.
 * Date: 2022-08-03
 * Time: 22:17
 */

namespace App\Services;

use App\Constants\Api;
use App\Constants\Message;
use App\Repositories\OrderRepository;
use Illuminate\Support\Facades\DB;

class OrderService
{
    private $orderRepository;
    private $transactionService;

    public function __construct(OrderRepository $orderRepository, TransactionService $transactionService)
    {
        $this->orderRepository = $orderRepository;
        $this->transactionService = $transactionService;
    }

    public function doCreate(array $data)
    {
        try {
            DB::beginTransaction();

            $order = $this->orderRepository->create($data);
            if(!$order || !$order->id){
                throw new \Exception(Message::ERR_SHOPBE_CREATE_FAIL);
            }

            $ok = $this->transactionService->doCreate([
                'type' => Api::TRANSACTION_TYPE_BUY,
                'order_id' => $order->id,
                'amount' => doubleval($data['amount'] ?? 0),
            ]);

            if($ok instanceof \Exception){
                throw new \Exception(Message::ERR_SHOPBE_CREATE_FAIL);
            }

            DB::commit();
        }
        catch (\Exception $e){
            DB::rollBack();
            return $e;
        }

        return NULL;
    }

    public function getList(array $args): array
    {
        $rows = $paging = [];

        try {
            $model = $this->orderRepository->getModel()->where(['deleted' => '0']);

            if (isset($args['filters']) && is_array($args['filters'])){
                $model = $model->where($args['filters']);
            }

            if (!!$args['page']){
                $page = (int)$args['page'];
                $size = (int)$args['size'];

                $paging = [
                    'page' => $page,
                    'size' => $size,
                    'total' => 0,
                    'last_page' => $page
                ];

                $data = $model->paginate($size, ['*'], 'page', $page);
                if(!!$data){
                    $paging = array_merge($paging, [
                        'total' => $data->total(),
                        'last_page' => $data->lastPage(),
                    ]);

                    $rows = $data->items();
                }
            } else {
                $rows = $model->all();
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
            DB::beginTransaction();

            $ok = $this->orderRepository->update($data, $id);
            if(!$ok){
                throw new \Exception(Message::ERR_SHOPBE_UPDATE_FAIL);
            }

            $willTransUpdate = [];
            if(isset($data['amount'])){
                $willTransUpdate['amount'] = doubleval($data['amount']);
            }
            if(isset($data['product_id'])){
                $willTransUpdate['product_id'] = doubleval($data['product_id']);
            }

            if(count($willTransUpdate) > 0){
                list($transactionData, $err) = $this->transactionService->getViewBy(['order_id' => $id]);
                if($err === NULL && $transactionData->exists){
                    $ok = $this->transactionService->doUpdate($transactionData->id, $willTransUpdate);
                }
                else{
                    $ok = $this->transactionService->doCreate(array_merge($willTransUpdate, [
                        'type' => Api::TRANSACTION_TYPE_BUY,
                        'order_id' => $id
                    ]));
                }

                if($ok instanceof \Exception){
                    throw new \Exception(Message::ERR_SHOPBE_UPDATE_FAIL);
                }
            }

            DB::commit();
        }
        catch (\Exception $e){
            DB::rollBack();
            return $e;
        }

        return NULL;
    }

    public function doDelete(string $id, bool $softDelete = TRUE)
    {
        try {
            DB::beginTransaction();

            if($softDelete){
                $ok = $this->orderRepository->update(['deleted' => '1'], $id);
            }
            else{
                $ok = $this->orderRepository->deleteWhere(['id' => $id]);
            }
            if(!$ok){
                throw new \Exception(Message::ERR_SHOPBE_DELETE_FAIL);
            }

            list($transactionData, $err) = $this->transactionService->getViewBy(['order_id' => $id]);
            if($err === NULL){
                $ok = $this->transactionService->doDelete($transactionData->id);
                if($ok instanceof \Exception){
                    throw new \Exception(Message::ERR_SHOPBE_DELETE_FAIL);
                }
            }

            DB::commit();
        }
        catch (\Exception $e){
            DB::rollBack();
            return $e;
        }

        return NULL;
    }
}

<?php
/**
 * Created by PhpStorm.
 * Date: 2022-08-03
 * Time: 22:17
 */

namespace App\Services;

use App\Constants\Message;
use App\Repositories\EsTransactionRepository;
use App\Repositories\TransactionRepository;

class TransactionService
{
    private $transactionRepository;
    private $esTransactionRepository;

    public function __construct(TransactionRepository $transactionRepository, EsTransactionRepository $esTransactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
        $this->esTransactionRepository = $esTransactionRepository;
    }

    public function doCreate(array $data)
    {
        try {
            $dataModel = $this->transactionRepository->firstOrNew($data);

            if($dataModel->exists){
                throw new \Exception(Message::ERR_SHOPBE_ALREADY_EXISTS);
            }

            if($dataModel->save()){
                throw new \Exception(Message::ERR_SHOPBE_CREATE_FAIL);
            }

            // create one item in elasticsearch.
            $this->esTransactionRepository->syncOne($dataModel->toEsData());
        }
        catch (\Exception $e){
            return $e;
        }

        return NULL;
    }

    private function _getListFromSql(array $args): array
    {
        $rows = $paging = [];

        try {
            $model = $this->transactionRepository->getModel()->where(['deleted' => '0']);

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

    private function _getListFromNoSql(array $args): array
    {
        $rows = $paging = [];

        try {
            if (!!$args['page']){
                $page = (int)$args['page'];
                $size = (int)$args['size'];

                $paging = [
                    'page' => $page,
                    'size' => $size,
                ];
            }

            list($total, $docs) = $this->esTransactionRepository->search(
                $paging,
                isset($args['filters']) && is_array($args['filters']) ? $args['filters'] : []
            );

            $paging['total'] = $total;
            if($total > 0){
                $paging['last_page'] = 0; //@todo
                $rows = $docs;
            }
        }
        catch (\Exception $e){
            return [NULL, $paging, $e];
        }

        return [$rows, $paging, NULL];
    }

    public function getList(array $args, bool $isNoSql = FALSE): array
    {
        if($isNoSql){
            return $this->_getListFromNoSql($args);
        }
        return $this->_getListFromSql($args);
    }

    /***
     * only get one by conditions
     *
     * @param array $conditions
     *
     * @return array
     * @since: 2022/08/07 22:37
     */
    public function getViewBy(array $conditions): array
    {
        try {
            $data = $this->transactionRepository->findWhere(array_merge($conditions, ['deleted' => '0']));
            if($data->isEmpty()){
                throw new \Exception(Message::ERR_SHOPBE_NO_DATA_FOUND);
            }
            $data = $data->first();
        }
        catch (\Exception $e){
            return [NULL, $e];
        }

        return [$data, NULL];
    }

    /***
     * now was using for product order updated.
     *
     * @param int   $id
     * @param array $data
     *
     * @return \Exception|null
     * @since: 2022/08/07 22:16
     */
    public function doUpdate(int $id, array $data)
    {
        try {
            $ok = $this->transactionRepository->update($data, $id);
            if(!$ok){
                throw new \Exception(Message::ERR_SHOPBE_UPDATE_FAIL);
            }
        }
        catch (\Exception $e){
            return $e;
        }

        return NULL;
    }

    /***
     * Now was not using.
     *
     * @param string $id
     * @param bool   $softDelete
     *
     * @return \Exception|null
     * @since: 2022/08/07 22:37
     */
    public function doDelete(string $id, bool $softDelete = TRUE)
    {
        try {
            if($softDelete){
                $ok = $this->transactionRepository->update(['deleted' => '1'], $id);
            }
            else{
                $ok = $this->transactionRepository->deleteWhere(['id' => $id]);
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

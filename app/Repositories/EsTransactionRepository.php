<?php
/**
 * Created by PhpStorm.
 * Date: 2022-08-16
 * Time: 22:25
 */

namespace App\Repositories;

class EsTransactionRepository extends EsBaseRepository
{
    public function getMapping(): array
    {
        return [
            'properties' => [
                'id' => [
                    'type' => 'integer'
                ],
                'user_id' => [
                    'type' => 'integer'
                ],
                'order_id' => [
                    'type' => 'integer'
                ],
                'amount' => [
                    'type' => 'double'
                ],
                'description' => [
                    'type' => 'text'
                ],
                'created_at' => [
                    'type' => 'date',
                    'format' => 'yyyy-MM-dd HH:mm:ss'
                ]
            ]
        ];
    }

    public function getIndex(): string
    {
        return 'shopbe_transactions';
    }

    public function getDefaultSetting(): array
    {
        return [
            'index' =>[
                'number_of_shards' => 3,
                'number_of_replicas' => 1,
                'refresh_interval' => '1s'
            ],
        ];
    }
}

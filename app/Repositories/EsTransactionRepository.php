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

    public function getSearchBody(array $paging, array $options): array
    {
        $body = [];

        if (isset($paging['limit'])){
            $body['size'] = $paging['limit'];
            if(isset($paging['page'])){
                $body['from'] = (($paging['page'] - 1) * $paging['limit']);
            }
        }

        if(isset($options['id']) && $options['id']){
            $query = is_array($options['id']) ? 'terms' : 'term';
            $body['query']['bool']['must'][] = [
                $query => [
                    'id' => $options['id']
                ]
            ];
        }

        if(isset($options['user_id']) && $options['user_id']){
            $query = is_array($options['user_id']) ? 'terms' : 'term';
            $body['query']['bool']['must'][] = [
                $query => [
                    'user_id' => $options['user_id']
                ]
            ];
        }

        if(isset($options['order_id']) && $options['order_id']){
            $query = is_array($options['order_id']) ? 'terms' : 'term';
            $body['query']['bool']['must'][] = [
                $query => [
                    'order_id' => $options['order_id']
                ]
            ];
        }

        if(isset($options['amount']) && $options['amount']){
            $ranges = [];
            if(isset($options['amount']['start'])){
                $ranges['gte'] = $options['amount']['start'];
            }
            if(isset($options['amount']['end'])){
                $ranges['lte'] = $options['amount']['end'];
            }
            if (count($ranges)){
                $body['query']['bool']['must'][] = [
                    'range' => [
                        'amount' => $ranges
                    ]
                ];
            }
        }

        if(isset($options['description']) && $options['description']){
            $body['query']['bool']['must'][] = [
                'match' => [
                    'description' => [
                        'query' => $options['description'],
                        'operator' => 'and'
                    ]
                ]
            ];
        }

        if(isset($options['created_at']) && $options['created_at']){
            $ranges = [];
            if(isset($options['created_at']['start'])){
                $ranges['gte'] = $options['created_at']['start'];
            }
            if(isset($options['created_at']['end'])){
                $ranges['lte'] = $options['created_at']['end'];
            }
            if (count($ranges)){
                $body['query']['bool']['must'][] = [
                    'range' => [
                        'created_at' => $ranges
                    ]
                ];
            }
        }

        return $body;
    }
}

<?php
/**
 * Created by PhpStorm.
 * Date: 2022-08-16
 * Time: 21:00
 */

namespace App\Repositories;

use App\Contracts\EsRepositoryInterface;
use Elastic\Elasticsearch\ClientBuilder;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

abstract class EsBaseRepository implements EsRepositoryInterface
{
    protected const ACTION_CREATE = 'create';
    protected const ACTION_UPDATE = 'update';
    protected const ACTION_DELETE = 'delete';

    protected $client;
    protected $index;

    public function __construct()
    {
        $host = env('ES_HOST', 'localhost');
        $port = env('ES_PORT', '9200');
        $this->client = $this->connect([$host . ':' . $port]);
        $this->index = $this->getIndex();
    }

    protected function connect(array $hosts)
    {
        try {
            $client = ClientBuilder::create()->setHosts($hosts)->build();

            if (empty($client)) {
                throw new Exception("Couldn't connect to elasticsearch");
            }
        }
        catch (Exception $e) {
            Log::error($e->getMessage());
        }

        return $client;
    }

    abstract public function getMapping(): array;

    abstract public function getIndex(): string;

    abstract public function getDefaultSetting(): array;

    public function createIndex()
    {
        try {
            if ($this->client->indices()->exists(['index' => $this->index])) {
                return $this->index;
            }

            $params = [
                'index' => $this->index,
                'body' => [
                    'mappings' => $this->getMapping(),
                    'settings' => $this->getDefaultSetting()
                ]
            ];

            if(!$index = $this->client->indices()->create($params)){
                throw new Exception("Es - Cannot create index. [" . $index . "]");
            }

            return $index;
        }
        catch (Exception $e) {
            Log::error($e->getMessage());
            return NULL;
        }
    }

    /***
     * find document id.
     *
     * @param $id
     *
     * @return false|mixed
     * @throws \Elastic\Elasticsearch\Exception\ClientResponseException
     * @throws \Elastic\Elasticsearch\Exception\ServerResponseException
     * @since: 2022/08/16 22:10
     */
    public function searchById($id)
    {
        $body = [
            'query' => [
                'term' => [
                    'id' => is_array($id) ? $id : [$id]
                ]
            ]
        ];
        if ($result = $this->client->search([
            'body' => $body,
            'index' => $this->index
        ])) {
            if (!empty($result['hits']) && !empty($result['hits']['hits'])) {
                return is_array($id) ? $result['hits']['hits'] : $result['hits']['hits'][0];
            }
        }

        return FALSE;
    }

    /***
     * just simplify inserting.
     *
     * @param string $docId
     * @param array  $data
     *
     * @return false|mixed
     * @throws \Elastic\Elasticsearch\Exception\ClientResponseException
     * @throws \Elastic\Elasticsearch\Exception\MissingParameterException
     * @throws \Elastic\Elasticsearch\Exception\ServerResponseException
     * @since: 2022/08/16 22:43
     */
    public function insert(string $docId, array $data)
    {
        $params = is_null($docId) ? ['body' => $data] : ['id' => $docId, 'body' => $data];

        $result = $this->client->index(array_merge($params, [
            'index' => $this->index
        ]));
        if ($result) {
            return $result['_id'];
        }

        return FALSE;
    }

    /***
     * just simplify updating.
     *
     * @param string $docId
     * @param array  $data
     *
     * @return false|mixed
     * @throws \Elastic\Elasticsearch\Exception\ClientResponseException
     * @throws \Elastic\Elasticsearch\Exception\MissingParameterException
     * @throws \Elastic\Elasticsearch\Exception\ServerResponseException
     * @since: 2022/08/16 22:44
     */
    public function update(string $docId, array $data)
    {
        if (!empty($index)) {
            if ($result = $this->client->update(['id' => $docId, 'body' => ['doc' => $data], 'index' => $this->index])) {
                return $result['_id'];
            }
        }

        return FALSE;
    }

    /***
     * just simplify delete
     *
     * @param string $docId
     *
     * @return \Elastic\Elasticsearch\Response\Elasticsearch|\Http\Promise\Promise
     * @throws \Elastic\Elasticsearch\Exception\ClientResponseException
     * @throws \Elastic\Elasticsearch\Exception\MissingParameterException
     * @throws \Elastic\Elasticsearch\Exception\ServerResponseException
     * @since: 2022/08/16 22:44
     */
    public function delete(string $docId)
    {
        return $this->client->delete(['id' => $docId, 'index' => $this->index]);
    }

    /***
     * we have two types of id (id, and document id)
     * wrapped insert/update/delete by find document id.
     *
     * @param array  $data
     * @param null   $id
     * @param string $action
     *
     * @return \Elastic\Elasticsearch\Response\Elasticsearch|false|\Http\Promise\Promise|mixed|void
     * @since: 2022/08/16 22:29
     */
    public function syncOne(array $data, $id = NULL, string $action = self::ACTION_CREATE)
    {
        try {
            if (!in_array($action, [self::ACTION_CREATE, self::ACTION_UPDATE, self::ACTION_DELETE], TRUE)) {
                throw new Exception('ES - action required');
            }

            if ($action === self::ACTION_CREATE){
                $docID = Str::orderedUuid();
            }
            else{
                if (!$id){
                    throw new Exception('ES - Id required');
                }

                $searchDoc = $this->searchById($id);
                if (empty($searchDoc)) {
                    throw new Exception('ES - No Doc found');
                }
                $docID = $searchDoc['_id'];
            }

            if ($action === self::ACTION_CREATE) {
                $result = $this->insert($docID, $data);
            }
            elseif ($action === self::ACTION_UPDATE) {
                $result = $this->update($docID, $data);
            }
            else {
                $result = $this->delete($docID);
            }

            return $result;
        }
        catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }

    /***
     * now was not used
     * will implement later.
     *
     * @param array  $data
     * @param string $action
     *
     * @since: 2022/08/16 22:42
     */
    public function syncBulk(array $data, string $action = self::ACTION_CREATE){

    }
}

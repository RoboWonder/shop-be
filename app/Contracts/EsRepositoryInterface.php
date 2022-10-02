<?php
/**
 * Created by PhpStorm.
 * Date: 2022-08-16
 * Time: 20:55
 */

namespace App\Contracts;

interface EsRepositoryInterface
{
    public function getMapping(): array;

    public function getDefaultSetting(): array;

    public function getIndex(): string;

    public function createIndex();

    public function insert(string $docId, array $data);

    public function search(array $paging, array $options): array;

    public function searchById($id);

    public function update(string $docId, array $data);

    public function delete(string $docId);

    public function syncOne(array $data, $id, string $action);

    public function syncBulk(array $data, string $action);
}

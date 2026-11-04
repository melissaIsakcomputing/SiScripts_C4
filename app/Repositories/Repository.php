<?php

namespace App\Repositories;

use App\Models\RestFM;

class Repository
{
    public array $tokenSettings;

    public array $scripts;

    public $mapper;

    public string $layout;

    public string $dateFilterField = '_zhk_CreatedDate';

    public array $sort = [];

    public function __construct($layout, $mapper, $scripts = [])
    {
        $this->layout = $layout;
        $this->mapper = $mapper;
        $this->scripts = $scripts;
    }

    public function initFM($tokenSettings = [])
    {
        if (count($tokenSettings) == 0) {
            $tokenSettings = [
                'host' => env('Data_Server'),
                'database' => env('Data_File'),
                'username' => env('Data_Server_Username'),
                'password' => env('Data_Server_Pass')
            ];
        }
        $this->tokenSettings = $tokenSettings;
        $this->fm = new RestFM($tokenSettings['host'], $tokenSettings['database'], $tokenSettings['username'], $tokenSettings['password'], $this->layout);
    }

    function all($query): array
    {
        $filters = $this->mapper->filters($this->tokenSettings, (array)$query->getJSON());
        return $this->match($filters, $query);
    }

    function allRelated($fk, $id, $query): array
    {
        if (!$this->mapper->isRelatedTokenOwnerMatch($this->tokenSettings, $id, $fk)) {
            return ['error' => 401];
        }
        $data = array_merge((array)$query->getJSON(), [$fk => $id]);
        $filters = $this->mapper->filters($this->tokenSettings, $data);
        return $this->match($filters, $query);
    }

    function find($id): array
    {
        if (!$this->mapper->isTokenOwnerMatch($this->tokenSettings, $id)) {
            return ['error' => 401];
        }
        $findByResponse = $this->findBy(['id' => $id]);
        if ($findByResponse["error"] != 0) {
            return $findByResponse;
        }
        $found = $findByResponse['data'][0];
        return ["error" => 0, "data" => $found];
    }

    function create($data): array
    {
        $fmFieldData = $this->mapper->toFM($data);

        $belongsTo = $this->mapper->getAuthBelongsTo($this->tokenSettings);
        if (count($belongsTo) > 0) {
            $fmFieldData = array_merge((array)$fmFieldData, (array)$belongsTo[0]);
        }
        if (!array_key_exists('create', $this->scripts)) {
            $result = $this->_create($fmFieldData);
            $recordId = $result['response']['recordId'];
        } else {
            $result = $this->executeScript($this->scripts['create'], json_encode($fmFieldData));
            if ($result['error'] != 0) {
                return $result;
            }
            if (isset($result['data']->type) and $result['data']->type == 'enqueued') {
                return ['data' => [0 => $result['data']]];
            }
            $recordId = $result['data']->_zhk_RecordID;
        }
        return $this->findBy(["_zhk_RecordID" => $recordId]);
    }

    function update($id, $data): array
    {
        $fmFieldData = $this->mapper->toFM($data);
        if (array_key_exists('update', $this->scripts)) {
            $fmFieldData['id'] = $id;
            print_r( json_encode($fmFieldData));
            $result = $this->executeScript($this->scripts['update'], json_encode($fmFieldData));
            if ($result['error'] != 0) {
                return $result;
            }
            $recordId = $result['data']->_zhk_RecordID;
        } else {
            $result = $this->_update($id, $fmFieldData);
            $recordId = $result['response']['recordId'];
        }
        return $this->findBy(["_zhk_RecordID" => $recordId]);
    }

    function delete($id, $data)
    {
        if (array_key_exists('delete', $this->scripts)) {
            $data['id'] = $id;
            $result = $this->executeScript($this->scripts['delete'], json_encode($data));
        } else {

            $result = $this->_update($id, ['status' => 0]);
        }
        return $result;
    }

    function _delete($id)
    {
        return $this->fm->deleteRecord($id, $this->layout);
    }

    function _create($fmFieldData)
    {
        $data = array();
        $data['fieldData'] = $fmFieldData;
        $result = $this->fm->createRecord($data, $this->layout);

        if ($this->error($result) != 0) {
            $data['messages'] = $result['messages'];
            $data['layout'] = $this->layout;
            return ['error' => count($result['messages']), 'data' => $data];
        }
        return $result;
    }

    function _update($id, $fmFieldData)
    {
        $data = array();
        $data['fieldData'] = $fmFieldData;
        $findByResponse = $this->findBy(['id' => $id]);
        if ($findByResponse['error'] != 0) {
            return $findByResponse;
        }
        $recordId = $findByResponse['source'][0]['_zhk_RecordID'];
        $result = $this->fm->editRecord($recordId, $data, $this->layout);
        if ($this->error($result) != 0) {
            $data['messages'] = $result['messages'];
            $data['layout'] = $this->layout;
            return ['error' => count($result['messages']), 'data' => $data];
        }
        $result['response']['recordId'] = $recordId;
        return $result;
    }

    function _updateByRecordId($recordId, $fmFieldData)
    {
        $fmFieldData = $this->mapper->toFM($fmFieldData, true);
        $data['fieldData'] = $fmFieldData;
        $result = $this->fm->editRecord($recordId, $data, $this->layout);
        if ($this->error($result) != 0) {
            $data['messages'] = $result['messages'];
            $data['layout'] = $this->layout;
            return ['error' => count($result['messages']), 'data' => $data];
        }
        $result['response']['recordId'] = $recordId;
        return $result;
    }

    function exist($filters): bool
    {
        $response = $this->findBy($filters);
        return $response["error"] == 0;
    }

    function findBy($query): array
    {
        $filters = $this->mapper->filters($this->tokenSettings, $query);
        $findByResponse = $this->match($filters);
        if ($findByResponse["error"] != 0) {
            return $findByResponse;
        }
        $found = $findByResponse['data'];
        foreach ($found as $item) {
            if (!$this->mapper->isTokenOwnerMatch($this->tokenSettings, $item)) {
                return ['error' => 401];
            }
        }
        return $findByResponse;
    }

    function match($filters, $queryParams = null): array
    {
        $criteria = $this->buildCriteria($filters, $queryParams);
        if (!array_key_exists('list', $this->scripts)) {
            $result = $this->fm->findRecords($criteria, $this->layout);
        } else {
            $result = $this->executeScript($this->scripts['list'], json_encode($criteria));
            $result = json_decode(json_encode($result['data']), true);
        }
        return $this->map($result);
    }

    function buildCriteria($filters, $queryParams = null): array
    {
        $page = (int)($queryParams ? ($queryParams->getGet('page') ?? 1) : 1);
        $limit = (int)($queryParams ? ($queryParams->getGet('limit') ?? 10) : 10);
        $offset = ($page - 1) * $limit + 1;
        $criteria = array();
        $criteria['limit'] = $limit;
        $criteria['offset'] = $offset;
        $criteria['query'] = $this->buildQueryCriteria($filters, $queryParams);
        $sort = $this->buildSortCriteria($queryParams);
        if (count($sort) > 0) {
            $criteria['sort'] = $sort;
        }
        return $criteria;
    }

    function buildQueryCriteria($filters, $queryParams = null): array
    {
        $from = ($queryParams ? ($queryParams->getGet('from') ?? null) : null);
        $to = ($queryParams ? ($queryParams->getGet('to') ?? null) : null);
        $query = [];
        foreach ($filters as $key => $fields) {
            $request = array();
            foreach ($fields as $field => $value) {
                $request[$field] = ltrim($value) == "" ? "*" : ltrim($value);
            }
            if ($from != null && $to != null) {
                $request[$this->dateFilterField] = fmDOB($from) . '...' . fmDOB($to);
            } else if ($from != null) {
                $request[$this->dateFilterField] = '>' . fmDOB($from);
            } else if ($to != null) {
                $request[$this->dateFilterField] = ' ≤ ' . fmDOB($to);
            }
            $query[] = $request;
        }
        return $query;
    }

    function buildSortCriteria($queryParams): array
    {
        $sort = [];
        $sortAsc = ($queryParams !== null) ? $queryParams->getGet('sortAsc') : '';
        $sortDesc = ($queryParams !== null) ? $queryParams->getGet('sortDesc') : '';
        if (!empty($sortAsc)) {
            $fields = array_filter(array_map('trim', explode(',', $sortAsc)));
            foreach ($fields as $field) {
                $sort[] = [
                    'fieldName' => $this->mapper->map($field),
                    'sortOrder' => 'ascend'
                ];
            }
        }
        if (!empty($sortDesc)) {
            $fields = array_filter(array_map('trim', explode(',', $sortDesc)));
            foreach ($fields as $field) {
                $sort[] = [
                    'fieldName' => $this->mapper->map($field),
                    'sortOrder' => 'descend'
                ];
            }
        }

        return $sort;
    }

    function executeScript(string $scriptName, $string, string $layout = null): array
    {
        $response = $this->fm->ExecuteScript($scriptName, $string, $layout);

        if (empty($response['response'])) {
            $error = 1;
            $scriptResult = $response;
        } else {
            $response = $response['response'];
            $error = $response['scriptError'] == 0 ? json_decode($response['scriptResult'])->error : $response['scriptError'];
            $scriptResult = array_key_exists('scriptResult', $response) ? json_decode($response['scriptResult']) : null;
        }
        return [
            "error" => $error,
            'data' => $scriptResult
        ];
    }

    function map($result)
    {
        if ($this->error($result) != 0) {
            $data['messages'] = $result['messages'];
            $data['layout'] = $this->layout;
            return ['error' => count($result['messages']), 'data' => $data];
        }
        $count = sizeof($result['response']['data']);
        $public = [];
        $protected = [];
        for ($i = 0; $i < $count; $i++) {
            $entity = $this->mapper->toEntity($result['response']['data'][$i]);
            $public[] = $entity['public'];
            $protected[] = $entity['protected'];
        }
        return ['error' => 0, 'data' => $public, 'source' => $protected, 'total' => $result['response']['dataInfo']['foundCount']];
    }

    function error($result)
    {
        return $result['messages'][0]['code'];
    }
}
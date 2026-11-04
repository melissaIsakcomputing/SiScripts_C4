<?php

namespace App\Mapper;

class Mapper
{
    protected $type = '';

    public bool $requireMapping = true;

    protected $contexts = [
        'default' => '',
    ];

    protected array $mappings = [
        'id' => 'default:__pk_ID'
    ];

    protected array $portals = [];

    protected array $decorators = [];

    protected array $scopes = ['protected' => []];

    protected array $searchable = [];

    protected array $mappers = [];

    function __construct($contexts = [], $type = 'default')
    {
        $this->type = $type;
        if (!empty($contexts)) {
            $this->contexts = $contexts;
        }
    }

    public function toEntity($data, $contexts = []): array
    {
        if (count($contexts) == 0) {
            $contexts = $this->contexts;
        }
        return $this->entity($data, $contexts);
    }

    public function toFM($data): array
    {
        if (!$this->requireMapping) {
            return (array)$data;
        }
        $entity = [];
        $flattenMappings = flattenArray($this->mappings);
        foreach ($flattenMappings as $map => $mappedField) {
            $decorator = array_key_exists($map, $this->decorators) ? $this->decorators[$map]['toFM'] : null;
            $fields = $this->map($map);
//            $fields = map($map, $this->mappings, $this->contexts);
            foreach (explode('|', $fields) as $field) {
                $value = nestedGet((array)$data, $map);
                setIfExists($entity, $field, $value, $decorator);
            }
        }
        return $entity;
    }

    function filters($tokenSettings, $data = []): array
    {
        $filters = $this->getAuthBelongsTo($tokenSettings, true);
        $flattenData = flattenArray($data);
        foreach ($flattenData as $field => $value) {
            $decorator = array_key_exists($field, $this->decorators) && array_key_exists('toFM', $this->decorators[$field]) ? $this->decorators[$field]['toFM'] : null;
            $value = ($decorator != null) ? $decorator($value) : $value;
            $maps = $this->map($field, true);
            /* _fk_Institution_ID|prescription:_fk_InstitutionID */
            $filters = buildOrFilters($filters, $maps, $value);
        }
        if (is_callable([$this, 'applyCustomRules'])) {
            $customRules = $this->applyCustomRules($tokenSettings, $data);
            $filters = recursiveMerge($filters, $customRules);
        }
        if (count($filters) == 0) {
            $map = $this->map('id');
            if ($map != null) {
                $filter[$map] = "*";
                $filters[] = $filter;
            }
        }
        return $filters;
    }

    function getAuthBelongsTo($data, $searching = false): array
    {
        $filters = [];
        if ($searching && ($this->type == 'patient')) {
            return $filters;
        }
        $mappings = $this->mappings;
        if ($searching) {
            $mappings = array_merge($mappings, $this->searchable);
        }
        if (array_key_exists('data', $data)
            && array_key_exists('belongsToType', $data['data'])
            && array_key_exists($data['data']['belongsToType'], $mappings)) {
            $field = $data['data']['belongsToType'] . '.id';
            $decorator = array_key_exists($field, $this->decorators) && array_key_exists('toFM', $this->decorators[$field]) ? $this->decorators[$field]['toFM'] : null;
            $maps = $this->map($field, $searching);
            $value = ($decorator != null) ? $decorator($data['data']['belongsTo']) : $data['data']['belongsTo'];
            $filters = buildOrFilters($filters, $maps, $value);
        }
        return $filters;
    }

    function isTokenOwnerMatch($data, $id, $key = 'id'): bool
    {
        if (!array_key_exists('data', $data)
            || !array_key_exists('belongsToType', $data['data'])
            || $this->type != $data['data']['belongsToType']) {
            return true;
        }
        //If the search is not by ID, this check is unnecessary since belongsTo is handled in the filters.
        if ($this->map($key) == $this->map($data['data']['belongsToType'] . '.id')) {
            if (is_array($id)) {
                return $data['data']['belongsTo'] == $id['id'];
            }
            return $data['data']['belongsTo'] == $id;
        }
        return false;
    }

    function isRelatedTokenOwnerMatch($data, $id, $key = 'id'): bool
    {
        if ($this->map($key) == $this->map($data['data']['belongsToType'] . '.id')) {
            return $data['data']['belongsTo'] == $id;
        }
        return true;
    }

    function map($field, $filtering = false)
    {
        if ($filtering && nestedGet($this->searchable, $field) != null) {
            return map($field, $this->searchable, $this->contexts);
        }
        if (nestedGet($this->mappings, $field) != null) {
            return map($field, $this->mappings, $this->contexts);
        }
        if (strpos($field, '.') === false) {
            return $field;
        }
        [$mapperKey, $mapperValue] = explode('.', $field, 2);
        if (!array_key_exists($mapperKey, $this->mappers)) {
            return $field;
        }
        $mapper = $this->mappers[$mapperKey];
        if ($filtering && nestedGet($mapper->searchable, $mapperValue) != null) {
            return map($mapperValue, $mapper->searchable, $this->contexts[$mapperKey]);
        }
        if (nestedGet($mapper->mappings, $mapperValue) != null) {
            return map($mapperValue, $mapper->mappings, $this->contexts[$mapperKey]);
        }
        return $field;
    }

    function entity($source, $contexts = []): array
    {
        $fields = $source['fieldData'] ?? $source;
        $public = [];
        $protected = [];
        $flattenMappings = flattenArray($this->mappings);
        foreach ($flattenMappings as $map => $mappedField) {
            $mapFields = map($map, $this->mappings, $contexts);
            $decorator = array_key_exists($map, $this->decorators) ? $this->decorators[$map]['toJson'] : null;
            $value = '';
            foreach (explode('|', $mapFields) as $field) {
                if (array_key_exists($field, $fields)) {
                    if ($value != '') {
                        $value .= ' ';
                    }
                    $value .= $fields[$field];
                }
            }
            setIfExists($protected, $map, $value, $decorator, $fields);
            if (!in_array($map, $this->scopes['protected'])) {
                setIfExists($public, $map, $value, $decorator, $fields);
            }
        }
        foreach ($this->mappers as $key => $mapper) {
            if (array_key_exists($key, $contexts)) {
                $entity = $mapper->toEntity($source, $contexts[$key]);
                $protectedRelated = $entity['public']; /*$mapper->toSourceJson($source, $contexts[$key]);*/
                $publicRelated = $entity['protected']; /*$mapper->toJson($source, $contexts[$key]);*/
                if (count($protectedRelated) > 0) {
                    $protected[$key] = $protectedRelated;
                }
                if (count($publicRelated) > 0) {
                    $public[$key] = $publicRelated;
                }
            }
        }
        $portals = $source['portalData'] ?? [];
        foreach ($this->portals as $portal => $portalData) {
            $items = array_key_exists($portalData['name'], $portals) ? $portals[$portalData['name']] : [];
            $relatedMapper = $portalData['mapper'];
            $relatedMapper = new $relatedMapper();
            $publicItems = [];
            $protectedItems = [];
            foreach ($items as $item) {
                $entity = $relatedMapper->toEntity($item, $contexts[$portal]);
                $publicItems[] = $entity['public'];
                $protectedItems[] = $entity['protected'];
            }
            if (!array_key_exists($portalData['property'], $public) && count($publicItems) > 0) {
                $public[$portalData['property']] = [];
                $protected[$portalData['property']] = [];
            }
            if (count($publicItems) > 0) {
                $public[$portalData['property']] = array_merge($public[$portalData['property']], $publicItems);
                $protected[$portalData['property']] = array_merge($protected[$portalData['property']], $protectedItems);;
            }
        }
        return ['public' => $public, 'protected' => $protected];
    }
}
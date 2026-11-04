<?php

function cleanPhoneNumber($phoneNumber)
{
    $phoneNumber = preg_replace("/[^A-Za-z0-9 ]/", "", $phoneNumber); // CLEAR MASK
    // CLEAR SPACE
    return str_replace(' ', '', $phoneNumber);
}

function cleanDOB($dob)
{
    $dob = date("m/d/Y", strtotime($dob));
    return preg_replace("/[^A-Za-z0-9 ]/", "", $dob); // CLEAR MASK
}

function fmDOB($dob)
{
    $dob = date("m/d/Y", strtotime($dob));
    return $dob;
}

function genderId($gender)
{
    return $gender == 'Male' ? 0 : ($gender == 'Female' ? 1 : 2);
}

function genderName($gender)
{
    if ($gender == '') {
        return $gender;
    }
    return $gender == 0 ? 'Male' : ($gender == 1 ? 'Female' : 'Not Specified');
}

function toString($data)
{
    if (!is_string($data)) {
        return $data;
    }
    return implode(",", $data);
}

function toAllergiesArray($data)
{
    $allergies = array_values(
        array_filter(
            array_map('trim', explode('|', $data)),
            fn($item) => $item !== ''
        )
    );
    return $allergies;
}

function toMedicationsArray($data)
{
    return array_values(
        array_filter(
            array_map(
                'trim',
                preg_split('/[\r\n]+/', $data)
            ),
            fn($item) => $item !== ''
        )
    );
}

function isLocaleURI()
{
    return service('uri')->getSegments()[1] == 'faq';
}

function translate($locale, $json)
{
    return $json->$locale ?? "";
}

function language($json)
{
    if (!isset($json)) {
        return [];
    }
    $keys = array_keys(get_object_vars($json));
    return $keys;
}

function formatDate($date)
{
    if ($date != "") {
        return date("Y-m-d", strtotime($date));
    }
    return $date;
}

function formatTime($date)
{
    return date("H:i:s", strtotime($date));
}

function setIfExists(&$array, $key, $value, $decorator = null, $fields = [])
{
    if ($decorator != null) {
        $value = $decorator($value, $fields);
    }
    if (!empty($value) || $value == "0") { // include zero as valid
        nestedSet($array, $key, $value);
    }
}

function flattenArray(array $array, string $prefix = ''): array
{
    $result = [];
    foreach ($array as $key => $value) {
        $newKey = $prefix ? $prefix . '.' . $key : $key;
        if (is_object($value)) {
            $value = (array)$value;
        }
        if (is_array($value)) {
            $result = array_merge($result, flattenArray($value, $newKey));
        } else {
            $result[$newKey] = $value;
        }
    }

    return $result;
}

function mergeJson($json1, $json2)
{
//    $arr1 = json_decode($json1, true);
//    $arr2 = json_decode($json2, true);

    if ($json1 != null && $json2 != null) {
        $merged = array_merge($json1, $json2);
    } else if ($json1 != null) {
        $merged = $json1;
    } else {
        $merged = $json2;
    }

    return json_encode($merged);
}

function map($field, $mappings, $contexts)
{
    if (count($mappings) <= 0) {
        return $field;
    }
    $result = '';
    $fmFields = nestedGet($mappings, $field);
    foreach (explode('|', $fmFields) as $fmField) {
        $mapping = explode(':', $fmField);
        if (count($mapping) < 2) {
            $contextSegment = 'default';
            [$fmField] = $mapping;
        } else {
            $contextSegment = $mapping[0];
            $fmField = $mapping[1];
        }

        $context = nestedGet($contexts, $contextSegment);
        if ($context != null || $contextSegment == "default") {
            if ($result != '') {
                $result .= '|';
            }
            $result .= $context . $fmField;
        }
    }
    return $result;
}

function nestedSet(&$array, $key, $value)
{
    $nestedKey = explode('.', $key);
    $current = array_shift($nestedKey);
    if (count($nestedKey) === 0) {
        $array[$current] = $value;
        return;
    }
    if (!isset($array[$current]) || !is_array($array[$current])) {
        $array[$current] = [];
    }
    nestedSet($array[$current], implode('.', $nestedKey), $value);
}

function nestedGet($array, $key)
{
    $nestedKey = explode('.', $key);
    if (count($nestedKey) == 1) {
        if (is_array($array)) {
            return array_key_exists($key, $array) ? $array[$key] : null;
        } else if (isset($array->{$key})) {
            return $array->{$key};
        }
        return null;
    }
    if (!array_key_exists($nestedKey[0], $array)) {
        return null;
    }

    return nestedGet((array)$array[$nestedKey[0]], substr($key, strpos($key, '.') + 1));
}

function trim_explode($string): ?array
{
    if (empty($string)) {
        return null;
    }
    return array_map('trim', explode(',', $string));
}

function isCompounding($value): string
{
    return $value != "" ? 'Y' : 'N';
}

function refillStatus($value): string
{
    return $value == 1 ? "Processed" : "Received";
}

function trackingNumber($value)
{
    $shippingTracking = !empty($value)
        ? json_decode($value, true)
        : [];
    return array_key_exists('tracking_number', $shippingTracking)
        ? $shippingTracking['tracking_number'] : '';
}

function trackingURL($value)
{
    $shippingTracking = !empty($value)
        ? json_decode($value, true)
        : [];
    return array_key_exists('tracking_number', $shippingTracking)
        ? $shippingTracking['tracking_url_provider'] : '';
}

function shippingStatus($value): string
{
    $shippingTracking = !empty($value)
        ? json_decode($value, true)
        : [];
    return array_key_exists('tracking_number', $shippingTracking)
    && $shippingTracking['tracking_number'] ? 'Shipped' : '';
}

function shippingOptionType($value): string
{
    $type = '';
    if ($value == 0) {
        $type = "Pick Up";
    } else if ($value == 1) {
        $type = "Shipping";
    } else if ($value == 2) {
        $type = "Courier";
    }
    return $type;
}

function shippingOptionService($value, $item, $mapper)
{
    $service = '';
    if ($value == 0) {
        $service = "Pick Up";
    } else if ($value == 1) {
        $field = $mapper->map('serviceName');
        $service = $item[$field];
    } else if ($value == 2) {
        $field = $mapper->map('courierName');
        $service = "Courier " . $item[$field];
    }
    return $service;
}

function totals($value)
{
    return json_decode($value);

}

function subTotals($value)
{
    $totals = json_decode($value);
    return [
        'items' => ['name' => 'items', 'value' => $totals->items],
        'fees' => ['name' => 'fees', 'value' => $totals->fees],
        'taxes' => ['name' => 'taxes', 'value' => $totals->taxes ?? 0],
        'discount' => ['name' => 'discount', 'value' => $totals->discount]
    ];
}


function recursiveMerge($filters, $customFilters): array
{
    if (count($filters) == 0) {
        return $customFilters;
    }
    if (count($customFilters) == 0) {
        return $filters;
    }
    $result = [];
    foreach ($filters as $filter) {
        $newFilter = $filter;
        foreach ($customFilters as $customFilter) {
            $result[] = array_merge($newFilter, $customFilter);
        }
    }
    return $result;
}

function buildOrFilters($filters, $map, $value): array
{
    $fields = explode('|', $map);
    if (empty($filters)) {
        $filters = [[]];
    }
    $temp = buildCombinations($filters, $fields, $value);
    $result = array_values($temp);
    return clearSuperSets($result);
}

function buildOrFilters1($filters, $map, $value): array
{
    $fields = explode('|', $map);
    if (empty($filters)) {
        $filters = [[]];
    }
    $temp = buildCombinations($filters, $fields, $value);
    $result = array_values($temp);
    return clearSuperSets($result);
}

function buildCombinations($filters, $fields, $value): array
{
    $temp = [];
    // build combinations
    foreach ($filters as $filter) {
        foreach ($fields as $field) {
            $newFilter = $filter;
            if (!isset($newFilter[$field]) || $newFilter[$field] != $value) {
                $newFilter[$field] = $value;
            }
            //avoid duplicates
            ksort($newFilter);
            $key = json_encode($newFilter);
            $temp[$key] = $newFilter;
        }
    }
    return $temp;
}

function clearSuperSets($result): array
{
    // remove supersets
    /* A v B already contains A ^ B */
    $final = [];
    foreach ($result as $i => $filterA) {
        $isSuperset = false;
        foreach ($result as $j => $filterB) {
            if ($i === $j) continue;
            // if A contains all of B AND is bigger → redundant
            if (count($filterA) > count($filterB) &&
                !array_diff_assoc($filterB, $filterA)) {
                $isSuperset = true;
                break;
            }
        }
        if (!$isSuperset) {
            $final[] = $filterA;
        }
    }
    return $final;
}

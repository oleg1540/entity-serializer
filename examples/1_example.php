<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Ovksoft\EntitySerializer\Examples\Item;
use Ovksoft\EntitySerializer\SerializerParams;
use Ovksoft\EntitySerializer\EntitySerializer;
use Ovksoft\EntitySerializer\Examples\Request;

$params = new SerializerParams(
    dateTimeFormat: DATE_RFC3339_EXTENDED,
);
$serializer = new EntitySerializer($params);
/*
 * Or use camel <-> >snake case decorator
 * $serializer = new EntitySerializerCamelToSnake($serializer);
 */

$request = new Request();
$request->limit = 10;
$requestParams = $serializer->serialize($request);
// Do request and get response

$responseData = [];
$item = $serializer->deserialize(Item::class, $responseData);
// Use Item object instead of array

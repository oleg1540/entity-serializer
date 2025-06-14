<?php

declare(strict_types=1);

namespace Ovksoft\EntitySerializer\Tests\unit;

use DateTime;
use Exception;
use Ovksoft\EntitySerializer\EntitySerializerCamelToSnake;
use Ovksoft\EntitySerializer\Examples\Item;
use Ovksoft\EntitySerializer\Examples\SizeEnum;
use Ovksoft\EntitySerializer\Examples\StatusEnum;
use Ovksoft\EntitySerializer\Examples\SubItem;
use PHPUnit\Framework\TestCase;
use ReflectionException;

class EntitySerializerCamelToSnakeTest extends TestCase
{
    /**
     * @throws ReflectionException
     */
    public function testSerialize(): void
    {
        $item = new Item();
        $item->id = 1;
        $item->name = 'test-item';
        $item->nameList = [
            'ti',
            'Test Item',
        ];
        $item->isDeleted = false;
        $item->floatValue = 100.25;
        $item->dateAdded = new DateTime();
        $item->status = StatusEnum::NEW;
        $item->size = SizeEnum::S;
        $item->externalId = null;

        $subItem1 = new SubItem();
        $subItem1->id = 1;
        $subItem1->name = 'test-sub-item';
        $subItem1->isDeleted = false;

        $subItem2 = new SubItem();
        $subItem2->id = 1;
        $subItem2->name = 'test-sub-item-2';
        $subItem2->isDeleted = true;

        $item->subItems = [
            $subItem1,
            $subItem2,
        ];

        $subItem = new SubItem();
        $subItem->id = 99;
        $subItem->name = 'test-sub-item-single';
        $subItem->isDeleted = false;
        $item->subItem = $subItem;

        $serializer = new EntitySerializerCamelToSnake();
        $data = $serializer->serialize($item);

        $this->assertEquals($item->id, $data['id'], 'Wrong id');
        $this->assertEquals($item->name, $data['name'], 'Wrong name');
        $this->assertEquals($item->nameList, $data['name_list'], 'Wrong name list');
        $this->assertEquals($item->isDeleted, $data['is_deleted'], 'Wrong isDeleted');
        $this->assertEquals($item->floatValue, $data['float_value'], 'Wrong floatValue');
        $this->assertEquals($item->dateAdded->format(DATE_RFC3339_EXTENDED), $data['date_added'], 'Wrong dateAdded');
        $this->assertEquals($item->status->name, $data['status'], 'Wrong status');
        $this->assertEquals($item->size->value, $data['size'], 'Wrong size');
        $this->assertEquals($item->externalId, $data['external_id'], 'Wrong externalId');
        $this->assertArrayNotHasKey('uninitialized', $data, 'Wrong uninitialized');

        $this->assertIsArray($data['sub_items'], 'SubItems is not array');
        $this->assertCount(count($item->subItems), $data['sub_items'], 'Wrong subItems number');

        $this->assertIsArray($data['sub_items'][0], 'SubItems[0] is not array');
        $this->assertEquals($item->subItems[0]->id, $data['sub_items'][0]['id'], 'Wrong subItem id');
        $this->assertEquals($item->subItems[0]->name, $data['sub_items'][0]['name'], 'Wrong subItem name');
        $this->assertEquals($item->subItems[0]->isDeleted, $data['sub_items'][0]['is_deleted'], 'Wrong subItem isDeleted');

        $this->assertIsArray($data['sub_items'][1], 'SubItems[1] is not array');
        $this->assertEquals($item->subItems[1]->id, $data['sub_items'][1]['id'], 'Wrong subItem id');
        $this->assertEquals($item->subItems[1]->name, $data['sub_items'][1]['name'], 'Wrong subItem name');
        $this->assertEquals($item->subItems[1]->isDeleted, $data['sub_items'][1]['is_deleted'], 'Wrong subItem isDeleted');

        $this->assertIsArray($data['sub_item'], 'SubItem is not array');
        $this->assertEquals($item->subItem->id, $data['sub_item']['id'], 'Wrong subItem id');
        $this->assertEquals($item->subItem->name, $data['sub_item']['name'], 'Wrong subItem name');
        $this->assertEquals($item->subItem->isDeleted, $data['sub_item']['is_deleted'], 'Wrong subItem isDeleted');
    }

    /**
     * @throws Exception
     */
    public function testDeserialize(): void
    {

        $data = [
            'id' => 1,
            'name' => 'test-item',
            'name_list' => [
                'ti',
                'Test Item',
            ],
            'is_deleted' => false,
            'float_value' => 100.25,
            'date_added' => '2025-04-19T10:46:31.158+00:00',
            'status' => StatusEnum::NEW->name,
            'size' => SizeEnum::S->value,
            'external_id' => null,
            'sub_items' => [
                [
                    'id' => 1,
                    'name' => 'test-sub-item',
                    'is_deleted' => false,
                ],
                [
                    'id' => 2,
                    'name' => 'test-sub-item-2',
                    'is_deleted' => true,
                ],
            ],
            'sub_item' => [
                'id' => 99,
                'name' => 'test-sub-item-single',
                'is_deleted' => false,
            ],
        ];

        $serializer = new EntitySerializerCamelToSnake();
        $item = $serializer->deserialize(Item::class, $data);

        $this->assertEquals($data['id'], $item->id, 'Wrong id');
        $this->assertEquals($data['name'], $item->name, 'Wrong name');
        $this->assertEquals($data['name_list'], $item->nameList, 'Wrong name list');
        $this->assertEquals($data['is_deleted'], $item->isDeleted, 'Wrong isDeleted');
        $this->assertEquals($data['float_value'], $item->floatValue, 'Wrong floatValue');
        $this->assertEquals($data['date_added'], $item->dateAdded->format(DATE_RFC3339_EXTENDED), 'Wrong dateAdded');
        $this->assertEquals($data['status'], $item->status->name, 'Wrong status');
        $this->assertEquals($data['size'], $item->size->value, 'Wrong size');
        $this->assertEquals($data['external_id'], $item->externalId, 'Wrong externalId');
        $this->assertTrue(!isset($item->uninitialized), 'Wrong uninitialized');

        $this->assertCount(count($data['sub_items']), $item->subItems, 'Wrong subItems number');

        $this->assertEquals($data['sub_items'][0]['id'], $item->subItems[0]->id, 'Wrong subItem id');
        $this->assertEquals($data['sub_items'][0]['name'], $item->subItems[0]->name, 'Wrong subItem name');
        $this->assertEquals($data['sub_items'][0]['is_deleted'], $item->subItems[0]->isDeleted, 'Wrong subItem isDeleted');

        $this->assertEquals($data['sub_items'][1]['id'], $item->subItems[1]->id, 'Wrong subItem id');
        $this->assertEquals($data['sub_items'][1]['name'], $item->subItems[1]->name, 'Wrong subItem name');
        $this->assertEquals($data['sub_items'][1]['is_deleted'], $item->subItems[1]->isDeleted, 'Wrong subItem isDeleted');

        $this->assertEquals($data['sub_item']['id'], $item->subItem->id, 'Wrong subItem id');
        $this->assertEquals($data['sub_item']['name'], $item->subItem->name, 'Wrong subItem name');
        $this->assertEquals($data['sub_item']['is_deleted'], $item->subItem->isDeleted, 'Wrong subItem isDeleted');
    }
}

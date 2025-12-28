<?php

declare(strict_types=1);

namespace Ovksoft\EntitySerializer\Tests\unit;

use DateTime;
use Exception;
use Ovksoft\EntitySerializer\EntitySerializer;
use Ovksoft\EntitySerializer\Examples\Item;
use Ovksoft\EntitySerializer\Examples\SizeEnum;
use Ovksoft\EntitySerializer\Examples\StatusEnum;
use Ovksoft\EntitySerializer\Examples\SubItem;
use PHPUnit\Framework\TestCase;
use ReflectionException;

class EntitySerializerTest extends TestCase
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
        $item->mixedValue = 1;

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

        $serializer = new EntitySerializer();
        $data = $serializer->serialize($item);

        $this->assertEquals($item->id, $data['id'], 'Wrong id');
        $this->assertEquals($item->name, $data['name'], 'Wrong name');
        $this->assertEquals($item->nameList, $data['nameList'], 'Wrong name list');
        $this->assertEquals($item->isDeleted, $data['isDeleted'], 'Wrong isDeleted');
        $this->assertEquals($item->floatValue, $data['floatValue'], 'Wrong floatValue');
        $this->assertEquals($item->dateAdded->format(DATE_RFC3339_EXTENDED), $data['dateAdded'], 'Wrong dateAdded');
        $this->assertEquals($item->status->name, $data['status'], 'Wrong status');
        $this->assertEquals($item->size->value, $data['size'], 'Wrong size');
        $this->assertEquals($item->externalId, $data['externalId'], 'Wrong externalId');
        $this->assertArrayNotHasKey('uninitialized', $data, 'Wrong uninitialized');
        $this->assertEquals($item->mixedValue, $data['mixedValue'], 'Wrong mixedValue');

        $this->assertIsArray($data['subItems'], 'SubItems is not array');
        $this->assertCount(count($item->subItems), $data['subItems'], 'Wrong subItems number');

        $this->assertIsArray($data['subItems'][0], 'SubItems[0] is not array');
        $this->assertEquals($item->subItems[0]->id, $data['subItems'][0]['id'], 'Wrong subItem id');
        $this->assertEquals($item->subItems[0]->name, $data['subItems'][0]['name'], 'Wrong subItem name');
        $this->assertEquals($item->subItems[0]->isDeleted, $data['subItems'][0]['isDeleted'], 'Wrong subItem isDeleted');

        $this->assertIsArray($data['subItems'][1], 'SubItems[1] is not array');
        $this->assertEquals($item->subItems[1]->id, $data['subItems'][1]['id'], 'Wrong subItem id');
        $this->assertEquals($item->subItems[1]->name, $data['subItems'][1]['name'], 'Wrong subItem name');
        $this->assertEquals($item->subItems[1]->isDeleted, $data['subItems'][1]['isDeleted'], 'Wrong subItem isDeleted');

        $this->assertIsArray($data['subItem'], 'SubItem is not array');
        $this->assertEquals($item->subItem->id, $data['subItem']['id'], 'Wrong subItem id');
        $this->assertEquals($item->subItem->name, $data['subItem']['name'], 'Wrong subItem name');
        $this->assertEquals($item->subItem->isDeleted, $data['subItem']['isDeleted'], 'Wrong subItem isDeleted');
    }

    /**
     * @throws Exception
     */
    public function testDeserialize(): void
    {

        $data = [
            'id' => 1,
            'name' => 'test-item',
            'nameList' => [
                'ti',
                'Test Item',
            ],
            'isDeleted' => false,
            'floatValue' => 100.25,
            'dateAdded' => '2025-04-19T10:46:31.158+00:00',
            'status' => StatusEnum::NEW->name,
            'size' => SizeEnum::S->value,
            'usSize' => '',
            'externalId' => null,
            'mixedValue' => 1.123,
            'subItems' => [
                [
                    'id' => 1,
                    'name' => 'test-sub-item',
                    'isDeleted' => false,
                ],
                [
                    'id' => 2,
                    'name' => 'test-sub-item-2',
                    'isDeleted' => true,
                ],
            ],
            'subItem' => [
                'id' => 99,
                'name' => 'test-sub-item-single',
                'isDeleted' => false,
            ],
        ];

        $serializer = new EntitySerializer();
        $item = $serializer->deserialize(Item::class, $data);

        $this->assertEquals($data['id'], $item->id, 'Wrong id');
        $this->assertEquals($data['name'], $item->name, 'Wrong name');
        $this->assertEquals($data['nameList'], $item->nameList, 'Wrong name list');
        $this->assertEquals($data['isDeleted'], $item->isDeleted, 'Wrong isDeleted');
        $this->assertEquals($data['floatValue'], $item->floatValue, 'Wrong floatValue');
        $this->assertEquals($data['dateAdded'], $item->dateAdded->format(DATE_RFC3339_EXTENDED), 'Wrong dateAdded');
        $this->assertEquals($data['status'], $item->status->name, 'Wrong status');
        $this->assertEquals($data['size'], $item->size->value, 'Wrong size');
        $this->assertEquals($data['externalId'], $item->externalId, 'Wrong externalId');
        $this->assertTrue(!isset($item->uninitialized), 'Wrong uninitialized');
        $this->assertTrue(!isset($item->usSize), 'Wrong usSize');
        $this->assertEquals($data['mixedValue'], $item->mixedValue, 'Wrong mixedValue');

        $this->assertCount(count($data['subItems']), $item->subItems, 'Wrong subItems number');

        $this->assertEquals($data['subItems'][0]['id'], $item->subItems[0]->id, 'Wrong subItem id');
        $this->assertEquals($data['subItems'][0]['name'], $item->subItems[0]->name, 'Wrong subItem name');
        $this->assertEquals($data['subItems'][0]['isDeleted'], $item->subItems[0]->isDeleted, 'Wrong subItem isDeleted');

        $this->assertEquals($data['subItems'][1]['id'], $item->subItems[1]->id, 'Wrong subItem id');
        $this->assertEquals($data['subItems'][1]['name'], $item->subItems[1]->name, 'Wrong subItem name');
        $this->assertEquals($data['subItems'][1]['isDeleted'], $item->subItems[1]->isDeleted, 'Wrong subItem isDeleted');

        $this->assertEquals($data['subItem']['id'], $item->subItem->id, 'Wrong subItem id');
        $this->assertEquals($data['subItem']['name'], $item->subItem->name, 'Wrong subItem name');
        $this->assertEquals($data['subItem']['isDeleted'], $item->subItem->isDeleted, 'Wrong subItem isDeleted');
    }
}

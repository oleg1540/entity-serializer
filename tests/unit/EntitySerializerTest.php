<?php

declare(strict_types=1);

namespace Ovksoft\EntitySerializer\Tests\unit;

use DateTime;
use Ovksoft\EntitySerializer\EntitySerializer;
use Ovksoft\EntitySerializer\Tests\mock\Item;
use Ovksoft\EntitySerializer\Tests\mock\SizeEnum;
use Ovksoft\EntitySerializer\Tests\mock\StatusEnum;
use Ovksoft\EntitySerializer\Tests\mock\SubItem;
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
        $item->isDeleted = false;
        $item->floatValue = 100.25;
        $item->dateAdded = new DateTime();
        $item->status = StatusEnum::NEW;
        $item->size = SizeEnum::S;
        $item->externalId = null;

        $subItem = new SubItem();
        $subItem->id = 1;
        $subItem->name = 'test-sub-item';

        $subItem2 = new SubItem();
        $subItem2->id = 1;
        $subItem2->name = 'test-sub-item-2';

        $item->subItems = [
            $subItem,
            $subItem2,
        ];

        $serializer = new EntitySerializer(Item::class);
        $data = $serializer->serialize($item);

        $this->assertEquals($item->id, $data['id'], 'Wrong id');
        $this->assertEquals($item->name, $data['name'], 'Wrong name');
        $this->assertEquals($item->isDeleted, $data['isDeleted'], 'Wrong isDeleted');
        $this->assertEquals($item->floatValue, $data['floatValue'], 'Wrong floatValue');
        $this->assertEquals($item->dateAdded->format(DATE_RFC3339_EXTENDED), $data['dateAdded'], 'Wrong dateAdded');
        $this->assertEquals($item->status->name, $data['status'], 'Wrong status');
        $this->assertEquals($item->size->value, $data['size'], 'Wrong size');
        $this->assertEquals($item->externalId, $data['externalId'], 'Wrong externalId');
        $this->assertArrayNotHasKey('uninitialized', $data, 'Wrong uninitialized');

        $this->assertCount(count($item->subItems), $data['subItems'], 'Wrong subItems number');

        $this->assertEquals($item->subItems[0]->id, $data['subItems'][0]['id'], 'Wrong subItem id');
        $this->assertEquals($item->subItems[0]->name, $data['subItems'][0]['name'], 'Wrong subItem name');

        $this->assertEquals($item->subItems[1]->id, $data['subItems'][1]['id'], 'Wrong subItem id');
        $this->assertEquals($item->subItems[1]->name, $data['subItems'][1]['name'], 'Wrong subItem name');
    }
}

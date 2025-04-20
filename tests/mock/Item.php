<?php

declare(strict_types=1);

namespace Ovksoft\EntitySerializer\Tests\mock;

use DateTime;
use Ovksoft\EntitySerializer\EntitySerializableInterface;
use Ovksoft\EntitySerializer\SerializeAs;

class Item implements EntitySerializableInterface
{
    public int $id;
    public string $name;

    /** @var string[] $nameList */
    public array $nameList = [];
    public bool $isDeleted;
    public float $floatValue;
    public DateTime $dateAdded;
    public StatusEnum $status;
    public SizeEnum $size;
    public ?string $externalId = null;
    public int $uninitialized;

    /** @var SubItem[] $subItems */
    #[SerializeAs(SubItem::class)]
    public array $subItems;
}

<?php

declare(strict_types=1);

namespace Ovksoft\EntitySerializer\Tests\mock;

use Ovksoft\EntitySerializer\EntitySerializableInterface;

class SubItem implements EntitySerializableInterface
{
    public int $id;
    public string $name;
}

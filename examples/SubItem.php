<?php

declare(strict_types=1);

namespace Ovksoft\EntitySerializer\Examples;

use Ovksoft\EntitySerializer\EntitySerializableInterface;

class SubItem implements EntitySerializableInterface
{
    public int $id;
    public string $name;
}

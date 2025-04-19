<?php

declare(strict_types=1);

namespace Ovksoft\EntitySerializer;

use Attribute;

#[Attribute]
class SerializeAs
{
    /**
     * @param class-string<EntitySerializableInterface> $className
     */
    public function __construct(string $className)
    {
    }
}

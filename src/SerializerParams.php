<?php

declare(strict_types=1);

namespace Ovksoft\EntitySerializer;

final class SerializerParams
{
    public function __construct(
        public readonly string $dateTimeFormat = DATE_RFC3339_EXTENDED,
    )
    {}
}
